<?php
/**
 * Cipherguard ~ Open source password manager for teams
 * Copyright (c) Khulnasoft Ltd' (https://www.cipherguard.khulnasoft.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Khulnasoft Ltd' (https://www.cipherguard.khulnasoft.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.cipherguard.khulnasoft.com Cipherguard(tm)
 * @since         2.13.0
 * @var \App\View\AppView $this
 * @var array $report
 */
use App\Utility\Purifier;
use Cake\Http\Exception\InternalErrorException;
use Cake\I18n\FrozenTime;
use Cake\Routing\Router;

if (!isset($report)) {
    throw new InternalErrorException();
}

$reportName = Purifier::clean($report['name']);
$reportCreated = $report['created'] ?? FrozenTime::now();
$reportCreator = $report['creator']['profile']['first_name'] . ' ' . $report['creator']['profile']['last_name'];
$reportCreator = Purifier::clean($reportCreator);
?>
<div class="row report-header">
    <div class="col6 creator-info">
        <h1><?= __('Cipherguard report'); ?></h1>
        <ul>
            <li>
                <span class="label"><?= __('Report name'); ?>:</span>
                <span class="value"><?= $reportName; ?></span>
            </li>
            <li>
                <span class="label"><?= __('Generated by'); ?>:</span>
                <span class="value"><?= $reportCreator; ?></span>
            </li>
            <li>
                <span class="label"><?= __('Creation date'); ?>:</span>
                <span class="value"><?= $reportCreated->format('yy-m-d H:m:s'); ?></span>
            </li>
        </ul>
    </div>
    <div class="col6 company-info last">
        <div class="logo">
            <img src="<?= Router::url('/img/logo/logo.png', true); ?>" alt="Cipherguard logo"/>
        </div>
    </div>
</div>

