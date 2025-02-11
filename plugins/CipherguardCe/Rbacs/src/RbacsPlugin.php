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
 * @since         4.1.0
 */
namespace Cipherguard\Rbacs;

use Cake\Core\BasePlugin;
use Cake\Core\PluginApplicationInterface;
use Cake\ORM\TableRegistry;

class RbacsPlugin extends BasePlugin
{
    /**
     * @inheritDoc
     */
    public function bootstrap(PluginApplicationInterface $app): void
    {
        parent::bootstrap($app);
        $this->registerListeners($app);
        $this->addAssociationsToActionsTable();
        $this->addAssociationsToRolesTable();
    }

    /**
     * Register Tags related listeners.
     *
     * @param \Cake\Core\PluginApplicationInterface $app App
     * @return void
     */
    public function registerListeners(PluginApplicationInterface $app): void
    {
//        $app->getEventManager()
//            ->on(new RbacsNotificationSettingsDefinition())
//            ->on(new RbacsEmailRedactorPool());
    }

    /**
     * @return void
     */
    public static function addAssociationsToActionsTable(): void
    {
        $table = TableRegistry::getTableLocator()->get('Actions');

        $table->hasMany('Rbacs', [
            'className' => 'Cipherguard/Rbacs.Rbacs',
            'foreignKey' => 'foreign_id',
            'conditions' => [
                'Rbacs.foreign_model' => 'Action',
            ],
        ]);
    }

    /**
     * @return void
     */
    public static function addAssociationsToRolesTable(): void
    {
        $table = TableRegistry::getTableLocator()->get('Roles');

        $table->hasMany('Rbacs', [
            'className' => 'Cipherguard/Rbacs.Rbacs',
            'foreignKey' => 'role_id',
        ]);
    }
}
