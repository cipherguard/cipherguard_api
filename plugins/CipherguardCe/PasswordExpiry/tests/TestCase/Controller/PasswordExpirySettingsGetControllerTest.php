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

namespace Cipherguard\PasswordExpiry\Test\TestCase\Controller;

use App\Test\Lib\AppIntegrationTestCase;
use Cipherguard\PasswordExpiry\Model\Dto\PasswordExpirySettingsDto;
use Cipherguard\PasswordExpiry\PasswordExpiryPlugin;
use Cipherguard\PasswordExpiry\Test\Factory\PasswordExpirySettingFactory;
use Cipherguard\PasswordExpiry\Test\Lib\PasswordExpiryTestTrait;

/**
 * @covers \Cipherguard\PasswordExpiry\Controller\PasswordExpirySettingsGetController
 */
class PasswordExpirySettingsGetControllerTest extends AppIntegrationTestCase
{
    use PasswordExpiryTestTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->enableFeaturePlugin(PasswordExpiryPlugin::class);
    }

    public function testPasswordExpiryGetController_Authentication()
    {
        $this->getJson('/password-expiry/settings.json');
        $this->assertAuthenticationError();
    }

    public function testPasswordExpiryGetController_Success_Settings_In_DB()
    {
        $setting = PasswordExpirySettingFactory::make()->persist();
        $this->logInAsUser();

        $this->getJson('/password-expiry/settings.json');
        $this->assertSuccess();

        $response = $this->getResponseBodyAsArray();
        $this->assertSame($setting->get('id'), $response['id']);
        $this->assertTrue($response[PasswordExpirySettingsDto::AUTOMATIC_EXPIRY]);
        $this->assertTrue($response[PasswordExpirySettingsDto::AUTOMATIC_UPDATE]);
        $this->assertFalse($response[PasswordExpirySettingsDto::POLICY_OVERRIDE]);
        $this->assertNull($response[PasswordExpirySettingsDto::DEFAULT_EXPIRY_PERIOD]);
        $this->assertArrayNotHasKey(PasswordExpirySettingsDto::EXPIRY_NOTIFICATION, $response);
        $this->assertNotNull($response['created']);
        $this->assertNotNull($response['modified']);
        $this->assertNotNull($response['created_by']);
        $this->assertNotNull($response['modified_by']);
    }

    public function testPasswordExpiryGetController_Success_Settings_Not_In_DB_Plugin_Disabled()
    {
        $this->logInAsUser();

        $this->getJson('/password-expiry/settings.json');
        $this->assertSuccess();

        $response = (array)$this->_responseJsonBody;
        $this->assertSame([
            PasswordExpirySettingsDto::AUTOMATIC_EXPIRY => false,
            PasswordExpirySettingsDto::AUTOMATIC_UPDATE => false,
        ], $response);
    }
}
