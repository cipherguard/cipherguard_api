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

namespace Cipherguard\Metadata\Test\TestCase\Service;

use App\Test\Factory\OrganizationSettingFactory;
use App\Test\Lib\AppTestCaseV5;
use Cipherguard\Metadata\Model\Dto\MetadataTypesSettingsDto;
use Cipherguard\Metadata\Service\MetadataTypesSettingsGetService;
use Cipherguard\Metadata\Test\Factory\MetadataTypesSettingsFactory;

class MetadataTypesSettingsGetServiceTest extends AppTestCaseV5
{
    public function testMetadataTypesSettingsGetService_getSettings_NotEntryReturnsDefault(): void
    {
        $settings = MetadataTypesSettingsGetService::getSettings();
        $this->assertEquals(MetadataTypesSettingsFactory::getDefaultDataV4(), $settings->toArray());
    }

    public function testMetadataTypesSettingsGetService_getSettings_NotDefault(): void
    {
        $data = MetadataTypesSettingsFactory::getDefaultDataV4();
        $data[MetadataTypesSettingsDto::DEFAULT_COMMENT_TYPE] = 'v5';
        $data[MetadataTypesSettingsDto::ALLOW_CREATION_OF_V4_COMMENTS] = false;
        $data[MetadataTypesSettingsDto::ALLOW_CREATION_OF_V5_COMMENTS] = true;
        $data[MetadataTypesSettingsDto::ALLOW_V5_V4_DOWNGRADE] = true;
        MetadataTypesSettingsFactory::make()->value(json_encode($data))->persist();
        $settings = MetadataTypesSettingsGetService::getSettings();
        $this->assertEquals($data, $settings->toArray());
    }

    public function testMetadataTypesSettingsGetService_getSettings_BrokenSettingsReturnsDefault(): void
    {
        $this->assertEquals(0, MetadataTypesSettingsFactory::count());
        $data = MetadataTypesSettingsFactory::getDefaultDataV4();
        $data[MetadataTypesSettingsDto::ALLOW_CREATION_OF_V4_COMMENTS] = '🔥';
        MetadataTypesSettingsFactory::make()->value(json_encode($data))->persist();
        $settings = MetadataTypesSettingsGetService::getSettings();
        $this->assertEquals(MetadataTypesSettingsFactory::getDefaultDataV4(), $settings->toArray());
    }

    public function testMetadataTypesSettingsGetService_getSettings_BrokenJsonSettingsReturnsDefault(): void
    {
        OrganizationSettingFactory::make()
            ->setPropertyAndValue(MetadataTypesSettingsGetService::ORG_SETTING_PROPERTY, '🔥')
            ->persist();
        $settings = MetadataTypesSettingsGetService::getSettings();
        $this->assertEquals(MetadataTypesSettingsFactory::getDefaultDataV4(), $settings->toArray());
    }
}
