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
 * @since         2.13.0
 */

namespace Cipherguard\Folders\Controller\Folders;

use App\Controller\AppController;
use Cake\Http\Exception\BadRequestException;
use Cake\Utility\Hash;
use Cake\Validation\Validation;
use Cipherguard\Folders\Service\Folders\FoldersShareService;
use Cipherguard\Metadata\Model\Dto\MetadataFolderDto;
use Cipherguard\Metadata\Service\Folders\MetadataFoldersRenderService;

class FoldersShareController extends AppController
{
    /**
     * Folders update permissions action
     *
     * @param string $id The identifier of the folder.
     * @return void
     * @throws \Cake\Http\Exception\BadRequestException If the folder id is not valid
     * @throws \Exception If an unexpected error occurred.
     */
    public function share(string $id)
    {
        if (!Validation::uuid($id)) {
            throw new BadRequestException(__('The folder id is not valid.'));
        }

        $uac = $this->User->getAccessControl();
        $foldersUpdatePermissionsService = new FoldersShareService();

        $data = $this->getData();

        /** @var \Cipherguard\Folders\Model\Entity\Folder $folder */
        $folder = $foldersUpdatePermissionsService->share($uac, $id, $data);
        $folderDto = MetadataFolderDto::fromArray($folder->toArray());
        $folder = (new MetadataFoldersRenderService())->renderFolder($folder->toArray(), $folderDto->isV5());

        $this->success(__('The operation was successful.'), $folder);
    }

    /**
     * Extract data from the request body.
     *
     * @return array
     */
    private function getData()
    {
        $data = [];
        $body = $this->getRequest()->getParsedBody();

        $name = Hash::get($body, 'name');
        if (isset($name)) {
            $data['name'] = $name;
        }

        if (array_key_exists('folder_parent_id', $body)) {
            $data['folder_parent_id'] = $body['folder_parent_id'];
        }
        if (array_key_exists('permissions', $body)) {
            $data['permissions'] = $body['permissions'];
        }

        return $data;
    }
}
