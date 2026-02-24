# VendorWahaGenerated

<b>WhatsApp HTTP API</b> that you can run in a click!<br/><a href=\"/dashboard\"><b>📊 Dashboard</b></a><br/><br/>Learn more:<ul><li><a href=\"https://waha.devlike.pro/\" target=\"_blank\">Documentation</a></li><li><a href=\"https://waha.devlike.pro/docs/how-to/engines/#features\" target=\"_blank\">Supported features in engines</a></li><li><a href=\"https://github.com/devlikeapro/waha\" target=\"_blank\">GitHub - WAHA Core</a></li><li><a href=\"https://github.com/devlikeapro/waha-plus\" target=\"_blank\">GitHub - WAHA Plus</a></li></ul><p>Support the project and get WAHA Plus version!</p><ul><li><a href=\"https://waha.devlike.pro/docs/how-to/plus-version/\" target=\"_blank\">WAHA Plus</a></li><li><a href=\"https://patreon.com/wa_http_api/\" target=\"_blank\">Patreon</a></li><li><a href=\"https://boosty.to/wa-http-api/\" target=\"_blank\">Boosty</a></li><li><a href=\"https://portal.devlike.pro/\" target=\"_blank\">Patron Portal</a></li></ul>


## Installation & Usage

### Requirements

PHP 7.4 and later.
Should also work with PHP 8.0.

### Composer

To install the bindings via [Composer](https://getcomposer.org/), add the following to `composer.json`:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/GIT_USER_ID/GIT_REPO_ID.git"
    }
  ],
  "require": {
    "GIT_USER_ID/GIT_REPO_ID": "*@dev"
  }
}
```

Then run `composer install`

### Manual Installation

Download the files and include `autoload.php`:

```php
<?php
require_once('/path/to/VendorWahaGenerated/vendor/autoload.php');
```

## Getting Started

Please follow the [installation procedure](#installation--usage) and then run the following:

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



// Configure API key authorization: api_key
$config = Vendor\Waha\Generated\Configuration::getDefaultConfiguration()->setApiKey('X-Api-Key', 'YOUR_API_KEY');
// Uncomment below to setup prefix (e.g. Bearer) for API key, if needed
// $config = Vendor\Waha\Generated\Configuration::getDefaultConfiguration()->setApiKeyPrefix('X-Api-Key', 'Bearer');


$apiInstance = new Vendor\Waha\Generated\Api\ApiKeysApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$api_key_request = new \Vendor\Waha\Generated\Model\ApiKeyRequest(); // \Vendor\Waha\Generated\Model\ApiKeyRequest

try {
    $result = $apiInstance->apiKeysControllerCreate($api_key_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ApiKeysApi->apiKeysControllerCreate: ', $e->getMessage(), PHP_EOL;
}

```

## API Endpoints

All URIs are relative to *http://localhost*

