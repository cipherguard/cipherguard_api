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

use App\Test\Lib\AppTestCase;
use App\Test\Lib\Utility\ExtendedUserAccessControlTestTrait;
use App\Utility\UuidFactory;
use Cake\Event\EventList;
use Cake\Event\EventManager;
use Cake\Http\Exception\NotFoundException;
use Cipherguard\PasswordExpiry\Service\Settings\PasswordExpiryDeleteSettingsService;
use Cipherguard\PasswordExpiry\Service\Settings\PasswordExpirySetSettingsService;
use Cipherguard\PasswordExpiry\Test\Factory\PasswordExpirySettingFactory;

/**
 * @see \Cipherguard\PasswordExpiry\Service\Settings\PasswordExpiryDeleteSettingsService
 */
class PasswordExpiryDeleteSettingsServiceTest extends AppTestCase
{
    use ExtendedUserAccessControlTestTrait;

    private PasswordExpiryDeleteSettingsService $service;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->service = new PasswordExpiryDeleteSettingsService();
        // Enable event tracking, required to test events.
        EventManager::instance()->setEventList(new EventList());
    }

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        unset($this->service);

        parent::tearDown();
    }

    public function testPasswordExpiryDeleteSettingsService_Success()
    {
        $uac = $this->mockExtendedAdminAccessControl();
        $uuid = PasswordExpirySettingFactory::make()->persist()->get('id');

        $result = $this->service->delete($uac, $uuid);
        $this->assertTrue($result);
        $this->assertSame(0, PasswordExpirySettingFactory::count());

        // Assert event is dispatched & contains valid data
        $this->assertEventFiredWith(PasswordExpirySetSettingsService::EVENT_SETTINGS_UPDATED, 'uac', $uac);
    }

    public function testPasswordExpiryDeleteSettingsService_WrongID()
    {
        $uac = $this->mockExtendedAdminAccessControl();
        $uuid = UuidFactory::uuid();
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('The password expiry setting does not exist.');
        $this->service->delete($uac, $uuid);
    }
}
