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
 * @since         2.13.0
 */
namespace Cipherguard\Folders\Test\TestCase\Controller\Resources;

use App\Model\Entity\Permission;
use App\Test\Factory\GroupFactory;
use App\Test\Factory\UserFactory;
use App\Test\Lib\Model\PermissionsModelTrait;
use App\Test\Lib\Utility\FixtureProviderTrait;
use App\Utility\UuidFactory;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;
use Cipherguard\Folders\Model\Behavior\FolderizableBehavior;
use Cipherguard\Folders\Test\Factory\FolderFactory;
use Cipherguard\Folders\Test\Factory\FoldersRelationFactory;
use Cipherguard\Folders\Test\Factory\ResourceFactory;
use Cipherguard\Folders\Test\Lib\FoldersIntegrationTestCase;
use Cipherguard\Folders\Test\Lib\Model\FoldersModelTrait;
use Cipherguard\Folders\Test\Lib\Model\FoldersRelationsModelTrait;
use Closure;

/**
 * @covers \App\Controller\Resources\ResourcesIndexController
 */
class ResourcesIndexControllerTest extends FoldersIntegrationTestCase
{
    use FixtureProviderTrait;
    use FoldersModelTrait;
    use FoldersRelationsModelTrait;
    use PermissionsModelTrait;

    public $fixtures = [
        'app.Base/Users', 'app.Base/Profiles', 'app.Base/Roles', 'app.Base/Groups', 'app.Base/GroupsUsers',
        'app.Base/Resources', 'app.Base/Favorites', 'app.Base/Permissions',
    ];

    /**
     * @param string $folderParentId Folder parent id
     * @param array $childrenFolders Children folders
     * @param string $userId user id
     */
    private function addFolderAndItsChildren(string $folderParentId, array $childrenFolders, string $userId)
    {
        $this->addFolderFor(['id' => $folderParentId], [$userId => Permission::OWNER]);
        foreach ($childrenFolders as $childrenFolderId) {
            $this->addResourceFor(
                ['id' => $childrenFolderId, 'folder_parent_id' => $folderParentId],
                [$userId => Permission::OWNER]
            );
        }
    }

