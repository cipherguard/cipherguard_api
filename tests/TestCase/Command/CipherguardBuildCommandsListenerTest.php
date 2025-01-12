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
namespace App\Test\TestCase\Command;

use App\Command\CipherguardBuildCommandsListener;
use App\Command\CipherguardCommand;
use Cake\Console\CommandCollection;
use Cake\Core\Container;
use Cake\Event\Event;
use Cake\TestSuite\TestCase;
use CakephpFixtureFactories\Command\PersistCommand;
use CipherguardTestData\Command\DummyCommand;
use CipherguardTestData\Command\InsertCommand;
use Migrations\Command\MigrationsCreateCommand;

class CipherguardBuildCommandsListenerTest extends TestCase
{
    /**
     * Ensures that the cipherguard commands are correctly filtered form the non-cipherguard commands
     * and are sorted alphabetically
     */
    public function testCipherguardBuildCommandsListener()
    {
        $listener = new CipherguardBuildCommandsListener();
        $commands = new CommandCollection([
            'fixture_factories_persist' => PersistCommand::class,
            'cipherguard insert' => InsertCommand::class,
            'cipherguard dummy' => DummyCommand::class,
            'migrations create' => MigrationsCreateCommand::class,
        ]);

        $container = new Container();
        $listener->setCipherguardCommandCollection(new Event('foo'), $commands);
        $listener->addCommandCollectionToContainer(new Event('bar'), $container);

        /** @var CipherguardCommand $cipherguardCommand */
        $cipherguardCommand = $container->get(CipherguardCommand::class);
        $expectedCommands = new CommandCollection([
            'insert' => InsertCommand::class,
            'dummy' => DummyCommand::class,
        ]);
        $this->assertEquals($expectedCommands, $cipherguardCommand->getCipherguardCommandCollection());
    }
}
