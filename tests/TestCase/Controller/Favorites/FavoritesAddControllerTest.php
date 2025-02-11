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
 * @since         2.0.0
 */

namespace App\Test\TestCase\Controller\Favorites;

use App\Model\Table\FavoritesTable;
use App\Test\Factory\ResourceFactory;
use App\Test\Factory\UserFactory;
use App\Test\Lib\AppIntegrationTestCase;
use App\Test\Lib\Model\FavoritesModelTrait;
use Cake\ORM\TableRegistry;

class FavoritesAddControllerTest extends AppIntegrationTestCase
{
    use FavoritesModelTrait;

    /**
     * @var FavoritesTable
     */
    private $Favorites;

    public function setUp(): void
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('Favorites') ? [] : ['className' => FavoritesTable::class];
        $this->Favorites = TableRegistry::getTableLocator()->get('Favorites', $config);
    }

    public function testFavoritesAddController_Success(): void
    {
        $user = UserFactory::make()->user()->persist();
        $resource = ResourceFactory::make()->withCreatorAndPermission($user)->persist();
        $this->logInAs($user);

        $resourceId = $resource->get('id');
        $this->postJson("/favorites/resource/$resourceId.json");
        $this->assertSuccess();

        // Expected fields.
        $this->assertFavoriteAttributes($this->_responseJsonBody);
    }

    public function testFavoritesAddController_CannotModifyNotAccessibleFields(): void
    {
        $user = UserFactory::make()->user()->persist();
        $resource = ResourceFactory::make()->withCreatorAndPermission($user)->persist();
        $resourceId = $resource->get('id');
        $this->logInAs($user);

        $favoriteData = [
            'id' => 'modified_id',
            'foreign_model' => 'modified_foreign_model',
            'foreign_key' => 'modified_foreign_key',
            'created' => '2019-07-29 10:31:35',
            'modified' => '2019-07-29 10:31:35',
            'user_id' => 'modified_user_id',
        ];

        $this->postJson("/favorites/resource/$resourceId.json", $favoriteData);
        $this->assertSuccess();

        /** @var Favorite $favorite */
        $favorite = $this->Favorites->find()
            ->where(['foreign_key' => $resourceId])
            ->first();

        $this->assertNotEquals($favoriteData['id'], $favorite->id);
        $this->assertNotEquals($favoriteData['foreign_model'], $favorite->foreign_model);
        $this->assertNotEquals($favoriteData['foreign_key'], $favorite->foreign_key);
        $this->assertNotEquals($favoriteData['modified'], $favorite->modified);
        $this->assertNotEquals($favoriteData['created'], $favorite->created);
        $this->assertNotEquals($favoriteData['user_id'], $favorite->user_id);
    }

    public function testFavoritesAddController_ErrorCsrfToken(): void
    {
        $this->disableCsrfToken();

        $user = UserFactory::make()->user()->persist();
        $resource = ResourceFactory::make()->withCreatorAndPermission($user)->persist();
        $resourceId = $resource->get('id');
        $this->logInAs($user);

        $this->post("/favorites/resource/$resourceId.json");

        $this->assertResponseCode(403);
    }

    public function testFavoritesAddController_ErrorNotValidId(): void
    {
        $user = UserFactory::make()->user()->persist();
        $this->logInAs($user);
        $resourceId = 'invalid-id';

        $this->postJson("/favorites/resource/$resourceId.json");

        $this->assertError(400, 'The resource identifier should be a valid UUID.');
    }

    public function testFavoritesAddController_ErrorNotAuthenticated(): void
    {
        $user = UserFactory::make()->user()->persist();
        $resource = ResourceFactory::make()->withCreatorAndPermission($user)->persist();
        $resourceId = $resource->get('id');

        $this->postJson("/favorites/resource/$resourceId.json");

        $this->assertAuthenticationError();
    }

    /**
     * Check that calling url without JSON extension throws a 404
     */
    public function testFavoritesAddController_Error_NotJson(): void
    {
        $user = UserFactory::make()->user()->persist();
        $resource = ResourceFactory::make()->withCreatorAndPermission($user)->persist();
        $this->logInAs($user);

        $resourceId = $resource->get('id');
        $this->post("/favorites/resource/$resourceId");
        $this->assertResponseCode(404);
    }
}
