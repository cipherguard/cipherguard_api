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

namespace App\Test\TestCase\Model\Table\Traits\PermissionsFinder;

use App\Model\Table\PermissionsTable;
use App\Test\Factory\GroupFactory;
use App\Test\Factory\ResourceFactory;
use App\Test\Factory\UserFactory;
use Cake\Http\Exception\BadRequestException;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;

/**
 * @see \App\Model\Traits\Permissions\PermissionsFindersTrait::findAllByAro()
 */
class FindAllByAroTest extends TestCase
{
    use TruncateDirtyTables;

    public function testFindAllByAro_No_UUID_Should_Throw_An_Exception()
    {
        /** @var PermissionsTable $table */
        $table = TableRegistry::getTableLocator()->get('Permissions');

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('The identifier should be a valid UUID.');
        $table->findAllByAro(PermissionsTable::RESOURCE_ACO, 'foo');
    }

    public function testFindAllByAro_NoDirectOrInherited()
    {
        /** @var PermissionsTable $table */
        $table = TableRegistry::getTableLocator()->get('Permissions');

        // No permissions to retrieve for the user A.
        $userA = UserFactory::make()->persist();

        // Witness permissions to not retrieve.
        // A resource having a permission for another user than A.
        $userB = UserFactory::make()->persist();
        ResourceFactory::make()->withPermissionsFor([$userB])->persist();
        // A resource having a permission for another user and another group than A.
        $groupA = GroupFactory::make()->persist();
        ResourceFactory::make()->withPermissionsFor([$userB, $groupA])->persist();

        // Find all the direct permissions for the user, excluding the inherited ones.
        $result = $table->findAllByAro(PermissionsTable::RESOURCE_ACO, $userA->id);
        $this->assertEmpty($result->toArray());

        // Find all the direct and inherited permissions for the user
        $result = $table->findAllByAro(PermissionsTable::RESOURCE_ACO, $userA->id, ['checkGroupsUsers' => true]);
        $this->assertEmpty($result->toArray());
    }

    public function testFindAllByAro_WithDirectAndInherited()
    {
        /** @var PermissionsTable $table */
        $table = TableRegistry::getTableLocator()->get('Permissions');

        // Permissions to retrieve.
        // A direct permission on a personal resource
        $userA = UserFactory::make()->persist();
        $resourcesPersonalWithDirectPermission = ResourceFactory::make()->withPermissionsFor([$userA])->persist();
        // A direct permission on a shared resource
        $userB = UserFactory::make()->persist();
        $resourcesSharedWithDirectPermissions = ResourceFactory::make()->withPermissionsFor([$userA, $userB])->persist();
        // An inherited permission on a resource
        $groupA = GroupFactory::make()->withGroupsUsersFor([$userA])->persist();
        $resourcesSharedWithInheritedPermissions = ResourceFactory::make()->withPermissionsFor([$groupA])->persist();

        // Witness permissions to not retrieve.
        // A resource having a permission for another user than A.
        $userB = UserFactory::make()->persist();
        ResourceFactory::make()->withPermissionsFor([$userB])->persist();
        // A resource having a permission for another user and another group than A.
        $groupA = GroupFactory::make()->persist();
        ResourceFactory::make()->withPermissionsFor([$userB, $groupA])->persist();

        // Find all the direct permissions for the user, excluding the inherited ones.
        $result = $table->findAllByAro(PermissionsTable::RESOURCE_ACO, $userA->id)
            ->all()
            ->extract('id');
        $this->assertCount(2, $result);
        $this->assertContains($resourcesPersonalWithDirectPermission->permissions[0]->id, $result);
        $this->assertContains($resourcesSharedWithDirectPermissions->permissions[0]->id, $result);

        // Find all the direct and inherited permissions for the user
        $result = $table->findAllByAro(PermissionsTable::RESOURCE_ACO, $userA->id, ['checkGroupsUsers' => true])
            ->all()
            ->extract('id');
        $this->assertCount(3, $result);
        $this->assertContains($resourcesPersonalWithDirectPermission->permissions[0]->id, $result);
        $this->assertContains($resourcesSharedWithDirectPermissions->permissions[0]->id, $result);
        $this->assertContains($resourcesSharedWithInheritedPermissions->permissions[0]->id, $result);
    }
}
