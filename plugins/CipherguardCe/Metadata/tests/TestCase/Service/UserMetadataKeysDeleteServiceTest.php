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

use App\Test\Factory\UserFactory;
use App\Test\Lib\AppTestCaseV5;
use Cake\Http\Exception\BadRequestException;
use Cipherguard\Metadata\Service\UserMetadataKeysDeleteService;
use Cipherguard\Metadata\Test\Factory\MetadataPrivateKeyFactory;
use Cipherguard\Metadata\Test\Factory\MetadataSessionKeyFactory;
use Cipherguard\Metadata\Test\Utility\GpgMetadataKeysTestTrait;

/**
 * @covers \Cipherguard\Metadata\Service\UserMetadataKeysDeleteService
 */
class UserMetadataKeysDeleteServiceTest extends AppTestCaseV5
{
    use GpgMetadataKeysTestTrait;

    /**
     * @var UserMetadataKeysDeleteService|null
     */
    private ?UserMetadataKeysDeleteService $service = null;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->service = new UserMetadataKeysDeleteService();
    }

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        unset($this->service);

        parent::tearDown();
    }

    public function testUserMetadataKeysDeleteService_Success(): void
    {
        $user = UserFactory::make()->active()->persist();
        /** @var \Cipherguard\Metadata\Model\Entity\MetadataPrivateKey[] $privateKeysToBeDeleted */
        $privateKeysToBeDeleted = MetadataPrivateKeyFactory::make(2)->withUser($user)->withMetadataKey()->persist();
        MetadataPrivateKeyFactory::make(3)->withMetadataKey()->withUser()->persist();
        $sessionKeysToBeDeleted = MetadataSessionKeyFactory::make()->withUser($user)->persist();
        MetadataSessionKeyFactory::make(2)->withUser()->persist();

        $this->service->delete($user->get('id'));

        // assertions for private keys
        $metadataPrivateKeys = MetadataPrivateKeyFactory::find()->toArray();
        $this->assertCount(3, $metadataPrivateKeys);
        $deletedPrivateKeys = MetadataPrivateKeyFactory::find()
            ->where(['id IN' => [$privateKeysToBeDeleted[0]->get('id'), $privateKeysToBeDeleted[1]->get('id')]])
            ->toArray();
        $this->assertCount(0, $deletedPrivateKeys);
        // assertions for session keys
        $metadataSessionKeys = MetadataSessionKeyFactory::find()->toArray();
        $this->assertCount(2, $metadataSessionKeys);
        $deletedSessionKeys = MetadataPrivateKeyFactory::find()->where(['id' => $sessionKeysToBeDeleted->get('id')])->toArray();
        $this->assertCount(0, $deletedSessionKeys);
    }

    public function invalidUserIdsProvider(): array
    {
        return [
            ['foo-bar'],
            ['🔥'],
        ];
    }

    /**
     * @dataProvider invalidUserIdsProvider
     */
    public function testUserMetadataKeysDeleteService_Error_InvalidId($id): void
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('The user identifier should be a UUID');

        $this->service->delete($id);
    }
}
