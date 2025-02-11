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
use Cake\Http\Exception\NotFoundException;
use Cake\Validation\Validation;
use Cipherguard\Folders\Model\Behavior\FolderizableBehavior;
use Cipherguard\Metadata\Model\Dto\MetadataFolderDto;
use Cipherguard\Metadata\Service\Folders\MetadataFoldersRenderService;

/**
 * @property \Cipherguard\Folders\Model\Table\FoldersTable $Folders
 */
class FoldersViewController extends AppController
{
    /**
     * Folder View action
     *
     * @param string $id uuid Identifier of the folder
     * @return void
     * @throws \Cake\Http\Exception\NotFoundException if the folder does not exist
     * @throws \Cake\Http\Exception\BadRequestException if the folder id is not a uuid
     */
    public function view(string $id)
    {
        if (!Validation::uuid($id)) {
            throw new BadRequestException(__('The folder id is not valid.'));
        }

        /** @var \Cipherguard\Folders\Model\Table\FoldersTable $foldersTable */
        $foldersTable = $this->fetchTable('Cipherguard/Folders.Folders');

        // Retrieve and sanity the query options.
        $whitelist = [
            'contain' => [
                'children_folders',
                'children_resources',
                'creator',
                'creator.profile',
                'modifier',
                'modifier.profile',
                'permission',
                'permissions',
                'permissions.group',
                'permissions.user.profile',
            ],
            'filter' => ['has-id'],
        ];
        $options = $this->QueryString->get($whitelist);

        $folder = $foldersTable->findView($this->User->id(), $id, $options)->first();
        if (empty($folder)) {
            throw new NotFoundException('The folder does not exist.');
        }

        $folder = FolderizableBehavior::unsetPersonalPropertyIfNull($folder->toArray());
        $folderDto = MetadataFolderDto::fromArray($folder);
        $folder = (new MetadataFoldersRenderService())->renderFolder($folder, $folderDto->isV5());

        $this->success(__('The operation was successful.'), $folder);
    }
}
