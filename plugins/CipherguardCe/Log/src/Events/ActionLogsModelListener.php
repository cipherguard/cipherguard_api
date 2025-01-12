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
 */
namespace Cipherguard\Log\Events;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cipherguard\Log\Service\EntitiesHistory\EntitiesHistoryCreateService;

class ActionLogsModelListener implements EventListenerInterface
{
    /**
     * @inheritDoc
     */
    public function implementedEvents(): array
    {
        /**
         * Return a list if implemented Events, with their callback.
         * The callback is based on the camelized name of the event slug.
         * Example: event "user.add" will have callback "logUserAdd"
         */
        return [
            'Model.afterSave' => 'logEntityHistory',
            'Model.afterDelete' => 'logEntityHistory',
            'Model.afterRead' => 'logEntityHistory',
            'Model.initialize' => 'entityAssociationsInitialize',
        ];
    }

    /**
     * Entity associations initialize
     * Initialize needed associations for the required core models on the fly.
     * Example: we need to associate PermissionsHistory to Permissions in order to track the history.
     *
     * @param \Cake\Event\Event $event the event
     * @return void
     */
    public function entityAssociationsInitialize(Event $event)
    {
        $table = $event->getSubject();
        $modelName = $table->getAlias();

        if ($modelName == 'Permissions') {
            $table->belongsTo('Cipherguard/Log.PermissionsHistory', [
                'foreignKey' => 'foreign_key',
            ]);
        }
        if ($modelName == 'Resources') {
            $table->belongsTo('Cipherguard/Log.EntitiesHistory', [
                'foreignKey' => 'foreign_key',
            ]);
        }
        if ($modelName == 'Secrets') {
            $table->belongsTo('Cipherguard/Log.SecretsHistory', [
                'foreignKey' => 'foreign_key',
            ]);
            $table->hasMany('Cipherguard/Log.SecretAccesses');
        }
        if ($modelName == 'SecretAccesses') {
            $table->belongsTo('Cipherguard/Log.EntitiesHistory', [
                'foreignKey' => 'foreign_key',
            ]);
        }
        if (Configure::read('cipherguard.plugins.folders.enabled')) {
            if ($modelName == 'Folders') {
                $table->belongsTo('FoldersHistory', [
                    'className' => 'Cipherguard/Folders.FoldersHistory',
                    'foreignKey' => 'foreign_key',
                ]);
            }
            if ($modelName == 'FoldersRelations') {
                $table->belongsTo('FoldersRelationsHistory', [
                    'className' => 'Cipherguard/Folders.FoldersRelationsHistory',
                    'foreignKey' => 'foreign_key',
                ]);
            }
        }
    }

    /**
     * Log entity history.
     *
     * @param \Cake\Event\Event $event the event
     * @return void
     */
    public function logEntityHistory(Event $event)
    {
        $entitiesHistoryService = new EntitiesHistoryCreateService();
        $entitiesHistoryService->logEntityHistory($event);
    }
}
