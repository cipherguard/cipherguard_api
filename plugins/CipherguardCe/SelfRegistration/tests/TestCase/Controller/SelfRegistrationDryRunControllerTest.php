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
 * @since         3.10.0
 */

namespace Cipherguard\SelfRegistration\Test\TestCase\Controller;

use App\Test\Lib\AppIntegrationTestCase;
use Cipherguard\SelfRegistration\SelfRegistrationPlugin;
use Cipherguard\SelfRegistration\Test\Lib\SelfRegistrationTestTrait;

class SelfRegistrationDryRunControllerTest extends AppIntegrationTestCase
{
    use SelfRegistrationTestTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->enableFeaturePlugin(SelfRegistrationPlugin::class);
    }

    public function testSelfRegistrationDryRunControllerTest_Success()
    {
        $this->setSelfRegistrationSettingsData();
        $data = ['email' => 'john@cipherguard.com'];

        $this->post('/self-registration/dry-run.json', $data);
        $this->assertResponseOk();
    }

    public function testSelfRegistrationDryRunControllerTest_Domain_Not_Valid()
    {
        $this->setSelfRegistrationSettingsData();
        $data = ['email' => 'john@domain-not-allowed.com'];

        $this->postJson('/self-registration/dry-run.json', $data);
        $this->assertResponseCode(400);
    }

    public function testSelfRegistrationDryRunControllerTest_User_Logged_In_Should_Not_Have_Access()
    {
        $this->logInAsUser();
        $this->postJson('/self-registration/dry-run.json');
        $this->assertForbiddenError('Access restricted to unauthenticated users.');
    }
}
