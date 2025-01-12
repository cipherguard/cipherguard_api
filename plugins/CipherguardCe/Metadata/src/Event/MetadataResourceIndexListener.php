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
namespace Cipherguard\Metadata\Event;

use App\Model\Event\TableFindIndexBefore;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\Query;

/**
 * Listens to TableFindIndexBefore::EVENT_NAME event.
 */
class MetadataResourceIndexListener implements EventListenerInterface
{
    /**
     * @inheritDoc
     */
    public function implementedEvents(): array
    {
        return [TableFindIndexBefore::EVENT_NAME => 'filterResources'];
    }

    /**
     * Delete user metadata private & session keys after user is deleted.
     *
     * @param \Cake\Event\Event $event The event.
     * @return void
     * @throws \Exception
     */
    public function filterResources(Event $event)
    {
        $query = $event->getData('query');
        /** @var \App\Model\Table\Dto\FindIndexOptions $options */
        $options = $event->getData('options');
        $filterValues = $options->getFilter();
        if (isset($filterValues['metadata_key_type'])) {
            $this->filterByMetadataKeyType($query, $filterValues['metadata_key_type']);
        }
    }

    /**
     * @param \Cake\ORM\Query $query Query to filter
     * @param string $metadataKeyType filter value
     * @return void
     */
    private function filterByMetadataKeyType(Query $query, string $metadataKeyType): void
    {
        $fieldAlias = $query->getRepository()->aliasField('metadata_key_type');
        $query->where([$fieldAlias => $metadataKeyType]);
    }
}
