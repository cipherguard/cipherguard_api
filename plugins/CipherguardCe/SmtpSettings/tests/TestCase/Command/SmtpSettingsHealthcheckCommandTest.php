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
 * @since         3.8.0
 */
namespace Cipherguard\SmtpSettings\Test\TestCase\Command;

use App\Test\Lib\AppIntegrationTestCase;
use App\Test\Lib\Utility\CipherguardCommandTestTrait;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\Core\Configure;
use Cipherguard\SmtpSettings\Middleware\SmtpSettingsSecurityMiddleware;
use Cipherguard\SmtpSettings\SmtpSettingsPlugin;
use Cipherguard\SmtpSettings\Test\Lib\SmtpSettingsTestTrait;

class SmtpSettingsHealthcheckCommandTest extends AppIntegrationTestCase
{
    use ConsoleIntegrationTestTrait;
    use SmtpSettingsTestTrait;
    use CipherguardCommandTestTrait;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->useCommandRunner();
        $this->mockProcessUserService('www-data');
        $this->enableFeaturePlugin(SmtpSettingsPlugin::class);
    }

    public function testHealthcheckCommand_SmtpSettings_Plugin_Disabled()
    {
        $this->disableFeaturePlugin(SmtpSettingsPlugin::class);
        $this->exec('cipherguard healthcheck --smtpSettings');
        $this->assertExitSuccess();
        $this->assertOutputContains('<warning>[WARN] The SMTP Settings plugin is disabled.</warning>');
        $this->assertOutputContains('<info>[HELP]</info> Enable the plugin in order to define SMTP settings in the database.');
        $this->assertOutputContains('No error found. Nice one, sparky!');
    }

    public function testHealthcheckCommand_SmtpSettings_Valid()
    {
        Configure::write(
            SmtpSettingsSecurityMiddleware::CIPHERGUARD_SECURITY_SMTP_SETTINGS_ENDPOINTS_DISABLED,
            true
        );
        $this->setTransportConfig();
        $data = $this->getSmtpSettingsData();
        $this->encryptAndPersistSmtpSettings($data);
        $this->exec('cipherguard healthcheck --smtpSettings');
        $this->assertExitSuccess();
        $this->assertOutputContains('<success>[PASS]</success> The SMTP Settings plugin is enabled.');
        $this->assertOutputContains('<success>[PASS]</success> SMTP Settings coherent. You may send a test email to validate them.');
        $this->assertOutputContains('<success>[PASS]</success> The SMTP Settings source is: database.');
        $this->assertOutputContains('<success>[PASS]</success> The SMTP Settings plugin endpoints are disabled.');
        $this->assertOutputContains('No error found. Nice one, sparky!');
    }

    public function testHealthcheckCommand_SmtpSettings_Invalid()
    {
        $this->setTransportConfig('port', 0);
        $this->exec('cipherguard healthcheck --smtpSettings');
        $this->assertExitSuccess();
        $this->assertOutputContains('<success>[PASS]</success> The SMTP Settings plugin is enabled.');
        $this->assertOutputContains('<error>[FAIL] SMTP Setting errors: {"port":{"range":"The port number should be between 1 and 65535."}}</error>');
        $this->assertOutputContains('<warning>[WARN] The SMTP Settings source is:');
        $this->assertOutputContains('<warning>[WARN] The SMTP Settings plugin endpoints are enabled.');
        $this->assertOutputContains('<info>[HELP]</info> It is recommended to disable the plugin endpoints.');
    }
}
