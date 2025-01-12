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

use App\Model\Entity\Gpgkey;
use App\Model\Entity\User;
use App\Test\Factory\Traits\ArmoredKeyFactoryTrait;
use App\Test\Factory\UserFactory;
use Cake\Chronos\Chronos;
use Cake\I18n\FrozenTime;
use CakephpFixtureFactories\Factory\BaseFactory as CakephpBaseFactory;
use Cipherguard\Metadata\Test\Utility\GpgMetadataKeysTestTrait;
use Faker\Generator;

/**
 * Metadata key factory.

 * @method \Cipherguard\Metadata\Model\Entity\MetadataKey|\Cipherguard\Metadata\Model\Entity\MetadataKey[] persist()
 * @method \Cipherguard\Metadata\Model\Entity\MetadataKey getEntity()
 * @method \Cipherguard\Metadata\Model\Entity\MetadataKey[] getEntities()
 */
class MetadataKeyFactory extends CakephpBaseFactory
{
    use ArmoredKeyFactoryTrait;
    use GpgMetadataKeysTestTrait;

    /**
     * Defines the Table Registry used to generate entities with
     *
     * @return string
     */
    protected function getRootTableRegistryName(): string
    {
        return 'Cipherguard/Metadata.MetadataKeys';
    }

    /**
     * Defines the factory's default values. This is useful for
     * not nullable fields. You may use methods of the present factory here too.
     *
     * @return void
     */
    protected function setDefaultTemplate(): void
    {
        $dummyData = $this->getMetadataKeyInfo();

        $this->setDefaultData(function (Generator $faker) use ($dummyData) {
            return [
                'fingerprint' => $dummyData['fingerprint'],
                'armored_key' => $dummyData['public_key'],
                'created' => Chronos::now()->subDays($faker->randomNumber(3)),
                'modified' => Chronos::now()->subDays($faker->randomNumber(3)),
                'expired' => null,
                'deleted' => null,
                'created_by' => $faker->uuid(),
                'modified_by' => $faker->uuid(),
            ];
        });
    }

    /**
     * Set deleted in the past.
     *
     * @return $this
     */
    public function deleted()
    {
        return $this->setField('deleted', FrozenTime::yesterday());
    }

    /**
     * Set expired in the past.
     *
     * @return $this
     */
    public function expired()
    {
        return $this->setField('expired', FrozenTime::yesterday());
    }

    /**
     * @return $this
     */
    public function withExpiredKey()
    {
        $keyInfo = $this->getExpiredKeyInfo();

        return $this->patchData([
            'armored_key' => $keyInfo['armored_key'],
            'fingerprint' => $keyInfo['fingerprint'],
        ]);
    }

    /**
     * @return $this
     */
    public function withServerKey()
    {
        $keyInfo = $this->getMetadataKeyInfo();

        return $this->patchData([
            'armored_key' => $keyInfo['public_key'],
            'fingerprint' => $keyInfo['fingerprint'],
        ]);
    }

    public function withCreatorAndModifier(?User $user = null)
    {
        return $this->withModifier($user)->withCreator($user);
    }

    public function withModifier(?User $user = null)
    {
        if (is_null($user)) {
            $user = UserFactory::make()->persist();
        }

        return $this->with('Modifier', $user)->setField('modified_by', $user->get('id'));
    }

    public function withCreator(?User $user = null)
    {
        if (is_null($user)) {
            $user = UserFactory::make()->persist();
        }

        return $this->with('Creator', $user)->setField('created_by', $user->get('id'));
    }

    public function withUserPrivateKey(Gpgkey $gpgkey)
    {
        return $this->with('MetadataPrivateKeys', MetadataPrivateKeyFactory::make()->withUserPrivateKey($gpgkey));
    }

    public function withServerPrivateKey()
    {
        return $this->with('MetadataPrivateKeys', MetadataPrivateKeyFactory::make()->withServerPrivateKey());
    }
}
