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
 * @since         4.9.0
 */

namespace App\Test\TestCase\Service\Resources;

use App\Error\Exception\ValidationException;
use App\Model\Entity\Permission;
use App\Model\Entity\Role;
use App\Service\Resources\ResourcesExpireResourcesFallbackServiceService;
use App\Service\Resources\ResourcesShareService;
use App\Test\Factory\FavoriteFactory;
use App\Test\Factory\GroupFactory;
use App\Test\Factory\ResourceFactory;
use App\Test\Factory\RoleFactory;
use App\Test\Factory\UserFactory;
use App\Test\Lib\AppTestCase;
use App\Utility\UuidFactory;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

/**
 * \App\Test\TestCase\Service\Resources\ResourcesShareServiceWithFactoriesTest Test Case
 *
 * @covers \App\Test\TestCase\Service\Resources\ResourcesShareServiceWithFactoriesTest
 */
class ResourcesShareServiceWithFactoriesTest extends AppTestCase
{
    /**
     * @var ResourcesTable $Resources
     */
    public $Resources;

    /**
     * @var FavoritesTable $Favorites
     */
    public $Favorites;

    /**
     * @var PermissionsTable $Permissions
     */
    public $Permissions;

    /**
     * @var UsersTable $Users
     */
    public $Users;

