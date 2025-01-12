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

use Cake\ORM\Locator\LocatorAwareTrait;
use Cipherguard\Metadata\Service\MetadataKeyShareDefaultService;
use Cipherguard\Metadata\Service\MetadataKeyShareNothingService;
use Cipherguard\Metadata\Service\MetadataKeyShareServiceInterface;
use Cipherguard\Metadata\Service\MetadataKeysSettingsGetService;

class ShareMetadataKeyServiceFactory
{
    use LocatorAwareTrait;

    /**
     * @return \Cipherguard\Metadata\Service\MetadataKeyShareServiceInterface
     */
    public function get(): MetadataKeyShareServiceInterface
    {
        // Nothing to share
        $metadataKeysTable = $this->fetchTable('Cipherguard/Metadata.MetadataKeys');
        $keyCount = $metadataKeysTable->find()->all()->count();
        if ($keyCount === 0) {
            return new MetadataKeyShareNothingService();
        }

        // Key is sharable by server directly
        $settings = MetadataKeysSettingsGetService::getSettings();
        if (!$settings->isKeyShareZeroKnowledge()) {
            return new MetadataKeyShareDefaultService();
        } else {
            // Trigger an email prompt for an admin to login, and share it via the web extension
            return new MetadataKeyShareNothingService();
        }
    }
}