Class | Method | HTTP request | Description
------------ | ------------- | ------------- | -------------
*ApiKeysApi* | [**apiKeysControllerCreate**](docs/Api/ApiKeysApi.md#apikeyscontrollercreate) | **POST** /api/keys | Create a new API key
*ApiKeysApi* | [**apiKeysControllerDelete**](docs/Api/ApiKeysApi.md#apikeyscontrollerdelete) | **DELETE** /api/keys/{id} | Delete an API key
*ApiKeysApi* | [**apiKeysControllerList**](docs/Api/ApiKeysApi.md#apikeyscontrollerlist) | **GET** /api/keys | Get all API keys
*ApiKeysApi* | [**apiKeysControllerUpdate**](docs/Api/ApiKeysApi.md#apikeyscontrollerupdate) | **PUT** /api/keys/{id} | Update an API key
*AppsApi* | [**appsControllerCreate**](docs/Api/AppsApi.md#appscontrollercreate) | **POST** /api/apps | Create a new app
*AppsApi* | [**appsControllerDelete**](docs/Api/AppsApi.md#appscontrollerdelete) | **DELETE** /api/apps/{id} | Delete an app
*AppsApi* | [**appsControllerGet**](docs/Api/AppsApi.md#appscontrollerget) | **GET** /api/apps/{id} | Get app by ID
*AppsApi* | [**appsControllerList**](docs/Api/AppsApi.md#appscontrollerlist) | **GET** /api/apps | List all apps for a session
*AppsApi* | [**appsControllerUpdate**](docs/Api/AppsApi.md#appscontrollerupdate) | **PUT** /api/apps/{id} | Update an existing app
*AppsApi* | [**chatwootLocalesControllerGetLanguages**](docs/Api/AppsApi.md#chatwootlocalescontrollergetlanguages) | **GET** /api/apps/chatwoot/locales | Get available languages for Chatwoot app
*AppsApi* | [**chatwootWebhookControllerWebhook**](docs/Api/AppsApi.md#chatwootwebhookcontrollerwebhook) | **POST** /webhooks/chatwoot/{session}/{id} | Chatwoot Webhook
*CallsApi* | [**callsControllerRejectCall**](docs/Api/CallsApi.md#callscontrollerrejectcall) | **POST** /api/{session}/calls/reject | Reject incoming call
*ChannelsApi* | [**channelsControllerCreate**](docs/Api/ChannelsApi.md#channelscontrollercreate) | **POST** /api/{session}/channels | Create a new channel.
*ChannelsApi* | [**channelsControllerDelete**](docs/Api/ChannelsApi.md#channelscontrollerdelete) | **DELETE** /api/{session}/channels/{id} | Delete the channel.
*ChannelsApi* | [**channelsControllerFollow**](docs/Api/ChannelsApi.md#channelscontrollerfollow) | **POST** /api/{session}/channels/{id}/follow | Follow the channel.
*ChannelsApi* | [**channelsControllerGet**](docs/Api/ChannelsApi.md#channelscontrollerget) | **GET** /api/{session}/channels/{id} | Get the channel info
*ChannelsApi* | [**channelsControllerGetSearchCategories**](docs/Api/ChannelsApi.md#channelscontrollergetsearchcategories) | **GET** /api/{session}/channels/search/categories | Get list of categories for channel search
*ChannelsApi* | [**channelsControllerGetSearchCountries**](docs/Api/ChannelsApi.md#channelscontrollergetsearchcountries) | **GET** /api/{session}/channels/search/countries | Get list of countries for channel search
*ChannelsApi* | [**channelsControllerGetSearchViews**](docs/Api/ChannelsApi.md#channelscontrollergetsearchviews) | **GET** /api/{session}/channels/search/views | Get list of views for channel search
*ChannelsApi* | [**channelsControllerList**](docs/Api/ChannelsApi.md#channelscontrollerlist) | **GET** /api/{session}/channels | Get list of know channels
*ChannelsApi* | [**channelsControllerMute**](docs/Api/ChannelsApi.md#channelscontrollermute) | **POST** /api/{session}/channels/{id}/mute | Mute the channel.
*ChannelsApi* | [**channelsControllerPreviewChannelMessages**](docs/Api/ChannelsApi.md#channelscontrollerpreviewchannelmessages) | **GET** /api/{session}/channels/{id}/messages/preview | Preview channel messages
*ChannelsApi* | [**channelsControllerSearchByText**](docs/Api/ChannelsApi.md#channelscontrollersearchbytext) | **POST** /api/{session}/channels/search/by-text | Search for channels (by text)
*ChannelsApi* | [**channelsControllerSearchByView**](docs/Api/ChannelsApi.md#channelscontrollersearchbyview) | **POST** /api/{session}/channels/search/by-view | Search for channels (by view)
*ChannelsApi* | [**channelsControllerUnfollow**](docs/Api/ChannelsApi.md#channelscontrollerunfollow) | **POST** /api/{session}/channels/{id}/unfollow | Unfollow the channel.
*ChannelsApi* | [**channelsControllerUnmute**](docs/Api/ChannelsApi.md#channelscontrollerunmute) | **POST** /api/{session}/channels/{id}/unmute | Unmute the channel.
*ChatsApi* | [**chatsControllerArchiveChat**](docs/Api/ChatsApi.md#chatscontrollerarchivechat) | **POST** /api/{session}/chats/{chatId}/archive | Archive the chat
*ChatsApi* | [**chatsControllerClearMessages**](docs/Api/ChatsApi.md#chatscontrollerclearmessages) | **DELETE** /api/{session}/chats/{chatId}/messages | Clears all messages from the chat
*ChatsApi* | [**chatsControllerDeleteChat**](docs/Api/ChatsApi.md#chatscontrollerdeletechat) | **DELETE** /api/{session}/chats/{chatId} | Deletes the chat
*ChatsApi* | [**chatsControllerDeleteMessage**](docs/Api/ChatsApi.md#chatscontrollerdeletemessage) | **DELETE** /api/{session}/chats/{chatId}/messages/{messageId} | Deletes a message from the chat
*ChatsApi* | [**chatsControllerEditMessage**](docs/Api/ChatsApi.md#chatscontrollereditmessage) | **PUT** /api/{session}/chats/{chatId}/messages/{messageId} | Edits a message in the chat
*ChatsApi* | [**chatsControllerGetChatMessage**](docs/Api/ChatsApi.md#chatscontrollergetchatmessage) | **GET** /api/{session}/chats/{chatId}/messages/{messageId} | Gets message by id
*ChatsApi* | [**chatsControllerGetChatMessages**](docs/Api/ChatsApi.md#chatscontrollergetchatmessages) | **GET** /api/{session}/chats/{chatId}/messages | Gets messages in the chat
*ChatsApi* | [**chatsControllerGetChatPicture**](docs/Api/ChatsApi.md#chatscontrollergetchatpicture) | **GET** /api/{session}/chats/{chatId}/picture | Gets chat picture
*ChatsApi* | [**chatsControllerGetChats**](docs/Api/ChatsApi.md#chatscontrollergetchats) | **GET** /api/{session}/chats | Get chats
*ChatsApi* | [**chatsControllerGetChatsOverview**](docs/Api/ChatsApi.md#chatscontrollergetchatsoverview) | **GET** /api/{session}/chats/overview | Get chats overview. Includes all necessary things to build UI \&quot;your chats overview\&quot; page - chat id, name, picture, last message. Sorting by last message timestamp
*ChatsApi* | [**chatsControllerPinMessage**](docs/Api/ChatsApi.md#chatscontrollerpinmessage) | **POST** /api/{session}/chats/{chatId}/messages/{messageId}/pin | Pins a message in the chat
*ChatsApi* | [**chatsControllerPostChatsOverview**](docs/Api/ChatsApi.md#chatscontrollerpostchatsoverview) | **POST** /api/{session}/chats/overview | Get chats overview. Use POST if you have too many \&quot;ids\&quot; params - GET can limit it
*ChatsApi* | [**chatsControllerReadChatMessages**](docs/Api/ChatsApi.md#chatscontrollerreadchatmessages) | **POST** /api/{session}/chats/{chatId}/messages/read | Read unread messages in the chat
*ChatsApi* | [**chatsControllerUnarchiveChat**](docs/Api/ChatsApi.md#chatscontrollerunarchivechat) | **POST** /api/{session}/chats/{chatId}/unarchive | Unarchive the chat
*ChatsApi* | [**chatsControllerUnpinMessage**](docs/Api/ChatsApi.md#chatscontrollerunpinmessage) | **POST** /api/{session}/chats/{chatId}/messages/{messageId}/unpin | Unpins a message in the chat
*ChatsApi* | [**chatsControllerUnreadChat**](docs/Api/ChatsApi.md#chatscontrollerunreadchat) | **POST** /api/{session}/chats/{chatId}/unread | Unread the chat
*ChattingApi* | [**chattingControllerDEPRECATEDCheckNumberStatus**](docs/Api/ChattingApi.md#chattingcontrollerdeprecatedchecknumberstatus) | **GET** /api/checkNumberStatus | Check number status
*ChattingApi* | [**chattingControllerForwardMessage**](docs/Api/ChattingApi.md#chattingcontrollerforwardmessage) | **POST** /api/forwardMessage | 
*ChattingApi* | [**chattingControllerGetMessages**](docs/Api/ChattingApi.md#chattingcontrollergetmessages) | **GET** /api/messages | Get messages in a chat
*ChattingApi* | [**chattingControllerReply**](docs/Api/ChattingApi.md#chattingcontrollerreply) | **POST** /api/reply | DEPRECATED - you can set \&quot;reply_to\&quot; field when sending text, image, etc
*ChattingApi* | [**chattingControllerSendButtons**](docs/Api/ChattingApi.md#chattingcontrollersendbuttons) | **POST** /api/sendButtons | Send buttons message (interactive)
*ChattingApi* | [**chattingControllerSendButtonsReply**](docs/Api/ChattingApi.md#chattingcontrollersendbuttonsreply) | **POST** /api/send/buttons/reply | Reply on a button message
*ChattingApi* | [**chattingControllerSendContactVcard**](docs/Api/ChattingApi.md#chattingcontrollersendcontactvcard) | **POST** /api/sendContactVcard | 
*ChattingApi* | [**chattingControllerSendFile**](docs/Api/ChattingApi.md#chattingcontrollersendfile) | **POST** /api/sendFile | Send a file
*ChattingApi* | [**chattingControllerSendImage**](docs/Api/ChattingApi.md#chattingcontrollersendimage) | **POST** /api/sendImage | Send an image
*ChattingApi* | [**chattingControllerSendLinkCustomPreview**](docs/Api/ChattingApi.md#chattingcontrollersendlinkcustompreview) | **POST** /api/send/link-custom-preview | Send a text message with a CUSTOM link preview.
*ChattingApi* | [**chattingControllerSendLinkPreviewDEPRECATED**](docs/Api/ChattingApi.md#chattingcontrollersendlinkpreviewdeprecated) | **POST** /api/sendLinkPreview | 
*ChattingApi* | [**chattingControllerSendList**](docs/Api/ChattingApi.md#chattingcontrollersendlist) | **POST** /api/sendList | Send a list message (interactive)
*ChattingApi* | [**chattingControllerSendLocation**](docs/Api/ChattingApi.md#chattingcontrollersendlocation) | **POST** /api/sendLocation | 
*ChattingApi* | [**chattingControllerSendPoll**](docs/Api/ChattingApi.md#chattingcontrollersendpoll) | **POST** /api/sendPoll | Send a poll with options
*ChattingApi* | [**chattingControllerSendPollVote**](docs/Api/ChattingApi.md#chattingcontrollersendpollvote) | **POST** /api/sendPollVote | Vote on a poll
*ChattingApi* | [**chattingControllerSendSeen**](docs/Api/ChattingApi.md#chattingcontrollersendseen) | **POST** /api/sendSeen | 
*ChattingApi* | [**chattingControllerSendText**](docs/Api/ChattingApi.md#chattingcontrollersendtext) | **POST** /api/sendText | Send a text message
*ChattingApi* | [**chattingControllerSendTextGet**](docs/Api/ChattingApi.md#chattingcontrollersendtextget) | **GET** /api/sendText | Send a text message
*ChattingApi* | [**chattingControllerSendVideo**](docs/Api/ChattingApi.md#chattingcontrollersendvideo) | **POST** /api/sendVideo | Send a video
*ChattingApi* | [**chattingControllerSendVoice**](docs/Api/ChattingApi.md#chattingcontrollersendvoice) | **POST** /api/sendVoice | Send an voice message
*ChattingApi* | [**chattingControllerSetReaction**](docs/Api/ChattingApi.md#chattingcontrollersetreaction) | **PUT** /api/reaction | React to a message with an emoji
*ChattingApi* | [**chattingControllerSetStar**](docs/Api/ChattingApi.md#chattingcontrollersetstar) | **PUT** /api/star | Star or unstar a message
*ChattingApi* | [**chattingControllerStartTyping**](docs/Api/ChattingApi.md#chattingcontrollerstarttyping) | **POST** /api/startTyping | 
*ChattingApi* | [**chattingControllerStopTyping**](docs/Api/ChattingApi.md#chattingcontrollerstoptyping) | **POST** /api/stopTyping | 
*ContactsApi* | [**contactsControllerBlock**](docs/Api/ContactsApi.md#contactscontrollerblock) | **POST** /api/contacts/block | Block contact
*ContactsApi* | [**contactsControllerCheckExists**](docs/Api/ContactsApi.md#contactscontrollercheckexists) | **GET** /api/contacts/check-exists | Check phone number is registered in WhatsApp.
*ContactsApi* | [**contactsControllerGet**](docs/Api/ContactsApi.md#contactscontrollerget) | **GET** /api/contacts | Get contact basic info
*ContactsApi* | [**contactsControllerGetAbout**](docs/Api/ContactsApi.md#contactscontrollergetabout) | **GET** /api/contacts/about | Gets the Contact&#39;s \&quot;about\&quot; info
*ContactsApi* | [**contactsControllerGetAll**](docs/Api/ContactsApi.md#contactscontrollergetall) | **GET** /api/contacts/all | Get all contacts
*ContactsApi* | [**contactsControllerGetProfilePicture**](docs/Api/ContactsApi.md#contactscontrollergetprofilepicture) | **GET** /api/contacts/profile-picture | Get contact&#39;s profile picture URL
*ContactsApi* | [**contactsControllerUnblock**](docs/Api/ContactsApi.md#contactscontrollerunblock) | **POST** /api/contacts/unblock | Unblock contact
*ContactsApi* | [**contactsSessionControllerPut**](docs/Api/ContactsApi.md#contactssessioncontrollerput) | **PUT** /api/{session}/contacts/{chatId} | Create or update contact
*ContactsApi* | [**lidsControllerFindLIDByPhoneNumber**](docs/Api/ContactsApi.md#lidscontrollerfindlidbyphonenumber) | **GET** /api/{session}/lids/pn/{phoneNumber} | Get lid by phone number (chat id)
*ContactsApi* | [**lidsControllerFindPNByLid**](docs/Api/ContactsApi.md#lidscontrollerfindpnbylid) | **GET** /api/{session}/lids/{lid} | Get phone number by lid
*ContactsApi* | [**lidsControllerGetAll**](docs/Api/ContactsApi.md#lidscontrollergetall) | **GET** /api/{session}/lids | Get all known lids to phone number mapping
*ContactsApi* | [**lidsControllerGetLidsCount**](docs/Api/ContactsApi.md#lidscontrollergetlidscount) | **GET** /api/{session}/lids/count | Get the number of known lids
*EventsApi* | [**eventsControllerSendEvent**](docs/Api/EventsApi.md#eventscontrollersendevent) | **POST** /api/{session}/events | Send an event message
*GroupsApi* | [**groupsControllerAddParticipants**](docs/Api/GroupsApi.md#groupscontrolleraddparticipants) | **POST** /api/{session}/groups/{id}/participants/add | Add participants
*GroupsApi* | [**groupsControllerCreateGroup**](docs/Api/GroupsApi.md#groupscontrollercreategroup) | **POST** /api/{session}/groups | Create a new group.
*GroupsApi* | [**groupsControllerDeleteGroup**](docs/Api/GroupsApi.md#groupscontrollerdeletegroup) | **DELETE** /api/{session}/groups/{id} | Delete the group.
*GroupsApi* | [**groupsControllerDeletePicture**](docs/Api/GroupsApi.md#groupscontrollerdeletepicture) | **DELETE** /api/{session}/groups/{id}/picture | Delete group picture
*GroupsApi* | [**groupsControllerDemoteToAdmin**](docs/Api/GroupsApi.md#groupscontrollerdemotetoadmin) | **POST** /api/{session}/groups/{id}/admin/demote | Demotes participants to regular users.
*GroupsApi* | [**groupsControllerGetChatPicture**](docs/Api/GroupsApi.md#groupscontrollergetchatpicture) | **GET** /api/{session}/groups/{id}/picture | Get group picture
*GroupsApi* | [**groupsControllerGetGroup**](docs/Api/GroupsApi.md#groupscontrollergetgroup) | **GET** /api/{session}/groups/{id} | Get the group.
*GroupsApi* | [**groupsControllerGetGroupParticipants**](docs/Api/GroupsApi.md#groupscontrollergetgroupparticipants) | **GET** /api/{session}/groups/{id}/participants/v2 | Get group participants.
*GroupsApi* | [**groupsControllerGetGroups**](docs/Api/GroupsApi.md#groupscontrollergetgroups) | **GET** /api/{session}/groups | Get all groups.
*GroupsApi* | [**groupsControllerGetGroupsCount**](docs/Api/GroupsApi.md#groupscontrollergetgroupscount) | **GET** /api/{session}/groups/count | Get the number of groups.
*GroupsApi* | [**groupsControllerGetInfoAdminOnly**](docs/Api/GroupsApi.md#groupscontrollergetinfoadminonly) | **GET** /api/{session}/groups/{id}/settings/security/info-admin-only | Get the group&#39;s &#39;info admin only&#39; settings.
*GroupsApi* | [**groupsControllerGetInviteCode**](docs/Api/GroupsApi.md#groupscontrollergetinvitecode) | **GET** /api/{session}/groups/{id}/invite-code | Gets the invite code for the group.
*GroupsApi* | [**groupsControllerGetMessagesAdminOnly**](docs/Api/GroupsApi.md#groupscontrollergetmessagesadminonly) | **GET** /api/{session}/groups/{id}/settings/security/messages-admin-only | Get settings - who can send messages
*GroupsApi* | [**groupsControllerGetParticipants**](docs/Api/GroupsApi.md#groupscontrollergetparticipants) | **GET** /api/{session}/groups/{id}/participants | Get participants
*GroupsApi* | [**groupsControllerJoinGroup**](docs/Api/GroupsApi.md#groupscontrollerjoingroup) | **POST** /api/{session}/groups/join | Join group via code
*GroupsApi* | [**groupsControllerJoinInfoGroup**](docs/Api/GroupsApi.md#groupscontrollerjoininfogroup) | **GET** /api/{session}/groups/join-info | Get info about the group before joining.
*GroupsApi* | [**groupsControllerLeaveGroup**](docs/Api/GroupsApi.md#groupscontrollerleavegroup) | **POST** /api/{session}/groups/{id}/leave | Leave the group.
*GroupsApi* | [**groupsControllerPromoteToAdmin**](docs/Api/GroupsApi.md#groupscontrollerpromotetoadmin) | **POST** /api/{session}/groups/{id}/admin/promote | Promote participants to admin users.
*GroupsApi* | [**groupsControllerRefreshGroups**](docs/Api/GroupsApi.md#groupscontrollerrefreshgroups) | **POST** /api/{session}/groups/refresh | Refresh groups from the server.
*GroupsApi* | [**groupsControllerRemoveParticipants**](docs/Api/GroupsApi.md#groupscontrollerremoveparticipants) | **POST** /api/{session}/groups/{id}/participants/remove | Remove participants
*GroupsApi* | [**groupsControllerRevokeInviteCode**](docs/Api/GroupsApi.md#groupscontrollerrevokeinvitecode) | **POST** /api/{session}/groups/{id}/invite-code/revoke | Invalidates the current group invite code and generates a new one.
*GroupsApi* | [**groupsControllerSetDescription**](docs/Api/GroupsApi.md#groupscontrollersetdescription) | **PUT** /api/{session}/groups/{id}/description | Updates the group description.
*GroupsApi* | [**groupsControllerSetInfoAdminOnly**](docs/Api/GroupsApi.md#groupscontrollersetinfoadminonly) | **PUT** /api/{session}/groups/{id}/settings/security/info-admin-only | Updates the group \&quot;info admin only\&quot; settings.
*GroupsApi* | [**groupsControllerSetMessagesAdminOnly**](docs/Api/GroupsApi.md#groupscontrollersetmessagesadminonly) | **PUT** /api/{session}/groups/{id}/settings/security/messages-admin-only | Update settings - who can send messages
*GroupsApi* | [**groupsControllerSetPicture**](docs/Api/GroupsApi.md#groupscontrollersetpicture) | **PUT** /api/{session}/groups/{id}/picture | Set group picture
*GroupsApi* | [**groupsControllerSetSubject**](docs/Api/GroupsApi.md#groupscontrollersetsubject) | **PUT** /api/{session}/groups/{id}/subject | Updates the group subject
*LabelsApi* | [**labelsControllerCreate**](docs/Api/LabelsApi.md#labelscontrollercreate) | **POST** /api/{session}/labels | Create a new label
*LabelsApi* | [**labelsControllerDelete**](docs/Api/LabelsApi.md#labelscontrollerdelete) | **DELETE** /api/{session}/labels/{labelId} | Delete a label
*LabelsApi* | [**labelsControllerGetAll**](docs/Api/LabelsApi.md#labelscontrollergetall) | **GET** /api/{session}/labels | Get all labels
*LabelsApi* | [**labelsControllerGetChatLabels**](docs/Api/LabelsApi.md#labelscontrollergetchatlabels) | **GET** /api/{session}/labels/chats/{chatId} | Get labels for the chat
*LabelsApi* | [**labelsControllerGetChatsByLabel**](docs/Api/LabelsApi.md#labelscontrollergetchatsbylabel) | **GET** /api/{session}/labels/{labelId}/chats | Get chats by label
*LabelsApi* | [**labelsControllerPutChatLabels**](docs/Api/LabelsApi.md#labelscontrollerputchatlabels) | **PUT** /api/{session}/labels/chats/{chatId} | Save labels for the chat
*LabelsApi* | [**labelsControllerUpdate**](docs/Api/LabelsApi.md#labelscontrollerupdate) | **PUT** /api/{session}/labels/{labelId} | Update a label
*MediaApi* | [**mediaControllerConvertVideo**](docs/Api/MediaApi.md#mediacontrollerconvertvideo) | **POST** /api/{session}/media/convert/video | Convert video to WhatsApp format (mp4)
*MediaApi* | [**mediaControllerConvertVoice**](docs/Api/MediaApi.md#mediacontrollerconvertvoice) | **POST** /api/{session}/media/convert/voice | Convert voice to WhatsApp format (opus)
*ObservabilityApi* | [**healthControllerCheck**](docs/Api/ObservabilityApi.md#healthcontrollercheck) | **GET** /health | Check the health of the server
*ObservabilityApi* | [**pingControllerPing**](docs/Api/ObservabilityApi.md#pingcontrollerping) | **GET** /ping | Ping the server
*ObservabilityApi* | [**serverControllerEnvironment**](docs/Api/ObservabilityApi.md#servercontrollerenvironment) | **GET** /api/server/environment | Get the server environment
*ObservabilityApi* | [**serverControllerGet**](docs/Api/ObservabilityApi.md#servercontrollerget) | **GET** /api/server/version | Get the version of the server
*ObservabilityApi* | [**serverControllerStatus**](docs/Api/ObservabilityApi.md#servercontrollerstatus) | **GET** /api/server/status | Get the server status
*ObservabilityApi* | [**serverControllerStop**](docs/Api/ObservabilityApi.md#servercontrollerstop) | **POST** /api/server/stop | Stop (and restart) the server
*ObservabilityApi* | [**serverDebugControllerBrowserTrace**](docs/Api/ObservabilityApi.md#serverdebugcontrollerbrowsertrace) | **GET** /api/server/debug/browser/trace/{session} | Collect and get a trace.json for Chrome DevTools
*ObservabilityApi* | [**serverDebugControllerCpuProfile**](docs/Api/ObservabilityApi.md#serverdebugcontrollercpuprofile) | **GET** /api/server/debug/cpu | Collect and return a CPU profile for the current nodejs process
*ObservabilityApi* | [**serverDebugControllerHeapsnapshot**](docs/Api/ObservabilityApi.md#serverdebugcontrollerheapsnapshot) | **GET** /api/server/debug/heapsnapshot | Return a heapsnapshot for the current nodejs process
*ObservabilityApi* | [**versionControllerGet**](docs/Api/ObservabilityApi.md#versioncontrollerget) | **GET** /api/version | Get the server version
*PairingApi* | [**authControllerGetQR**](docs/Api/PairingApi.md#authcontrollergetqr) | **GET** /api/{session}/auth/qr | Get QR code for pairing WhatsApp API.
*PairingApi* | [**authControllerRequestCode**](docs/Api/PairingApi.md#authcontrollerrequestcode) | **POST** /api/{session}/auth/request-code | Request authentication code.
*PairingApi* | [**screenshotControllerScreenshot**](docs/Api/PairingApi.md#screenshotcontrollerscreenshot) | **GET** /api/screenshot | Get a screenshot of the current WhatsApp session (**WEBJS** only)
*PresenceApi* | [**presenceControllerGetPresence**](docs/Api/PresenceApi.md#presencecontrollergetpresence) | **GET** /api/{session}/presence/{chatId} | Get the presence for the chat id. If it hasn&#39;t been subscribed - it also subscribes to it.
*PresenceApi* | [**presenceControllerGetPresenceAll**](docs/Api/PresenceApi.md#presencecontrollergetpresenceall) | **GET** /api/{session}/presence | Get all subscribed presence information.
*PresenceApi* | [**presenceControllerSetPresence**](docs/Api/PresenceApi.md#presencecontrollersetpresence) | **POST** /api/{session}/presence | Set session presence
*PresenceApi* | [**presenceControllerSubscribe**](docs/Api/PresenceApi.md#presencecontrollersubscribe) | **POST** /api/{session}/presence/{chatId}/subscribe | Subscribe to presence events for the chat.
*ProfileApi* | [**profileControllerDeleteProfilePicture**](docs/Api/ProfileApi.md#profilecontrollerdeleteprofilepicture) | **DELETE** /api/{session}/profile/picture | Delete profile picture
*ProfileApi* | [**profileControllerGetMyProfile**](docs/Api/ProfileApi.md#profilecontrollergetmyprofile) | **GET** /api/{session}/profile | Get my profile
*ProfileApi* | [**profileControllerSetProfileName**](docs/Api/ProfileApi.md#profilecontrollersetprofilename) | **PUT** /api/{session}/profile/name | Set my profile name
*ProfileApi* | [**profileControllerSetProfilePicture**](docs/Api/ProfileApi.md#profilecontrollersetprofilepicture) | **PUT** /api/{session}/profile/picture | Set profile picture
*ProfileApi* | [**profileControllerSetProfileStatus**](docs/Api/ProfileApi.md#profilecontrollersetprofilestatus) | **PUT** /api/{session}/profile/status | Set profile status (About)
*SessionsApi* | [**sessionsControllerCreate**](docs/Api/SessionsApi.md#sessionscontrollercreate) | **POST** /api/sessions | Create a session
*SessionsApi* | [**sessionsControllerDEPRACATEDStart**](docs/Api/SessionsApi.md#sessionscontrollerdepracatedstart) | **POST** /api/sessions/start | Upsert and Start session
*SessionsApi* | [**sessionsControllerDEPRECATEDLogout**](docs/Api/SessionsApi.md#sessionscontrollerdeprecatedlogout) | **POST** /api/sessions/logout | Logout and Delete session.
*SessionsApi* | [**sessionsControllerDEPRECATEDStop**](docs/Api/SessionsApi.md#sessionscontrollerdeprecatedstop) | **POST** /api/sessions/stop | Stop (and Logout if asked) session
*SessionsApi* | [**sessionsControllerDelete**](docs/Api/SessionsApi.md#sessionscontrollerdelete) | **DELETE** /api/sessions/{session} | Delete the session
*SessionsApi* | [**sessionsControllerGet**](docs/Api/SessionsApi.md#sessionscontrollerget) | **GET** /api/sessions/{session} | Get session information
*SessionsApi* | [**sessionsControllerGetMe**](docs/Api/SessionsApi.md#sessionscontrollergetme) | **GET** /api/sessions/{session}/me | Get information about the authenticated account
*SessionsApi* | [**sessionsControllerList**](docs/Api/SessionsApi.md#sessionscontrollerlist) | **GET** /api/sessions | List all sessions
*SessionsApi* | [**sessionsControllerLogout**](docs/Api/SessionsApi.md#sessionscontrollerlogout) | **POST** /api/sessions/{session}/logout | Logout from the session
*SessionsApi* | [**sessionsControllerRestart**](docs/Api/SessionsApi.md#sessionscontrollerrestart) | **POST** /api/sessions/{session}/restart | Restart the session
*SessionsApi* | [**sessionsControllerStart**](docs/Api/SessionsApi.md#sessionscontrollerstart) | **POST** /api/sessions/{session}/start | Start the session
*SessionsApi* | [**sessionsControllerStop**](docs/Api/SessionsApi.md#sessionscontrollerstop) | **POST** /api/sessions/{session}/stop | Stop the session
*SessionsApi* | [**sessionsControllerUpdate**](docs/Api/SessionsApi.md#sessionscontrollerupdate) | **PUT** /api/sessions/{session} | Update a session
*StatusApi* | [**statusControllerDeleteStatus**](docs/Api/StatusApi.md#statuscontrollerdeletestatus) | **POST** /api/{session}/status/delete | DELETE sent status
*StatusApi* | [**statusControllerGetNewMessageId**](docs/Api/StatusApi.md#statuscontrollergetnewmessageid) | **GET** /api/{session}/status/new-message-id | Generate message ID you can use to batch contacts
*StatusApi* | [**statusControllerSendImageStatus**](docs/Api/StatusApi.md#statuscontrollersendimagestatus) | **POST** /api/{session}/status/image | Send image status
*StatusApi* | [**statusControllerSendTextStatus**](docs/Api/StatusApi.md#statuscontrollersendtextstatus) | **POST** /api/{session}/status/text | Send text status
*StatusApi* | [**statusControllerSendVideoStatus**](docs/Api/StatusApi.md#statuscontrollersendvideostatus) | **POST** /api/{session}/status/video | Send video status
*StatusApi* | [**statusControllerSendVoiceStatus**](docs/Api/StatusApi.md#statuscontrollersendvoicestatus) | **POST** /api/{session}/status/voice | Send voice status
*StorageApi* | [**s3ProxyControllerGet**](docs/Api/StorageApi.md#s3proxycontrollerget) | **GET** /api/s3/{bucket}/*parts | Get files from S3

## Models

- [ApiKeyDTO](docs/Model/ApiKeyDTO.md)
- [ApiKeyRequest](docs/Model/ApiKeyRequest.md)
- [App](docs/Model/App.md)
- [AuthControllerGetQR200Response](docs/Model/AuthControllerGetQR200Response.md)
- [Base64File](docs/Model/Base64File.md)
- [BinaryFile](docs/Model/BinaryFile.md)
- [Button](docs/Model/Button.md)
- [CallData](docs/Model/CallData.md)
- [CallsAppChannelConfig](docs/Model/CallsAppChannelConfig.md)
- [CallsAppConfig](docs/Model/CallsAppConfig.md)
- [Channel](docs/Model/Channel.md)
- [ChannelCategory](docs/Model/ChannelCategory.md)
- [ChannelCountry](docs/Model/ChannelCountry.md)
- [ChannelListResult](docs/Model/ChannelListResult.md)
- [ChannelMessage](docs/Model/ChannelMessage.md)
- [ChannelPagination](docs/Model/ChannelPagination.md)
- [ChannelPublicInfo](docs/Model/ChannelPublicInfo.md)
- [ChannelSearchByText](docs/Model/ChannelSearchByText.md)
- [ChannelSearchByView](docs/Model/ChannelSearchByView.md)
- [ChannelView](docs/Model/ChannelView.md)
- [ChatArchiveEvent](docs/Model/ChatArchiveEvent.md)
- [ChatPictureResponse](docs/Model/ChatPictureResponse.md)
- [ChatRequest](docs/Model/ChatRequest.md)
- [ChatSummary](docs/Model/ChatSummary.md)
- [ChatWootAppConfig](docs/Model/ChatWootAppConfig.md)
- [ChatWootCommandsConfig](docs/Model/ChatWootCommandsConfig.md)
- [ChatWootConversationsConfig](docs/Model/ChatWootConversationsConfig.md)
- [ClientSessionConfig](docs/Model/ClientSessionConfig.md)
- [Contact](docs/Model/Contact.md)
- [ContactRequest](docs/Model/ContactRequest.md)
- [ContactUpdateBody](docs/Model/ContactUpdateBody.md)
- [CountResponse](docs/Model/CountResponse.md)
- [CreateChannelRequest](docs/Model/CreateChannelRequest.md)
- [CreateGroupRequest](docs/Model/CreateGroupRequest.md)
- [CustomHeader](docs/Model/CustomHeader.md)
- [DeleteStatusRequest](docs/Model/DeleteStatusRequest.md)
- [DescriptionRequest](docs/Model/DescriptionRequest.md)
- [EditMessageRequest](docs/Model/EditMessageRequest.md)
- [EnginePayload](docs/Model/EnginePayload.md)
- [EventLocation](docs/Model/EventLocation.md)
- [EventMessage](docs/Model/EventMessage.md)
- [EventMessageRequest](docs/Model/EventMessageRequest.md)
- [EventResponse](docs/Model/EventResponse.md)
- [EventResponsePayload](docs/Model/EventResponsePayload.md)
- [FileContent](docs/Model/FileContent.md)
- [FileURL](docs/Model/FileURL.md)
- [GowsConfig](docs/Model/GowsConfig.md)
- [GowsStorageConfig](docs/Model/GowsStorageConfig.md)
- [GroupId](docs/Model/GroupId.md)
- [GroupInfo](docs/Model/GroupInfo.md)
- [GroupParticipant](docs/Model/GroupParticipant.md)
- [GroupV2JoinEvent](docs/Model/GroupV2JoinEvent.md)
- [GroupV2LeaveEvent](docs/Model/GroupV2LeaveEvent.md)
- [GroupV2ParticipantsEvent](docs/Model/GroupV2ParticipantsEvent.md)
- [GroupV2UpdateEvent](docs/Model/GroupV2UpdateEvent.md)
- [HealthControllerCheck200Response](docs/Model/HealthControllerCheck200Response.md)
- [HealthControllerCheck200ResponseErrorValue](docs/Model/HealthControllerCheck200ResponseErrorValue.md)
- [HealthControllerCheck200ResponseInfoValue](docs/Model/HealthControllerCheck200ResponseInfoValue.md)
- [HealthControllerCheck503Response](docs/Model/HealthControllerCheck503Response.md)
- [HmacConfiguration](docs/Model/HmacConfiguration.md)
- [IgnoreConfig](docs/Model/IgnoreConfig.md)
- [ImageStatus](docs/Model/ImageStatus.md)
- [JoinGroupRequest](docs/Model/JoinGroupRequest.md)
- [JoinGroupResponse](docs/Model/JoinGroupResponse.md)
- [Label](docs/Model/Label.md)
- [LabelBody](docs/Model/LabelBody.md)
- [LabelChatAssociation](docs/Model/LabelChatAssociation.md)
- [LabelID](docs/Model/LabelID.md)
- [LidToPhoneNumber](docs/Model/LidToPhoneNumber.md)
- [LinkPreviewData](docs/Model/LinkPreviewData.md)
- [LinkPreviewDataImage](docs/Model/LinkPreviewDataImage.md)
- [MeInfo](docs/Model/MeInfo.md)
- [MessageButtonReply](docs/Model/MessageButtonReply.md)
- [MessageContactVcardRequest](docs/Model/MessageContactVcardRequest.md)
- [MessageContactVcardRequestContactsInner](docs/Model/MessageContactVcardRequestContactsInner.md)
- [MessageDestination](docs/Model/MessageDestination.md)
- [MessageFileRequest](docs/Model/MessageFileRequest.md)
- [MessageForwardRequest](docs/Model/MessageForwardRequest.md)
- [MessageImageRequest](docs/Model/MessageImageRequest.md)
- [MessageLinkCustomPreviewRequest](docs/Model/MessageLinkCustomPreviewRequest.md)
- [MessageLinkPreviewRequest](docs/Model/MessageLinkPreviewRequest.md)
- [MessageLocationRequest](docs/Model/MessageLocationRequest.md)
- [MessagePoll](docs/Model/MessagePoll.md)
- [MessagePollRequest](docs/Model/MessagePollRequest.md)
- [MessagePollVoteRequest](docs/Model/MessagePollVoteRequest.md)
- [MessageReactionRequest](docs/Model/MessageReactionRequest.md)
- [MessageReplyRequest](docs/Model/MessageReplyRequest.md)
- [MessageStarRequest](docs/Model/MessageStarRequest.md)
- [MessageTextRequest](docs/Model/MessageTextRequest.md)
- [MessageVideoRequest](docs/Model/MessageVideoRequest.md)
- [MessageVideoRequestFile](docs/Model/MessageVideoRequestFile.md)
- [MessageVoiceRequest](docs/Model/MessageVoiceRequest.md)
- [MessageVoiceRequestFile](docs/Model/MessageVoiceRequestFile.md)
- [MyProfile](docs/Model/MyProfile.md)
- [NewMessageIDResponse](docs/Model/NewMessageIDResponse.md)
- [NowebConfig](docs/Model/NowebConfig.md)
- [NowebStoreConfig](docs/Model/NowebStoreConfig.md)
- [OverviewBodyRequest](docs/Model/OverviewBodyRequest.md)
- [OverviewFilter](docs/Model/OverviewFilter.md)
- [OverviewPaginationParams](docs/Model/OverviewPaginationParams.md)
- [Participant](docs/Model/Participant.md)
- [ParticipantsRequest](docs/Model/ParticipantsRequest.md)
- [PinMessageRequest](docs/Model/PinMessageRequest.md)
- [PingResponse](docs/Model/PingResponse.md)
- [PollVote](docs/Model/PollVote.md)
- [PollVotePayload](docs/Model/PollVotePayload.md)
- [ProfileNameRequest](docs/Model/ProfileNameRequest.md)
- [ProfilePictureRequest](docs/Model/ProfilePictureRequest.md)
- [ProfilePictureRequestFile](docs/Model/ProfilePictureRequestFile.md)
- [ProfileStatusRequest](docs/Model/ProfileStatusRequest.md)
- [ProxyConfig](docs/Model/ProxyConfig.md)
- [QRCodeValue](docs/Model/QRCodeValue.md)
- [ReadChatMessagesResponse](docs/Model/ReadChatMessagesResponse.md)
- [RejectCallRequest](docs/Model/RejectCallRequest.md)
- [RemoteFile](docs/Model/RemoteFile.md)
- [ReplyToMessage](docs/Model/ReplyToMessage.md)
- [RequestCodeRequest](docs/Model/RequestCodeRequest.md)
- [Result](docs/Model/Result.md)
- [RetriesConfiguration](docs/Model/RetriesConfiguration.md)
- [Row](docs/Model/Row.md)
- [S3MediaData](docs/Model/S3MediaData.md)
- [ScreenshotControllerScreenshot200Response](docs/Model/ScreenshotControllerScreenshot200Response.md)
- [Section](docs/Model/Section.md)
- [SendButtonsRequest](docs/Model/SendButtonsRequest.md)
- [SendListMessage](docs/Model/SendListMessage.md)
- [SendListRequest](docs/Model/SendListRequest.md)
- [SendSeenRequest](docs/Model/SendSeenRequest.md)
- [ServerStatusResponse](docs/Model/ServerStatusResponse.md)
- [SessionConfig](docs/Model/SessionConfig.md)
- [SessionCreateRequest](docs/Model/SessionCreateRequest.md)
- [SessionDTO](docs/Model/SessionDTO.md)
- [SessionInfo](docs/Model/SessionInfo.md)
- [SessionInfoTimestamps](docs/Model/SessionInfoTimestamps.md)
- [SessionLogoutDeprecatedRequest](docs/Model/SessionLogoutDeprecatedRequest.md)
- [SessionStartDeprecatedRequest](docs/Model/SessionStartDeprecatedRequest.md)
- [SessionStatusPoint](docs/Model/SessionStatusPoint.md)
- [SessionStopDeprecatedRequest](docs/Model/SessionStopDeprecatedRequest.md)
- [SessionUpdateRequest](docs/Model/SessionUpdateRequest.md)
- [SetLabelsRequest](docs/Model/SetLabelsRequest.md)
- [SettingsSecurityChangeInfo](docs/Model/SettingsSecurityChangeInfo.md)
- [StopRequest](docs/Model/StopRequest.md)
- [StopResponse](docs/Model/StopResponse.md)
- [SubjectRequest](docs/Model/SubjectRequest.md)
- [TextStatus](docs/Model/TextStatus.md)
- [VCardContact](docs/Model/VCardContact.md)
- [VideoBinaryFile](docs/Model/VideoBinaryFile.md)
- [VideoFileDTO](docs/Model/VideoFileDTO.md)
- [VideoRemoteFile](docs/Model/VideoRemoteFile.md)
- [VideoStatus](docs/Model/VideoStatus.md)
- [VoiceBinaryFile](docs/Model/VoiceBinaryFile.md)
- [VoiceFileDTO](docs/Model/VoiceFileDTO.md)
- [VoiceRemoteFile](docs/Model/VoiceRemoteFile.md)
- [VoiceStatus](docs/Model/VoiceStatus.md)
- [WAHAChatPresences](docs/Model/WAHAChatPresences.md)
- [WAHAEnvironment](docs/Model/WAHAEnvironment.md)
- [WAHAEnvironmentWorker](docs/Model/WAHAEnvironmentWorker.md)
- [WAHAPresenceData](docs/Model/WAHAPresenceData.md)
- [WAHASessionPresence](docs/Model/WAHASessionPresence.md)
- [WAHAWebhookCallAccepted](docs/Model/WAHAWebhookCallAccepted.md)
- [WAHAWebhookCallReceived](docs/Model/WAHAWebhookCallReceived.md)
- [WAHAWebhookCallRejected](docs/Model/WAHAWebhookCallRejected.md)
- [WAHAWebhookChatArchive](docs/Model/WAHAWebhookChatArchive.md)
- [WAHAWebhookEngineEvent](docs/Model/WAHAWebhookEngineEvent.md)
- [WAHAWebhookEventResponse](docs/Model/WAHAWebhookEventResponse.md)
- [WAHAWebhookEventResponseFailed](docs/Model/WAHAWebhookEventResponseFailed.md)
- [WAHAWebhookGroupJoin](docs/Model/WAHAWebhookGroupJoin.md)
- [WAHAWebhookGroupLeave](docs/Model/WAHAWebhookGroupLeave.md)
- [WAHAWebhookLabelChatAdded](docs/Model/WAHAWebhookLabelChatAdded.md)
- [WAHAWebhookLabelChatDeleted](docs/Model/WAHAWebhookLabelChatDeleted.md)
- [WAHAWebhookLabelDeleted](docs/Model/WAHAWebhookLabelDeleted.md)
- [WAHAWebhookLabelUpsert](docs/Model/WAHAWebhookLabelUpsert.md)
- [WAHAWebhookMessage](docs/Model/WAHAWebhookMessage.md)
- [WAHAWebhookMessageAck](docs/Model/WAHAWebhookMessageAck.md)
- [WAHAWebhookMessageAckGroup](docs/Model/WAHAWebhookMessageAckGroup.md)
- [WAHAWebhookMessageAny](docs/Model/WAHAWebhookMessageAny.md)
- [WAHAWebhookMessageEdited](docs/Model/WAHAWebhookMessageEdited.md)
- [WAHAWebhookMessageReaction](docs/Model/WAHAWebhookMessageReaction.md)
- [WAHAWebhookMessageRevoked](docs/Model/WAHAWebhookMessageRevoked.md)
- [WAHAWebhookPollVote](docs/Model/WAHAWebhookPollVote.md)
- [WAHAWebhookPollVoteFailed](docs/Model/WAHAWebhookPollVoteFailed.md)
- [WAHAWebhookPresenceUpdate](docs/Model/WAHAWebhookPresenceUpdate.md)
- [WAHAWebhookSessionStatus](docs/Model/WAHAWebhookSessionStatus.md)
- [WAHAWebhookStateChange](docs/Model/WAHAWebhookStateChange.md)
- [WALocation](docs/Model/WALocation.md)
- [WAMedia](docs/Model/WAMedia.md)
- [WAMessage](docs/Model/WAMessage.md)
- [WAMessageAckBody](docs/Model/WAMessageAckBody.md)
- [WAMessageEditedBody](docs/Model/WAMessageEditedBody.md)
- [WAMessageReaction](docs/Model/WAMessageReaction.md)
- [WAMessageRevokedBody](docs/Model/WAMessageRevokedBody.md)
- [WANumberExistResult](docs/Model/WANumberExistResult.md)
- [WAReaction](docs/Model/WAReaction.md)
- [WASessionStatusBody](docs/Model/WASessionStatusBody.md)
- [WebhookConfig](docs/Model/WebhookConfig.md)
- [WebhookGroupV2Join](docs/Model/WebhookGroupV2Join.md)
- [WebhookGroupV2Leave](docs/Model/WebhookGroupV2Leave.md)
- [WebhookGroupV2Participants](docs/Model/WebhookGroupV2Participants.md)
- [WebhookGroupV2Update](docs/Model/WebhookGroupV2Update.md)
- [WebjsConfig](docs/Model/WebjsConfig.md)
- [WorkerInfo](docs/Model/WorkerInfo.md)

## Authorization

Authentication schemes defined for the API:
### api_key

- **Type**: API key
- **API key parameter name**: X-Api-Key
- **Location**: HTTP header


## Tests

To run the tests, use:

```bash
composer install
vendor/bin/phpunit
```

## Author



## About this package

This PHP package is automatically generated by the [OpenAPI Generator](https://openapi-generator.tech) project:

- API version: `2026.2.2`
    - Generator version: `7.6.0`
- Build package: `org.openapitools.codegen.languages.PhpClientCodegen`
