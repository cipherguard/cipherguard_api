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

class MetadataKeyIdExistsInRule
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

        if ($metadataKeyType === 'user_key') {
            $table = TableRegistry::getTableLocator()->get('Gpgkeys');
            $conditions = ['id' => $id, 'deleted' => false];
        } else {
            $table = TableRegistry::getTableLocator()->get('Cipherguard/Metadata.MetadataKeys');
            $conditions = ['id' => $id, 'deleted IS NULL'];
        }

        return $table->exists($conditions);
    }
}
