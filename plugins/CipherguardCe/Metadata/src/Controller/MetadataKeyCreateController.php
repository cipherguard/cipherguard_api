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
use App\Error\Exception\FormValidationException;
use Cipherguard\Metadata\Form\MetadataKeyForm;
use Cipherguard\Metadata\Model\Dto\MetadataKeyDto;
use Cipherguard\Metadata\Service\MetadataKeyCreateService;

class MetadataKeyCreateController extends AppController
{
    /**
     * Metadata key save action.
     *
     * @return void
     */
    public function create()
    {
        $this->assertJson();
        $this->User->assertIsAdmin();
        $this->assertNotEmptyArrayData();

        $form = new MetadataKeyForm();
        if (!$form->execute($this->getRequest()->getData())) {
            throw new FormValidationException(__('Could not validate the metadata key data.'), $form);
        }

        $dto = MetadataKeyDto::fromArray($form->getData());
        $uac = $this->User->getAccessControl();
        $metadataKey = (new MetadataKeyCreateService())->create($uac, $dto);

        $this->success(__('The operation was successful.'), $metadataKey);
    }
}
