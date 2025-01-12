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

namespace App\Command;

use Cake\Console\CommandCollection;
use Cake\Core\Container;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;

class CipherguardBuildCommandsListener implements EventListenerInterface
{
    protected ?CommandCollection $cipherguardCommandCollection;

    /**
     * @inheritDoc
     */
    public function implementedEvents(): array
    {
        return [
            'Console.buildCommands' => 'setCipherguardCommandCollection',
            'Application.buildContainer' => 'addCommandCollectionToContainer',
        ];
    }

    /**
     * When the command dispatcher is initialized, collect all the commands
     *
     * @param \Cake\Event\Event $event event triggered when all the commands are collected
     * @param \Cake\Console\CommandCollection $commands collection of commands
     * @return void
     */
    public function setCipherguardCommandCollection(Event $event, CommandCollection $commands): void
    {
        $cipherguardCommands = [];
        foreach ($commands as $name => $commandFQN) {
            if (strpos($name, 'cipherguard ') === 0) {
                $subCommand = substr($name, 9);
                $cipherguardCommands[$subCommand] = $commandFQN;
            }
        }
        ksort($cipherguardCommands);

        $this->cipherguardCommandCollection = new CommandCollection($cipherguardCommands);
    }

    /**
     * Inject the list of all commands in the Cipherguard command. This will be usefull
     * to display a clean list of all the cipherguard command.
     *
     * @param \Cake\Event\Event $event event triggered when the application DIC is created
     * @param \Cake\Core\Container $container DIC
     * @return void
     */
    public function addCommandCollectionToContainer(Event $event, Container $container): void
    {
        $container
            ->add(CipherguardCommand::class)
            ->addArgument($this->cipherguardCommandCollection);
    }
}
