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
use Cipherguard\Metadata\Model\Dto\MetadataKeysSettingsDto;
use Cipherguard\Metadata\Service\MetadataKeysSettingsGetService;
use Cipherguard\Metadata\Test\Factory\MetadataKeysSettingsFactory;

/**
 * @covers \Cipherguard\Metadata\Service\MetadataKeysSettingsGetService
 */
class MetadataKeysSettingsGetServiceTest extends AppTestCaseV5
{
    public function testMetadataKeysSettingsGetService_getSettings_NotEntryReturnsDefault(): void
    {
        $settings = MetadataKeysSettingsGetService::getSettings();
        $this->assertEquals(MetadataKeysSettingsFactory::getDefaultData(), $settings->toArray());
    }

    public function testMetadataKeysSettingsGetService_getSettings_NotDefault(): void
    {
        $data = MetadataKeysSettingsFactory::getDefaultData();
        $data[MetadataKeysSettingsDto::ALLOW_USAGE_OF_PERSONAL_KEYS] = false;
        $data[MetadataKeysSettingsDto::ZERO_KNOWLEDGE_KEY_SHARE] = true;
        MetadataKeysSettingsFactory::make()->value(json_encode($data))->persist();
        $settings = MetadataKeysSettingsGetService::getSettings();
        $this->assertEquals($data, $settings->toArray());
    }

    public function testMetadataKeysSettingsGetService_getSettings_BrokenSettingsReturnsDefault(): void
    {
        $this->assertEquals(0, MetadataKeysSettingsFactory::count());
        $data = MetadataKeysSettingsFactory::getDefaultData();
        $data[MetadataKeysSettingsDto::ALLOW_USAGE_OF_PERSONAL_KEYS] = '🔥';
        $data[MetadataKeysSettingsDto::ZERO_KNOWLEDGE_KEY_SHARE] = '🔥';
        MetadataKeysSettingsFactory::make()->value(json_encode($data))->persist();
        $settings = MetadataKeysSettingsGetService::getSettings();
        $this->assertEquals(MetadataKeysSettingsFactory::getDefaultData(), $settings->toArray());
    }

    public function testMetadataKeysSettingsGetService_getSettings_BrokenJsonSettingsReturnsDefault(): void
    {
        OrganizationSettingFactory::make()
            ->setPropertyAndValue(MetadataKeysSettingsGetService::ORG_SETTING_PROPERTY, '🔥')
            ->persist();
        $settings = MetadataKeysSettingsGetService::getSettings();
        $this->assertEquals(MetadataKeysSettingsFactory::getDefaultData(), $settings->toArray());
    }
}
