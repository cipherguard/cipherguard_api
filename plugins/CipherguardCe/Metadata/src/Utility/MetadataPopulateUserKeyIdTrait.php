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
namespace Cipherguard\Metadata\Utility;

use Cake\Http\Exception\BadRequestException;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validation;
use Cipherguard\Metadata\Model\Dto\MetadataDto;
use Cipherguard\Metadata\Model\Entity\MetadataKey;
use InvalidArgumentException;

trait MetadataPopulateUserKeyIdTrait
{
    /**
     * Update sent v5 entities request data when METADATA_KEY_TYPE TYPE_USER_KEY is set to null
     * By dynamically inserting the current gpgkey_id in its place
     *
     * @param string $userId uuid
     * @param mixed $data request data
     * @return array
     */
    public function populatedMetadataUserKeyId(string $userId, $data): array
    {
        if (!Validation::uuid($userId)) {
            throw new InvalidArgumentException(__('Invalid user ID format.'));
        }
        if (!isset($data) || !is_array($data)) {
            throw new BadRequestException(__('The data is required.'));
        }
        if (
            isset($data[MetadataDto::METADATA])
            && isset($data[MetadataDto::METADATA_KEY_TYPE])
            && is_string($data[MetadataDto::METADATA_KEY_TYPE])
            && $data[MetadataDto::METADATA_KEY_TYPE] === MetadataKey::TYPE_USER_KEY
            && !isset($data[MetadataDto::METADATA_KEY_ID])
        ) {
            $keyTable = TableRegistry::getTableLocator()->get('Gpgkeys');
            $key = $keyTable->find('current', ['user_id' => $userId])->firstOrFail();
            $id = $key->get('id');
            if (Validation::uuid($id)) {
                $data[MetadataDto::METADATA_KEY_ID] = $id;
            }
        }

        return $data;
    }
}
