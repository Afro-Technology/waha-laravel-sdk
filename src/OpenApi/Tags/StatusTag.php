<?php

namespace Vendor\Waha\OpenApi\Tags;

use Vendor\Waha\OpenApi\WahaTagProxy;

/**
 * AUTO-GENERATED IDE HELPER (DO NOT EDIT MANUALLY)
 * Tag: 🟢 Status
 *
 * @method mixed sendTextStatus(string $text, string $backgroundColor, float $font, ?string $id = null, ?array $contacts = null, ?bool $linkPreview = true, ?bool $linkPreviewHighQuality = false, ?string $session = null)
 * @method mixed sendImageStatus(mixed $file, ?string $id = null, ?array $contacts = null, ?string $caption = null, ?string $session = null)
 * @method mixed sendVoiceStatus(mixed $file, bool $convert, string $backgroundColor, ?string $id = null, ?array $contacts = null, ?string $session = null)
 * @method mixed sendVideoStatus(mixed $file, bool $convert, ?string $id = null, ?array $contacts = null, ?string $caption = null, ?string $session = null)
 * @method mixed deleteStatus(?string $id = null, ?array $contacts = null, ?string $session = null)
 * @method \Vendor\Waha\Generated\Model\NewMessageIDResponse getNewMessageId(?string $session = null)
 */
final class StatusTag extends WahaTagProxy
{
}
