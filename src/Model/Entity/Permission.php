<?php
declare(strict_types=1);

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
 * @since         2.0.0
 */

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Permission Entity
 *
 * @property string $id
 * @property string $aco
 * @property string $aco_foreign_key
 * @property string $aro
 * @property string|null $aro_foreign_key
 * @property int $type
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property \App\Model\Entity\Group|null $group
 * @property \App\Model\Entity\Resource $resource
 * @property \App\Model\Entity\User|null $user
 * @property \Cipherguard\Log\Model\Entity\PermissionHistory $permissions_history
 */
class Permission extends Entity
{
    /**
     * The types of permissions.
     */
    public const READ = 1;
    public const UPDATE = 7;
    public const OWNER = 15;

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected $_accessible = [
        'aco' => false,
        'aco_foreign_key' => false,
        'aro' => false,
        'aro_foreign_key' => false,
        'type' => false,
        'created' => false,
        'modified' => false,

        // Associated entities
        'group' => false,
        'user' => false,
    ];
}
