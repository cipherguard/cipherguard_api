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
 * @since         2.0.0
 */

namespace Cipherguard\MultiFactorAuthentication\Test\TestCase\Controllers\Users;

use App\Test\Factory\RoleFactory;
use App\Test\Factory\UserFactory;
use App\Test\Lib\Utility\UserAccessControlTrait;
use Cipherguard\MultiFactorAuthentication\Test\Lib\MfaIntegrationTestCase;
use Cipherguard\MultiFactorAuthentication\Test\Lib\MfaOrgSettingsTestTrait;
use Cipherguard\MultiFactorAuthentication\Test\Scenario\Duo\MfaDuoScenario;
use Cipherguard\MultiFactorAuthentication\Test\Scenario\Multi\MfaTotpDuoScenario;
use Cipherguard\MultiFactorAuthentication\Test\Scenario\Totp\MfaTotpUserOnlyScenario;
use Cipherguard\MultiFactorAuthentication\Test\Scenario\Yubikey\MfaYubikeyOrganizationOnlyScenario;

class UsersIndexControllerTest extends MfaIntegrationTestCase
{
    use MfaOrgSettingsTestTrait;
    use UserAccessControlTrait;

    /**
     * @return void
     */
    public function testMfaUsersIndex_AssertThatColumnIsMfaEnabledIsNotDisplayedByDefault()
    {
        RoleFactory::make()->guest()->persist();
        $this->logInAsAdmin();
        $this->getJson('/users.json');
        $this->assertSuccess();
        $this->assertObjectNotHasAttribute('is_mfa_enabled', $this->_responseJsonBody[0]);
    }

    /**
     * @return void
     */
    public function testMfaUsersIndex_AssertThatColumnIsMfaEnabledIsDisabledIfMfaIsDisabledForOrg()
    {
        RoleFactory::make()->guest()->persist();
        $admin = $this->logInAsAdmin();
        $this->loadFixtureScenario(MfaTotpUserOnlyScenario::class, $admin);
        $this->getJson('/users.json?contain[is_mfa_enabled]=1');
        $this->assertSuccess();
        $this->assertObjectHasAttribute('is_mfa_enabled', $this->_responseJsonBody[0]);
        $this->assertFalse($this->_responseJsonBody[0]->is_mfa_enabled);
    }

    /**
     * @return void
     */
    public function testMfaUsersIndex_ThatColumnIsMfaEnabledIsEnabledIfMfaIsEnabledForOrg()
    {
        RoleFactory::make()->guest()->persist();
        $user = UserFactory::make()->user()->persist();
        $this->logInAsAdmin();
        $this->loadFixtureScenario(MfaDuoScenario::class, $user);
        $this->getJson('/users.json?contain[is_mfa_enabled]=1');
        $this->assertSuccess();
        foreach ($this->_responseJsonBody as $userInResponse) {
            if ($userInResponse->id === $user->id) {
                $this->assertTrue($userInResponse->is_mfa_enabled);
            } else {
                $this->assertFalse($userInResponse->is_mfa_enabled);
            }
        }
    }

    /**
     * @return void
     */
    public function testMfaUsersIndex_ThatColumnIsMfaEnabledIsInvisibleToUsersIfMfaIsEnabledForOrg()
    {
        RoleFactory::make()->guest()->persist();
        $userWithMfa = UserFactory::make()->user()->persist();
        UserFactory::make()->admin()->persist();
        $this->logInAsUser();
        $this->loadFixtureScenario(MfaDuoScenario::class, $userWithMfa);
        $this->getJson('/users.json?contain[is_mfa_enabled]=1');
        $this->assertSuccess();
        foreach ($this->_responseJsonBody as $userInResponse) {
            $this->assertObjectNotHasAttribute('is_mfa_enabled', $userInResponse);
        }
    }

    /**
     * @return void
     */
    public function testMfaUsersIndex_ThatUsersIndexResultsAreFilteredWhenFilterParameterHaveIsMfaEnabled()
    {
        RoleFactory::make()->guest()->persist();
        $admin = $this->logInAsAdmin();
        $userWithMfa = UserFactory::make()->user()->persist();
        $this->loadFixtureScenario(MfaTotpDuoScenario::class, $userWithMfa);

        $this->getJson('/users.json?filter[is-mfa-enabled]=1&contain[is_mfa_enabled]=1');
        $this->assertSuccess();
        $responseJsonBody = (array)$this->_responseJsonBody;
        $this->assertSame(1, count($responseJsonBody));
        $userInResponse = array_pop($responseJsonBody);
        $this->assertTrue($userInResponse->is_mfa_enabled);
        $this->assertSame($userWithMfa->get('id'), $userInResponse->id);

        $this->getJson('/users.json?filter[is-mfa-enabled]=0&contain[is_mfa_enabled]=1');
        $this->assertSuccess();
        $responseJsonBody = (array)$this->_responseJsonBody;
        $this->assertSame(1, count($responseJsonBody));
        $userInResponse = array_pop($responseJsonBody);
        $this->assertFalse($userInResponse->is_mfa_enabled);
        $this->assertSame($admin->get('id'), $userInResponse->id);
    }

    /**
     * @return void
     */
    public function testMfaUsersIndex_Is_Mfa_Enabled_Filter_Without_Contain_Should_Not_Throw_Exception()
    {
        RoleFactory::make()->guest()->persist();
        $admin = $this->logInAsAdmin();
        $this->loadFixtureScenario(MfaYubikeyOrganizationOnlyScenario::class);

        $this->getJson('/users.json?filter[is-mfa-enabled]=0');
        $this->assertSuccess();
        $responseJsonBody = (array)$this->_responseJsonBody;
        $this->assertSame(1, count($responseJsonBody));
        $userInResponse = array_pop($responseJsonBody);
        $this->assertFalse(isset($userInResponse->is_mfa_enabled));
        $this->assertSame($admin->get('id'), $userInResponse->id);
    }
}
