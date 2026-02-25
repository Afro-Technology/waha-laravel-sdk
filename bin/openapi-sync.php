<?php

declare(strict_types=1);

use Vendor\Waha\Actions\FetchOpenApiSpecAction;
use Vendor\Waha\Actions\GenerateIdeHelperAction;
use Vendor\Waha\Actions\GenerateOpenApiClientAction;
use Vendor\Waha\Support\PackagePath;

require __DIR__.'/../vendor/autoload.php';

function envString(string $key, string $default = ''): string
{
    $v = getenv($key);
    if ($v === false) {
        return $default;
    }

    return (string) $v;
}

function sha256File(string $path): string
{
    if (! is_file($path)) {
        return '';
    }
    $h = hash_file('sha256', $path);

    return is_string($h) ? $h : '';
}

function logLine(string $msg): void
{
    fwrite(STDOUT, $msg."\n");
}

/**
 * Decide whether the given input is a remote URL or a local path.
 * Supports:
 * - https://...
 * - http://...
 * - file:///absolute/path.json
 * - relative/path.json
 * - /absolute/path.json
 */
function isRemoteUrl(string $input): bool
{
    return str_starts_with($input, 'http://') || str_starts_with($input, 'https://');
}

function stripFileScheme(string $input): string
{
    if (str_starts_with($input, 'file://')) {
        // file:///path -> /path (or file://C:/path on Windows)
        return substr($input, 7);
    }

    return $input;
}

$urlOrPath = envString('WAHA_OPENAPI_URL', 'https://waha.devlike.pro/swagger/openapi.json');
$specPath = envString('WAHA_SPEC_PATH', PackagePath::path('resources/openapi/openapi.json'));
$outDir = envString('WAHA_GENERATED_PATH', PackagePath::path('src/Generated'));

$basicAuth = [
    'username' => envString('WAHA_SWAGGER_USERNAME', ''),
    'password' => envString('WAHA_SWAGGER_PASSWORD', ''),
];

$fetchTimeout = (int) envString('WAHA_OPENAPI_TIMEOUT', '30');
$genTimeout = (int) envString('WAHA_OPENAPI_GENERATOR_TIMEOUT', '300');

$generatorOptions = [
    'driver' => envString('WAHA_OPENAPI_GENERATOR_DRIVER', 'auto'),
    'docker_image' => envString('WAHA_OPENAPI_GENERATOR_IMAGE', 'openapitools/openapi-generator-cli:v7.6.0'),
    'jar' => envString('WAHA_OPENAPI_GENERATOR_JAR', ''),
    'binary' => envString('WAHA_OPENAPI_GENERATOR_BINARY', 'openapi-generator-cli'),
    'timeout_seconds' => $genTimeout,
];

$tmp = sys_get_temp_dir().DIRECTORY_SEPARATOR.'waha-openapi-'.uniqid('', true).'.json';

/**
 * Step 1: Obtain the spec into a temp file.
 * - If WAHA_OPENAPI_URL is http(s), fetch it into $tmp.
 * - Otherwise treat it as a local file path and copy it into $tmp.
 */
if (isRemoteUrl($urlOrPath)) {
    logLine("Fetching spec URL into temp: {$tmp}");
    (new FetchOpenApiSpecAction)->execute($urlOrPath, $tmp, $basicAuth, $fetchTimeout);
} else {
    $localPath = stripFileScheme($urlOrPath);

    logLine("Using local spec file: {$localPath}");
    if (! is_file($localPath)) {
        fwrite(STDERR, "Local spec file not found: {$localPath}\n");
        exit(1);
    }

    if (@copy($localPath, $tmp) === false) {
        fwrite(STDERR, "Failed to copy local spec to temp: {$tmp}\n");
        exit(1);
    }
}

$oldHash = sha256File($specPath);
$newHash = sha256File($tmp);

if ($newHash === '' || ! is_file($tmp)) {
    fwrite(STDERR, "Failed to obtain spec.\n");
    exit(1);
}

if ($oldHash !== '' && $oldHash === $newHash) {
    logLine('Spec unchanged. Nothing to do.');
    @unlink($tmp);
    exit(0);
}

logLine('Spec changed. Updating repository spec + generating client + ide-helper...');

/**
 * Step 2: Copy temp spec into repository spec location.
 */
$dir = dirname($specPath);
if (! is_dir($dir)) {
    @mkdir($dir, 0777, true);
}
if (@copy($tmp, $specPath) === false) {
    fwrite(STDERR, "Failed to write spec to: {$specPath}\n");
    @unlink($tmp);
    exit(1);
}
@unlink($tmp);

$logger = static function (string $msg): void {
    fwrite(STDOUT, $msg);
};

/**
 * Step 3: Generate client + IDE helper from the repository spec path.
 */
(new GenerateOpenApiClientAction)->execute($specPath, $outDir, $generatorOptions, $logger);
(new GenerateIdeHelperAction)->execute($specPath, PackagePath::root());

logLine('Done.');
