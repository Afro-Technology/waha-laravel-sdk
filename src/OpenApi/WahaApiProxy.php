<?php

namespace Vendor\Waha\OpenApi;

use Vendor\Waha\Debug\WahaDebugManager;

/**
 * AUTO-GENERATED IDE HELPER (DO NOT EDIT MANUALLY)
 *
 * @method \Vendor\Waha\OpenApi\Tags\SessionsTag sessions()
 * @method \Vendor\Waha\OpenApi\Tags\PairingTag pairing()
 * @method \Vendor\Waha\OpenApi\Tags\ProfileTag profile()
 * @method \Vendor\Waha\OpenApi\Tags\ChattingTag chatting()
 * @method \Vendor\Waha\OpenApi\Tags\PresenceTag presence()
 * @method \Vendor\Waha\OpenApi\Tags\ChannelsTag channels()
 * @method \Vendor\Waha\OpenApi\Tags\StatusTag status()
 * @method \Vendor\Waha\OpenApi\Tags\ChatsTag chats()
 * @method \Vendor\Waha\OpenApi\Tags\ApiKeysTag apiKeys()
 * @method \Vendor\Waha\OpenApi\Tags\ContactsTag contacts()
 * @method \Vendor\Waha\OpenApi\Tags\GroupsTag groups()
 * @method \Vendor\Waha\OpenApi\Tags\CallsTag calls()
 * @method \Vendor\Waha\OpenApi\Tags\EventsTag events()
 * @method \Vendor\Waha\OpenApi\Tags\LabelsTag labels()
 * @method \Vendor\Waha\OpenApi\Tags\MediaTag media()
 * @method \Vendor\Waha\OpenApi\Tags\AppsTag apps()
 * @method \Vendor\Waha\OpenApi\Tags\ObservabilityTag observability()
 * @method \Vendor\Waha\OpenApi\Tags\StorageTag storage()
 * @method mixed getQR(?string $format = 'image', ?string $session = null)
 * @method mixed requestCode(string $phoneNumber, ?string $method = null, ?string $session = null)
 * @method mixed screenshot(?string $session = null)
 * @method \Vendor\Waha\Generated\Model\MeInfo getMe(?string $session = null)
 * @method \Vendor\Waha\Generated\Model\SessionDTO restart(?string $session = null)
 * @method \Vendor\Waha\Generated\Model\MyProfile getMyProfile(?string $session = null)
 * @method \Vendor\Waha\Generated\Model\Result setProfileName(string $name, ?string $session = null)
 * @method \Vendor\Waha\Generated\Model\Result setProfileStatus(string $status, ?string $session = null)
 * @method \Vendor\Waha\Generated\Model\Result setProfilePicture(mixed $file, ?string $session = null)
 * @method \Vendor\Waha\Generated\Model\Result deleteProfilePicture(?string $session = null)
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
 * @method mixed getChats(?string $sortBy = null, ?string $sortOrder = null, ?float $limit = null, ?float $offset = null, ?string $session = null)
 * @method array getChatsOverview(?float $limit = 20, ?float $offset = null, ?array $ids = null, ?string $session = null)
 * @method array postChatsOverview(\Vendor\Waha\Generated\Model\OverviewPaginationParams $pagination, \Vendor\Waha\Generated\Model\OverviewFilter $filter, ?string $session = null)
 * @method mixed deleteChat(string $chatId, ?string $session = null)
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
 * @method mixed rejectCall(string $from, string $id, ?string $session = null)
 * @method array previewChannelMessages(mixed $id, ?bool $downloadMedia = false, ?float $limit = 10, ?string $session = null)
 * @method mixed follow(mixed $id, ?string $session = null)
 * @method mixed unfollow(mixed $id, ?string $session = null)
 * @method mixed mute(mixed $id, ?string $session = null)
 * @method mixed unmute(mixed $id, ?string $session = null)
 * @method \Vendor\Waha\Generated\Model\ChannelListResult searchByView(string $view, array $countries, array $categories, float $limit, string $startCursor, ?string $session = null)
 * @method \Vendor\Waha\Generated\Model\ChannelListResult searchByText(string $text, array $categories, float $limit, string $startCursor, ?string $session = null)
 * @method array getSearchViews(?string $session = null)
 * @method array getSearchCountries(?string $session = null)
 * @method array getSearchCategories(?string $session = null)
 * @method mixed sendTextStatus(string $text, string $backgroundColor, float $font, ?string $id = null, ?array $contacts = null, ?bool $linkPreview = true, ?bool $linkPreviewHighQuality = false, ?string $session = null)
 * @method mixed sendImageStatus(mixed $file, ?string $id = null, ?array $contacts = null, ?string $caption = null, ?string $session = null)
 * @method mixed sendVoiceStatus(mixed $file, bool $convert, string $backgroundColor, ?string $id = null, ?array $contacts = null, ?string $session = null)
 * @method mixed sendVideoStatus(mixed $file, bool $convert, ?string $id = null, ?array $contacts = null, ?string $caption = null, ?string $session = null)
 * @method mixed deleteStatus(?string $id = null, ?array $contacts = null, ?string $session = null)
 * @method \Vendor\Waha\Generated\Model\NewMessageIDResponse getNewMessageId(?string $session = null)
 * @method array getChatLabels(string $chatId, ?string $session = null)
 * @method mixed putChatLabels(string $chatId, array $labels, ?string $session = null)
 * @method mixed getChatsByLabel(string $labelId, ?string $session = null)
 * @method \Vendor\Waha\Generated\Model\WANumberExistResult checkExists(?string $phone = null, ?string $session = null)
 * @method mixed getAbout(?string $contactId = null, ?string $session = null)
 * @method mixed getProfilePicture(?string $contactId = null, ?bool $refresh = false, ?string $session = null)
 * @method mixed block(string $contactId, ?string $session = null)
 * @method mixed unblock(string $contactId, ?string $session = null)
 * @method \Vendor\Waha\Generated\Model\Result put(string $chatId, string $firstName, string $lastName, ?string $session = null)
 * @method \Vendor\Waha\Generated\Model\CountResponse getLidsCount(?string $session = null)
 * @method \Vendor\Waha\Generated\Model\LidToPhoneNumber findPNByLid(string $lid, ?string $session = null)
 * @method \Vendor\Waha\Generated\Model\LidToPhoneNumber findLIDByPhoneNumber(string $phoneNumber, ?string $session = null)
 * @method mixed createGroup(string $name, array $participants, ?string $session = null)
 * @method array getGroups(?string $sortBy = null, ?string $sortOrder = null, ?float $limit = null, ?float $offset = null, ?array $exclude = null, ?string $session = null)
 * @method array joinInfoGroup(?string $code = null, ?string $session = null)
 * @method \Vendor\Waha\Generated\Model\JoinGroupResponse joinGroup(string $code, ?string $session = null)
 * @method \Vendor\Waha\Generated\Model\CountResponse getGroupsCount(?string $session = null)
 * @method mixed refreshGroups(?string $session = null)
 * @method mixed getGroup(string $id, ?string $session = null)
 * @method mixed deleteGroup(string $id, ?string $session = null)
 * @method mixed leaveGroup(string $id, ?string $session = null)
 * @method \Vendor\Waha\Generated\Model\Result setPicture(string $id, mixed $file, ?string $session = null)
 * @method \Vendor\Waha\Generated\Model\Result deletePicture(string $id, ?string $session = null)
 * @method mixed setDescription(string $id, string $description, ?string $session = null)
 * @method mixed setSubject(string $id, string $subject, ?string $session = null)
 * @method mixed setInfoAdminOnly(string $id, bool $adminsOnly, ?string $session = null)
 * @method \Vendor\Waha\Generated\Model\SettingsSecurityChangeInfo getInfoAdminOnly(string $id, ?string $session = null)
 * @method mixed setMessagesAdminOnly(string $id, bool $adminsOnly, ?string $session = null)
 * @method \Vendor\Waha\Generated\Model\SettingsSecurityChangeInfo getMessagesAdminOnly(string $id, ?string $session = null)
 * @method string getInviteCode(string $id, ?string $session = null)
 * @method string revokeInviteCode(string $id, ?string $session = null)
 * @method mixed getParticipants(string $id, ?string $session = null)
 * @method array getGroupParticipants(string $id, ?string $session = null)
 * @method mixed addParticipants(string $id, array $participants, ?string $session = null)
 * @method mixed removeParticipants(string $id, array $participants, ?string $session = null)
 * @method mixed promoteToAdmin(string $id, array $participants, ?string $session = null)
 * @method mixed demoteToAdmin(string $id, array $participants, ?string $session = null)
 * @method mixed setPresence(string $chatId, string $presence, ?string $session = null)
 * @method array getPresenceAll(?string $session = null)
 * @method \Vendor\Waha\Generated\Model\WAHAChatPresences getPresence(string $chatId, ?string $session = null)
 * @method mixed subscribe(string $chatId, ?string $session = null)
 * @method \Vendor\Waha\Generated\Model\WAMessage sendEvent(string $chatId, \Vendor\Waha\Generated\Model\EventMessage $event, ?string $reply_to = null, ?string $session = null)
 * @method \Vendor\Waha\Generated\Model\PingResponse ping()
 * @method array check()
 * @method array environment(?bool $all = false)
 * @method mixed cpuProfile(?float $seconds = 30)
 * @method mixed heapsnapshot()
 * @method mixed browserTrace(?float $seconds = 30, ?array $categories = ['*'], ?string $session = null)
 * @method mixed convertVoice(?string $url = null, ?string $data = null, ?string $session = null)
 * @method mixed convertVideo(?string $url = null, ?string $data = null, ?string $session = null)
 * @method array getLanguages()
 */
