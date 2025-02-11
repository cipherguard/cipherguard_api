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
namespace Cipherguard\Log\Test\TestCase\Model\Traits;

trait PermissionsHistoryTrait
{
    public function assertPermissionHistoryExists($conditions)
    {
        $permissionHistory = $this->PermissionsHistory
            ->find()
            ->where($conditions)
            ->first();

        $this->assertNotEmpty($permissionHistory, 'No corresponding permissionsHistory could be found');

        return $permissionHistory;
    }

    public function assertPermissionsHistoryCount($count, ?array $conditions = [])
    {
        $entityHistoryCount = $this->EntitiesHistory
            ->find()
            ->count();

        $this->assertEquals($entityHistoryCount, $count);
    }

    public function assertOnePermissionHistory(?array $conditions = [])
    {
        return $this->assertPermissionsHistoryCount(1);
    }

    public function assertPermissionsHistoryEmpty()
    {
        $this->assertPermissionsHistoryCount(0);
    }
}
