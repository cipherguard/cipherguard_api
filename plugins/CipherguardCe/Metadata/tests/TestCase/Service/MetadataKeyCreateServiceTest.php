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

namespace Cipherguard\Metadata\Test\TestCase\Service;

use App\Error\Exception\CustomValidationException;
use App\Test\Factory\GpgkeyFactory;
use App\Test\Factory\UserFactory;
use App\Test\Lib\AppTestCaseV5;
use App\Utility\UuidFactory;
use Cake\Utility\Hash;
use Cipherguard\Metadata\Model\Dto\MetadataKeyDto;
use Cipherguard\Metadata\Model\Entity\MetadataKey;
use Cipherguard\Metadata\Service\MetadataKeyCreateService;
use Cipherguard\Metadata\Test\Factory\MetadataKeyFactory;
use Cipherguard\Metadata\Test\Factory\MetadataPrivateKeyFactory;
use Cipherguard\Metadata\Test\Utility\GpgMetadataKeysTestTrait;

/**
 * @covers \Cipherguard\Metadata\Service\MetadataKeyCreateService
 */
class MetadataKeyCreateServiceTest extends AppTestCaseV5
{
    use GpgMetadataKeysTestTrait;

    /**
     * @var MetadataKeyCreateService|null
     */
    private ?MetadataKeyCreateService $service = null;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->service = new MetadataKeyCreateService();
    }

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        unset($this->service);

        parent::tearDown();
    }

    public function testMetadataKeyCreateService_Success(): void
    {
        $keyInfo = $this->getUserKeyInfo();
        $gpgkey = GpgkeyFactory::make(['armored_key' => $keyInfo['armored_key'], 'fingerprint' => $keyInfo['fingerprint']]);
        $user = UserFactory::make()
            ->with('Gpgkeys', $gpgkey)
            ->admin()
            ->active()
            ->persist();
        $uac = $this->makeUac($user);
        $dummyKey = $this->getMetadataKeyInfo();

        $dto = MetadataKeyDto::fromArray([
            'armored_key' => $dummyKey['public_key'],
            'fingerprint' => $dummyKey['fingerprint'],
            'metadata_private_keys' => [
                [
                    'user_id' => null, // server key
                    'data' => $this->getEncryptedMetadataPrivateKeyForServerKey(),
                ],
                [
                    'user_id' => $uac->getId(),
                    'data' => $this->getEncryptedMetadataPrivateKeyFoUser(),
                ],
            ],
        ]);
        $result = $this->service->create($uac, $dto);

        $this->assertInstanceOf(MetadataKey::class, $result);
        $metadataKeys = MetadataKeyFactory::find()->all()->toArray();
        $this->assertCount(1, $metadataKeys);
        $metadataPrivateKeys = MetadataPrivateKeyFactory::find()->all()->toArray();
        $this->assertCount(2, $metadataPrivateKeys);
    }

    public function invalidMetadataKeyDataProvider(): array
    {
        $dummyKey = $this->getMetadataKeyInfo();
        $makiKey = $this->getUserKeyInfo();
        $expiredKey = $this->getExpiredKeyInfo();
        $msgForServer = $this->getEncryptedMetadataPrivateKeyForServerKey();
        $invalidAlgKey = $this->getInvalidAlgKeyInfo();

        return [
            [
                'data (invalid types)' => [
                    'armored_key' => 'bar-foo',
                    'fingerprint' => '🔥🔥🔥',
                    'metadata_private_keys' => [
                        [
                            'user_id' => 'foo-bar',
                            'data' => 'some data',
                        ],
                        [
                            'user_id' => 1230,
                            'data' => '*()_+(!#$%',
                        ],
                    ],
                ],
                'expected errors paths' => [
                    'armored_key.isParsableArmoredPublicKey',
                    'fingerprint.alphaNumeric',
                    'metadata_private_keys.{n}.user_id.uuid',
                    'metadata_private_keys.{n}.data.isValidOpenPGPMessage',
                ],
            ],
            [
                'data (expired armored key)' => [
                    'armored_key' => $expiredKey['armored_key'],
                    'fingerprint' => $expiredKey['fingerprint'],
                    'metadata_private_keys' => [
                        [
                            'user_id' => UuidFactory::uuid(),
                            'data' => $this->getDummyPrivateKeyOpenPGPMessage(),
                        ],
                        [
                            'user_id' => null,
                            'data' => $this->getDummyPrivateKeyOpenPGPMessage(),
                        ],
                    ],
                ],
                'expected errors paths' => ['armored_key.isPublicKeyValidStrict'],
            ],
            [
                'data (more than one user_id null)' => [
                    'armored_key' => $dummyKey['public_key'],
                    'fingerprint' => $dummyKey['fingerprint'],
                    'metadata_private_keys' => [
                        [
                            'user_id' => null,
                            'data' => $msgForServer,
                        ],
                        [
                            'user_id' => null,
                            'data' => $msgForServer,
                        ],
                    ],
                ],
                'expected errors paths' => ['metadata_private_keys.{n}.user_id._isUnique'],
            ],
            [
                'data (more than one invalid uuid in user_id)' => [
                    'armored_key' => $dummyKey['public_key'],
                    'fingerprint' => $dummyKey['fingerprint'],
                    'metadata_private_keys' => [
                        [
                            'user_id' => 'foo-bar',
                            'data' => $this->getDummyPrivateKeyOpenPGPMessage(),
                        ],
                        [
                            'user_id' => '🔥🔥🔥',
                            'data' => $this->getDummyPrivateKeyOpenPGPMessage(),
                        ],
                        [
                            'user_id' => 12345,
                            'data' => $this->getDummyPrivateKeyOpenPGPMessage(),
                        ],
                    ],
                ],
                'expected errors paths' => ['metadata_private_keys.{n}.user_id.uuid'],
            ],
            [
                'data (data is not encrypted with the server key if user_id if set to null)' => [
                    'armored_key' => $dummyKey['public_key'],
                    'fingerprint' => $dummyKey['fingerprint'],
                    'metadata_private_keys' => [
                        [
                            'user_id' => null,
                            'data' => $this->getDummyPrivateKeyOpenPGPMessage(),
                        ],
                    ],
                ],
                'expected errors paths' => ['metadata_private_keys.{n}.data.isValidEncryptedMetadataPrivateKey'],
            ],
            [
                'data (fingerprint not matching public key)' => [
                    'armored_key' => $makiKey['armored_key'],
                    'fingerprint' => $dummyKey['fingerprint'],
                    'metadata_private_keys' => [
                        [
                            'user_id' => null,
                            'data' => $msgForServer,
                        ],
                    ],
                ],
                'expected errors paths' => [
                    'fingerprint.isMatchingKeyFingerprint',
                ],
            ],
            [
                'data (valid algorithm for public key)' => [
                    'armored_key' => $invalidAlgKey['armored_key'],
                    'fingerprint' => $invalidAlgKey['fingerprint'],
                    'metadata_private_keys' => [
                        [
                            'user_id' => null,
                            'data' => $msgForServer,
                        ],
                    ],
                ],
                'expected errors paths' => [
                    'armored_key.isPublicKeyValidStrict',
                ],
            ],
        ];
    }

    /**
     * @dataProvider invalidMetadataKeyDataProvider
     */
    public function testMetadataKeyCreateService_Error_Validation(array $data, array $expectedErrors): void
    {
        $user = UserFactory::make()->admin()->active()->persist();
        $uac = $this->makeUac($user);

        try {
            $this->service->create($uac, MetadataKeyDto::fromArray($data));
        } catch (CustomValidationException $e) {
            // Use assertions (instead of expectException) in catch to assert errors thrown
            $this->assertStringContainsString('The metadata key could not be saved', $e->getMessage());

            $errors = $e->getErrors();
            foreach ($expectedErrors as $expectedErrorPath) {
                $this->assertTrue(Hash::check($errors, $expectedErrorPath));
            }
        }
    }

    public function testMetadataKeyCreateService_Error_UserDeleted(): void
    {
        $keyInfo = $this->getUserKeyInfo();
        $gpgkey = GpgkeyFactory::make(['armored_key' => $keyInfo['armored_key'], 'fingerprint' => $keyInfo['fingerprint']]);
        $user = UserFactory::make()
            ->with('Gpgkeys', $gpgkey)
            ->admin()
            ->deleted()
            ->persist();
        $uac = $this->makeUac($user);
        $dummyKey = $this->getMetadataKeyInfo();

        $dto = MetadataKeyDto::fromArray([
            'armored_key' => $dummyKey['public_key'],
            'fingerprint' => $dummyKey['fingerprint'],
            'metadata_private_keys' => [
                [
                    'user_id' => null, // server key
                    'data' => $this->getEncryptedMetadataPrivateKeyFoUser(),
                ],
                [
                    'user_id' => $uac->getId(),
                    'data' => $this->getEncryptedMetadataPrivateKeyFoUser(),
                ],
            ],
        ]);

        $this->expectException(CustomValidationException::class);

        $this->service->create($uac, $dto);
    }

    public function testMetadataKeyCreateService_Error_MoreThanOnePrivateKeysPerUser(): void
    {
        $keyInfo = $this->getUserKeyInfo();
        $gpgkey = GpgkeyFactory::make(['armored_key' => $keyInfo['armored_key'], 'fingerprint' => $keyInfo['fingerprint']]);
        $user = UserFactory::make()
            ->with('Gpgkeys', $gpgkey)
            ->admin()
            ->active()
            ->persist();
        $uac = $this->makeUac($user);
        $dummyKey = $this->getMetadataKeyInfo();

        $dto = MetadataKeyDto::fromArray([
            'armored_key' => $dummyKey['public_key'],
            'fingerprint' => $dummyKey['fingerprint'],
            'metadata_private_keys' => [
                [
                    'user_id' => null, // server key
                    'data' => $this->getEncryptedMetadataPrivateKeyForServerKey(),
                ],
                [
                    'user_id' => $uac->getId(),
                    'data' => $this->getEncryptedMetadataPrivateKeyFoUser(),
                ],
                [
                    // Same user, different encrypted message
                    'user_id' => $uac->getId(),
                    'data' => $this->getEncryptedMetadataPrivateKeyFoUserDifferent(),
                ],
            ],
        ]);

        try {
            $this->service->create($uac, $dto);
        } catch (CustomValidationException $e) {
            $errors = $e->getErrors();

            $this->assertTrue(Hash::check($errors, 'metadata_private_keys.{n}.user_id._isUnique'));
        }
    }
}
