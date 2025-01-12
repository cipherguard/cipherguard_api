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
namespace Cipherguard\Metadata\Test\TestCase\Model\Table;

use App\Test\Factory\GpgkeyFactory;
use App\Test\Factory\UserFactory;
use App\Test\Lib\AppTestCaseV5;
use App\Test\Lib\Model\FormatValidationTrait;
use App\Utility\UuidFactory;
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;
use Cipherguard\Metadata\MetadataPlugin;
use Cipherguard\Metadata\Model\Entity\MetadataKey;
use Cipherguard\Metadata\Test\Factory\MetadataKeyFactory;

/**
 * @covers \Cipherguard\Metadata\Model\Table\MetadataKeysTable
 */
class MetadataKeysTableTest extends AppTestCaseV5
{
    use FormatValidationTrait;

    /**
     * Test subject
     *
     * @var \Cipherguard\Metadata\Model\Table\MetadataKeysTable
     */
    protected $MetadataKeys;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->enableFeaturePlugin(MetadataPlugin::class);
        $this->MetadataKeys = TableRegistry::getTableLocator()->get('Cipherguard/Metadata.MetadataKeys');
    }

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        unset($this->MetadataKeys);

        parent::tearDown();
    }

    public function testMetadataKeysTable_Save_Success(): void
    {
        $user = UserFactory::make()
            ->with('Gpgkeys', GpgkeyFactory::make()->withAdaKey())
            ->user()
            ->active()
            ->persist();
        $metadataKey = MetadataKeyFactory::make()->withCreatorAndModifier($user)->getEntity();

        $entity = $this->buildEntity([
            'fingerprint' => $metadataKey->get('fingerprint'),
            'armored_key' => $metadataKey->get('armored_key'),
            'created_by' => $user['id'],
            'modified_by' => $user['id'],
        ]);
        $result = $this->MetadataKeys->save($entity);

        $this->assertInstanceOf(MetadataKey::class, $result);
        $this->assertEmpty($entity->getErrors());
        $this->assertNotEmpty($result->get('id'));
        $this->assertSame($metadataKey->get('fingerprint'), $result->get('fingerprint'));
        $this->assertSame($metadataKey->get('armored_key'), $result->get('armored_key'));
        $this->assertSame($user['id'], $result->get('created_by'));
        $this->assertSame($user['id'], $result->get('modified_by'));
        $this->assertInstanceOf(FrozenTime::class, $result->get('created'));
        $this->assertInstanceOf(FrozenTime::class, $result->get('modified'));
    }

    /**
     * @return void
     * @uses \Cipherguard\Metadata\Model\Table\MetadataKeysTable::validationDefault()
     */
    public function testMetadataKeysTable_ValidationDefault_Fingerprint(): void
    {
        $testCases = [
            'requirePresence' => self::getRequirePresenceTestCases(),
            // 'maxLength' => self::getMaxLengthTestCases(51), // couldn't test as it'll throw valid fingerprint (custom) rule validation
            'notEmptyString' => self::getNotEmptyTestCases(),
            // 'alphaNumeric' => self::getAsciiTestCases(40), // couldn't test as it'll throw valid fingerprint (custom) rule validation
        ];
        $this->assertFieldFormatValidation(
            $this->MetadataKeys,
            'fingerprint',
            $this->getDummyMetadataKeysData(),
            $this->getEntityFieldOptions(),
            $testCases
        );
    }

    /**
     * @return void
     * @uses \Cipherguard\Metadata\Model\Table\MetadataKeysTable::validationDefault()
     */
    public function testMetadataKeysTable_ValidationDefault_ArmoredKey(): void
    {
        $testCases = [
            'requirePresence' => self::getRequirePresenceTestCases(),
            'notEmptyString' => self::getNotEmptyTestCases(),
            //'ascii' => self::getAsciiTestCases(),
        ];
        $this->assertFieldFormatValidation(
            $this->MetadataKeys,
            'armored_key',
            $this->getDummyMetadataKeysData(),
            $this->getEntityFieldOptions(),
            $testCases
        );
    }

    /**
     * @return void
     * @uses \Cipherguard\Metadata\Model\Table\MetadataKeysTable::validationDefault()
     */
    public function testMetadataKeysTable_ValidationDefault_ArmoredKey_IsArmoredKeyNotExpiredRuleFail(): void
    {
        $user = UserFactory::make()->user()->active()->persist();
        $metadataKey = MetadataKeyFactory::make()->withExpiredKey()->withCreatorAndModifier($user)->persist();

        $entity = $this->buildEntity([
            'fingerprint' => $metadataKey->get('fingerprint'),
            'armored_key' => $metadataKey->get('armored_key'),
            'created_by' => $user['id'],
            'modified_by' => $user['id'],
        ]);

        $this->assertNotEmpty($entity->getErrors());
        $this->assertArrayHasKey('isPublicKeyValidStrict', $entity->getErrors()['armored_key']);
    }

    /**
     * @return void
     * @uses \Cipherguard\Metadata\Model\Table\MetadataKeysTable::validationDefault()
     */
    public function testMetadataKeysTable_ValidationDefault_Expired_NotValidDate(): void
    {
        $user = UserFactory::make()
            ->with('Gpgkeys', GpgkeyFactory::make()->withAdaKey())
            ->user()
            ->active()
            ->persist();
        $metadataKey = MetadataKeyFactory::make()->withCreatorAndModifier($user)->getEntity();

        $entity = $this->buildEntity([
            'fingerprint' => $metadataKey->get('fingerprint'),
            'armored_key' => $metadataKey->get('armored_key'),
            'expired' => '🔥',
            'created_by' => $user['id'],
            'modified_by' => $user['id'],
        ]);

        $this->assertNotEmpty($entity->getErrors());
        $this->assertArrayHasKey('dateTime', $entity->getErrors()['expired']);
    }

    /**
     * @return void
     * @uses \Cipherguard\Metadata\Model\Table\MetadataKeysTable::validationDefault()
     */
    public function testMetadataKeysTable_ValidationDefault_Deleted_NotValidDate(): void
    {
        $user = UserFactory::make()
            ->with('Gpgkeys', GpgkeyFactory::make()->withAdaKey())
            ->user()
            ->active()
            ->persist();
        $metadataKey = MetadataKeyFactory::make()->withCreatorAndModifier($user)->getEntity();

        $entity = $this->buildEntity([
            'fingerprint' => $metadataKey->get('fingerprint'),
            'armored_key' => $metadataKey->get('armored_key'),
            'deleted' => '🔥',
            'created_by' => $user['id'],
            'modified_by' => $user['id'],
        ]);

        $this->assertNotEmpty($entity->getErrors());
        $this->assertArrayHasKey('dateTime', $entity->getErrors()['deleted']);
    }

    /**
     * @return void
     * @uses \Cipherguard\Metadata\Model\Table\MetadataKeysTable::buildRules()
     */
    public function testMetadataKeysTable_BuildRules_UniqueFingerPrintActive(): void
    {
        $user = UserFactory::make()
            ->with('Gpgkeys', GpgkeyFactory::make()->withAdaKey())
            ->user()
            ->active()
            ->persist();
        $metadataKey = MetadataKeyFactory::make()->withCreatorAndModifier($user)->persist();

        $entity = $this->buildEntity([
            'fingerprint' => $metadataKey->get('fingerprint'),
            'armored_key' => $metadataKey->get('armored_key'),
            'created_by' => $user['id'],
            'modified_by' => $user['id'],
        ]);
        $result = $this->MetadataKeys->save($entity);

        $this->assertFalse($result);
        $this->assertNotEmpty($entity->getErrors());
        $this->assertCount(1, $entity->getErrors()['fingerprint']);
        $this->assertArrayHasKey('_isUnique', $entity->getErrors()['fingerprint']);
    }

    /**
     * @return void
     * @uses \Cipherguard\Metadata\Model\Table\MetadataKeysTable::buildRules()
     */
    public function testMetadataKeysTable_BuildRules_UniqueFingerPrintDeleted(): void
    {
        $user = UserFactory::make()
            ->with('Gpgkeys', GpgkeyFactory::make()->withAdaKey())
            ->user()
            ->active()
            ->persist();
        $metadataKey = MetadataKeyFactory::make()->deleted()->withCreatorAndModifier($user)->persist();

        $entity = $this->buildEntity([
            'fingerprint' => $metadataKey->get('fingerprint'),
            'armored_key' => $metadataKey->get('armored_key'),
            'created_by' => $user['id'],
            'modified_by' => $user['id'],
        ]);
        $result = $this->MetadataKeys->save($entity);

        $this->assertInstanceOf(MetadataKey::class, $result);
        $this->assertEmpty($entity->getErrors());
    }

    /**
     * @return void
     * @uses \Cipherguard\Metadata\Model\Table\MetadataKeysTable::buildRules()
     */
    public function testMetadataKeysTable_BuildRules_IsNotServerKeyFingerprintRule(): void
    {
        $user = UserFactory::make()->user()->active()->persist();

        $entity = $this->buildEntity([
            'fingerprint' => Configure::read('cipherguard.gpg.serverKey.fingerprint'), // server key
            'armored_key' => file_get_contents(Configure::read('cipherguard.gpg.serverKey.public')),
            'created_by' => $user['id'],
            'modified_by' => $user['id'],
        ]);
        $result = $this->MetadataKeys->save($entity);

        $this->assertFalse($result);
        $this->assertNotEmpty($entity->getErrors());
        $this->assertCount(1, $entity->getErrors()['fingerprint']);
        $this->assertArrayHasKey('isNotServerKeyFingerprintRule', $entity->getErrors()['fingerprint']);
    }

    /**
     * @return void
     * @uses \Cipherguard\Metadata\Model\Table\MetadataKeysTable::buildRules()
     */
    public function testMetadataKeysTable_BuildRules_IsNotUserKeyFingerprintRule(): void
    {
        $user = UserFactory::make()
            ->with('Gpgkeys', GpgkeyFactory::make()->withAdaKey())
            ->user()
            ->active()
            ->persist();

        $entity = $this->buildEntity([
            'fingerprint' => $user['gpgkey']['fingerprint'], // Ada's gpgkey, already exists
            'armored_key' => $user['gpgkey']['armored_key'],
            'created_by' => $user['id'],
            'modified_by' => $user['id'],
        ]);
        $result = $this->MetadataKeys->save($entity);

        $this->assertFalse($result);
        $this->assertNotEmpty($entity->getErrors());
        $this->assertCount(1, $entity->getErrors()['fingerprint']);
        $this->assertArrayHasKey('isNotUserKeyFingerprintRule', $entity->getErrors()['fingerprint']);
    }

    // ---------------------------
    // Helper methods
    // ---------------------------

    private function getDummyMetadataKeysData(): array
    {
        $factoryData = MetadataKeyFactory::make()->getEntity();

        return [
            'fingerprint' => $factoryData->get('fingerprint'),
            'armored_key' => $factoryData->get('armored_key'),
            'created_by' => UuidFactory::uuid(),
            'modified_by' => UuidFactory::uuid(),
        ];
    }

    private function getEntityFieldOptions(): array
    {
        return [
            'checkRules' => true,
            'accessibleFields' => [
                'fingerprint' => true,
                'armored_key' => true,
                'created_by' => true,
                'modified_by' => true,
            ],
        ];
    }

    private function buildEntity(array $data): Entity
    {
        return $this->MetadataKeys->newEntity(
            $data,
            [
                'accessibleFields' => [
                    'fingerprint' => true,
                    'armored_key' => true,
                    'created_by' => true,
                    'modified_by' => true,
                ],
            ]
        );
    }
}
