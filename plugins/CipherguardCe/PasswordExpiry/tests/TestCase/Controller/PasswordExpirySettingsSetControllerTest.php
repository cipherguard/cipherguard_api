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

use App\Test\Factory\UserFactory;
use App\Test\Lib\AppIntegrationTestCase;
use App\Test\Lib\Model\EmailQueueTrait;
use Cipherguard\PasswordExpiry\Model\Dto\PasswordExpirySettingsDto;
use Cipherguard\PasswordExpiry\PasswordExpiryPlugin;
use Cipherguard\PasswordExpiry\Test\Factory\PasswordExpirySettingFactory;
use Cipherguard\PasswordExpiry\Test\Lib\PasswordExpiryTestTrait;

/**
 * @covers \Cipherguard\PasswordExpiry\Controller\PasswordExpirySettingsSetController
 */
class PasswordExpirySettingsSetControllerTest extends AppIntegrationTestCase
{
    use EmailQueueTrait;
    use PasswordExpiryTestTrait;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();

        // Mock user agent and IP so extended user access control don't fail
        $this->mockUserAgent();
        $this->mockUserIp();
        $this->enableFeaturePlugin(PasswordExpiryPlugin::class);
    }

    public function testPasswordExpirySetController_Success()
    {
        /** @var \App\Model\Entity\User $otherAdmin */
        $otherAdmin = UserFactory::make()->admin()->persist();
        $activeAdmin = $this->logInAsAdmin();
        $this->postJson('/password-expiry/settings.json', $this->getValidPasswordExpiryPayload());
        $this->assertSuccess();
        $this->assertSame(1, PasswordExpirySettingFactory::count());
        /** @var \Cipherguard\PasswordExpiry\Model\Entity\PasswordExpirySetting $setting */
        $setting = PasswordExpirySettingFactory::find()->firstOrFail();
        $response = (array)$this->_responseJsonBody;
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

        $this->assertEmailQueueCount(2);
        $this->assertEmailInBatchContains('You edited the password expiry settings', $activeAdmin->username);
        $this->assertEmailInBatchContains(
            $activeAdmin->profile->full_name . ' edited the password expiry settings',
            $otherAdmin->username
        );
    }

    public function testPasswordExpirySetController_Authentication()
    {
        $this->logInAsUser();
        $this->postJson('/password-expiry/settings.json');
        $this->assertForbiddenError('Access restricted to administrators.');
    }
}
