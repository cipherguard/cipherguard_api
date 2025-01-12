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

namespace App\Test\TestCase\Controller\Resources;

use App\Model\Entity\Permission;
use App\Test\Factory\ResourceFactory;
use App\Test\Factory\RoleFactory;
use App\Test\Lib\AppIntegrationTestCase;
use App\Utility\UuidFactory;
use Cipherguard\Folders\FoldersPlugin;

class ResourcesDeleteControllerTest extends AppIntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->enableFeaturePlugin(FoldersPlugin::class);
        RoleFactory::make()->guest()->persist();
    }

    public function testResourcesDeleteController_Success(): void
    {
        $user = $this->logInAsUser();
        $resourceId = ResourceFactory::make()->withPermissionsFor([$user])->persist()->id;
        $this->deleteJson("/resources/$resourceId.json");
        $this->assertSuccess();
    }

    public function testResourcesDeleteController_Error_CsrfToken(): void
    {
        $this->disableCsrfToken();
        $user = $this->logInAsUser();
        $resourceId = ResourceFactory::make()->withPermissionsFor([$user])->persist()->id;
        $this->delete("/resources/$resourceId.json");
        $this->assertResponseCode(403);
    }

    public function testResourcesDeleteController_Error_ResourceIsSoftDeleted(): void
    {
        $this->logInAsUser();
        $resourceId = ResourceFactory::make()->deleted()->persist()->id;
        $this->deleteJson("/resources/$resourceId.json");
        $this->assertError(404, 'The resource does not exist.');
    }

    public function testResourcesDeleteController_Error_AccessDenied(): void
    {
        $this->logInAsUser();
        $resourceId = ResourceFactory::make()->persist()->id;
        $this->deleteJson("/resources/$resourceId.json");
        $this->assertError(404, 'The resource does not exist.');
    }

    public function testResourcesDeleteController_Error_AccessDenied_ReadAccess(): void
    {
        $user = $this->logInAsUser();
        $resourceId = ResourceFactory::make()->withPermissionsFor([$user], Permission::READ)->persist()->id;
        $this->deleteJson("/resources/$resourceId.json");
        $this->assertError(403, 'You do not have the permission to delete this resource.');
    }

    public function testResourcesDeleteController_Error_NotAuthenticated(): void
    {
        $resourceId = UuidFactory::uuid();
        $this->deleteJson("/resources/$resourceId.json");
        $this->assertAuthenticationError();
    }

    /**
     * Check that calling url without JSON extension throws a 404
     */
    public function testResourcesDeleteController_Error_NotJson(): void
    {
        $this->logInAsUser();
        $resourceId = UuidFactory::uuid();
        $this->delete("/resources/$resourceId");
        $this->assertResponseCode(404);
    }
}
