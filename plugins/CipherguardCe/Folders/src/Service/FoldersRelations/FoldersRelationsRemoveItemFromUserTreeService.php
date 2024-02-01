<?php
declare(strict_types=1);

/**
 * Cipherguard ~ Open source password manager for teams
 * Copyright (c) Khulnasoft Ltd' (https://www.cipherguard.khulnasoft.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Khulnasoft Ltd' (https://www.cipherguard.khulnasoft.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.cipherguard.khulnasoft.com Cipherguard(tm)
 * @since         2.13.0
 */

namespace Cipherguard\Folders\Service\FoldersRelations;

use Cake\ORM\TableRegistry;

class FoldersRelationsRemoveItemFromUserTreeService
{
    /**
     * @var \Cipherguard\Folders\Service\FoldersRelations\FoldersRelationsDeleteService
     */
    private $foldersRelationsDeleteService;

    /**
     * @var \Cipherguard\Folders\Model\Table\FoldersRelationsTable
     */
    private $foldersRelationsTable;

    /**
     * Instantiate the service.
     */
    public function __construct()
    {
        $this->foldersRelationsDeleteService = new FoldersRelationsDeleteService();
        $this->foldersRelationsTable = TableRegistry::getTableLocator()->get('Cipherguard/Folders.FoldersRelations');
    }

    /**
     * Remove an item from a folder tree.
     *
     * @param string $foreignId The target item
     * @param string $userId The target user
     * @param bool $moveContentToRoot (optional) Should the content be moved to root. Default false.
     * @return void
     * @throws \Exception
     */
    public function removeItemFromUserTree(string $foreignId, string $userId, ?bool $moveContentToRoot = false): void
    {
        $this->foldersRelationsTable->getConnection()->transactional(
            function () use ($userId, $foreignId, $moveContentToRoot) {
                if ($moveContentToRoot) {
                    $this->moveContentToRoot($foreignId, $userId);
                }
                $this->foldersRelationsDeleteService->delete($userId, $foreignId);
            }
        );
    }

    /**
     * Move folder content to root.
     *
     * @param string $foreignId The folder to move the content to the root
     * @param string $userId The target user tree
     * @return void
     */
    private function moveContentToRoot(string $foreignId, string $userId)
    {
        $fields = [
            'folder_parent_id' => null,
        ];
        $conditions = [
            'folder_parent_id' => $foreignId,
            'user_id' => $userId,
        ];
        $this->foldersRelationsTable->updateAll($fields, $conditions);
    }
}
