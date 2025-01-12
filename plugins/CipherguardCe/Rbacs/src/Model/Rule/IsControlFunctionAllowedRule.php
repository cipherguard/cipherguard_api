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
 * @since         4.5.0
 */

namespace Cipherguard\Rbacs\Model\Rule;

use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cipherguard\Rbacs\Model\Entity\Rbac;
use Cipherguard\Rbacs\Model\Entity\UiAction;

class IsControlFunctionAllowedRule
{
    use LocatorAwareTrait;

    /**
     * @param \Cake\Datasource\EntityInterface $entity The entity to check
     * @param array $options Options passed to the check
     * @return bool
     */
    public function __invoke(EntityInterface $entity, array $options)
    {
        if ($entity->get('control_function') === null || $entity->get('foreign_id') === null) {
            return false;
        }

        // Validate only for UI Action for now
        if ($entity->get('foreign_model') !== Rbac::FOREIGN_MODEL_UI_ACTION) {
            return true;
        }

        $uiActionsTable = $this->fetchTable('Cipherguard/Rbacs.UiActions');

        try {
            /** @var \Cipherguard\Rbacs\Model\Entity\UiAction $uiAction */
            $uiAction = $uiActionsTable
                ->find()
                ->select(['name'])
                ->where(['id' => $entity->get('foreign_id')])
                ->firstOrFail();
        } catch (RecordNotFoundException $e) {
            return false;
        }

        if (!isset(UiAction::CONTROL_FUNCTION_MAPPING[$uiAction->name])) {
            return false;
        }

        return in_array($entity->get('control_function'), UiAction::CONTROL_FUNCTION_MAPPING[$uiAction->name]);
    }
}
