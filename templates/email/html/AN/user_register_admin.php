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
use App\Utility\Purifier;
use App\View\Helper\AvatarHelper;
use Cake\Routing\Router;
if (PHP_SAPI === 'cli') {
    Router::fullBaseUrl($body['fullBaseUrl']);
}
$user = $body['user'];
$admin = $body['admin'];
$token = $body['token'];

echo $this->element('Email/module/avatar',[
    'url' => AvatarHelper::getAvatarUrl($user['profile']['avatar']),
    'text' => $this->element('Email/module/avatar_text', [
        'user' => $user,
        'datetime' => $user['created'],
        'text' => __('{0} just created an account for you on cipherguard!', Purifier::clean($admin['profile']['first_name']))
    ])
]);

$text = '<h3>' . __('Welcome {0}', Purifier::clean($user['profile']['first_name'])) . ',</h3><br/>';
$text .= __('{0} just invited you to join cipherguard at {1}',
        ucfirst(Purifier::clean($admin['profile']['first_name'])),
        '<a href="' . Router::url('/',true) . '">' . Router::url('/',true) . '</a>'
        );
$text .= ' ' . __('Cipherguard is an open source password manager.');
$text .= ' ' . __('It is designed to allow sharing credentials securely with your team!');
$text .= '<br/><br/>';
$text .= __('Let\'s take the next five minutes to get you started!');
$text .= '<br/>';
echo $this->element('Email/module/text', [
    'text' => $text
]);

echo $this->element('Email/module/button', [
    'url' => Router::url('/setup/start/' . $user['id'] . '/' . $token['token'], true),
    'text' => __('get started')
]);
