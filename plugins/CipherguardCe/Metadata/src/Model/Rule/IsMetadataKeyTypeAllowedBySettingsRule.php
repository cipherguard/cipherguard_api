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

namespace Cipherguard\Metadata\Model\Rule;

use Cake\ORM\Entity;
use Cipherguard\Metadata\Model\Entity\MetadataKey;
use Cipherguard\Metadata\Service\MetadataKeysSettingsGetService;

class IsMetadataKeyTypeAllowedBySettingsRule
{
    /**
     * Checks if the `metadata_key_type` is 'user_key' then the metadata settings allows using personal keys.
     *
     * @param \Cake\ORM\Entity $entity The entity to check
     * @param array $options Options passed to the check
     * @return bool
     */
    public function __invoke(Entity $entity, array $options): bool
    {
        // If the item's metadata key type is not personal, the present rule does not apply
        $isPersonal = $entity->get('metadata_key_type') === MetadataKey::TYPE_USER_KEY;
        if (!$isPersonal) {
            return true;
        }

        $settings = MetadataKeysSettingsGetService::getSettings();

        return $settings->isUsageOfPersonalKeysAllowed();
    }
}
