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
 * @since         3.5.0
 */

namespace App\Service\Setup;

interface RecoverCompleteServiceInterface
{
    public const COMPLETE_SUCCESS_EVENT_NAME = 'RecoverCompleteServiceInterface.complete.success';

    /**
     * Retrieves user and token information for the setup controllers
     *
     * @param string $userId User ID
     * @return void
     */
    public function complete(string $userId): void;
}
