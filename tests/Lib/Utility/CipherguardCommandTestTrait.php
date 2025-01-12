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
namespace App\Test\Lib\Utility;

use App\Service\Command\ProcessUserService;

trait CipherguardCommandTestTrait
{
    public function assertCommandCannotBeRunAsRootUser(string $commandName)
    {
        $this->mockProcessUserService('root');

        $this->exec('cipherguard ' . $commandName);

        $this->assertOutputContains('Cipherguard commands cannot be executed as root.');
        $this->assertExitError();
    }

    public function mockProcessUserService(string $username): void
    {
        $this->mockService(ProcessUserService::class, function () use ($username) {
            $stub = $this->getMockBuilder(ProcessUserService::class)
                ->onlyMethods(['getName'])
                ->getMock();
            $stub->method('getName')->willReturn($username);

            return $stub;
        });
    }

    /**
     * Delete all files in a directory.
     *
     * @param string $dir
     */
    public function emptyDirectory(string $dir)
    {
        $files = glob($dir . '*'); // get all file names
        foreach ($files as $file) { // iterate files
            if (is_file($file) && $file !== $dir . 'empty') {
                unlink($file); // delete file
            }
        }
    }
}
