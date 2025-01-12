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

namespace Cipherguard\Metadata\Test\TestCase\Service\MetadataKey;

use App\Test\Factory\ResourceFactory;
use App\Test\Factory\UserFactory;
use App\Test\Lib\AppTestCaseV5;
use App\Utility\UuidFactory;
use Cake\Event\EventList;
use Cake\Event\EventManager;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cipherguard\Metadata\Service\MetadataKey\MetadataKeyDeleteService;
use Cipherguard\Metadata\Test\Factory\MetadataKeyFactory;
use Cipherguard\Metadata\Test\Factory\MetadataPrivateKeyFactory;

/**
 * @covers \Cipherguard\Metadata\Service\MetadataKey\MetadataKeyDeleteService
 */
class MetadataKeyDeleteServiceTest extends AppTestCaseV5
{
    public function testMetadataKeyDeleteService_Success(): void
    {
        EventManager::instance()->setEventList(new EventList());
        $user = UserFactory::make()->admin()->withValidGpgKey()->persist();
        $gpg = $user->get('gpgkey');
        MetadataKeyFactory::make()->withServerPrivateKey()->persist();
        /** @var \Cipherguard\Metadata\Model\Entity\MetadataKey $key */
        $key = MetadataKeyFactory::make()->withServerPrivateKey()->withUserPrivateKey($gpg)->expired()->persist();

        $this->assertEquals(2, MetadataKeyFactory::count());
        $this->assertEquals(3, MetadataPrivateKeyFactory::count());

        $this->assertFalse($key->isDeleted());
        $id = $key->get('id');
        $uac = $this->makeUac($user);

        $sut = new MetadataKeyDeleteService();
        $sut->delete($uac, $id);

        $this->assertEquals(2, MetadataKeyFactory::count());
        $this->assertEquals(1, MetadataPrivateKeyFactory::count());

        /** @var \Cipherguard\Metadata\Model\Entity\MetadataKey $updatedKey */
        $updatedKey = MetadataKeyFactory::get($id);
        $this->assertTrue($updatedKey->isDeleted());

        $this->assertEventFired(MetadataKeyDeleteService::AFTER_METADATA_KEY_DELETE_SUCCESS_EVENT_NAME);
    }

    public function testMetadataKeyDeleteService_Error_NotAdmin(): void
    {
        $key = MetadataKeyFactory::make()->withServerPrivateKey()->expired()->persist();
        $id = $key->get('id');
        $user = UserFactory::make()->user()->persist();
        $uac = $this->makeUac($user);
        $sut = new MetadataKeyDeleteService();

        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('Access restricted to administrators.');
        $sut->delete($uac, $id);
    }

    public function testMetadataKeyDeleteService_Error_IdNotUUID(): void
    {
        $user = UserFactory::make()->admin()->persist();
        $uac = $this->makeUac($user);
        $sut = new MetadataKeyDeleteService();

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('The metadata key ID should be a valid UUID.');
        $sut->delete($uac, '🔥');
    }

    public function testMetadataKeyDeleteService_Error_RecordNotFound(): void
    {
        $user = UserFactory::make()->admin()->persist();
        $uac = $this->makeUac($user);
        $sut = new MetadataKeyDeleteService();

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('The metadata key does not exist or has been deleted.');
        $sut->delete($uac, UuidFactory::uuid());
    }

    public function testMetadataKeyDeleteService_Error_KeyIsAlreadyDeleted(): void
    {
        $key = MetadataKeyFactory::make()->withServerPrivateKey()->expired()->deleted()->persist();
        $id = $key->get('id');
        $user = UserFactory::make()->admin()->persist();
        $uac = $this->makeUac($user);
        $sut = new MetadataKeyDeleteService();

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('deleted');
        $sut->delete($uac, $id);
    }

    public function testMetadataKeyDeleteService_Error_KeyIsNotExpired(): void
    {
        $key = MetadataKeyFactory::make()->withServerPrivateKey()->persist();
        $id = $key->get('id');
        $user = UserFactory::make()->admin()->persist();
        $uac = $this->makeUac($user);
        $sut = new MetadataKeyDeleteService();

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('expired first');
        $sut->delete($uac, $id);
    }

    public function testMetadataKeyDeleteService_Error_KeyIsInUseByResources(): void
    {
        $key = MetadataKeyFactory::make()->withServerPrivateKey()->expired()->persist();
        $id = $key->get('id');
        $user = UserFactory::make()->admin()->persist();
        ResourceFactory::make()->v5Fields(true)->setField('metadata_key_id', $id)->persist();
        $uac = $this->makeUac($user);
        $sut = new MetadataKeyDeleteService();

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('migrate the remaining items');
        $sut->delete($uac, $id);
    }
}
