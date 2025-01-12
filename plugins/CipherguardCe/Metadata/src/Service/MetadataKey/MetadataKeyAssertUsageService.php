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
namespace Cipherguard\Metadata\Service\MetadataKey;

use Cake\Core\Configure;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Validation\Validation;

class MetadataKeyAssertUsageService
{
    use LocatorAwareTrait;

    /**
     * @param string $metadataKeyId key uuid
     * @param bool $assertKeyId assert key id default true
     * @return void
     * @throws \InvalidArgumentException if $metadataKeyId is not a valid uuid
     */
    private function assertKeyId(string $metadataKeyId, bool $assertKeyId = true): void
    {
        if ($assertKeyId && !Validation::uuid($metadataKeyId)) {
            throw new \InvalidArgumentException(__('The metadata key ID should be a valid UUID.'));
        }
    }

    /**
     * @param string $tableName name
     * @param string $metadataKeyId key uuid
     * @param bool $assertKeyId assert key id default true
     * @return bool if some tags are using the metadata key
     * @throws \InvalidArgumentException if $metadataKeyId is not a valid uuid and assertKeyId true
     */
    private function isUsedByTable(string $tableName, string $metadataKeyId, bool $assertKeyId = true): bool
    {
        $this->assertKeyId($metadataKeyId, $assertKeyId);

        return $this->fetchTable($tableName)
                ->find()
                ->where(['metadata_key_id' => $metadataKeyId])
                ->all()
                ->count() > 0;
    }

    /**
     * @param string $metadataKeyId key uuid
     * @return bool if some items are using the metadata key
     * @throws \InvalidArgumentException if $metadataKeyId is not a valid uuid
     */
    public function isKeyInUse(string $metadataKeyId): bool
    {
        $this->assertKeyId($metadataKeyId);

        return $this->isUsedByResources($metadataKeyId, false)
            || $this->isUsedByFolders($metadataKeyId, false)
            || $this->isUsedByTags($metadataKeyId, false);
    }

    /**
     * @param string $metadataKeyId key uuid
     * @param bool $assertKeyId assert key id default true
     * @return bool if some folders are using the metadata key
     * @throws \InvalidArgumentException if $metadataKeyId is not a valid uuid and assertKeyId true
     */
    public function isUsedByResources(string $metadataKeyId, bool $assertKeyId = true): bool
    {
        return $this->isUsedByTable('Resources', $metadataKeyId, $assertKeyId);
    }

    /**
     * @param string $metadataKeyId key uuid
     * @param bool $assertKeyId assert key id default true
     * @return bool if some folders are using the metadata key
     * @throws \InvalidArgumentException if $metadataKeyId is not a valid uuid and assertKeyId true
     */
    public function isUsedByFolders(string $metadataKeyId, bool $assertKeyId = true): bool
    {
        if (Configure::read('cipherguard.plugins.folders.enabled')) {
            return $this->isUsedByTable('Cipherguard/Folders.Folders', $metadataKeyId, $assertKeyId);
        }

        return false;
    }

    /**
     * @param string $metadataKeyId key uuid
     * @param bool $assertKeyId assert key id default true
     * @return bool if some tags are using the metadata key
     * @throws \InvalidArgumentException if $metadataKeyId is not a valid uuid and assertKeyId true
     */
    public function isUsedByTags(string $metadataKeyId, bool $assertKeyId = true): bool
    {
        if (Configure::read('cipherguard.plugins.tags')) {
            return $this->isUsedByTable('Cipherguard/Tags.Tags', $metadataKeyId, $assertKeyId);
        }

        return false;
    }
}
