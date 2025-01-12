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
 * @since         4.6.0
 */

use Cake\Log\Log;
use Migrations\AbstractMigration;
use Cipherguard\Rbacs\Service\Rbacs\RbacsInsertDefaultsService;

class V460ShareFolderDefaultRbacsUiActions extends AbstractMigration
{
    /**
     * Up
     *
     * @throws \Exception if insertion fails
     * @return void
     */
    public function up()
    {
        try {
            (new RbacsInsertDefaultsService())->allowAllUiActionsForUsers();
        } catch (\Throwable $e) {
            Log::error('There was an error in V460ShareFolderDefaultRbacsUiActions');
            Log::error($e->getMessage());
        }
    }
}
