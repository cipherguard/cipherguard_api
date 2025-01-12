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
use Cake\ORM\Locator\LocatorAwareTrait;

class MetadataSessionKeysGetService
{
    use LocatorAwareTrait;

    /**
     * @param \App\Utility\UserAccessControl $uac UAC.
     * @return \Cipherguard\Metadata\Model\Entity\MetadataSessionKey[]
     */
    public function get(UserAccessControl $uac): array
    {
        /** @var \Cipherguard\Metadata\Model\Table\MetadataSessionKeysTable $metadataSessionKeysTable */
        $metadataSessionKeysTable = $this->fetchTable('Cipherguard/Metadata.MetadataSessionKeys');

        return $metadataSessionKeysTable->find()->where(['user_id' => $uac->getId()])->toArray();
    }
}
