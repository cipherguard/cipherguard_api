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
namespace Cipherguard\Metadata\Service;

use App\Utility\UserAccessControl;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\InternalErrorException;
use Cake\Http\Exception\NotFoundException;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Validation\Validation;

class MetadataSessionKeyDeleteService
{
    use LocatorAwareTrait;

    /**
     * Delete the given metadata session key.
     *
     * @param \App\Utility\UserAccessControl $uac UAC.
     * @param string $id The metadata session key identifier.
     * @return void
     */
    public function delete(UserAccessControl $uac, string $id): void
    {
        if (!Validation::uuid($id)) {
            throw new BadRequestException(__('The metadata session key identifier should be a UUID.'));
        }

        /** @var \Cipherguard\Metadata\Model\Table\MetadataSessionKeysTable $metadataSessionKeysTable */
        $metadataSessionKeysTable = $this->fetchTable('Cipherguard/Metadata.MetadataSessionKeys');

        try {
            /** @var \Cipherguard\Metadata\Model\Entity\MetadataSessionKey $metadataSessionKey */
            $metadataSessionKey = $metadataSessionKeysTable
                ->find()
                ->where(['id' => $id, 'user_id' => $uac->getId()])
                ->firstOrFail();
        } catch (RecordNotFoundException $e) {
            throw new NotFoundException(__('The metadata session key does not exist or does not belong to this user.'));
        }

        if (!$metadataSessionKeysTable->delete($metadataSessionKey)) {
            throw new InternalErrorException(__('The metadata session key could not be deleted.'));
        }
    }
}
