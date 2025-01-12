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

use App\Error\Exception\FormValidationException;
use App\Test\Lib\AppTestCaseV5;
use Cipherguard\Metadata\Service\MetadataTypesSettingsAssertService;
use Cipherguard\Metadata\Test\Factory\MetadataTypesSettingsFactory;

class MetadataTypesSettingsAssertServiceTest extends AppTestCaseV5
{
    public function testMetadataTypesSettingsAssertService_Success(): void
    {
        $data = MetadataTypesSettingsFactory::getDefaultDataV4();
        $sut = new MetadataTypesSettingsAssertService();
        $dto = $sut->assert($data);
        $this->assertEquals($data, $dto->toArray());
    }

    public function testMetadataTypesSettingsAssertService_ErrorFormat(): void
    {
        $sut = new MetadataTypesSettingsAssertService();
        $this->expectException(FormValidationException::class);
        $dto = $sut->assert([]);
    }

    /**
     * Admin select a default version but all resource types are deleted for this version
     *
     * @return void
     */
    public function testMetadataTypesSettingsAssertService_ErrorRules_DefaultResourceTypesAreDeleted(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * Error: v5 default selected but metadata key does not exist or is not available to all
     *
     * @return void
     */
    public function testMetadataTypesSettingsAssertService_ErrorRules_DefaultCryptoNoBueno(): void
    {
        $this->markTestIncomplete();
    }
}
