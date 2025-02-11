<?php
declare(strict_types=1);

/**
 * Cipherguard ~ Open source password manager for teams
 * Copyright (c) Cipherguard SA (https://www.cipherguard.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cipherguard SARL (https://www.cipherguard.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.cipherguard.com Cipherguard(tm)
 * @since         2.0.0
 */

namespace Cipherguard\Log\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property string $id
 * @property string $action_log_id
 * @property string $foreign_model
 * @property string $foreign_key
 * @property string $crud
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cipherguard\Log\Model\Entity\ActionLog $action_log
 * @property \Cipherguard\Log\Model\Entity\PermissionHistory $permissions_history
 * @property \Cipherguard\Folders\Model\Entity\FolderHistory $folders_history
 * @property \Cipherguard\Log\Model\Entity\SecretHistory $secrets_history
 * @property \App\Model\Entity\Resource $resource
 * @property \Cipherguard\Log\Model\Entity\SecretAccess $secret_access
 * @property \App\Model\Entity\User $user
 */
class EntityHistory extends Entity
{
    public const CRUD_CREATE = 'c';
    public const CRUD_READ = 'r';
    public const CRUD_UPDATE = 'u';
    public const CRUD_DELETE = 'd';

    /**
     * Allowed CRUD operations.
     */
    public const CRUD = [
        self::CRUD_CREATE,
        self::CRUD_READ,
        self::CRUD_UPDATE,
        self::CRUD_DELETE,
    ];

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
        'action_log_id' => false,
        'foreign_key' => false,
        'foreign_model' => false,
        'crud' => false,
        'created' => false,
    ];
}
