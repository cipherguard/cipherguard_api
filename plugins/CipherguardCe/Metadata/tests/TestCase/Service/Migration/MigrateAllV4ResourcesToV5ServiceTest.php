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

namespace Cipherguard\Metadata\Test\TestCase\Service\Migration;

use App\Test\Factory\GpgkeyFactory;
use App\Test\Factory\GroupFactory;
use App\Test\Factory\ResourceFactory;
use App\Test\Factory\UserFactory;
use App\Test\Lib\AppTestCaseV5;
use Cake\Http\Exception\BadRequestException;
use Cipherguard\Folders\Test\Factory\PermissionFactory;
use Cipherguard\Metadata\Service\Migration\MigrateAllV4ResourcesToV5Service;
use Cipherguard\Metadata\Test\Factory\MetadataKeyFactory;
use Cipherguard\Metadata\Test\Factory\MetadataTypesSettingsFactory;
use Cipherguard\Metadata\Test\Utility\GpgMetadataKeysTestTrait;
use Cipherguard\Metadata\Test\Utility\MigrateResourcesTestTrait;
use Cipherguard\ResourceTypes\Test\Factory\ResourceTypeFactory;

/**
 * @covers \Cipherguard\Metadata\Service\Migration\MigrateAllV4ResourcesToV5Service
 */
class MigrateAllV4ResourcesToV5ServiceTest extends AppTestCaseV5
{
    use GpgMetadataKeysTestTrait;
    use MigrateResourcesTestTrait;

