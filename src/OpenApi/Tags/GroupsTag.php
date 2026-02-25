<?php

namespace Vendor\Waha\OpenApi\Tags;

use Vendor\Waha\OpenApi\WahaTagProxy;

/**
 * AUTO-GENERATED IDE HELPER (DO NOT EDIT MANUALLY)
 * Tag: 👥 Groups
 *
 * @method mixed createGroup(string $name, array $participants, ?string $session = null)
 * @method array getGroups(?string $sortBy = null, ?string $sortOrder = null, ?float $limit = null, ?float $offset = null, ?array $exclude = null, ?string $session = null)
 * @method array joinInfoGroup(?string $code = null, ?string $session = null)
 * @method \Vendor\Waha\Generated\Model\JoinGroupResponse joinGroup(string $code, ?string $session = null)
 * @method \Vendor\Waha\Generated\Model\CountResponse getGroupsCount(?string $session = null)
 * @method mixed refreshGroups(?string $session = null)
 * @method mixed getGroup(string $id, ?string $session = null)
 * @method mixed deleteGroup(string $id, ?string $session = null)
 * @method mixed leaveGroup(string $id, ?string $session = null)
 * @method \Vendor\Waha\Generated\Model\ChatPictureResponse getChatPicture(string $id, ?bool $refresh = false, ?string $session = null)
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
 */
final class GroupsTag extends WahaTagProxy
{
}
