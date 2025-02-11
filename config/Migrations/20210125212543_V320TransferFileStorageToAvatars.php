<?php
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
 * @since         3.0.0
 */

use App\Service\Avatars\AvatarsTransferService;
use App\Utility\Filesystem\DirectoryUtility;
use Cake\Core\Configure;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;
use Laminas\Diactoros\UploadedFile;
use League\Flysystem\FilesystemException;
use Migrations\AbstractMigration;

class V320TransferFileStorageToAvatars extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function up()
    {
        /** @var \App\Model\Table\AvatarsTable $AvatarsTable */
        $AvatarsTable = TableRegistry::getTableLocator()->get('Avatars');
        $FileStorageTable = TableRegistry::getTableLocator()
            ->get('FileStorage')
            ->setTable('file_storage');

        // This line is required for Postgres support.
        $this->getAdapter()->commitTransaction();

        try {
            (new AvatarsTransferService($AvatarsTable, $FileStorageTable))->transfer();
        } catch (\Throwable $e) {
            Log::error('There was an error in V320TransferFileStorageToAvatars');
            Log::error($e->getMessage());
        }
    }

    public function down()
    {}
}
