<?php

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
 * @since         3.10.0
 */
use Cake\Routing\RouteBuilder;

/** @var \Cake\Routing\RouteBuilder $routes */
$routes->plugin(
    'Cipherguard/SelfRegistration',
    ['path' => '/self-registration'],
    function (RouteBuilder $routes) {
        $routes->setExtensions(['json']);

        $routes->connect('/settings', ['controller' => 'SelfRegistrationGetSettings', 'action' => 'getSettings'])
            ->setMethods(['GET']);

        $routes->connect('/settings', ['controller' => 'SelfRegistrationSetSettings', 'action' => 'setSettings'])
            ->setMethods(['POST', 'PUT']);

        $routes
            ->connect(
                '/settings/{id}',
                ['controller' => 'SelfRegistrationDeleteSettings', 'action' => 'deleteSettings']
            )
            ->setPass(['id'])
            ->setMethods(['DELETE']);

        $routes->connect('/dry-run', ['controller' => 'SelfRegistrationDryRun', 'action' => 'dryRun'])
            ->setMethods(['POST']);
    }
);
