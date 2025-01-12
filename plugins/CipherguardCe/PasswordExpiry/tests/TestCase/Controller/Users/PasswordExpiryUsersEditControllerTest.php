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
 * @since         4.6.0
 */
namespace Cipherguard\PasswordExpiry\Test\TestCase\Controller\Users;

use App\Test\Factory\RoleFactory;
use App\Test\Lib\AppIntegrationTestCase;
use Cipherguard\PasswordExpiry\PasswordExpiryPlugin;

class PasswordExpiryUsersEditControllerTest extends AppIntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        RoleFactory::make()->guest()->persist();
        // Mock user agent and IP
        $this->mockUserAgent('PHPUnit');
        $this->mockUserIp();
    }

    public function testPasswordExpiryUsersEditController_Plugin_Disabled(): void
    {
        $this->disableFeaturePlugin(PasswordExpiryPlugin::class);
        $user = $this->logInAsUser();
        $data = [
            'id' => $user->id,
            'profile' => [
                'first_name' => 'ada edited',
            ],
        ];
        $this->postJson('/users/' . $user->id . '.json', $data);
        $this->assertSuccess();
        $this->assertEquals($this->_responseJsonBody->profile->first_name, 'ada edited');
    }
}
