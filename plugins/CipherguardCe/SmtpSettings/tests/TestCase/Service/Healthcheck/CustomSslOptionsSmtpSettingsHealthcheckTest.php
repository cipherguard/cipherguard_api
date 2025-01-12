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
 * @since         4.8.0
 */

namespace Cipherguard\SmtpSettings\Test\TestCase\Service\Healthcheck;

use App\Service\Healthcheck\HealthcheckServiceCollector;
use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;
use Cipherguard\SmtpSettings\Service\Healthcheck\CustomSslOptionsSmtpSettingsHealthcheck;
use Cipherguard\SmtpSettings\Test\Lib\SmtpSettingsTestTrait;

/**
 * @covers \Cipherguard\SmtpSettings\Service\CustomSslOptionsSmtpSettingsHealthcheck
 */
class CustomSslOptionsSmtpSettingsHealthcheckTest extends TestCase
{
    use SmtpSettingsTestTrait;
    use TruncateDirtyTables;

    protected CustomSslOptionsSmtpSettingsHealthcheck $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = new CustomSslOptionsSmtpSettingsHealthcheck();
    }

    public function tearDown(): void
    {
        unset($this->service);

        parent::tearDown();
    }

    public function testCustomSslOptionsSmtpSettingsHealthcheck_Pass_WithDefaultValues(): void
    {
        $this->service->check();

        $this->assertTrue($this->service->isPassed());
        $this->assertSame(HealthcheckServiceCollector::LEVEL_WARNING, $this->service->level());
        $this->assertTextContains('No custom SSL configuration for SMTP server', $this->service->getSuccessMessage());
        $this->assertSame(HealthcheckServiceCollector::DOMAIN_SMTP_SETTINGS, $this->service->domain());
        $this->assertSame(HealthcheckServiceCollector::DOMAIN_SMTP_SETTINGS, $this->service->cliOption());
    }

    public function testCustomSslOptionsSmtpSettingsHealthcheck_Fail_WithInfoIfUsingCustomSSLOptions(): void
    {
        Configure::write('cipherguard.plugins.smtpSettings.security', [
            'sslVerifyPeer' => true,
            'sslVerifyPeerName' => true,
            'sslAllowSelfSigned' => true,
            'sslCafile' => '/path/to/rootCA.crt',
        ]);

        $this->service->check();

        $this->assertFalse($this->service->isPassed());
        $this->assertSame(HealthcheckServiceCollector::LEVEL_NOTICE, $this->service->level());
        $this->assertTextContains('Custom SSL certificate options for SMTP server is in use', $this->service->getFailureMessage());
    }

    public function testCustomSslOptionsSmtpSettingsHealthcheck_Fail_WithWarningIfSslVerificationIsDisabled(): void
    {
        Configure::write('cipherguard.plugins.smtpSettings.security', [
            'sslVerifyPeer' => false,
            'sslVerifyPeerName' => false,
            'sslAllowSelfSigned' => true,
        ]);

        $this->service->check();

        $this->assertFalse($this->service->isPassed());
        $this->assertSame(HealthcheckServiceCollector::LEVEL_WARNING, $this->service->level());
        $this->assertTextContains('SSL certification validation for SMTP server is disabled', $this->service->getFailureMessage());
    }

    public function testCustomSslOptionsSmtpSettingsHealthcheck_Fail_InvalidConfigValues(): void
    {
        Configure::write('cipherguard.plugins.smtpSettings.security', ['sslVerifyPeer' => ['foo' => 'bar']]);

        $this->service->check();

        $this->assertFalse($this->service->isPassed());
        $this->assertSame(HealthcheckServiceCollector::LEVEL_ERROR, $this->service->level());
        $this->assertTextContains('Custom SSL configuration options set are invalid', $this->service->getFailureMessage());
    }
}
