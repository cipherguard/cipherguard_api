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
 * @since         4.10.0
 */

namespace Cipherguard\Metadata\Test\TestCase\Controller;

use App\Test\Factory\OrganizationSettingFactory;
use App\Test\Factory\UserFactory;
use App\Test\Lib\AppIntegrationTestCaseV5;
use App\Test\Lib\Model\EmailQueueTrait;
use Cake\Core\Configure;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cipherguard\Metadata\Model\Dto\MetadataTypesSettingsDto;
use Cipherguard\Metadata\Test\Factory\MetadataTypesSettingsFactory;

/**
 * @uses \Cipherguard\Metadata\Controller\MetadataTypesSettingsPostController
 */
class MetadataTypesSettingsPostControllerTest extends AppIntegrationTestCaseV5
{
    use EmailQueueTrait;
    use LocatorAwareTrait;

    public function testMetadataTypesSettingsPostController_Success_v4(): void
    {
        [$loggedInUser, $otherAdmin] = UserFactory::make(2)->admin()->persist();
        // Create a disabled admin and a user to test emails
        UserFactory::make()->admin()->disabled()->persist();
        UserFactory::make()->user()->persist();
        $this->logInAs($loggedInUser);
        $data = MetadataTypesSettingsFactory::getDefaultDataV4();
        $this->postJson('/metadata/types/settings.json', $data);
        $this->assertSuccess();
        $this->assertEquals(1, OrganizationSettingFactory::count());

        $this->assertEmailQueueCount(2);
        $this->assertEmailInBatchContains([
            'You edited the metadata settings',
            'Default resource types: v4',
            'Default comment type: v4',
        ], $loggedInUser->username);
        $this->assertEmailInBatchContains($loggedInUser->profile->last_name . ' edited the metadata settings', $otherAdmin->username);
    }

    public function testMetadataTypesSettingsPostController_Success_v5(): void
    {
        [$loggedInUser, $otherAdmin] = UserFactory::make(2)->admin()->persist();
        // Create a disabled admin and a user to test emails
        UserFactory::make()->admin()->disabled()->persist();
        UserFactory::make()->user()->persist();
        $this->logInAs($loggedInUser);
        $data = MetadataTypesSettingsFactory::getDefaultDataV4();

        $data[MetadataTypesSettingsDto::DEFAULT_COMMENT_TYPE] = 'v5';
        $data[MetadataTypesSettingsDto::ALLOW_CREATION_OF_V4_COMMENTS] = false;
        $data[MetadataTypesSettingsDto::ALLOW_CREATION_OF_V5_COMMENTS] = true;
        $this->postJson('/metadata/types/settings.json', $data);
        $this->assertSuccess();
        $this->assertEquals(1, OrganizationSettingFactory::count());
        $this->assertEmailQueueCount(2);
        $this->assertEmailInBatchContains([
            'You edited the metadata settings',
            'Default resource types: v4',
            'Default comment type: v5',
        ], $loggedInUser->username);
        $this->assertEmailInBatchContains($loggedInUser->profile->last_name . ' edited the metadata settings', $otherAdmin->username);
    }

    public function testMetadataTypesSettingsPostController_Error_AuthenticationNeeded()
    {
        $this->postJson('/metadata/types/settings.json', []);
        $this->assertAuthenticationError();
    }

    public function testMetadataTypesSettingsPostController_Error_NotAdmin(): void
    {
        $this->logInAsUser();
        $this->postJson('/metadata/types/settings.json', []);
        $this->assertResponseCode(403);
    }

    public function testMetadataTypesSettingsPostController_Error_InvalidData(): void
    {
        $this->logInAsAdmin();
        $data = MetadataTypesSettingsFactory::getDefaultDataV4();
        $data[MetadataTypesSettingsDto::DEFAULT_RESOURCE_TYPES] = 'v8';
        $this->postJson('/metadata/types/settings.json', $data);
        $this->assertResponseCode(400);
        $this->assertResponseContains('Could not validate the settings');
    }

    public function testMetadataTypesSettingsPostController_ErrorSettingsEditionDisabled(): void
    {
        $setting = Configure::read('cipherguard.security.metadata.settings.editionDisabled');
        Configure::write('cipherguard.security.metadata.settings.editionDisabled', true);
        $this->logInAsAdmin();
        $data = MetadataTypesSettingsFactory::getDefaultDataV4();
        $this->postJson('/metadata/types/settings.json', $data);
        $this->assertResponseCode(403);
        Configure::write('cipherguard.security.metadata.settings.editionDisabled', $setting);
    }
}
