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
 * @since         2.0.0
 */
use Cake\Core\Configure;
use Cake\Routing\Router;
?>
<?= $this->element('Header/banner'); ?>
    <meta name="description" content="<?= Configure::read('cipherguard.meta.description'); ?>">
    <meta name="keywords" content="Cipherguard, password manager, online password manager, open source password manager">
    <meta name="robots" content="<?= Configure::read('cipherguard.meta.robots'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/x-icon" href="<?= Router::url('/favicon.ico', true); ?>" />
    <link rel="icon" href="<?= Router::url('/favicon_32.png', true); ?>" sizes="32x32" />
    <link rel="icon" href="<?= Router::url('/favicon_57.png', true); ?>" sizes="57x57" />
    <link rel="icon" href="<?= Router::url('/favicon_76.png', true); ?>" sizes="76x76" />
    <link rel="icon" href="<?= Router::url('/favicon_96.png', true); ?>" sizes="96x96" />
    <link rel="icon" href="<?= Router::url('/favicon_128.png', true); ?>" sizes="128x128" />
    <link rel="icon" href="<?= Router::url('/favicon_192.png', true); ?>" sizes="192x192" />
    <link rel="icon" href="<?= Router::url('/favicon_228.png', true); ?>" sizes="228x228" />
    <base href="<?= Router::url('/', true); ?>">
