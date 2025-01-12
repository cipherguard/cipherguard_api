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
 * MetadataKey Entity
 *
 * @property string $id
 * @property string $fingerprint
 * @property string $armored_key
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property \Cake\I18n\FrozenTime|null $expired
 * @property \Cake\I18n\FrozenTime|null $deleted
 * @property string $created_by
 * @property string $modified_by
 *
 * @property \App\Model\Entity\User|null $creator
 * @property \App\Model\Entity\User|null $modifier
 * @property \Cipherguard\Metadata\Model\Entity\MetadataPrivateKey[] $metadata_private_keys
 */
class MetadataKey extends Entity
{
    public const TYPE_USER_KEY = 'user_key';
    public const TYPE_SHARED_KEY = 'shared_key';

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
        'fingerprint' => false,
        'armored_key' => false,
        'created' => false,
        'modified' => false,
        'expired' => false,
        'deleted' => false,
        'created_by' => false,
        'modified_by' => false,
        'metadata_private_keys' => false,
    ];

    /**
     * @return bool true if deleted is set
     */
    public function isDeleted(): bool
    {
        return $this->deleted !== null;
    }

    /**
     * @return bool true if expired is set
     */
    public function isExpired(): bool
    {
        return $this->expired !== null;
    }
}
