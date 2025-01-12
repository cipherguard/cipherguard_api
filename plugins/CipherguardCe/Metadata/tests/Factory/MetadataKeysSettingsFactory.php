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
namespace Cipherguard\Metadata\Test\Factory;

use App\Model\Entity\OrganizationSetting;
use App\Test\Factory\OrganizationSettingFactory;
use App\Utility\UuidFactory;
use Cipherguard\Metadata\Model\Dto\MetadataKeysSettingsDto;
use Cipherguard\Metadata\Service\MetadataKeysSettingsGetService;

/**
 * MetadataKeysSettingsFactory
 */
class MetadataKeysSettingsFactory extends OrganizationSettingFactory
{
    /**
     * @inheritDoc
     */
    protected function setDefaultTemplate(): void
    {
        parent::setDefaultTemplate();

        $this->patchData([
            'property' => MetadataKeysSettingsGetService::ORG_SETTING_PROPERTY,
            'property_id' => UuidFactory::uuid(OrganizationSetting::UUID_NAMESPACE . MetadataKeysSettingsGetService::ORG_SETTING_PROPERTY),
            'value' => json_encode(self::getDefaultData()),
        ]);
    }

    public static function getDefaultData(): array
    {
        return MetadataKeysSettingsGetService::getDefaultSettingsArray();
    }

    /**
     * @return $this
     */
    public function disableUsageOfPersonalKeys()
    {
        $data = MetadataKeysSettingsFactory::getDefaultData();
        $data[MetadataKeysSettingsDto::ALLOW_USAGE_OF_PERSONAL_KEYS] = false;

        return $this->value($data);
    }

    /**
     * @return $this
     */
    public function disableZeroTrustKeySharing()
    {
        $data = MetadataKeysSettingsFactory::getDefaultData();
        $data[MetadataKeysSettingsDto::ZERO_KNOWLEDGE_KEY_SHARE] = false;

        return $this->value($data);
    }
}
