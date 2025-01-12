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

namespace Cipherguard\PasswordExpiry\Controller;

use App\Controller\AppController;
use Cipherguard\PasswordExpiry\Service\Settings\PasswordExpiryGetSettingsServiceInterface;

class PasswordExpirySettingsGetController extends AppController
{
    /**
     * Returns Password expiry settings.
     *
     * @param \Cipherguard\PasswordExpiry\Service\Settings\PasswordExpiryGetSettingsServiceInterface $getSettingsService Get settings service instance.
     * @return void
     */
    public function get(PasswordExpiryGetSettingsServiceInterface $getSettingsService): void
    {
        $this->assertJson();
        $settings = $getSettingsService->get();
        $this->success(__('The operation was successful.'), $settings->toArray());
    }
}
