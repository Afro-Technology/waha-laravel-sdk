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

$url = envString('WAHA_OPENAPI_URL', 'https://waha.devlike.pro/swagger/openapi.json');
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

logLine("Fetching spec into temp: {$tmp}");
(new FetchOpenApiSpecAction)->execute($url, $tmp, $basicAuth, $fetchTimeout);

$oldHash = sha256File($specPath);
$newHash = sha256File($tmp);

if ($newHash === '' || ! is_file($tmp)) {
    fwrite(STDERR, "Failed to fetch spec.\n");
    exit(1);
}

if ($oldHash !== '' && $oldHash === $newHash) {
    logLine('Spec unchanged. Nothing to do.');
    @unlink($tmp);
    exit(0);
}

logLine('Spec changed. Updating repository spec + generating client + ide-helper...');

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

(new GenerateOpenApiClientAction)->execute($specPath, $outDir, $generatorOptions, $logger);
(new GenerateIdeHelperAction)->execute($specPath, PackagePath::root());

logLine('Done.');
