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
 * @since         3.3.0
 */
use Cake\Routing\RouteBuilder;

/** @var \Cake\Routing\RouteBuilder $routes */

$routes->plugin('Cipherguard/Rbacs', ['path' => '/rbacs'], function (RouteBuilder $routes) {
    $routes->setExtensions(['json']);

    $routes->connect('/me', ['prefix' => 'Rbacs', 'controller' => 'RbacsView', 'action' => 'viewForCurrentRole'])
        ->setMethods(['GET']);

    $routes->connect('/', ['prefix' => 'Rbacs', 'controller' => 'RbacsIndex', 'action' => 'index'])
        ->setMethods(['GET']);

    $routes->connect('/', ['prefix' => 'Rbacs', 'controller' => 'RbacsUpdate', 'action' => 'update'])
        ->setMethods(['POST', 'PUT']);

    $routes->connect('/uiactions', ['prefix' => 'UiActions', 'controller' => 'UiActionsIndex', 'action' => 'index'])
        ->setMethods(['GET']);
});
