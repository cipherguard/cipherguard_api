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
use Cake\Http\Exception\NotFoundException;
use Cipherguard\Metadata\Service\MetadataSessionKeyDeleteService;
use Cipherguard\Metadata\Test\Factory\MetadataSessionKeyFactory;
use Cipherguard\Metadata\Test\Utility\GpgMetadataKeysTestTrait;

/**
 * @covers \Cipherguard\Metadata\Service\MetadataSessionKeyDeleteService
 */
class MetadataSessionKeyDeleteServiceTest extends AppTestCaseV5
{
    use GpgMetadataKeysTestTrait;

    /**
     * @var MetadataSessionKeyDeleteService|null
     */
    private ?MetadataSessionKeyDeleteService $service = null;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->service = new MetadataSessionKeyDeleteService();
    }

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        unset($this->service);

        parent::tearDown();
    }

    public function testMetadataSessionKeyDeleteService_Success(): void
    {
        MetadataSessionKeyFactory::make(5)->withUser()->persist();
        $sessionKey = MetadataSessionKeyFactory::make()->withMakiSessionKey()->persist();
        $uac = $this->makeUac($sessionKey->get('user'));

        $this->service->delete($uac, $sessionKey->get('id'));

        $this->assertCount(0, MetadataSessionKeyFactory::find()->where(['id' => $sessionKey->get('id')])->toArray());
        $metadataSessionKeys = MetadataSessionKeyFactory::find()->all()->toArray();
        $this->assertCount(5, $metadataSessionKeys);
    }

    public function testMetadataSessionKeyDeleteService_Success_MultipleKeys(): void
    {
        MetadataSessionKeyFactory::make(3)->withUser()->persist();
        /** @var \Cipherguard\Metadata\Model\Entity\MetadataSessionKey[] $sessionKeys */
        $sessionKeys = MetadataSessionKeyFactory::make(2)->withMakiSessionKey()->persist();
        $uac = $this->makeUac($sessionKeys[0]->get('user'));

        $this->service->delete($uac, $sessionKeys[0]->get('id'));
        $this->service->delete($uac, $sessionKeys[1]->get('id'));

        $metadataSessionKeys = MetadataSessionKeyFactory::find()->all()->toArray();
        $this->assertCount(3, $metadataSessionKeys);
    }

    public function invalidMetadataSessionKeyIdProvider(): array
    {
        return [
            ['foo-bar'],
            ['🔥'],
        ];
    }

    /**
     * @dataProvider invalidMetadataSessionKeyIdProvider
     */
    public function testMetadataSessionKeyDeleteService_Error_InvalidId($id): void
    {
        $user = UserFactory::make()->admin()->active()->persist();
        $uac = $this->makeUac($user);

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('The metadata session key identifier should be a UUID');

        $this->service->delete($uac, $id);
    }

    public function testMetadataSessionKeyDeleteService_Error_SessionKeyDoesNotBelongsToCurrentUser(): void
    {
        $sessionKeys = MetadataSessionKeyFactory::make(1)->withMakiSessionKey()->persist();
        $uac = $this->makeUac(UserFactory::make()->active()->persist());

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('The metadata session key does not exist or does not belong to this user');

        $this->service->delete($uac, $sessionKeys->get('id'));
    }
}
