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
use Cipherguard\Metadata\Service\MetadataKeysSettingsSetService;

class MetadataKeysSettingsPostController extends AppController
{
    /**
     * Metadata settings POST action
     *
     * @return void
     */
    public function post()
    {
        $this->User->assertIsAdmin();
        $this->assertNotEmptyArrayData();

        $service = new MetadataKeysSettingsSetService();
        $settings = $service->saveSettings($this->User->getAccessControl(), $this->getRequest()->getData());

        $this->success(__('The operation was successful.'), $settings->toArray());
    }
}
