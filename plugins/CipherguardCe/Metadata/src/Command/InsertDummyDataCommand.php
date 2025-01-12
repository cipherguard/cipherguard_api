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
namespace Cipherguard\Metadata\Command;

use CipherguardTestData\Command\Base\FoldersDataCommand;
use CipherguardTestData\Command\Base\FoldersPermissionsDataCommand;
use CipherguardTestData\Command\Base\FoldersRelationsDataCommand;
use CipherguardTestData\Command\Base\GpgkeysDataCommand;
use CipherguardTestData\Command\Base\GroupsDataCommand;
use CipherguardTestData\Command\Base\GroupsUsersDataCommand;
use CipherguardTestData\Command\Base\PermissionsDataCommand;
use CipherguardTestData\Command\Base\ProfilesDataCommand;
use CipherguardTestData\Command\Base\ResourcesDataCommand;
use CipherguardTestData\Command\Base\SecretsDataCommand;
use CipherguardTestData\Command\Base\UsersDataCommand;
use CipherguardTestData\Command\InsertCommand;

class InsertDummyDataCommand extends InsertCommand
{
    /**
     * Get the tasks to execute.
     *
     * @param string $scenario Scenario.
     * @return array
     */
    protected function getShellTasks(string $scenario)
    {
        if ($scenario === 'default') {
            return [
                UsersDataCommand::class,
                ProfilesDataCommand::class,
                GpgkeysDataCommand::class,
                GroupsDataCommand::class,
                GroupsUsersDataCommand::class,
                ResourcesDataCommand::class,
                PermissionsDataCommand::class,
                SecretsDataCommand::class,
                FoldersDataCommand::class,
                FoldersRelationsDataCommand::class,
                FoldersPermissionsDataCommand::class,
            ];
        }

        return parent::getShellTasks($scenario);
    }
}
