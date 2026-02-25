<?php

namespace Vendor\Waha\OpenApi\Tags;

use Vendor\Waha\OpenApi\WahaTagProxy;

/**
 * AUTO-GENERATED IDE HELPER (DO NOT EDIT MANUALLY)
 * Tag: 💬 Chats
 *
 * @method mixed getChats(?string $sortBy = null, ?string $sortOrder = null, ?float $limit = null, ?float $offset = null, ?string $session = null)
 * @method array getChatsOverview(?float $limit = 20, ?float $offset = null, ?array $ids = null, ?string $session = null)
 * @method array postChatsOverview(\Vendor\Waha\Generated\Model\OverviewPaginationParams $pagination, \Vendor\Waha\Generated\Model\OverviewFilter $filter, ?string $session = null)
 * @method mixed deleteChat(string $chatId, ?string $session = null)
 * @method \Vendor\Waha\Generated\Model\ChatPictureResponse getChatPicture(string $chatId, ?bool $refresh = false, ?string $session = null)
 * @method array getChatMessages(string $chatId, ?string $sortBy = 'timestamp', ?string $sortOrder = null, ?bool $downloadMedia = true, ?float $limit = 10, ?float $offset = null, ?float $filter_timestamp_lte = null, ?float $filter_timestamp_gte = null, ?bool $filter_fromMe = null, ?string $filter_ack = null, ?string $session = null)
 * @method mixed clearMessages(string $chatId, ?string $session = null)
 * @method \Vendor\Waha\Generated\Model\ReadChatMessagesResponse readChatMessages(string $chatId, ?float $messages = null, ?float $days = 7, ?string $session = null)
 * @method \Vendor\Waha\Generated\Model\WAMessage getChatMessage(string $chatId, string $messageId, ?bool $downloadMedia = true, ?string $session = null)
 * @method mixed deleteMessage(string $chatId, string $messageId, ?string $session = null)
 * @method mixed editMessage(string $chatId, string $messageId, string $text, ?bool $linkPreview = true, ?bool $linkPreviewHighQuality = false, ?string $session = null)
 * @method mixed pinMessage(string $chatId, string $messageId, float $duration, ?string $session = null)
 * @method mixed unpinMessage(string $chatId, string $messageId, ?string $session = null)
 * @method array archiveChat(string $chatId, ?string $session = null)
 * @method array unarchiveChat(string $chatId, ?string $session = null)
 * @method array unreadChat(string $chatId, ?string $session = null)
 */
final class ChatsTag extends WahaTagProxy
{
}
