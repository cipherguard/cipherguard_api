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
use App\Test\Factory\UserFactory;
use Cake\Chronos\Chronos;
use CakephpFixtureFactories\Factory\BaseFactory as CakephpBaseFactory;
use Cipherguard\Metadata\Model\Entity\MetadataKey;
use Cipherguard\Metadata\Test\Utility\GpgMetadataKeysTestTrait;
use Faker\Generator;

/**
 * MetadataPrivateKeyFactory
 *
 * @method \Cipherguard\Metadata\Model\Entity\MetadataPrivateKey getEntity()
 * @method \Cipherguard\Metadata\Model\Entity\MetadataPrivateKey[] getEntities()
 * @method \Cipherguard\Metadata\Model\Entity\MetadataPrivateKey|\Cipherguard\Metadata\Model\Entity\MetadataPrivateKey[] persist()
 * @method static \Cipherguard\Metadata\Model\Entity\MetadataPrivateKey get(mixed $primaryKey, array $options = [])
 */
class MetadataPrivateKeyFactory extends CakephpBaseFactory
{
    use GpgMetadataKeysTestTrait;

    /**
     * Defines the Table Registry used to generate entities with
     *
     * @return string
     */
    protected function getRootTableRegistryName(): string
    {
        return 'Cipherguard/Metadata.MetadataPrivateKeys';
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
                'data' => $this->getDummyPrivateKeyOpenPGPMessage(),
                'created' => Chronos::now()->subDays($faker->randomNumber(3)),
                'modified' => Chronos::now()->subDays($faker->randomNumber(3)),
            ];
        });
    }

    /**
     * Sets user_id to null.
     *
     * @return $this
     */
    public function serverKey()
    {
        return $this->setField('user_id', null);
    }

    /**
     * @param MetadataKey|null $metadataKey Metadata key entity.
     * @return $this
     */
    public function withMetadataKey(?MetadataKey $metadataKey = null)
    {
        if (is_null($metadataKey)) {
            if (is_null($this->getEntity()->get('user_id'))) {
                $metadataKey = MetadataKeyFactory::make()->withServerKey()->withCreatorAndModifier()->persist();
            } else {
                // user related
                $metadataKey = MetadataKeyFactory::make()->withCreatorAndModifier()->persist();
            }
        }

        return $this->with('MetadataKeys', $metadataKey);
    }

    /**
     * @param User|null $user
     * @return $this
     */
    public function withUser(?User $user = null)
    {
        if (is_null($user)) {
            $user = UserFactory::make()->persist();
        }

        return $this->with('Users', $user);
    }

    public function withUserPrivateKey(Gpgkey $gpgkey)
    {
        return $this->patchData([
            'data' => $this->getValidPrivateKeyData($gpgkey->armored_key),
            'user_id' => $gpgkey->user_id,
        ]);
    }

    public function withServerPrivateKey()
    {
        return $this->patchData([
            'data' => $this->getValidPrivateKeyDataForServer(),
            'user_id' => null,
        ]);
    }
}
