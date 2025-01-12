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
 * @since         4.5.0
 */

namespace Cipherguard\PasswordExpiry\Test\TestCase\Service\Settings;

use Cake\Http\Exception\InternalErrorException;
use Cake\TestSuite\TestCase;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;
use Cipherguard\PasswordExpiry\Model\Dto\PasswordExpirySettingsDto;
use Cipherguard\PasswordExpiry\Service\Settings\PasswordExpiryGetSettingsService;
use Cipherguard\PasswordExpiry\Test\Factory\PasswordExpirySettingFactory;
use Cipherguard\PasswordExpiry\Test\Lib\PasswordExpiryTestTrait;

class PasswordExpiryGetSettingsServiceTest extends TestCase
{
    use PasswordExpiryTestTrait;
    use TruncateDirtyTables;

    private PasswordExpiryGetSettingsService $service;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->service = new PasswordExpiryGetSettingsService();
    }

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        unset($this->service);

        parent::tearDown();
    }

    public function testPasswordExpiryGetSettingsService_Success_SettingsNotInDB()
    {
        $this->assertSame([
            PasswordExpirySettingsDto::AUTOMATIC_EXPIRY => false,
            PasswordExpirySettingsDto::AUTOMATIC_UPDATE => false,
        ], $this->service->get()->toArray());
    }

    public function testPasswordExpiryGetSettingsService_Success_SettingsInDB()
    {
        $settingInDB = PasswordExpirySettingFactory::make()->persist();
        $result = $this->service->get()->toArray();
        $this->assertPasswordExpirySettingsMatchesEntity($settingInDB, $result);
    }

    public function testPasswordExpiryGetSettingsService_Error_SettingsInDB_Not_Array()
    {
        PasswordExpirySettingFactory::make()->setField('value', 'foo')->persist();
        $this->expectException(InternalErrorException::class);
        $this->service->get();
    }
}
