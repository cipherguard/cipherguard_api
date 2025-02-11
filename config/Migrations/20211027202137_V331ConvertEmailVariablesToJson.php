<?php
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
 * @since         3.4.0
 */
// @codingStandardsIgnoreStart
use Cake\Log\Log;
use Migrations\AbstractMigration;
use Cipherguard\EmailDigest\Service\ConvertEmailVariablesToJsonService;

class V331ConvertEmailVariablesToJson extends AbstractMigration
{
    /**
     * Converts the template variables emails in the queue from serialized format
     * to JSON.
     *
     * @return void
     */
    public function up(): void
    {
        try {
            (new ConvertEmailVariablesToJsonService())->convert();
        } catch (Throwable $e) {
            Log::error('There was an error in V331ConvertEmailVariablesToJson');
            Log::error($e->getMessage());
        }
    }
}
