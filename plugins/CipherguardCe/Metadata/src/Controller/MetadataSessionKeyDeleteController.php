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
use Cipherguard\Metadata\Service\MetadataSessionKeyDeleteService;

class MetadataSessionKeyDeleteController extends AppController
{
    /**
     * Delete a metadata session key.
     *
     * @param string $id The identifier of metadata session key to delete.
     * @throws \Cake\Http\Exception\BadRequestException
     * @throws \Cake\Http\Exception\NotFoundException
     * @return void
     */
    public function delete(string $id)
    {
        $this->assertJson();
        (new MetadataSessionKeyDeleteService())->delete($this->User->getAccessControl(), $id);
        $this->success(__('The operation was successful.'));
    }
}
