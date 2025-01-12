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
 * @since         4.0.0
 */

namespace Cipherguard\TotpResourceTypes\Test\TestCase\Controller\Settings;

use App\Test\Lib\AppIntegrationTestCase;
use Cipherguard\ResourceTypes\ResourceTypesPlugin;
use Cipherguard\TotpResourceTypes\TotpResourceTypesPlugin;

class SettingsIndexControllerTest extends AppIntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->enableFeaturePlugin(TotpResourceTypesPlugin::class);
        $this->enableFeaturePlugin(ResourceTypesPlugin::class);
    }

    public function testSettingsIndexController_SuccessAsLU()
    {
        $this->logInAsUser();
        $this->getJson('/settings.json?api-version=2');
        $this->assertSuccess();
        // Assert TOTP resource type enabled by default
        $this->assertTrue($this->_responseJsonBody->cipherguard->plugins->totpResourceTypes->enabled);
    }

    public function testSettingsIndexController_SuccessAsAN()
    {
        $this->getJson('/settings.json?api-version=2');
        $this->assertSuccess();
        // Assert TOTP resource type plugin is not visible for anonymous users
        $this->assertObjectNotHasAttribute('totpResourceTypes', $this->_responseJsonBody->cipherguard->plugins);
    }
}
