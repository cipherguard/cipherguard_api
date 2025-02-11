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
 * @since         2.4.0
 */
namespace Cipherguard\MultiFactorAuthentication\Controller\Yubikey;

use Cipherguard\MultiFactorAuthentication\Controller\MfaSetupDeleteController;
use Cipherguard\MultiFactorAuthentication\Utility\MfaSettings;

class YubikeySetupDeleteController extends MfaSetupDeleteController
{
    /**
     * Delete Totp setup
     *
     * @return void
     */
    public function delete()
    {
        $this->_handleDelete(MfaSettings::PROVIDER_YUBIKEY);
    }
}
