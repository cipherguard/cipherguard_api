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

use Cipherguard\PasswordExpiry\Model\Dto\PasswordExpirySettingsDto;

interface PasswordExpiryGetSettingsServiceInterface
{
    /**
     * Returns Password expiry settings.
     *
     * @return \Cipherguard\PasswordExpiry\Model\Dto\PasswordExpirySettingsDto
     * @throws \Cake\Http\Exception\InternalErrorException When value is not an array.
     */
    public function get(): PasswordExpirySettingsDto;
}
