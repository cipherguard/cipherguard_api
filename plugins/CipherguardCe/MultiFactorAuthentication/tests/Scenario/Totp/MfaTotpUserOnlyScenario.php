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
 * @since         3.7.2
 */
namespace Cipherguard\MultiFactorAuthentication\Test\Scenario\Totp;

use App\Test\Factory\UserFactory;
use App\Test\Lib\Utility\UserAccessControlTrait;
use CakephpFixtureFactories\Scenario\FixtureScenarioInterface;
use CakephpFixtureFactories\Scenario\ScenarioAwareTrait;
use Cipherguard\MultiFactorAuthentication\Test\Factory\MfaAccountSettingFactory;
use Cipherguard\MultiFactorAuthentication\Utility\MfaOtpFactory;

/**
 * MfaTotpScenario
 */
class MfaTotpUserOnlyScenario implements FixtureScenarioInterface
{
    use ScenarioAwareTrait;
    use UserAccessControlTrait;

    public function load(...$args): array
    {
        $user = $args[0] ?? null;

        if (is_null($user)) {
            $user = UserFactory::make()->user()->persist();
        }

        $uri = MfaOtpFactory::generateTOTP($this->makeUac($user));
        MfaAccountSettingFactory::make()
            ->setField('user_id', $user->id)
            ->totp($uri)
            ->persist();

        return [$uri];
    }
}
