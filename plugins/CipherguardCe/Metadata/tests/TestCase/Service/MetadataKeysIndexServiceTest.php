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
use Cake\I18n\FrozenTime;
use Cipherguard\Metadata\Service\MetadataKeysIndexService;
use Cipherguard\Metadata\Test\Factory\MetadataKeyFactory;
use Cipherguard\Metadata\Test\Factory\MetadataPrivateKeyFactory;

/**
 * @covers \Cipherguard\Metadata\Service\MetadataKeysIndexService
 */
class MetadataKeysIndexServiceTest extends AppTestCaseV5
{
    /**
     * @var MetadataKeysIndexService|null
     */
    private ?MetadataKeysIndexService $service = null;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->service = new MetadataKeysIndexService();
    }

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        unset($this->service);

        parent::tearDown();
    }

    public function testMetadataKeysIndexService_Success_NoKeys(): void
    {
        $userId = UserFactory::make()->active()->persist()->get('id');
        $result = $this->service->get($userId);
        $this->assertEquals([], $result->toArray());
    }

    public function testMetadataKeysIndexService_Success_SingleMetadataKey(): void
    {
        $userId = UserFactory::make()->active()->persist()->get('id');
        MetadataKeyFactory::make()->withCreatorAndModifier()->persist();
        $result = $this->service->get($userId);
        $this->assertNotEmpty($result->toArray());
        $this->assertCount(1, $result->toArray());
        // assert that contain is not there
        $this->assertArrayNotHasKey('metadata_private_keys', $result->toArray()[0]);
    }

    public function testMetadataKeysIndexService_Success_MultipleMetadataKeys(): void
    {
        MetadataKeyFactory::make(5)->withCreatorAndModifier()->persist();
        MetadataKeyFactory::make()->deleted()->withCreatorAndModifier()->persist();
        $userId = UserFactory::make()->active()->persist()->get('id');

        $result = $this->service->get($userId);

        $this->assertNotEmpty($result->toArray());
        $this->assertCount(6, $result->toArray());
    }

    public function testMetadataKeysIndexService_Success_FilterDeletedKeys(): void
    {
        MetadataKeyFactory::make(3)->withCreatorAndModifier()->persist();
        MetadataKeyFactory::make(2)->deleted()->withCreatorAndModifier()->persist();
        $userId = UserFactory::make()->active()->persist()->get('id');

        $result = $this->service->get($userId, null, ['deleted' => true]);

        $this->assertNotEmpty($result->toArray());
        $this->assertCount(2, $result->toArray());
        foreach ($result->toArray() as $metadataKey) {
            $this->assertInstanceOf(FrozenTime::class, $metadataKey['deleted']);
        }
    }

    public function testMetadataKeysIndexService_Success_ContainMetadataPrivateKeys(): void
    {
        $user = UserFactory::make()->user()->active()->persist();
        $metadataKey = MetadataKeyFactory::make()->withCreatorAndModifier()->persist();
        MetadataPrivateKeyFactory::make()->withMetadataKey($metadataKey)->withUser($user)->persist();
        MetadataPrivateKeyFactory::make()->withMetadataKey($metadataKey)->withUser($user)->persist();

        $result = $this->service->get($user->get('id'), ['metadata_private_keys' => true]);

        $this->assertNotEmpty($result->toArray());
        $this->assertCount(1, $result->toArray());
        $this->assertArrayHasKey('metadata_private_keys', $result->toArray()[0]);
        $metadataPrivateKeys = $result->toArray()[0]['metadata_private_keys'];
        $this->assertCount(2, $metadataPrivateKeys);
    }
}
