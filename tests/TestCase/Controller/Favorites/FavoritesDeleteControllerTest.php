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

use App\Test\Factory\FavoriteFactory;
use App\Test\Factory\ResourceFactory;
use App\Test\Factory\UserFactory;
use App\Test\Lib\AppIntegrationTestCase;

class FavoritesDeleteControllerTest extends AppIntegrationTestCase
{
    public function testFavoritesDeleteController_Success(): void
    {
        $user = UserFactory::make()->user()->persist();
        $resource = ResourceFactory::make()->withCreatorAndPermission($user)->persist();
        $favorite = FavoriteFactory::make()->setUser($user)->setResource($resource)->persist();
        $favoriteId = $favorite->get('id');
        $this->logInAs($user);

        $this->deleteJson("/favorites/$favoriteId.json");

        $this->assertSuccess();
        $deletedFavorite = FavoriteFactory::find()->where(['Favorites.id' => $favoriteId])->first();
        $this->assertempty($deletedFavorite);
    }

    public function testFavoritesDeleteController_Error_CsrfToken(): void
    {
        $this->disableCsrfToken();
        $user = UserFactory::make()->user()->persist();
        $resource = ResourceFactory::make()->withCreatorAndPermission($user)->persist();
        $favorite = FavoriteFactory::make()->setUser($user)->setResource($resource)->persist();
        $favoriteId = $favorite->get('id');
        $this->logInAs($user);

        $this->delete("/favorites/$favoriteId.json");

        $this->assertResponseCode(403);
    }

    public function testFavoritesDeleteController_Error_NotValidId(): void
    {
        $user = UserFactory::make()->user()->persist();
        $favoriteId = 'invalid-id';
        $this->logInAs($user);

        $this->deleteJson("/favorites/$favoriteId.json");

        $this->assertError(400, 'The favorite id is not valid.');
    }

    public function testFavoritesDeleteController_Error_NotAuthenticated(): void
    {
        $user = UserFactory::make()->user()->persist();
        $resource = ResourceFactory::make()->withCreatorAndPermission($user)->persist();
        $favorite = FavoriteFactory::make()->setUser($user)->setResource($resource)->persist();
        $favoriteId = $favorite->get('id');

        $this->deleteJson("/favorites/$favoriteId.json");

        $this->assertAuthenticationError();
    }

    /**
     * Check that calling url without JSON extension throws a 404
     */
    public function testFavoritesDeleteController_Error_NotJson(): void
    {
        $user = UserFactory::make()->user()->persist();
        $resource = ResourceFactory::make()->withCreatorAndPermission($user)->persist();
        $favorite = FavoriteFactory::make()->setUser($user)->setResource($resource)->persist();
        $favoriteId = $favorite->get('id');
        $this->logInAs($user);

        $this->delete("/favorites/$favoriteId");
        $this->assertResponseCode(404);
    }
}
