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

use Cake\Datasource\EntityInterface;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;

class IsNotSoftDeletedRule
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
        if (!isset($options['errorField']) || !isset($options['table'])) {
            return false;
        }

        try {
            $Table = TableRegistry::getTableLocator()->get($options['table']);
            $id = $entity->get($options['errorField']);
            $lookupEntity = $Table->get($id);
            $deleted = $lookupEntity->get('deleted');
            if ($deleted instanceof FrozenTime) {
                return $deleted->isFuture();
            }

            return $lookupEntity->get('deleted') !== true;
        } catch (\Exception $e) {
        }

        return false;
    }
}
