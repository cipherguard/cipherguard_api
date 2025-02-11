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
 */
namespace Cipherguard\Log\Test\Lib\Traits;

trait SecretsHistoryTestTrait
{
    public function assertSecretHistoryExists($conditions)
    {
        $secretHistory = $this->SecretsHistory
            ->find()
            ->where($conditions)
            ->first();
        $this->assertNotEmpty($secretHistory, 'No corresponding secretsHistory could be found');

        return $secretHistory;
    }

    public function assertSecretsHistoryCount($expectedCount)
    {
        $count = $this->SecretsHistory
            ->find()
            ->count();
        $this->assertEquals($expectedCount, $count);
    }
}
