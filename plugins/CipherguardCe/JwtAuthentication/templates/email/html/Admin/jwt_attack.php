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
use App\Utility\Purifier;
use App\View\Helper\AvatarHelper;
use Cake\I18n\FrozenTime;

$user = $body['user'];
$ip = $body['ip'];
$message = $body['message'];

echo $this->element('Email/module/avatar',[
    'url' => AvatarHelper::getAvatarUrl($user['profile']['avatar']),
    'text' => $this->element('Email/module/avatar_text', [
        'user' => $user,
        'datetime' => FrozenTime::now(),
        'text' => __('Security warning!')
    ])
]);

$text = '<h3>' . __('Security warning!') . '</h3><br/>';
$text = '<h4>' . $message . '</h4><br/>';
$text .= __('An unknown user with IP: {0} attempted to identify as {1}.', $ip, $user['username']);
$text .= ' ' . __('This is a potential security issue.');
$text .= ' ' . __('Please investigate!');
echo $this->element('Email/module/text', [
    'text' => $text
]);
