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
 * @since         2.0.0
 */

namespace App\Model\Rule;

use App\Model\Entity\Permission;
use App\Model\Table\PermissionsTable;
use Cake\Datasource\EntityInterface;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

class HasResourceAccessRule
{
    /**
     * Performs the check
     *
     * @param \Cake\Datasource\EntityInterface $entity The entity to check
     * @param array $options Options passed to the check
     * @return bool
     */
    public function __invoke(EntityInterface $entity, array $options)
    {
        if (!isset($options['resourceField']) && !isset($options['userField'])) {
            return false;
        }
        $resourceId = $entity->get($options['resourceField']);
        $userId = $entity->get($options['userField']);
        /** @var \App\Model\Table\PermissionsTable $Permissions */
        $Permissions = TableRegistry::getTableLocator()->get('Permissions');
        $permissionType = Hash::get($options, 'permissionType', Permission::READ);

        return $Permissions->hasAccess(PermissionsTable::RESOURCE_ACO, $resourceId, $userId, $permissionType);
    }
}