final class WahaApiProxy
{
    private bool $nextCallDebug = false;

    private ?string $responseFormatOverride = null;

    public function __construct(
        private readonly OpenApiRouter $router,
        private readonly GeneratedClientFactory $clientFactory,
        private readonly string $hostKey,
        private readonly ?WahaDebugManager $debug = null,
        ?string $responseFormatOverride = null,
    ) {
        $this->responseFormatOverride = $responseFormatOverride;
    }

    /**
     * Enable debug capture for the next *HTTP call* only.
     *
     * Examples:
     * - Waha::debug()->sendText(...)
     * - Waha::debug()->sessions()->start(...)
     */
    public function debug(): self
    {
        $clone = clone $this;
        $clone->nextCallDebug = true;

        return $clone;
    }

    /** Normalize responses to arrays for this chain. */
    public function asArray(): self
    {
        $clone = clone $this;
        $clone->responseFormatOverride = 'array';

        return $clone;
    }

    /** Normalize responses to JSON strings for this chain. */
    public function asJson(): self
    {
        $clone = clone $this;
        $clone->responseFormatOverride = 'json';

        return $clone;
    }

    /** Return generated OpenAPI model objects (default behavior). */
    public function asModel(): self
    {
        $clone = clone $this;
        $clone->responseFormatOverride = 'model';

        return $clone;
    }

