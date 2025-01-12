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
use Cipherguard\Metadata\Service\MetadataPrivateKeysUpdateService;

class MetadataPrivateKeysUpdateController extends AppController
{
    /**
     * Update a user private key
     *
     * @param string $id private key id
     * @return void
     */
    public function update(string $id)
    {
        $this->assertJson();
        $this->assertNotEmptyArrayData();

        if (!Validation::uuid($id)) {
            throw new BadRequestException(__('The private key identifier should be a UUID.'));
        }
        $data = $this->request->getData();

        $updated = (new MetadataPrivateKeysUpdateService())->update($this->User->getAccessControl(), $id, $data);
        $this->success(__('The operation was successful.'), $updated);
    }
}
