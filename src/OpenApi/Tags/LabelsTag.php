<?php

namespace AfroTechnology\Waha\OpenApi\Tags;

use AfroTechnology\Waha\OpenApi\WahaTagProxy;

/**
 * AUTO-GENERATED IDE HELPER (DO NOT EDIT MANUALLY)
 * Tag: 🏷️ Labels
 *
 * @method array getAll(?string $session = null)
 * @method \AfroTechnology\Waha\Generated\Model\Label create(string $name, ?string $colorHex = null, ?float $color = null, ?string $session = null)
 * @method \AfroTechnology\Waha\Generated\Model\Label update(string $labelId, string $name, ?string $colorHex = null, ?float $color = null, ?string $session = null)
 * @method array delete(string $labelId, ?string $session = null)
 * @method array getChatLabels(string $chatId, ?string $session = null)
 * @method mixed putChatLabels(string $chatId, array $labels, ?string $session = null)
 * @method mixed getChatsByLabel(string $labelId, ?string $session = null)
 */
final class LabelsTag extends WahaTagProxy {}
