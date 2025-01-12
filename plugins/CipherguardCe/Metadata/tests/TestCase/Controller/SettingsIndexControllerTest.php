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
 * @since         4.10.0
 */

namespace Cipherguard\Metadata\Test\TestCase\Controller;

use App\Test\Lib\AppIntegrationTestCaseV5;
use Cake\Core\Configure;

class SettingsIndexControllerTest extends AppIntegrationTestCaseV5
{
    public function testSettingsIndexController_MetadataPlugin_Enabled_Logged_In(): void
    {
        $this->logInAsUser();
        $this->getJson('/settings.json');
        $this->assertTrue($this->_responseJsonBody->cipherguard->plugins->metadata->enabled);
        $this->assertSame('1.0.0', $this->_responseJsonBody->cipherguard->plugins->metadata->version);
    }

    public function testSettingsIndexController_MetadataPlugin_Enabled_Not_Logged_In(): void
    {
        $this->getJson('/settings.json');
        $this->assertFalse(isset($this->_responseJsonBody->cipherguard->plugins->metadata));
    }

    public function testSettingsIndexController_MetadataPlugin_Not_Enabled_Logged_In(): void
    {
        // Disable the v5 flag
        Configure::write('cipherguard.v5.enabled', false);
        $this->logInAsUser();
        $this->getJson('/settings.json');
        $this->assertFalse(isset($this->_responseJsonBody->cipherguard->plugins->metadata));
    }
}
