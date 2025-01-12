<?php
declare(strict_types=1);

/**
 * Cipherguard ~ Open source password manager for teams
 * Copyright (c) Cipherguard SA (https://www.cipherguard.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cipherguard SA (https://www.cipherguard.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.cipherguard.com Cipherguard(tm)
 * @since         4.5.0
 */

namespace Cipherguard\PasswordExpiry\Service\Settings;

use App\Utility\ExtendedUserAccessControl;
use Cipherguard\PasswordExpiry\Model\Dto\PasswordExpirySettingsDto;

interface PasswordExpirySetSettingsServiceInterface
{
    /**
     * Create Password expiry settings if not present already in DB or updates the settings value if already exists.
     *
     * @param \App\Utility\ExtendedUserAccessControl $uac Extended user access control.
     * @param array $data Payload
     * @return \Cipherguard\PasswordExpiry\Model\Dto\PasswordExpirySettingsDto
     * @throws \App\Error\Exception\FormValidationException if the data is not valid
     */
    public function createOrUpdate(ExtendedUserAccessControl $uac, array $data): PasswordExpirySettingsDto;
}
