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
 * @since         4.0.0
 */
use Cake\Core\Configure;
use Cake\Routing\RouteBuilder;

/** @var \Cake\Routing\RouteBuilder $routes */

$routes->plugin('Cipherguard/ResourceTypes', ['path' => '/resource-types'], function (RouteBuilder $routes) {
    $routes->setExtensions(['json']);

    $routes
        ->connect('/', ['controller' => 'ResourceTypesIndex', 'action' => 'index'])
        ->setMethods(['GET']);

    $routes
        ->connect('/{id}', ['controller' => 'ResourceTypesView', 'action' => 'view'])
        ->setPass(['id'])
        ->setMethods(['GET']);

    if (Configure::read('cipherguard.v5.enabled')) {
        $routes
            ->connect('/{id}', ['controller' => 'ResourceTypesDelete', 'action' => 'delete'])
            ->setPass(['id'])
            ->setMethods(['DELETE']);

        $routes
            ->connect('/{id}', ['controller' => 'ResourceTypesUpdate', 'action' => 'update'])
            ->setPass(['id'])
            ->setMethods(['PUT']);
    }
});
