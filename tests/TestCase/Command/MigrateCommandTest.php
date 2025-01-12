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
 * @since         3.1.0
 */
namespace App\Test\TestCase\Command;

use App\Test\Lib\AppTestCase;
use App\Test\Lib\Utility\CipherguardCommandTestTrait;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use CakephpTestSuiteLight\Fixture\TruncateDirtyTables;

/**
 * App\Command\MigrateCommand Test Case
 *
 * @uses \App\Command\MigrateCommand
 */
class MigrateCommandTest extends AppTestCase
{
    use ConsoleIntegrationTestTrait;
    use CipherguardCommandTestTrait;
    use TruncateDirtyTables;

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
    }

    /**
     * Basic help test
     */
    public function testMigrateCommandHelp()
    {
        $this->exec('cipherguard migrate -h');
        $this->assertExitSuccess();
        $this->assertOutputContains('Run database migrations.');
        $this->assertOutputContains('cake cipherguard migrate');
    }

    /**
     * @Given I am root
     * @When I run "cipherguard migrate"
     * @Then the migrations cannot be run.
     */
    public function testMigrateCommandAsRoot()
    {
        $this->assertCommandCannotBeRunAsRootUser('migrate');
    }

    /**
     * @Given I am not root
     * @When I run "cipherguard migrate"
     * @Then the migrations get run without generating the .lock file and the cache gets cleared.
     */
    public function testMigrateCommandAsNonRootWithoutBackup()
    {
        $this->exec('cipherguard migrate -q -d test');
        $this->assertExitSuccess();
        $this->assertOutputEmpty();
    }

    /**
     * This will fail because the backup will be written at
     * some unreachable location. Still it is important to run this.
     *
     * @group mysqldump
     */
    public function testMigrateCommandAsNonRootWithBackup()
    {
        $this->exec('cipherguard migrate -q --backup -d test');
        $this->assertExitSuccess();
        $this->assertOutputEmpty();
    }
}
