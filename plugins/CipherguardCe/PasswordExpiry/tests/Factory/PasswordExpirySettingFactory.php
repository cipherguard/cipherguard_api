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
 * @since         4.5.0
 */

namespace Cipherguard\PasswordExpiry\Test\Factory;

use App\Test\Factory\OrganizationSettingFactory;
use App\Utility\UuidFactory;
use Faker\Generator;
use Cipherguard\PasswordExpiry\Model\Dto\PasswordExpirySettingsDto;
use Cipherguard\PasswordExpiry\Model\Table\PasswordExpirySettingsTable;

/**
 * @method \Cipherguard\PasswordExpiry\Model\Entity\PasswordExpirySetting|\Cipherguard\PasswordExpiry\Model\Entity\PasswordExpirySetting[] persist()
 * @method \Cipherguard\PasswordExpiry\Model\Entity\PasswordExpirySetting getEntity()
 * @method \Cipherguard\PasswordExpiry\Model\Entity\PasswordExpirySetting[] getEntities()
 * @method static \Cipherguard\PasswordExpiry\Model\Entity\PasswordExpirySetting get($primaryKey, array $options = [])
 * @method static \Cipherguard\PasswordExpiry\Model\Entity\PasswordExpirySetting firstOrFail($conditions = null)
 * @see \Cipherguard\PasswordExpiry\Model\Table\PasswordExpirySettingsTable
 */
class PasswordExpirySettingFactory extends OrganizationSettingFactory
{
    /**
     * Defines the Table Registry used to generate entities with
     *
     * @return string
     */
    protected function getRootTableRegistryName(): string
    {
        return PasswordExpirySettingsTable::class;
    }

    /**
     * Defines the factory's default values. This is useful for
     * not nullable fields. You may use methods of the present factory here too.
     *
     * @return void
     */
    protected function setDefaultTemplate(): void
    {
        $this->setDefaultData(function (Generator $faker) {
            return [
                'property' => PasswordExpirySettingsTable::PROPERTY_NAME,
                'property_id' => UuidFactory::uuid('passwordExpiry'),
                'value' => $this->getDefaultValidSettings(),
                'created_by' => UuidFactory::uuid(),
                'modified_by' => UuidFactory::uuid(),
            ];
        });
    }

    protected function getDefaultValidSettings(): array
    {
        return [
            PasswordExpirySettingsDto::AUTOMATIC_EXPIRY => true,
            PasswordExpirySettingsDto::AUTOMATIC_UPDATE => true,
            PasswordExpirySettingsDto::POLICY_OVERRIDE => false,
            PasswordExpirySettingsDto::DEFAULT_EXPIRY_PERIOD => null,
        ];
    }
}
