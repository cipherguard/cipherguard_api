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

namespace Cipherguard\Metadata\Test\TestCase\Controller;

use App\Test\Factory\GpgkeyFactory;
use App\Test\Factory\UserFactory;
use App\Test\Lib\AppIntegrationTestCaseV5;
use App\Test\Lib\Model\EmailQueueTrait;
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Utility\Hash;
use Cipherguard\Metadata\MetadataPlugin;
use Cipherguard\Metadata\Test\Factory\MetadataKeyFactory;
use Cipherguard\Metadata\Test\Factory\MetadataPrivateKeyFactory;
use Cipherguard\Metadata\Test\Utility\GpgMetadataKeysTestTrait;

/**
 * @uses \Cipherguard\Metadata\Controller\MetadataKeyCreateController
 */
class MetadataKeyCreateControllerTest extends AppIntegrationTestCaseV5
{
    use EmailQueueTrait;
    use LocatorAwareTrait;
    use GpgMetadataKeysTestTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->enableFeaturePlugin(MetadataPlugin::class);
    }

    public function testMetadataKeyCreateController_Success()
    {
        $keyInfo = $this->getUserKeyInfo();
        $gpgkey = GpgkeyFactory::make(['armored_key' => $keyInfo['armored_key'], 'fingerprint' => $keyInfo['fingerprint']]);
        /** @var \App\Model\Entity\User $user */
        $user = UserFactory::make()
            ->with('Gpgkeys', $gpgkey)
            ->admin()
            ->active()
            ->persist();
        $otherAdmin = UserFactory::make()->admin()->persist();
        // Create a disabled admin and a user to test emails
        UserFactory::make()->admin()->disabled()->persist();
        UserFactory::make()->user()->persist();
        $this->logInAs($user);

        $dummyKey = $this->getMetadataKeyInfo();
        $fingerprint = $dummyKey['fingerprint'];
        $this->postJson('/metadata/keys.json', [
            'armored_key' => $dummyKey['public_key'],
            'fingerprint' => $fingerprint,
            'metadata_private_keys' => [
                [
                    'user_id' => null, // server key
                    'data' => $this->getEncryptedMetadataPrivateKeyForServerKey(),
                ],
                [
                    'user_id' => $user['id'],
                    'data' => $this->getEncryptedMetadataPrivateKeyFoUser(),
                ],
            ],
        ]);

        $this->assertSuccess();
        $response = $this->getResponseBodyAsArray();
        $this->assertArrayHasAttributes([
            'id',
            'fingerprint',
            'armored_key',
            'created_by',
            'modified_by',
            'created',
            'modified',
            'metadata_private_keys',
        ], $response);
        // check metadata_keys table data
        $metadataKeys = MetadataKeyFactory::find()->all()->toArray();
        $this->assertCount(1, $metadataKeys);
        $metadataKey = $metadataKeys[0];
        $this->assertSame($dummyKey['fingerprint'], $metadataKey['fingerprint']);
        $this->assertSame($dummyKey['public_key'], $metadataKey['armored_key']);
        $this->assertSame($user->get('id'), $metadataKey['created_by']);
        $this->assertSame($user->get('id'), $metadataKey['modified_by']);
        $this->assertNull($metadataKey['deleted']);
        // check metadata_private_keys table data
        $metadataPrivateKeys = MetadataPrivateKeyFactory::find()->all()->toArray();
        $this->assertCount(2, $metadataPrivateKeys);
        $this->assertSame($user->get('id'), $metadataPrivateKeys[0]['created_by']);
        $this->assertSame($user->get('id'), $metadataPrivateKeys[0]['modified_by']);
        // assert that an email is sent to all admins
        $this->assertEmailQueueCount(2);
        $this->assertEmailInBatchContains([
            'Fingerprint: ' . $fingerprint,
        ], $user->username);
        $this->assertEmailInBatchContains($user->profile->last_name . ' created a new metadata key', $otherAdmin->get('username'));
    }

    public function testMetadataKeyCreateController_Success_ExpiredDeletedNull()
    {
        $keyInfo = $this->getUserKeyInfo();
        $gpgkey = GpgkeyFactory::make(['armored_key' => $keyInfo['armored_key'], 'fingerprint' => $keyInfo['fingerprint']]);
        /** @var \App\Model\Entity\User $user */
        $user = UserFactory::make()->with('Gpgkeys', $gpgkey)->admin()->active()->persist();
        $this->logInAs($user);

        $dummyKey = $this->getMetadataKeyInfo();
        $fingerprint = $dummyKey['fingerprint'];
        $this->postJson('/metadata/keys.json', [
            'armored_key' => $dummyKey['public_key'],
            'fingerprint' => $fingerprint,
            'metadata_private_keys' => [
                [
                    'user_id' => null, // server key
                    'data' => $this->getEncryptedMetadataPrivateKeyForServerKey(),
                ],
                [
                    'user_id' => $user['id'],
                    'data' => $this->getEncryptedMetadataPrivateKeyFoUser(),
                ],
            ],
            'expired' => null,
            'deleted' => null,
        ]);

        $this->assertSuccess();
    }

    public function testMetadataKeyCreateController_Error_AuthenticationRequired()
    {
        $this->postJson('/metadata/keys.json');
        $this->assertAuthenticationError();
    }

    public function testMetadataKeyCreateController_Error_NotJson()
    {
        $this->logInAsUser();
        $this->post('/metadata/keys');
        $this->assertResponseCode(404);
    }

    public function testMetadataKeyCreateController_Error_Forbidden()
    {
        $this->logInAsUser();
        $this->postJson('/metadata/keys.json');
        $this->assertForbiddenError('Access restricted to administrators.');
    }

    public function invalidRequestDataProvider(): array
    {
        $dummyKey = $this->getMetadataKeyInfo();

        return [
            [
                 'request data' => [
                    'armored_key' => ['foo' => 'bar'], // invalid
                    'fingerprint' => '&^#$%!', // invalid
                    'metadata_private_keys' => [
                        [
                            'user_id' => null, // valid - server key
                            'data' => $this->getDummyPrivateKeyOpenPGPMessage(),
                        ],
                    ],
                 ],
                 'expected errors paths' => ['armored_key.ascii', 'fingerprint.alphaNumeric'],
            ],
            [
                'request data' => [
                    'armored_key' => $dummyKey['public_key'],
                    'fingerprint' => $dummyKey['fingerprint'],
                    'metadata_private_keys' => 'foo', // invalid
                ],
                'expected errors paths' => ['metadata_private_keys.array', 'metadata_private_keys.hasAtLeast'],
            ],
            [
                'request data' => [
                    'armored_key' => $dummyKey['public_key'],
                    'fingerprint' => $dummyKey['fingerprint'],
                    'metadata_private_keys' => [], // empty metadata private keys
                ],
                'expected errors paths' => ['metadata_private_keys.hasAtLeast'],
            ],
            [
                'request data' => [
                    'armored_key' => $dummyKey['public_key'],
                    'fingerprint' => 1000,
                    'metadata_private_keys' => [
                        [
                            // invalid
                            'user_id' => 123,
                            'data' => '😎',
                        ],
                    ],
                ],
                'expected errors paths' => ['metadata_private_keys.{n}.user_id.uuid', 'metadata_private_keys.{n}.data.ascii'],
            ],
        ];
    }

    /**
     * @dataProvider invalidRequestDataProvider
     */
    public function testMetadataKeyCreateController_Error_BadRequest(array $requestData, array $expectedErrors)
    {
        $this->logInAsAdmin();
        $this->postJson('/metadata/keys.json', $requestData);
        $this->assertResponseCode(400);
        $responseBody = $this->getResponseBodyAsArray();
        foreach ($expectedErrors as $expectedErrorPath) {
            $this->assertTrue(Hash::check($responseBody, $expectedErrorPath));
        }
    }

    public function testMetadataKeyCreateController_ErrorSettingEditionDisabled()
    {
        $setting = Configure::read('cipherguard.security.metadata.settings.editionDisabled');
        Configure::write('cipherguard.security.metadata.settings.editionDisabled', true);
        $keyInfo = $this->getUserKeyInfo();
        $gpgkey = GpgkeyFactory::make(['armored_key' => $keyInfo['armored_key'], 'fingerprint' => $keyInfo['fingerprint']]);
        $user = UserFactory::make()
            ->with('Gpgkeys', $gpgkey)
            ->admin()
            ->active()
            ->persist();
        $this->logInAs($user);

        $dummyKey = $this->getMetadataKeyInfo();
        $this->postJson('/metadata/keys.json', [
            'armored_key' => $dummyKey['public_key'],
            'fingerprint' => $dummyKey['fingerprint'],
            'metadata_private_keys' => [
                [
                    'user_id' => null, // server key
                    'data' => $this->getEncryptedMetadataPrivateKeyForServerKey(),
                ],
                [
                    'user_id' => $user['id'],
                    'data' => $this->getEncryptedMetadataPrivateKeyFoUser(),
                ],
            ],
        ]);

        $this->assertResponseCode(403);
        Configure::write('cipherguard.security.metadata.settings.editionDisabled', $setting);
    }

    public function testMetadataKeyCreateController_ErrorDeletedAndExpired()
    {
        $keyInfo = $this->getUserKeyInfo();
        $gpgkey = GpgkeyFactory::make(['armored_key' => $keyInfo['armored_key'], 'fingerprint' => $keyInfo['fingerprint']]);
        /** @var \App\Model\Entity\User $user */
        $user = UserFactory::make()->with('Gpgkeys', $gpgkey)->admin()->active()->persist();
        $this->logInAs($user);

        $dummyKey = $this->getMetadataKeyInfo();
        $this->postJson('/metadata/keys.json', [
            'armored_key' => $dummyKey['public_key'],
            'fingerprint' => $dummyKey['fingerprint'],
            'metadata_private_keys' => [
                [
                    'user_id' => null, // server key
                    'data' => $this->getEncryptedMetadataPrivateKeyForServerKey(),
                ],
                [
                    'user_id' => $user['id'],
                    'data' => $this->getEncryptedMetadataPrivateKeyFoUser(),
                ],
            ],
            'deleted' => FrozenTime::yesterday()->format('Y-m-d H:i:s'),
            'expired' => FrozenTime::yesterday()->format('Y-m-d H:i:s'),
        ]);

        $this->assertResponseCode(400);
        $errors = $this->getResponseBodyAsArray();
        $this->assertTrue(isset($errors['deleted']['isNullOnCreate']));
        $this->assertTrue(isset($errors['expired']['isNullOnCreate']));
    }
}