    /**
     * @return array
     */
    public function provideFoldersIndexFilterHasParentSuccessRelations()
    {
        $fixture = function () {
            // Relations are expressed as follow: folder_parent_id => [child_folder_id]
            $folderRelations = [
                UuidFactory::uuid('folder.id.a') => [],
                UuidFactory::uuid('folder.id.c') => [
                    UuidFactory::uuid('resource.id.e'),
                ],
                UuidFactory::uuid('folder.id.d') => [
                    UuidFactory::uuid('resource.id.f'),
                    UuidFactory::uuid('resource.id.g'),
                ],
            ];

            $rootResources = [
                UuidFactory::uuid('resource.id.a'),
                UuidFactory::uuid('resource.id.c'),
                UuidFactory::uuid('resource.id.d'),
            ];

            $userId = UuidFactory::uuid('user.id.ada');

            foreach ($folderRelations as $folderParentId => $childrenFolders) {
                $this->addFolderAndItsChildren($folderParentId, $childrenFolders, $userId);
            }

            foreach ($rootResources as $resourceId) {
                $this->addResourceFor(['id' => $resourceId], [$userId => Permission::OWNER]);
            }
        };

        return [
            'When has parent is false' => [
                $fixture,
                [false],
                [
                    UuidFactory::uuid('resource.id.a'),
                    UuidFactory::uuid('resource.id.c'),
                    UuidFactory::uuid('resource.id.d'),
                ],
            ],
            'When has parent is false as a string' => [
                $fixture,
                ['false'],
                [
                    UuidFactory::uuid('resource.id.a'),
                    UuidFactory::uuid('resource.id.c'),
                    UuidFactory::uuid('resource.id.d'),
                ],
            ],
            'When has-parent is single and return only 1 item' => [
                $fixture,
                [
                    UuidFactory::uuid('folder.id.c'),
                ],
                [
                    UuidFactory::uuid('resource.id.e'),
                ],
            ],
            'When has-parent is single and return more than 1 item' => [
                $fixture,
                [
                    UuidFactory::uuid('folder.id.d'),
                ],
                [
                    UuidFactory::uuid('resource.id.f'),
                    UuidFactory::uuid('resource.id.g'),
                ],
            ],
            'When has-parent is multiple and return 1 item' => [
                $fixture,
                [
                    UuidFactory::uuid('folder.id.a'), // has no children
                    UuidFactory::uuid('folder.id.c'), // has 1 child
                ],
                [
                    UuidFactory::uuid('resource.id.e'),
                ],
            ],
            'When has-parent is multiple and return more than 1 item' => [
                $fixture,
                [
                    UuidFactory::uuid('folder.id.c'), // has 1 child
                    UuidFactory::uuid('folder.id.d'), // has 2 children
                ],
                [
                    UuidFactory::uuid('resource.id.e'),
                    UuidFactory::uuid('resource.id.f'),
                    UuidFactory::uuid('resource.id.g'),
                ],
            ],
            'When has-parent is mixed with root and ids' => [
                $fixture,
                [
                    false, // has no children
                    UuidFactory::uuid('folder.id.c'),
                ],
                [
                    UuidFactory::uuid('resource.id.e'),
                    UuidFactory::uuid('resource.id.a'),
                    UuidFactory::uuid('resource.id.c'),
                    UuidFactory::uuid('resource.id.d'),
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideFoldersIndexFilterHasParentSuccessRelations
     * @param Closure $fixture Fixture data
     * @param mixed $hasParentFilterId
     * @param array $expectedFolderChildrenIds
     * @return void
     */
    public function testResourcesIndexController_FilterHasParentSuccess(Closure $fixture, $hasParentFilterId, array $expectedFolderChildrenIds)
    {
        $this->executeFixture($fixture);

        TableRegistry::getTableLocator()->clear(); // We clean up the registry for clean initialization of the tables during tests.

        $queryParameters = http_build_query([
            'api-version' => 2,
            'filter' => [
                'has-parent' => $hasParentFilterId,
            ],
        ]);

        $this->authenticateAs('ada');
        $this->getJson('/resources.json?' . $queryParameters);
        $this->assertSuccess();

        $resultFolderIds = Hash::extract($this->_responseJsonBody, '{n}.id');

        foreach ($expectedFolderChildrenIds as $expectedFolderChildrenId) {
            $this->assertContains($expectedFolderChildrenId, $resultFolderIds, 'Expected children is missing for the given parent folder.');
        }
    }

    public function testResourcesIndexController_Personal_Should_Be_Unset_If_Null()
    {
        $user = $this->logInAsUser();
        ResourceFactory::make()->withPermissionsFor([$user])->persist();
        $this->getJson('/resources.json');

        $result = (array)$this->_responseJsonBody[0];
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayNotHasKey(FolderizableBehavior::PERSONAL_PROPERTY, $result);
    }

    public function testResourcesIndexController_FilterHasParentWithFolderAndGroup()
    {
        $ada = UserFactory::make()->user()->persist();
        $betty = UserFactory::make()->user()->persist();
        $group = GroupFactory::make()->withGroupsManagersFor([$ada])->withGroupsUsersFor([$betty])->persist();
        $resource1 = ResourceFactory::make()->withPermissionsFor([$group], Permission::READ)->persist();
        $resource2 = ResourceFactory::make()->withPermissionsFor([$group], Permission::READ)->persist();
        $folder = FolderFactory::make()->withPermissionsFor([$group], Permission::READ)->persist();
        FoldersRelationFactory::make()->foreignModelResource($resource1)->user($ada)->folderParent($folder)->persist();
        FoldersRelationFactory::make()->foreignModelResource($resource2)->user($ada)->folderParent($folder)->persist();
        FoldersRelationFactory::make()->foreignModelResource($resource1)->user($betty)->folderParent($folder)->persist();
        FoldersRelationFactory::make()->foreignModelResource($resource2)->user($betty)->folderParent($folder)->persist();
        // login with Ada
        $this->logInAs($ada);

        $this->getJson('/resources.json?filter[has-parent]=' . $folder->get('id'));

        $response = $this->getResponseBodyAsArray();
        $this->assertCount(2, $response);
        $expectedResourceIds = [$resource1->get('id'), $resource2->get('id')];
        $this->assertEqualsCanonicalizing($expectedResourceIds, [$response[0]['id'], $response[1]['id']]);
    }
}
