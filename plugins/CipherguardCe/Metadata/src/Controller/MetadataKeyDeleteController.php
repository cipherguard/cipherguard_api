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
namespace Cipherguard\Metadata\Controller;

use App\Controller\AppController;
use Cake\Http\Exception\BadRequestException;
use Cake\Validation\Validation;
use Cipherguard\Metadata\Service\MetadataKey\MetadataKeyDeleteService;

class MetadataKeyDeleteController extends AppController
{
    /**
     * Delete a given metadata key
     *
     * @param string $id key uuid
     * @return void
     * @throws \Cake\Http\Exception\NotFoundException if the key does not exist or is already deleted
     * @throws \Cake\Http\Exception\BadRequestException if the key id format is Invalid or some items are still using the key
     * @throws \Cake\Http\Exception\InternalErrorException if there was an issue during the save/delete
     */
    public function delete(string $id): void
    {
        $this->assertJson();
        $this->User->assertIsAdmin();
        if (!Validation::uuid($id)) {
            throw new BadRequestException(__('The metadata key ID should be a valid UUID.'));
        }

        (new MetadataKeyDeleteService())->delete($this->User->getAccessControl(), $id);
        $this->success(__('The operation was successful.'));
    }
}
