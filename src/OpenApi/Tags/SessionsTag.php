<?php

namespace Vendor\Waha\OpenApi\Tags;

use Vendor\Waha\OpenApi\WahaTagProxy;

/**
 * AUTO-GENERATED IDE HELPER (DO NOT EDIT MANUALLY)
 * Tag: 🖥️ Sessions
 *
 * @method array list(?array $expand = null, ?bool $all = null)
 * @method \Vendor\Waha\Generated\Model\SessionDTO create(?string $name = null, ?array $apps = null, ?bool $start = true, ?\Vendor\Waha\Generated\Model\SessionConfig $config = null)
 * @method \Vendor\Waha\Generated\Model\SessionInfo get(?array $expand = null, ?string $session = null)
 * @method \Vendor\Waha\Generated\Model\SessionDTO update(?array $apps = null, ?\Vendor\Waha\Generated\Model\SessionConfig $config = null, ?string $session = null)
 * @method mixed delete(?string $session = null)
 * @method \Vendor\Waha\Generated\Model\MeInfo getMe(?string $session = null)
 * @method \Vendor\Waha\Generated\Model\SessionDTO start(?string $session = null)
 * @method \Vendor\Waha\Generated\Model\SessionDTO stop(?string $session = null)
 * @method \Vendor\Waha\Generated\Model\SessionDTO logout(?string $session = null)
 * @method \Vendor\Waha\Generated\Model\SessionDTO restart(?string $session = null)
 * @method \Vendor\Waha\Generated\Model\SessionDTO start(string $name, ?\Vendor\Waha\Generated\Model\SessionConfig $config = null)
 * @method mixed stop(string $name, ?bool $logout = false)
 * @method mixed logout(string $name)
 */
final class SessionsTag extends WahaTagProxy {}