    public function __call(string $name, array $arguments)
    {
        // tag method?
        $tagMethods = $this->router->tagMethods();
        if (array_key_exists($name, $tagMethods)) {
            $tagName = $tagMethods[$name];

            $tagProxy = $this->makeTagProxy($tagName);

            if ($this->nextCallDebug) {
                $tagProxy = $tagProxy->debug();
            }

            return $tagProxy;
        }

        // top-level forwarding?
        $resolved = $this->router->resolveTopLevel($name);
        if ($resolved) {
            $tagProxy = $this->makeTagProxy($resolved['tag']);

            // if user called Waha::debug()->sendText(...)
            if ($this->nextCallDebug) {
                $tagProxy = $tagProxy->debug();
            }

            // If the tag proxy provides a concrete convenience method (e.g. ChattingTag::sendText)
            // prefer it, so positional signatures stay stable and IDE autocomplete remains accurate.
            if (method_exists($tagProxy, $name)) {
                return $tagProxy->{$name}(...$arguments);
            }

            return $tagProxy->__call($name, $arguments);
        }

        throw new \BadMethodCallException(
            "Unknown WAHA method '{$name}'. Use tag methods (e.g. sessions(), chatting(), groups()) or regenerate OpenAPI client."
        );
    }

    private function makeTagProxy(string $tagName): WahaTagProxy
    {
        $method = OpenApiRouter::normalizeTagToMethod($tagName);
        $class = 'Vendor\\Waha\\OpenApi\\Tags\\'.ucfirst($method).'Tag';

        if (class_exists($class)) {
            /** @var WahaTagProxy $obj */
            $obj = new $class(
                router: $this->router,
                clientFactory: $this->clientFactory,
                hostKey: $this->hostKey,
                tagName: $tagName,
                debug: $this->debug,
                responseFormatOverride: $this->responseFormatOverride,
            );

            return $obj;
        }

        return new WahaTagProxy(
            router: $this->router,
            clientFactory: $this->clientFactory,
            hostKey: $this->hostKey,
            tagName: $tagName,
            debug: $this->debug,
            responseFormatOverride: $this->responseFormatOverride,
        );
    }
}
