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

use App\Test\Factory\GpgkeyFactory;
use App\Test\Factory\UserFactory;
use App\Test\Lib\AppTestCaseV5;
use Cake\Http\Exception\BadRequestException;
use Cipherguard\Metadata\Model\Entity\MetadataSessionKey;
use Cipherguard\Metadata\Service\MetadataSessionKeyCreateService;
use Cipherguard\Metadata\Test\Factory\MetadataSessionKeyFactory;
use Cipherguard\Metadata\Test\Utility\GpgMetadataKeysTestTrait;

/**
 * @covers \Cipherguard\Metadata\Service\MetadataSessionKeyCreateService
 */
class MetadataSessionKeyCreateServiceTest extends AppTestCaseV5
{
    use GpgMetadataKeysTestTrait;

    /**
     * @var MetadataSessionKeyCreateService|null
     */
    private ?MetadataSessionKeyCreateService $service = null;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->service = new MetadataSessionKeyCreateService();
    }

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        unset($this->service);

        parent::tearDown();
    }

    public function testMetadataSessionKeyCreateService_Success(): void
    {
        $keyInfo = $this->getUserKeyInfo();
        $gpgkey = GpgkeyFactory::make(['armored_key' => $keyInfo['armored_key'], 'fingerprint' => $keyInfo['fingerprint']]);
        $user = UserFactory::make()
            ->with('Gpgkeys', $gpgkey)
            ->admin()
            ->active()
            ->persist();
        $uac = $this->makeUac($user);

        $result = $this->service->create($uac, $this->getEncryptedMetadataSessionKeyForMaki());

        $this->assertInstanceOf(MetadataSessionKey::class, $result);
        $metadataSessionKeys = MetadataSessionKeyFactory::find()->all()->toArray();
        $this->assertCount(1, $metadataSessionKeys);
    }

    public function testMetadataSessionKeyCreateService_Success_MultipleKeys(): void
    {
        $keyInfo = $this->getUserKeyInfo();
        $gpgkey = GpgkeyFactory::make(['armored_key' => $keyInfo['armored_key'], 'fingerprint' => $keyInfo['fingerprint']]);
        $user = UserFactory::make()
            ->with('Gpgkeys', $gpgkey)
            ->admin()
            ->active()
            ->persist();
        $uac = $this->makeUac($user);

        $this->service->create($uac, $this->getEncryptedMetadataSessionKeyForMaki());
        $this->service->create($uac, $this->getEncryptedMetadataSessionKeyForMaki());

        $metadataSessionKeys = MetadataSessionKeyFactory::find()->all()->toArray();
        $this->assertCount(2, $metadataSessionKeys);
    }

    public function invalidDataFieldValueProvider(): array
    {
        return [
            [null],
            [[]],
            [new \stdClass()],
        ];
    }

    /**
     * @dataProvider invalidDataFieldValueProvider
     */
    public function testMetadataSessionKeyCreateService_Error_DataIsNotString($data): void
    {
        $user = UserFactory::make()->admin()->active()->persist();
        $uac = $this->makeUac($user);

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('The data must be a string');

        $this->service->create($uac, $data);
    }
}
