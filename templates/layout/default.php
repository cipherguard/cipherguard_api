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
?>
<!doctype html>
<html class="cipherguard no-js version launching no-cipherguardplugin" lang="en">
<head>
    <?= $this->Html->charset() ?>

    <title><?= Configure::read('cipherguard.meta.title'); ?> | <?= $this->fetch('title') ?></title>
    <?= $this->element('Header/meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('scriptTop') ?>
</head>
<body spellcheck="false">
<!-- main -->
<div id="container" class="page <?php echo $this->fetch('page_classes') ?>">
<?php echo $this->fetch('content'); ?>
</div>
<?php echo $this->fetch('scriptBottom'); ?>
</body>
</html>
