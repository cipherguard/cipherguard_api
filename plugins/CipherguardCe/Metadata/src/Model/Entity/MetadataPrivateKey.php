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
 * @copyright     Copyright (c) Cipherguard SA (https://www.cipherguard.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.cipherguard.com Cipherguard(tm)
 * @since         4.10.0
 */
namespace Cipherguard\Metadata\Model\Entity;

use Cake\ORM\Entity;

/**
 * MetadataPrivateKey Entity
 *
 * @property string $id
 * @property string $metadata_key_id
 * @property string|null $user_id
 * @property string $data
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property string|null $created_by
 * @property string|null $modified_by
 *
 * @property \Cipherguard\Metadata\Model\Entity\MetadataKey $metadata_key
 * @property \App\Model\Entity\User|null $user
 */
class MetadataPrivateKey extends Entity
{
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
        'metadata_key_id' => false,
        'user_id' => false,
        'data' => false,
        'created' => false,
        'modified' => false,
        'created_by' => false,
        'modified_by' => false,
        'metadata_key' => false,
        'user' => false,
    ];
}