    /**
     * @var ResourcesShareService $service
     */
    public $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->Favorites = TableRegistry::getTableLocator()->get('Favorites');
        $this->Resources = TableRegistry::getTableLocator()->get('Resources');
        $this->Permissions = TableRegistry::getTableLocator()->get('Permissions');
        $this->Users = TableRegistry::getTableLocator()->get('Users');
        $this->service = new ResourcesShareService(
            new ResourcesExpireResourcesFallbackServiceService()
        );
        RoleFactory::make()->guest()->persist();
    }

    public function tearDown(): void
    {
        unset($this->Resources);
        unset($this->Permissions);
        unset($this->Users);

        parent::tearDown();
    }

    protected function getValidSecret()
    {
        return '-----BEGIN PGP MESSAGE-----
Version: GnuPG v1.4.12 (GNU/Linux)

hQEMAwvNmZMMcWZiAQf9HpfcNeuC5W/VAzEtAe8mTBUk1vcJENtGpMyRkVTC8KbQ
xaEr3+UG6h0ZVzfrMFYrYLolS3fie83cj4FnC3gg1uijo7zTf9QhJMdi7p/ASB6N
y7//8AriVqUAOJ2WCxAVseQx8qt2KqkQvS7F7iNUdHfhEhiHkczTlehyel7PEeas
SdM/kKEsYKk6i4KLPBrbWsflFOkfQGcPL07uRK3laFz8z4LNzvNQOoU7P/C1L0X3
tlK3vuq+r01zRwmflCaFXaHVifj3X74ljhlk5i/JKLoPRvbxlPTevMNag5e6QhPQ
kpj+TJD2frfGlLhyM50hQMdJ7YVypDllOBmnTRwZ0tJFAXm+F987ovAVLMXGJtGO
P+b3c493CfF0fQ1MBYFluVK/Wka8usg/b0pNkRGVWzBcZ1BOONYlOe/JmUyMutL5
hcciUFw5
=TcQF
-----END PGP MESSAGE-----';
    }

    public function testResourceShareService_Success_WithFactories()
    {
        [$userA, $userB, $userC, $userD, $userE, $userF, $userG, $userH, $userI] = UserFactory::make(9)->user()
            ->persist();
        $uac = $this->makeUac($userA);

        // Groups
        $groupA = GroupFactory::make()->persist();
        $groupB = GroupFactory::make()
            ->withGroupsUsersFor([$userD, $userE, $userF, $userG, $userH])
            ->persist();
        $groupC = GroupFactory::make()
            ->withGroupsUsersFor([$userI])
            ->persist();

        // Define actors of this tests
        $resource = ResourceFactory::make()
            ->withSecretsFor([$userA, $userB, $userC, $userD, $userE, $userF, $userG, $userH, $userI])
            ->withPermissionsFor([$userA, $groupB])
            ->withPermissionsFor([$userB, $groupA], Permission::READ)
            ->persist();

        // Permissions
        $permissionUserAId = $resource->permissions[0]->id;
        $permissionGroupBId = $resource->permissions[1]->id;
        $permissionUserBId = $resource->permissions[2]->id;
        $permissionGroupAId = $resource->permissions[3]->id;
        // Expected results.
        $expectedAddedUsersIds = [];
        $expectedRemovedUsersIds = [];

        // Build the changes.
        $changes = [];
        $secrets = [];

        // Users permissions changes.
        // Change the permission of the user Ada to read (no users are expected to be added or removed).

        $changes[] = ['id' => $permissionUserAId, 'type' => Permission::READ];
        // Delete the permission of the userB.
        $changes[] = ['id' => $permissionUserBId, 'delete' => true];
        $expectedRemovedUsersIds[] = $userB->id;
        // Add an owner permission for the userC
        $changes[] = ['aro' => 'User', 'aro_foreign_key' => $userC->id, 'type' => Permission::OWNER];
        $secrets[] = ['user_id' => $userC->id, 'data' => $this->getValidSecret()];
        $expectedAddedUsersIds[] = $userC->id;

        // Groups permissions changes.
        // Change the permission of the groupB (no users are expected to be added or removed).

        $changes[] = ['id' => $permissionGroupAId, 'type' => Permission::OWNER];
        // Delete the permission of the group Freelancer.
        $changes[] = ['id' => $permissionGroupBId, 'delete' => true];
        $expectedRemovedUsersIds = array_merge($expectedRemovedUsersIds, [$userD->id, $userE->id, $userF->id,
            $userG->id,
            $userH->id]);
        // Add a read permission for the group Accounting.
        $changes[] = ['aro' => 'Group', 'aro_foreign_key' => $groupC->id, 'type' => Permission::READ];
        $secrets[] = ['user_id' => $userI->id, 'data' => $this->getValidSecret()];
        $expectedAddedUsersIds = array_merge($expectedAddedUsersIds, [$userI->id]);

        // Share
        $resource = $this->service->share($uac, $resource->id, $changes, $secrets);
        $this->assertFalse($resource->hasErrors());

        // Load the resource.
        $resource = $this->Resources->get($resource->id, ['contain' => ['Permissions', 'Secrets']]);

        // Verify that all the allowed users have a secret for the resource.
        $secretsUsersIds = Hash::extract($resource->secrets, '{n}.user_id');
        $hasAccessUsers = $this->Users->findIndex(Role::USER, ['filter' => ['has-access' => [$resource->id]]])->all()
            ->toArray();
        $hasAccessUsersIds = Hash::extract($hasAccessUsers, '{n}.id');
        $this->assertEquals(count($secretsUsersIds), count($hasAccessUsersIds));
        $this->assertEmpty(array_diff($secretsUsersIds, $hasAccessUsersIds));

        // Ensure that the newly added users have a secret, and are allowed to access the resource.
        foreach ($expectedAddedUsersIds as $userId) {
            $this->assertContains($userId, $secretsUsersIds);
            $this->assertContains($userId, $hasAccessUsersIds);
        }
        // Ensure that the removed users don't have a secret, and are no more allowed to access the resource.
        foreach ($expectedRemovedUsersIds as $userId) {
            $this->assertNotContains($userId, $secretsUsersIds);
            $this->assertNotContains($userId, $hasAccessUsersIds);
        }
    }

    /* SHARE */

    public function testResourceShareService_LostAccessFavoritesDeleted_WithFactories()
    {
        [$userA, $userB] = UserFactory::make(2)->user()->persist();
        $uac = $this->makeUac($userA);

        // Define actors of this tests
        $resource = ResourceFactory::make()
            ->withSecretsFor([$userA, $userB])
            ->withPermissionsFor([$userA])
            ->withPermissionsFor([$userB], Permission::READ)
            ->persist();
        $resourceB = ResourceFactory::make()->withSecretsFor([$userB])->persist();

        // Permissions
        $permissionUserBId = $resource->permissions[1]->id;

        // Build the changes.
        $changes = [];
        $secrets = [];

        // Expected results.
        $expectedRemovedUsersIds = [];

        // Users permissions changes.
        // Delete the permission of the user Betty.

        $changes[] = ['id' => $permissionUserBId, 'delete' => true];
        $expectedRemovedUsersIds[] = $userB->id;

        // Share.
        $resource = $this->service->share($uac, $resource->id, $changes, $secrets);
        $this->assertFalse($resource->hasErrors());

        FavoriteFactory::make()->setUser($userB)->setResource($resourceB)->persist();

        // Ensure the apache favorite for Dame is deleted
        // But the other favorites for this resource are not touched.
        $resources = $this->Favorites->find()
            ->where(['user_id' => $userB->id])
            ->all();
        $resourcesId = Hash::extract($resources->toArray(), '{n}.foreign_key');
        $this->assertNotContains($resource->id, $resourcesId);
        $this->assertcontains($resourceB->id, $resourcesId);
    }

    public function testResourceShareService_ValidationError_WithFactories()
    {
        [$userA, $userE] = UserFactory::make(2)->user()->persist();
        $uac = $this->makeUac($userA);
        $resourceA = ResourceFactory::make()
            ->withPermissionsFor([$userA])
            ->persist();
        $resourceAPermissionId = $resourceA->permissions[0]->id;
        $resourceB = ResourceFactory::make()
            ->withPermissionsFor([$userE], Permission::READ)
            ->persist();
        $resourceBPermissionId = $resourceB->permissions[0]->id;

        $testCases = [
            'cannot update a permission that does not exist' => [
                'errorField' => 'permissions.0.id.exists',
                'data' => [
                    'permissions' => [[
                        'id' => UuidFactory::uuid()]]],
            ],
            'cannot delete a permission of another resource' => [
                'errorField' => 'permissions.0.id.exists',
                'data' => [
                    'permissions' => [[
                        'id' => $resourceBPermissionId,
                        'delete' => true]],
                ],
            ],
            'cannot add a permission with invalid data' => [
                'errorField' => 'permissions.0.aro_foreign_key._empty',
                'data' => [
                    'permissions' => [
                        ['aro' => 'User', 'type' => Permission::OWNER]]],
            ],
            'cannot update a permission with a wrong permission type' => [
                'errorField' => 'permissions.0.type.inList',
                'data' => [
                    'permissions' => [[
                        'id' => $resourceAPermissionId, 'type' => 42]]],
            ],
            'cannot add a secret with invalid data' => [
                'errorField' => 'secrets.0.data.isValidOpenPGPMessage',
                'data' => [
                    'permissions' => [[
                        'aro' => 'User', 'aro_foreign_key' => $userE->id, 'type' => Permission::READ]],
                    'secrets' => [[
                        'user_id' => $userE->id, 'data' => 'INVALID GPG MESSAGE']],
                ],
            ],
            // Test build rules.
            'cannot remove the latest owner' => [
                'errorField' => 'permissions.at_least_one_owner',
                'data' => [
                    'permissions' => [[
                        'id' => $resourceAPermissionId,
                        'delete' => true]],
                ],
            ],
            'cannot add a permissions for a deleted user' => [
                'errorField' => 'permissions.0.aro_foreign_key.aro_exists',
                'data' => [
                    'permissions' => [[
                        'aro' => 'User',
                        'aro_foreign_key' => UserFactory::make()->user()->deleted()->persist()->id,
                        'type' => Permission::OWNER]]],
            ],
            'cannot add a permissions for an inactive user' => [
                'errorField' => 'permissions.0.aro_foreign_key.aro_exists',
                'data' => [
                    'permissions' => [[
                        'aro' => 'User',
                        'aro_foreign_key' => UserFactory::make()->user()->inactive()->persist()->id,
                        'type' => Permission::OWNER]]],
            ],
        ];

        foreach ($testCases as $caseLabel => $case) {
            $permissions = Hash::get($case, 'data.permissions', []);
            $secrets = Hash::get($case, 'data.secrets', []);
            try {
                $this->service->share($uac, $resourceA->id, $permissions, $secrets);
            } catch (ValidationException $e) {
                $this->assertEquals('Could not validate resource data.', $e->getMessage());
                $error = Hash::get($e->getErrors(), $case['errorField']);
                $this->assertNotNull($error, "Expected error not found : {$case['errorField']}. Errors: " . json_encode($e->getErrors()));

                continue;
            }
            $this->assertFalse(true, 'The test should throw an exception.');
        }
    }

    public function testResourceShareService_Error_ResourceIsSoftDeleted_WithFactories()
    {
        $user = UserFactory::make()->user()->persist();
        $uac = $this->makeUac($user);
        $resourceId = ResourceFactory::make()->deleted()->persist()->id;
        $data = [[
            'aro' => 'User',
            'aro_foreign_key' => $user->id,
            'type' => Permission::OWNER,
        ]];
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('The resource does not exist.');
        $this->service->share($uac, $resourceId, $data);
    }

    /* SHARE DRY RUN */

    public function testResourceShareService_DryRun_Success_WithFactories()
    {
        [$userA, $userB, $userC, $userD, $userE, $userF, $userG, $userH, $userI] = UserFactory::make(10)->user()
            ->persist();
        $uac = $this->makeUac($userA);

        // Groups
        $groupA = GroupFactory::make()->persist();
        $groupB = GroupFactory::make()->withGroupsUsersFor([$userD, $userE, $userF, $userG, $userH])->persist();
        $groupC = GroupFactory::make()->withGroupsUsersFor([$userI])->persist();

        // Define actors of this tests
        $resource = ResourceFactory::make()->withSecretsFor([$userA, $userB, $userC, $userD, $userE, $userF, $userG, $userH, $userI])
            ->withPermissionsFor([$userA, $groupB])
            ->withPermissionsFor([$userB, $groupA], Permission::READ)
            ->persist();

        $permissionUserAId = $resource->permissions[0]->id;
        $permissionGroupBId = $resource->permissions[1]->id;
        $permissionUserBId = $resource->permissions[2]->id;
        $permissionGroupAId = $resource->permissions[3]->id;

        // Expected results.
        $expectedAddedUsersIds = [];
        $expectedRemovedUsersIds = [];

        // Build the changes.
        $changes = [];

        // Users permissions changes.
        // Change the permission of the user Ada to read (no users are expected to be added or removed).

        $changes[] = ['id' => $permissionUserAId, 'type' => Permission::READ];
        // Delete the permission of the user B.
        $changes[] = ['id' => $permissionUserBId, 'delete' => true];
        $expectedRemovedUsersIds[] = $userB->id;
        // Add an owner permission for the user C
        $changes[] = ['aro' => 'User', 'aro_foreign_key' => $userC->id, 'type' => Permission::OWNER];
        $expectedAddedUsersIds[] = $userC->id;

        // Groups permissions changes.
        // Change the permission of the group Board (no users are expected to be added or removed).
        $changes[] = ['id' => $permissionGroupAId, 'type' => Permission::OWNER];
        // Delete the permission of the group Freelancer.
        $changes[] = ['id' => $permissionGroupBId, 'delete' => true];
        $expectedRemovedUsersIds = array_merge($expectedRemovedUsersIds, [$userD->id, $userE->id, $userF->id,
            $userG->id,
            $userH->id]);
        // Add a read permission for the group Accounting.
        $changes[] = ['aro' => 'Group', 'aro_foreign_key' => $groupC->id, 'type' => Permission::READ];
        $expectedAddedUsersIds = array_merge($expectedAddedUsersIds, [$userI->id]);

        // Share dry run.
        $result = $this->service->shareDryRun($uac, $resource->id, $changes);
        $this->assertNotEmpty($result);
        $this->assertNotEmpty($result['added']);
        $addedUsersIds = $result['added'];
        $this->assertNotEmpty($result['deleted']);
        $removedUsersIds = $result['deleted'];

        // Assert the results.
        $this->assertCount(count($expectedAddedUsersIds), $addedUsersIds);
        $this->assertCount(count($expectedRemovedUsersIds), $removedUsersIds);
        $this->assertEmpty(array_diff($expectedAddedUsersIds, $addedUsersIds));
        $this->assertEmpty(array_diff($expectedRemovedUsersIds, $removedUsersIds));
    }

    /*
     * The format validation is done by the Permissions model.
     * @see App\Test\TestCase\Model\Table\Permissions\PatchEntitiesWithChangesTest
     */

    public function testResourceShareService_DryRun_ValidationError_WithFactories()
    {
        $userA = UserFactory::make()->user()->persist();
        $uac = $this->makeUac($userA);

        $resourceApache = ResourceFactory::make()
            ->withPermissionsFor([$userA])
            ->persist();
        $resourceApacheId = $resourceApache->id;
        $resourceApachePermissionId = $resourceApache->permissions[0]->id;
        $resourceApril = ResourceFactory::make()
            ->withPermissionsFor([UserFactory::make()->user()->persist()])
            ->persist();
        $resourceAprilPermissionId = $resourceApril->permissions[0]->id;
        $testCases = [
            // Check some validation format rules, just to ensure they are well returned by the
            // PatchEntitiesWithChanges function
            'cannot update a permission that does not exist' => [
                'errorField' => 'permissions.0.id.exists',
                'data' => [['id' => UuidFactory::uuid()]],
            ],
            'cannot delete a permission of another resource' => [
                'errorField' => 'permissions.0.id.exists',
                'data' => [[
                    'id' => $resourceAprilPermissionId,
                    'delete' => true]],
            ],
            'cannot add a permission with invalid data' => [
                'errorField' => 'permissions.0.aro_foreign_key._empty',
                'data' => [['aro' => 'User', 'type' => Permission::OWNER]],
            ],
            'cannot update a permission with a wrong permission type' => [
                'errorField' => 'permissions.0.type.inList',
                'data' => [['id' => $resourceApachePermissionId, 'type' => 42]],
            ],
            // Test build rules.
            'cannot remove the latest owner' => [
                'errorField' => 'permissions.at_least_one_owner',
                'data' => [[
                    'id' => $resourceApachePermissionId,
                    'delete' => true]],
            ],
            'cannot add a permissions for a deleted user' => [
                'errorField' => 'permissions.0.aro_foreign_key.aro_exists',
                'data' => [[
                    'aro' => 'User',
                    'aro_foreign_key' => UserFactory::make()->user()->deleted()->persist()->id,
                    'type' => Permission::OWNER]],
            ],
            'cannot add a permissions for an inactive user' => [
                'errorField' => 'permissions.0.aro_foreign_key.aro_exists',
                'data' => [[
                    'aro' => 'User',
                    'aro_foreign_key' => UserFactory::make()->user()->inactive()->persist()->id,
                    'type' => Permission::OWNER]],
            ],
        ];

        foreach ($testCases as $case) {
            try {
                $this->service->shareDryRun($uac, $resourceApacheId, $case['data']);
            } catch (ValidationException $e) {
                $this->assertEquals('Could not validate resource data.', $e->getMessage());
                $error = Hash::get($e->getErrors(), $case['errorField']);
                $this->assertNotNull($error, "Expected error not found : {$case['errorField']}. Errors: " . json_encode($e->getErrors()));

                continue;
            }
            $this->assertFalse(true, 'The test should throw an exception.');
        }
    }

    /**
     * @throws Exception
     */
    public function testResourceShareService_DryRun_Error_ResourceIsSoftDeleted_WithFactories()
    {
        $userA = UserFactory::make()->user()->persist();
        $uac = $this->makeUac($userA);

        $resourceId = ResourceFactory::make()
            ->deleted()
            ->persist()
            ->id;
        $data = [[
            'aro' => 'User',
            'aro_foreign_key' => $userA->id,
            'type' => Permission::OWNER,
        ]];
        $this->expectException(NotFoundException::class);
        $this->service->shareDryRun($uac, $resourceId, $data);
    }
}
