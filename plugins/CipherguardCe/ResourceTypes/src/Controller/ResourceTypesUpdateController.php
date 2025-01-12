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

namespace Cipherguard\ResourceTypes\Controller;

use App\Controller\AppController;
use Cake\Http\Exception\BadRequestException;
use Cipherguard\ResourceTypes\Service\ResourceTypesDeleteService;

class ResourceTypesUpdateController extends AppController
{
    /**
     * Update a resource type.
     *
     * @param string $id The identifier of resource type to delete.
     * @throws \Cake\Http\Exception\BadRequestException
     * @throws \Cake\Http\Exception\NotFoundException
     * @return void
     */
    public function update(string $id)
    {
        $this->assertJson();
        $this->User->assertIsAdmin();
        $this->assertNotEmptyArrayData();
        $data = $this->request->getData();
        if (!array_key_exists('deleted', $data) || $data['deleted'] !== null) {
            throw new BadRequestException(__('The deleted field must be set to null.'));
        }
        if (count($data) > 1) {
            throw new BadRequestException(__('It is not allowed to update these resource type properties.'));
        }

        (new ResourceTypesDeleteService())->undoDelete($this->User->getAccessControl(), $id);
        $this->success(__('The operation was successful.'));
    }
}