    /**
     * @var MigrateAllV4ResourcesToV5Service|null
     */
    private ?MigrateAllV4ResourcesToV5Service $service = null;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->service = new MigrateAllV4ResourcesToV5Service();
    }

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        unset($this->service);

        parent::tearDown();
    }

    public function testMetadataMigrateAllV4ResourcesToV5Service_Success_PersonalResource(): void
    {
        MetadataTypesSettingsFactory::make()->v5()->persist();
        $v4ResourceType = ResourceTypeFactory::make()->passwordAndDescription()->persist();
        /** @var \Cipherguard\ResourceTypes\Model\Entity\ResourceType $v5DefaultResourceType */
        $v5DefaultResourceType = ResourceTypeFactory::make()->v5Default()->persist();
        $adaKeyInfo = [
            'armored_key' => file_get_contents(FIXTURES . DS . 'Gpgkeys' . DS . 'ada_public.key'),
            'fingerprint' => '03F60E958F4CB29723ACDF761353B5B15D9B054F',
        ];
        $bettyKeyInfo = [
            'armored_key' => file_get_contents(FIXTURES . DS . 'Gpgkeys' . DS . 'betty_public.key'),
            'fingerprint' => 'A754860C3ADE5AB04599025ED3F1FE4BE61D7009',
        ];
        /** @var \App\Model\Entity\User $ada */
        $ada = UserFactory::make()
            ->user()
            ->with('Gpgkeys', GpgkeyFactory::make($adaKeyInfo))
            ->persist();
        /** @var \App\Model\Entity\Resource $resource */
        $resource = ResourceFactory::make()
            ->with('ResourceTypes', $v4ResourceType)
            ->withPermissionsFor([$ada])
            ->withCreator(UserFactory::make()->user()->with('Gpgkeys', GpgkeyFactory::make($bettyKeyInfo)))
            ->persist();

        $result = $this->service->migrate();

        $this->assertSame([], $result['errors']);
        /** @var \App\Model\Entity\Resource $updatedResource */
        $updatedResource = ResourceFactory::get($resource->id);
        $this->assertionsForPersonalResource($updatedResource, $resource, $ada->gpgkey, [
            'private_key' => file_get_contents(FIXTURES . DS . 'Gpgkeys' . DS . 'ada_private_nopassphrase.key'),
            'passphrase' => '',
        ]);
    }

    public function testMetadataMigrateAllV4ResourcesToV5Service_Success_SharedResource(): void
    {
        MetadataTypesSettingsFactory::make()->v5()->persist();
        /** @var \Cipherguard\ResourceTypes\Model\Entity\ResourceType $resourceType */
        $resourceType = ResourceTypeFactory::make()->passwordAndDescription()->persist();
        /** @var \Cipherguard\ResourceTypes\Model\Entity\ResourceType $v5ResourceType */
        $v5ResourceType = ResourceTypeFactory::make()->v5Default()->persist();
        $user = UserFactory::make()->user()->withValidGpgKey()->persist();
        $user2 = UserFactory::make()->user()->persist();
        /** @var \App\Model\Entity\Resource $resource */
        $resource = ResourceFactory::make()
            ->with('ResourceTypes', $resourceType)
            ->withCreatorAndPermission($user)
            ->persist();
        PermissionFactory::make()->acoResource($resource)->typeRead()->aroUser($user2)->persist();
        // metadata key
        $metadataKey = MetadataKeyFactory::make()->withServerPrivateKey()->persist();

        $result = $this->service->migrate();

        $this->assertSame([], $result['errors']);
        /** @var \App\Model\Entity\Resource $updatedResource */
        $updatedResource = ResourceFactory::get($resource->id);
        $this->assertionsForSharedResource($updatedResource, $resource, $metadataKey);
    }

    public function testMetadataMigrateAllV4ResourcesToV5Service_Success_MultipleResources(): void
    {
        MetadataTypesSettingsFactory::make()->v5()->persist();
        $totpStandalone = ResourceTypeFactory::make()->standaloneTotp()->persist();
        /** @var \Cipherguard\ResourceTypes\Model\Entity\ResourceType $v5TotpStandalone */
        $v5TotpStandalone = ResourceTypeFactory::make()->v5StandaloneTotp()->persist();
        // Shared resource.
        /** @var \App\Model\Entity\Resource $sharedResource */
        $sharedResource = ResourceFactory::make()
            ->with('ResourceTypes', $totpStandalone)
            ->withCreatorAndPermission(UserFactory::make()->persist())
            ->persist();
        PermissionFactory::make()->acoResource($sharedResource)->typeRead()->withAroUser()->persist();
        PermissionFactory::make()->acoResource($sharedResource)->typeUpdate()->withAroUser()->persist();
        // Personal resource.
        /** @var \App\Model\Entity\User $user */
        $user = UserFactory::make()
            ->user()
            ->with('Gpgkeys', GpgkeyFactory::make([
                'armored_key' => file_get_contents(FIXTURES . DS . 'Gpgkeys' . DS . 'ada_public.key'),
                'fingerprint' => '03F60E958F4CB29723ACDF761353B5B15D9B054F',
            ]))
            ->persist();
        /** @var \App\Model\Entity\Resource $personalResource */
        $personalResource = ResourceFactory::make()
            ->with('ResourceTypes', $totpStandalone)
            ->withCreatorAndPermission($user)
            ->persist();
        // metadata key
        $metadataKey = MetadataKeyFactory::make()->withServerPrivateKey()->persist();

        $result = $this->service->migrate();

        $this->assertSame([], $result['errors']);
        /** @var \App\Model\Entity\Resource[] $updatedResources */
        $updatedResources = ResourceFactory::find()->toArray();
        $this->assertCount(2, $updatedResources);
        foreach ($updatedResources as $updatedResource) {
            if ($updatedResource->id === $personalResource->id) { // personal resource assertions
                $this->assertionsForPersonalResource($updatedResource, $personalResource, $user->gpgkey, [
                    'private_key' => file_get_contents(FIXTURES . DS . 'Gpgkeys' . DS . 'ada_private_nopassphrase.key'),
                    'passphrase' => '',
                ]);
            } else { // shared resource assertions
                $this->assertionsForSharedResource($updatedResource, $sharedResource, $metadataKey);
            }
        }
    }

    public function testMetadataMigrateAllV4ResourcesToV5Service_Success_Group(): void
    {
        MetadataTypesSettingsFactory::make()->v5()->persist();
        $v4ResourceType = ResourceTypeFactory::make()->passwordAndDescription()->persist();
        /** @var \Cipherguard\ResourceTypes\Model\Entity\ResourceType $v5DefaultResourceType */
        $v5DefaultResourceType = ResourceTypeFactory::make()->v5Default()->persist();
        $adaKeyInfo = [
            'armored_key' => file_get_contents(FIXTURES . DS . 'Gpgkeys' . DS . 'ada_public.key'),
            'fingerprint' => '03F60E958F4CB29723ACDF761353B5B15D9B054F',
        ];
        // metadata key
        $metadataKey = MetadataKeyFactory::make()->withServerPrivateKey()->persist();
        /** @var \App\Model\Entity\User $ada */
        $ada = UserFactory::make()
            ->user()
            ->with('Gpgkeys', GpgkeyFactory::make($adaKeyInfo))
            ->persist();
        $group = GroupFactory::make()->withGroupsManagersFor([$ada])->persist();
        /** @var \App\Model\Entity\Resource $resource */
        $resource = ResourceFactory::make()
            ->with('ResourceTypes', $v4ResourceType)
            ->withPermissionsFor([$group])
            ->persist();

        $result = $this->service->migrate();

        $this->assertSame([], $result['errors']);
        /** @var \App\Model\Entity\Resource $updatedResource */
        $updatedResource = ResourceFactory::get($resource->id);
        $this->assertionsForSharedResource($updatedResource, $resource, $metadataKey);
    }

    public function testMetadataMigrateAllV4ResourcesToV5Service_Error_NoActiveMetadataKey(): void
    {
        MetadataTypesSettingsFactory::make()->v5()->persist();
        $v4ResourceType = ResourceTypeFactory::make()->passwordAndDescription()->persist();
        /** @var \Cipherguard\ResourceTypes\Model\Entity\ResourceType $v5ResourceTypePasswordString */
        ResourceTypeFactory::make()->v5Default()->persist();
        // Shared resource.
        /** @var \App\Model\Entity\Resource $resource */
        $sharedResource = ResourceFactory::make()
            ->with('ResourceTypes', $v4ResourceType)
            ->withCreatorAndPermission(UserFactory::make()->persist())
            ->persist();
        PermissionFactory::make()->acoResource($sharedResource)->typeRead()->withAroUser()->persist();
        PermissionFactory::make()->acoResource($sharedResource)->typeUpdate()->withAroUser()->persist();

        $result = $this->service->migrate();

        $this->assertFalse($result['success']);
        $this->assertCount(1, $result['errors']);
        $this->assertStringContainsString('Record not found in table "metadata_keys"', $result['errors'][0]['error_message']);
    }

    public function testMetadataMigrateAllV4ResourcesToV5Service_Error_ResourceIsAlreadyV5(): void
    {
        MetadataTypesSettingsFactory::make()->v5()->persist();
        $v4ResourceType = ResourceTypeFactory::make()->passwordAndDescription()->persist();
        /** @var \Cipherguard\ResourceTypes\Model\Entity\ResourceType $v5ResourceTypePasswordString */
        ResourceTypeFactory::make()->v5Default()->persist();
        // Shared resource.
        /** @var \App\Model\Entity\Resource $resource */
        $sharedResource = ResourceFactory::make()
            ->v5Fields(true)
            ->with('ResourceTypes', $v4ResourceType)
            ->withCreatorAndPermission(UserFactory::make()->persist())
            ->persist();
        PermissionFactory::make()->acoResource($sharedResource)->typeRead()->withAroUser()->persist();
        PermissionFactory::make()->acoResource($sharedResource)->typeUpdate()->withAroUser()->persist();

        $result = $this->service->migrate();

        $this->assertFalse($result['success']);
        $this->assertCount(1, $result['errors']);
    }

    public function testMetadataMigrateAllV4ResourcesToV5Service_Error_AllowCreationOfV5ResourcesDisabled(): void
    {
        // Allow only V4 format
        MetadataTypesSettingsFactory::make()->v4()->persist();
        $v4ResourceType = ResourceTypeFactory::make()->passwordAndDescription()->persist();
        /** @var \Cipherguard\ResourceTypes\Model\Entity\ResourceType $v5ResourceTypePasswordString */
        ResourceTypeFactory::make()->v5Default()->persist();
        // Shared resource.
        /** @var \App\Model\Entity\Resource $resource */
        $sharedResource = ResourceFactory::make()
            ->v5Fields(true)
            ->with('ResourceTypes', $v4ResourceType)
            ->withCreatorAndPermission(UserFactory::make()->persist())
            ->persist();
        PermissionFactory::make()->acoResource($sharedResource)->typeRead()->withAroUser()->persist();
        PermissionFactory::make()->acoResource($sharedResource)->typeUpdate()->withAroUser()->persist();

        $this->expectException(BadRequestException::class);
        $this->expectErrorMessage('Resource creation/modification with encrypted metadata not allowed');

        $this->service->migrate();
    }
}
