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
namespace Cipherguard\Metadata\Test\Utility;

use App\Model\Entity\Gpgkey;
use App\Service\OpenPGP\MessageRecipientValidationService;
use App\Service\OpenPGP\MessageValidationService;
use App\Service\OpenPGP\PublicKeyValidationService;
use Cipherguard\Folders\Model\Entity\Folder;
use Cipherguard\Metadata\Model\Entity\MetadataKey;

/**
 * Helper methods with common assertions.
 */
trait MigrateFoldersTestTrait
{
    /**
     * @param Folder $updatedFolder Folder entity to check.
     * @param Folder $oldFolder Old folder entity.
     * @param Gpgkey $userGpgkey Gpgkey entity.
     * @param array $userKeyInfo User key info (private, passphrase, etc.)
     * @throws \Exception If fingerprint provided doesn't exist in mapping
     * @return void
     */
    public function assertionsForPersonalFolder(
        Folder $updatedFolder,
        Folder $oldFolder,
        Gpgkey $userGpgkey,
        array $userKeyInfo
    ): void {
        $this->assertNull($updatedFolder->name);
        // Assertions for metadata
        $metadata = $updatedFolder->get('metadata');
        $this->assertNotNull($metadata);
        $this->assertSame($userGpgkey->id, $updatedFolder->get('metadata_key_id'));
        $this->assertSame('user_key', $updatedFolder->get('metadata_key_type'));
        // Assert is valid OpenPGP message
        $this->assertTrue(MessageValidationService::isParsableArmoredMessage($metadata));
        // Assert encrypted with ada key
        $userArmoredKey = $userGpgkey->armored_key;
        $userFingerprint = $userGpgkey->fingerprint;
        $rules = MessageValidationService::getAsymmetricMessageRules();
        $msgInfo = MessageValidationService::parseAndValidateMessage($metadata, $rules);
        $keyInfo = PublicKeyValidationService::getPublicKeyInfo($userArmoredKey);
        $this->assertTrue(MessageRecipientValidationService::isMessageForRecipient($msgInfo, $keyInfo));
        // Assert decrypted content contains same data as previous one
        $decryptedMetadata = $this->decryptOpenPGPMessage($metadata, [
            'fingerprint' => $userFingerprint,
            'armored_key' => $userKeyInfo['private_key'],
            'passphrase' => $userKeyInfo['passphrase'],
        ]);
        $metadataArray = json_decode($decryptedMetadata, true);
        $this->assertArrayEqualsCanonicalizing([
            'object_type' => 'CIPHERGUARD_FOLDER_METADATA',
            'name' => $oldFolder->name,
            'color' => null,
            'description' => null,
            'icon' => null,
        ], $metadataArray);
    }

    public function assertionsForSharedFolder(Folder $updatedFolder, Folder $oldFolder, MetadataKey $metadataKey): void
    {
        $this->assertNull($updatedFolder->name);
        // Assertions for metadata
        $metadata = $updatedFolder->get('metadata');
        $this->assertNotNull($metadata);
        $this->assertSame($metadataKey->id, $updatedFolder->get('metadata_key_id'));
        $this->assertSame('shared_key', $updatedFolder->get('metadata_key_type'));
        // Assert is valid OpenPGP message
        $this->assertTrue(MessageValidationService::isParsableArmoredMessage($metadata));
        // Assert encrypted with shared key
        $armoredKey = $metadataKey->armored_key;
        $rules = MessageValidationService::getAsymmetricMessageRules();
        $msgInfo = MessageValidationService::parseAndValidateMessage($metadata, $rules);
        $keyInfo = PublicKeyValidationService::getPublicKeyInfo($armoredKey);
        $this->assertTrue(MessageRecipientValidationService::isMessageForRecipient($msgInfo, $keyInfo));
        // Assert decrypted content contains same data as previous one
        $decryptedMetadata = $this->decryptOpenPGPMessage($metadata, $this->getValidPrivateKeyCleartext());
        $metadataArray = json_decode($decryptedMetadata, true);
        $this->assertArrayEqualsCanonicalizing([
            'object_type' => 'CIPHERGUARD_FOLDER_METADATA',
            'name' => $oldFolder->name,
            'color' => null,
            'description' => null,
            'icon' => null,
        ], $metadataArray);
    }
}
