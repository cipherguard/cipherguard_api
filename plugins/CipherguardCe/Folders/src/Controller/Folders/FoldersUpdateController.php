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
use Cake\Validation\Validation;
use Cipherguard\Folders\Model\Behavior\FolderizableBehavior;
use Cipherguard\Folders\Service\Folders\FoldersUpdateService;
use Cipherguard\Metadata\Model\Dto\MetadataFolderDto;
use Cipherguard\Metadata\Service\Folders\MetadataFoldersRenderService;
use Cipherguard\Metadata\Utility\MetadataPopulateUserKeyIdTrait;

class FoldersUpdateController extends AppController
{
    use MetadataPopulateUserKeyIdTrait;

    /**
     * Folders update action
     *
     * @param string $id The identifier of the folder.
     * @return void
     * @throws \Exception
     */
    public function update(string $id)
    {
        if (!Validation::uuid($id)) {
            throw new BadRequestException(__('The folder id is not valid.'));
        }

        $uac = $this->User->getAccessControl();
        $foldersUpdateService = new FoldersUpdateService();
        $requestData = $this->populatedMetadataUserKeyId($uac->getId(), $this->getRequest()->getData());
        $folderDto = MetadataFolderDto::fromArray($requestData);

        /** @var \Cipherguard\Folders\Model\Entity\Folder $folder */
        $folder = $foldersUpdateService->update($uac, $id, $folderDto);

        // Retrieve and sanity the query options.
        $whitelist = [
            'contain' => [
                'children_folders',
                'children_resources',
                'creator',
                'modifier',
                'permission',
                'permissions',
                'permissions.group',
                'permissions.user.profile',
            ],
        ];
        $options = $this->QueryString->get($whitelist);
        $folder = $foldersUpdateService->foldersTable->findView($this->User->id(), $folder->id, $options)->first();
        $folder = FolderizableBehavior::unsetPersonalPropertyIfNull($folder->toArray());
        $folder = (new MetadataFoldersRenderService())->renderFolder($folder, $folderDto->isV5());

        $this->success(__('The folder has been updated successfully.'), $folder);
    }
}
