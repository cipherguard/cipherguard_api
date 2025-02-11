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
use App\Controller\Component\QueryStringComponent;
use Cake\Http\Exception\BadRequestException;
use Cake\Utility\Hash;
use Cake\Validation\Validation;
use Cipherguard\Folders\Service\Folders\FoldersDeleteService;

class FoldersDeleteController extends AppController
{
    /**
     * Folders delete action
     *
     * @param string $id The identifier of the folder.
     * @return void
     * @throws \Exception
     */
    public function delete(string $id)
    {
        if (!Validation::uuid($id)) {
            throw new BadRequestException(__('The folder id is not valid.'));
        }

        $uac = $this->User->getAccessControl();
        $data = $this->request->getQueryParams();
        $cascade = Hash::get($data, 'cascade', false);
        $cascade = QueryStringComponent::normalizeBoolean($cascade);

        $foldersDeleteService = new FoldersDeleteService();
        $foldersDeleteService->delete($uac, $id, $cascade);

        $this->success(__('The folder has been deleted successfully.'));
    }
}
