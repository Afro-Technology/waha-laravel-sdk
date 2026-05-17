<?php

namespace AfroTechnology\Waha\OpenApi\Tags;

use AfroTechnology\Waha\OpenApi\WahaTagProxy;

/**
 * AUTO-GENERATED IDE HELPER (DO NOT EDIT MANUALLY)
 * Tag: 📢 Channels
 *
 * @method array list(?string $role = null, ?string $session = null)
 * @method \AfroTechnology\Waha\Generated\Model\Channel create(string $name, ?string $description = null, mixed $picture = null, ?string $session = null)
 * @method mixed delete(mixed $id, ?string $session = null)
 * @method \AfroTechnology\Waha\Generated\Model\Channel get(mixed $id, ?string $session = null)
 * @method array previewChannelMessages(mixed $id, ?bool $downloadMedia = false, ?float $limit = 10, ?string $session = null)
 * @method mixed follow(mixed $id, ?string $session = null)
 * @method mixed unfollow(mixed $id, ?string $session = null)
 * @method mixed mute(mixed $id, ?string $session = null)
 * @method mixed unmute(mixed $id, ?string $session = null)
 * @method \AfroTechnology\Waha\Generated\Model\ChannelListResult searchByView(string $view, array $countries, array $categories, float $limit, string $startCursor, ?string $session = null)
 * @method \AfroTechnology\Waha\Generated\Model\ChannelListResult searchByText(string $text, array $categories, float $limit, string $startCursor, ?string $session = null)
 * @method array getSearchViews(?string $session = null)
 * @method array getSearchCountries(?string $session = null)
 * @method array getSearchCategories(?string $session = null)
 */
final class ChannelsTag extends WahaTagProxy {}
