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
 * @since         4.0.0
 */
namespace Cipherguard\ResourceTypes\Service;

use Cake\ORM\Query;

interface ResourceTypesFinderInterface
{
    /**
     * Get resource types query.
     *
     * @return \Cake\ORM\Query
     */
    public function find(): Query;

    /**
     * @param \Cake\ORM\Query $query query to filter
     * @param array $options options with the filter option
     * @return void
     */
    public function filter(Query $query, array $options): void;

    /**
     * @param \Cake\ORM\Query $query query to filter
     * @param array $options options with the contain option
     * @return void
     */
    public function contain(Query $query, array $options): void;
}
