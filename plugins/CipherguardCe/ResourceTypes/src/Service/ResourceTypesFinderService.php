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
 * @since         3.0.0
 */
namespace Cipherguard\ResourceTypes\Service;

use Cake\Database\Expression\IdentifierExpression;
use Cake\Database\Expression\QueryExpression;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Cipherguard\ResourceTypes\Model\Entity\ResourceType;
use Cipherguard\ResourceTypes\Model\Table\ResourceTypesTable;

class ResourceTypesFinderService implements ResourceTypesFinderInterface
{
    protected ResourceTypesTable $resourceTypesTable;

    /**
     * Instantiate the service.
     */
    public function __construct()
    {
        $this->resourceTypesTable = TableRegistry::getTableLocator()->get('Cipherguard/ResourceTypes.ResourceTypes');
    }

    /**
     * Returns resource types without TOTP resource types.
     *
     * @return \Cake\ORM\Query
     */
    public function find(): Query
    {
        return $this->resourceTypesTable
            ->find()
            ->where([
                'slug NOT IN' => [
                    ResourceType::SLUG_STANDALONE_TOTP,
                    ResourceType::SLUG_PASSWORD_DESCRIPTION_TOTP,
                ],
            ])
            ->formatResults(ResourceTypesTable::resultFormatter());
    }

    /**
     * Get a resource type by Id
     *
     * @param string $id uuid
     * @throws \Cake\Datasource\Exception\RecordNotFoundException if resource type is not present
     * @return array|\Cake\Datasource\EntityInterface
     */
    public function get(string $id)
    {
        return $this->find()
            ->where(['id' => $id])
            ->firstOrFail();
    }

    /**
     * @inheritDoc
     */
    public function filter(Query $query, array $options): void
    {
        if (isset($options['filter']['is-deleted'])) {
            $isDeleted = (bool)$options['filter']['is-deleted'];
            $deletedField = $query->getRepository()->aliasField('deleted');
            if ($isDeleted) {
                $query->where(function (QueryExpression $exp) use ($deletedField) {
                    return $exp->isNotNull($deletedField);
                });
            } else {
                $query->where(function (QueryExpression $exp) use ($deletedField) {
                    return $exp->isNull($deletedField);
                });
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function contain(Query $query, array $options): void
    {
        if (isset($options['contain']['resources_count'])) {
            $containResourcesCount = (bool)$options['contain']['resources_count'];
            if ($containResourcesCount) {
                $query->leftJoinWith('Resources')
                    ->selectAlso([
                        'resources_count' => new IdentifierExpression('COUNT(Resources.id)'),
                    ])
                    ->group($query->getRepository()->aliasField('id'));
                $query->getSelectTypeMap()->addDefaults(['resources_count' => 'integer']);
            }
        }
    }
}
