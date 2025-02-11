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
 * @since         3.9.0
 */
namespace Cipherguard\MultiFactorAuthentication\Test\Scenario\Multi;

use CakephpFixtureFactories\Scenario\FixtureScenarioInterface;
use Cipherguard\MultiFactorAuthentication\Test\Factory\MfaOrganizationSettingFactory;
use Cipherguard\MultiFactorAuthentication\Utility\MfaSettings;

/**
 * MfaTotpDuoOrganizationOnlyScenario
 */
class MfaTotpDuoOrganizationOnlyScenario implements FixtureScenarioInterface
{
    public function load(...$args): array
    {
        $isSupported = $args[0] ?? true;
        $hostName = $args[1] ?? null;
        $orgSetting = MfaOrganizationSettingFactory::make()
            ->setProviders(MfaSettings::PROVIDER_DUO, $isSupported)
            ->duoWithTotp()
            ->persist();

        return [$orgSetting];
    }
}
