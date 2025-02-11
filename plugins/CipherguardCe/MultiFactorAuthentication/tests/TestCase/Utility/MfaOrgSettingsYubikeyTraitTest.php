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
 * @since         2.5.0
 */
namespace Cipherguard\MultiFactorAuthentication\Test\TestCase\Utility;

use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\TableRegistry;
use Cipherguard\MultiFactorAuthentication\Test\Lib\MfaIntegrationTestCase;
use Cipherguard\MultiFactorAuthentication\Utility\MfaOrgSettings;
use Cipherguard\MultiFactorAuthentication\Utility\MfaSettings;

class MfaOrgSettingsYubikeyTraitTest extends MfaIntegrationTestCase
{
    /**
     * @var \App\Model\Table\OrganizationSettingsTable
     */
    protected $OrganizationSettings;

    protected $defaultConfig = [
        'providers' => [
            MfaSettings::PROVIDER_YUBIKEY => true,
        ],
        MfaSettings::PROVIDER_YUBIKEY => [
            'clientId' => '40123',
            'secretKey' => 'i2/j3jIQBO/axOl3ah4mlgXlXU+Y=',
        ],
    ];

    /**
     * Setup.
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->OrganizationSettings = TableRegistry::getTableLocator()->get('OrganizationSettings');
    }

    /**
     * @group mfa
     * @group mfaOrgSettings
     */
    public function testMfaOrgSettingsGetYubikeyProp()
    {
        Configure::write('cipherguard.plugins.multiFactorAuthentication', $this->defaultConfig);
        $settings = MfaOrgSettings::get();
        $this->assertNotEmpty($settings->getYubikeyOTPSecretKey());
        $this->assertNotEmpty($settings->getYubikeyOTPClientId());
    }

    /**
     * @group mfa
     * @group mfaOrgSettings
     */
    public function testMfaOrgSettingsGetYubikeyIncompletePropsSeckey()
    {
        $config = ['providers' => [MfaSettings::PROVIDER_YUBIKEY => true], MfaSettings::PROVIDER_YUBIKEY => []];
        $this->mockMfaOrgSettings($config, 'configure');
        $settings = MfaOrgSettings::get();
        $this->expectException(RecordNotFoundException::class);
        $this->assertNotEmpty($settings->getYubikeyOTPSecretKey());
    }

    /**
     * @group mfa
     * @group mfaOrgSettings
     */
    public function testMfaOrgSettingsGetYubikeyIncompletePropsClientId()
    {
        $config = ['providers' => [MfaSettings::PROVIDER_YUBIKEY => true], MfaSettings::PROVIDER_YUBIKEY => []];
        $this->mockMfaOrgSettings($config, 'configure');
        $settings = MfaOrgSettings::get();
        $this->expectException(RecordNotFoundException::class);
        $this->assertNotEmpty($settings->getYubikeyOTPClientId());
    }
}
