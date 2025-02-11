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
 * @since         3.8.0
 */
namespace App\Service\ResourceTypes;

use Cake\ORM\TableRegistry;

class ResourceTypesTrimSpacesService
{
    /**
     * @var \Cipherguard\ResourceTypes\Model\Table\ResourceTypesTable
     */
    private $resourceTypesTable;

    /**
     * Instantiate the service.
     */
    public function __construct()
    {
        /** @phpstan-ignore-next-line */
        $this->resourceTypesTable = TableRegistry::getTableLocator()->get('ResourceTypes');
    }

    /**
     * Trim resource types slug and name
     *
     * @return void
     * @throws \Cake\ORM\Exception\PersistenceFailedException
     */
    public function trim(): void
    {
        $resourceTypes = $this->resourceTypesTable->find();
        /** @var \Cipherguard\ResourceTypes\Model\Entity\ResourceType $resourceType */
        foreach ($resourceTypes as $resourceType) {
            $slug = $resourceType->slug;
            $name = $resourceType->name;
            $trimmedSlug = trim($slug);
            $trimmedName = trim($name);
            if ($slug !== $trimmedSlug) {
                $resourceType->slug = $trimmedSlug;
            }
            if ($name !== $trimmedName) {
                $resourceType->name = $trimmedName;
            }
            $this->resourceTypesTable->saveOrFail($resourceType);
        }
    }
}
