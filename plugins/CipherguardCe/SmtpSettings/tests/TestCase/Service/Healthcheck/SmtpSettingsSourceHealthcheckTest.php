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

namespace Cipherguard\SmtpSettings\Test\TestCase\Service\Healthcheck;

use Cake\TestSuite\TestCase;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;
use Cipherguard\SmtpSettings\Service\Healthcheck\SmtpSettingsSettingsSourceHealthcheck;
use Cipherguard\SmtpSettings\Test\Lib\SmtpSettingsTestTrait;

class SmtpSettingsSourceHealthcheckTest extends TestCase
{
    use SmtpSettingsTestTrait;
    use TruncateDirtyTables;

    protected SmtpSettingsSettingsSourceHealthcheck $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = new SmtpSettingsSettingsSourceHealthcheck($this->dummyCipherguardFile);
    }

    public function tearDown(): void
    {
        unset($this->service);
        $this->deleteCipherguardDummyFile();
        parent::tearDown();
    }

    public function testSmtpSettingsSourceHealthcheck_Valid_DB()
    {
        $data = $this->getSmtpSettingsData();
        $this->encryptAndPersistSmtpSettings($data);

        $this->service->check();
        $this->assertTrue($this->service->isPassed());
        $this->assertSame('database', $this->service->getSource());
    }

    public function testSmtpSettingsSourceHealthcheck_Invalid_DB()
    {
        $data = $this->getSmtpSettingsData('port', 0);
        $this->encryptAndPersistSmtpSettings($data);

        $this->service->check();
        $this->assertTrue($this->service->isPassed());
        $this->assertSame('database', $this->service->getSource());
    }

    public function testSmtpSettingsSourceHealthcheck_Valid_File()
    {
        $this->setTransportConfig();
        $this->makeDummyCipherguardFile([
            'EmailTransport' => 'Foo',
            'Email' => 'Bar',
        ]);

        $this->service->check();
        $this->assertFalse($this->service->isPassed());
        $this->assertSame(CONFIG . 'cipherguard.php', $this->service->getSource());
    }

    public function testSmtpSettingsSourceHealthcheck_Invalid_File()
    {
        $this->setTransportConfig('port', 0);
        $this->makeDummyCipherguardFile([
            'EmailTransport' => 'Foo',
            'Email' => 'Bar',
        ]);

        $this->service->check();
        $this->assertFalse($this->service->isPassed());
        $this->assertSame(CONFIG . 'cipherguard.php', $this->service->getSource());
    }

    public function testSmtpSettingsSourceHealthcheck_Valid_Env()
    {
        $this->setTransportConfig();

        $this->service->check();
        $this->assertFalse($this->service->isPassed());
        $this->assertSame('env variables', $this->service->getSource());
    }

    public function testSmtpSettingsSourceHealthcheck_Invalid_Env()
    {
        $this->setTransportConfig('port', 0);

        $this->service->check();
        $this->assertFalse($this->service->isPassed());
        $this->assertSame('env variables', $this->service->getSource());
    }
}
