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

namespace Cipherguard\Metadata\Model\Rule;

use Cake\Datasource\EntityInterface;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validation;
use Cipherguard\Metadata\Model\Entity\MetadataKey;

class MetadataKeyIdNotExpiredRule
{
    /**
     * @param \Cake\Datasource\EntityInterface $entity The entity to check
     * @param array $options Options passed to the check
     * @return bool
     */
    public function __invoke(EntityInterface $entity, array $options): bool
    {
        $metadataKeyType = $entity->get('metadata_key_type');
        $id = $entity->get('metadata_key_id');

        if (!isset($id) || !is_string($id) || !Validation::uuid($id)) {
            return false;
        }
        if (!isset($metadataKeyType) || !is_string($metadataKeyType)) {
            return false;
        }

        if ($metadataKeyType === MetadataKey::TYPE_USER_KEY) {
            $table = TableRegistry::getTableLocator()->get('Gpgkeys');
            $conditions = ['id' => $id, 'expires IS NULL'];

            return $table->exists($conditions);
        }

        if ($metadataKeyType === MetadataKey::TYPE_SHARED_KEY) {
            $table = TableRegistry::getTableLocator()->get('Cipherguard/Metadata.MetadataKeys');
            $conditions = ['id' => $id, 'expired IS NULL'];

            return $table->exists($conditions);
        }

        return false;
    }
}
