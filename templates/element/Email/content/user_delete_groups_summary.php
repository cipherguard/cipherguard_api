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
?>
 <table id="added_users" style="border:0; margin:5px 0 0 5px;">
<?php foreach ($groups as $group): ?>
    <tr>
        <td style="width:15px;">&bull;</td>
        <td><?= Purifier::clean($group['name']); ?></td>
    </tr>
<?php endforeach; ?>
</table>
