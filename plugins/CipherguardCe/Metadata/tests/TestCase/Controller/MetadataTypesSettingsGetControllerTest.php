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
use App\Test\Lib\AppIntegrationTestCaseV5;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cipherguard\Metadata\Model\Dto\MetadataTypesSettingsDto;
use Cipherguard\Metadata\Test\Factory\MetadataTypesSettingsFactory;

/**
 * @uses \Cipherguard\Metadata\Controller\MetadataTypesSettingsGetController
 */
class MetadataTypesSettingsGetControllerTest extends AppIntegrationTestCaseV5
{
    use LocatorAwareTrait;

    public function testMetadataTypesSettingsGetController_Error_AuthenticationNeeded()
    {
        $this->getJson('/metadata/types/settings.json');
        $this->assertAuthenticationError();
    }

    public function testMetadataTypesSettingsGetController_Success_NoEntryReturnsDefault(): void
    {
        $this->logInAsAdmin();
        $this->getJson('/metadata/types/settings.json');
        $this->assertSuccess();
        $this->assertEquals(MetadataTypesSettingsFactory::getDefaultDataV4(), $this->getResponseBodyAsArray());
    }

    public function testMetadataTypesSettingsGetController_Success_SavedEntry(): void
    {
        $this->logInAsAdmin();
        $data = MetadataTypesSettingsFactory::getDefaultDataV4();
        $data[MetadataTypesSettingsDto::DEFAULT_COMMENT_TYPE] = 'v5';
        $data[MetadataTypesSettingsDto::ALLOW_CREATION_OF_V4_COMMENTS] = false;
        $data[MetadataTypesSettingsDto::ALLOW_CREATION_OF_V5_COMMENTS] = true;
        MetadataTypesSettingsFactory::make()->value(json_encode($data))->persist();
        $this->assertEquals(1, OrganizationSettingFactory::count());
        $this->getJson('/metadata/types/settings.json');
        $this->assertSuccess();
        $this->assertEquals($data, $this->getResponseBodyAsArray());
    }
}
