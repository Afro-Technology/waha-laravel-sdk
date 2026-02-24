<?php

namespace Vendor\Waha\OpenApi\Tags;

use Vendor\Waha\OpenApi\WahaTagProxy;

/**
 * AUTO-GENERATED IDE HELPER (DO NOT EDIT MANUALLY)
 * Tag: 👤 Contacts
 *
 * @method mixed getAll(?string $sortBy = null, ?string $sortOrder = null, ?float $limit = null, ?float $offset = null, ?string $session = null)
 * @method mixed get(?string $contactId = null, ?string $session = null)
 * @method \Vendor\Waha\Generated\Model\WANumberExistResult checkExists(?string $phone = null, ?string $session = null)
 * @method mixed getAbout(?string $contactId = null, ?string $session = null)
 * @method mixed getProfilePicture(?string $contactId = null, ?bool $refresh = false, ?string $session = null)
 * @method mixed block(string $contactId, ?string $session = null)
 * @method mixed unblock(string $contactId, ?string $session = null)
 * @method \Vendor\Waha\Generated\Model\Result put(string $chatId, string $firstName, string $lastName, ?string $session = null)
 * @method array getAll(?float $limit = 100, ?float $offset = 0, ?string $session = null)
 * @method \Vendor\Waha\Generated\Model\CountResponse getLidsCount(?string $session = null)
 * @method \Vendor\Waha\Generated\Model\LidToPhoneNumber findPNByLid(string $lid, ?string $session = null)
 * @method \Vendor\Waha\Generated\Model\LidToPhoneNumber findLIDByPhoneNumber(string $phoneNumber, ?string $session = null)
 */
final class ContactsTag extends WahaTagProxy {}
