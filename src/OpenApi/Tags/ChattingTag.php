<?php

namespace Vendor\Waha\OpenApi\Tags;

use Vendor\Waha\OpenApi\WahaTagProxy;

/**
 * AUTO-GENERATED IDE HELPER (DO NOT EDIT MANUALLY)
 * Tag: 📤 Chatting
 *
 * @method \Vendor\Waha\Generated\Model\WAMessage sendText(string $chatId, string $text, ?string $reply_to = null, ?bool $linkPreview = true, ?bool $linkPreviewHighQuality = false, ?string $session = null)
 * @method array sendTextGet(?string $phone = null, ?string $text = null, ?string $session = null)
 * @method array sendImage(string $chatId, mixed $file, ?string $reply_to = null, ?string $caption = null, ?string $session = null)
 * @method array sendFile(string $chatId, mixed $file, ?string $reply_to = null, ?string $caption = null, ?string $session = null)
 * @method array sendVoice(string $chatId, mixed $file, bool $convert, ?string $reply_to = null, ?string $session = null)
 * @method mixed sendVideo(string $chatId, mixed $file, bool $convert, ?string $reply_to = null, ?bool $asNote = null, ?string $caption = 'Just watch at this!', ?string $session = null)
 * @method array sendLinkCustomPreview(string $chatId, string $text, \Vendor\Waha\Generated\Model\LinkPreviewData $preview, ?string $reply_to = null, ?bool $linkPreviewHighQuality = true, ?string $session = null)
 * @method mixed sendButtons(string $chatId, string $header, string $body, string $footer, array $buttons, mixed $headerImage = null, ?string $session = null)
 * @method array sendList(string $chatId, mixed $message, ?string $reply_to = null, ?string $session = null)
 * @method \Vendor\Waha\Generated\Model\WAMessage forwardMessage(string $chatId, string $messageId, ?string $session = null)
 * @method array sendSeen(string $chatId, ?string $messageId = null, ?array $messageIds = null, ?string $participant = null, ?string $session = null)
 * @method mixed startTyping(string $chatId, ?string $session = null)
 * @method mixed stopTyping(string $chatId, ?string $session = null)
 * @method array setReaction(string $messageId, string $reaction, ?string $session = null)
 * @method mixed setStar(string $messageId, string $chatId, bool $star, ?string $session = null)
 * @method mixed sendPoll(string $chatId, \Vendor\Waha\Generated\Model\MessagePoll $poll, ?string $reply_to = null, ?string $session = null)
 * @method mixed sendPollVote(string $chatId, string $pollMessageId, array $votes, ?float $pollServerId = null, ?string $session = null)
 * @method array sendLocation(string $chatId, float $latitude, float $longitude, string $title, ?string $reply_to = null, ?string $session = null)
 * @method mixed sendContactVcard(string $chatId, array $contacts, ?string $reply_to = null, ?string $session = null)
 * @method mixed sendButtonsReply(string $chatId, string $selectedDisplayText, string $selectedButtonID, ?string $replyTo = null, ?string $session = null)
 * @method array getMessages(?string $sortBy = 'timestamp', ?string $sortOrder = null, ?bool $downloadMedia = true, ?string $chatId = null, ?float $limit = 10, ?float $offset = null, ?float $filter_timestamp_lte = null, ?float $filter_timestamp_gte = null, ?bool $filter_fromMe = null, ?string $filter_ack = null, ?string $session = null)
 * @method \Vendor\Waha\Generated\Model\WANumberExistResult checkNumberStatus(?string $phone = null, ?string $session = null)
 * @method array reply(string $chatId, string $text, ?string $reply_to = null, ?bool $linkPreview = true, ?bool $linkPreviewHighQuality = false, ?string $session = null)
 * @method mixed dEPRECATED(string $chatId, string $url, string $title, ?string $session = null)
 */
final class ChattingTag extends WahaTagProxy
{
}
