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
namespace Cipherguard\Metadata\Service;

use App\Error\Exception\ValidationException;
use App\Model\Entity\User;
use App\Service\OpenPGP\OpenPGPCommonServerOperationsTrait;
use App\Service\OpenPGP\OpenPGPCommonUserOperationsTrait;
use App\Utility\OpenPGP\OpenPGPBackend;
use App\Utility\OpenPGP\OpenPGPBackendFactory;
use Cake\Core\Configure;
use Cake\Http\Exception\InternalErrorException;
use Cake\Log\Log;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\TableRegistry;
use Cipherguard\Metadata\Exception\MetadataKeyShareException;
use Cipherguard\Metadata\Form\MetadataCleartextPrivateKeyForm;
use Cipherguard\Metadata\Model\Entity\MetadataPrivateKey;

class MetadataKeyShareDefaultService implements MetadataKeyShareServiceInterface
{
    use LocatorAwareTrait;
    use OpenPGPCommonServerOperationsTrait;
    use OpenPGPCommonUserOperationsTrait;

    /**
     * @inheritDoc
     */
    public function shareMetadataKeysWithUser(User $user): void
    {
        $metadataPrivateKeysTable = $this->fetchTable('Cipherguard/Metadata.MetadataPrivateKeys');

        // Find server copies
        // We share all the possible private keys, even if the parent entity is marked as expired
        // As the metadata key can be marked as expired but still in use for some stuffs
        // (resource, folder, comments, tags)
        $serverMetadataPrivateKeys = $metadataPrivateKeysTable->find()
            ->where(['user_id IS' => null])
            ->order(['created' => 'DESC'])
            ->all();

        if ($serverMetadataPrivateKeys->isEmpty()) {
            $msg = __('Server metadata private key was not found.') . ' ';
            $msg .= __('Metadata key could not be shared with user id: {0}.', $user->id);
            throw new MetadataKeyShareException($msg);
        }

        foreach ($serverMetadataPrivateKeys as $serverMetadataPrivateKey) {
            $this->shareMetadataKeyWithUser($user, $serverMetadataPrivateKey);
        }
    }

    /**
     * @param \App\Model\Entity\User $user user that completed the setup
     * @param \Cipherguard\Metadata\Model\Entity\MetadataPrivateKey $serverMetadataPrivateKey key to share
     * @return void
     * @throws \Cipherguard\Metadata\Exception\MetadataKeyShareException
     */
    protected function shareMetadataKeyWithUser(User $user, MetadataPrivateKey $serverMetadataPrivateKey): void
    {
        $metadataPrivateKeysTable = $this->fetchTable('Cipherguard/Metadata.MetadataPrivateKeys');

        // Decrypt, verify, validate, re-encrypt and sign for user
        try {
            $openpgp = OpenPGPBackendFactory::get();
            $openpgp = $this->setDecryptKeyWithServerKey($openpgp);
            $openpgp = $this->setKeyForVerify($openpgp, $serverMetadataPrivateKey->modified_by);
            $clearText = $openpgp->decrypt($serverMetadataPrivateKey->data, true);
            $this->assertPrivateKey($clearText);
            $openpgp->clearKeys();
            $openpgp = $this->setSignKeyWithServerKey($openpgp);
            $openpgp = $this->setEncryptKeyWithUserKey($openpgp, $user->gpgkey);
            $secret = $openpgp->encrypt($clearText, true);
        } catch (\Exception $exception) {
            $msg = $exception->getMessage() . ' ';
            $msg .= __('Metadata key could not be shared with user id: {0}.', $user->id);
            throw new MetadataKeyShareException($msg, 500, $exception);
        }

        // Build and validate private key entity for the user
        try {
            $userMetadataPrivateKey = $metadataPrivateKeysTable->newEntity([
                'metadata_key_id' => $serverMetadataPrivateKey->metadata_key_id,
                'user_id' => $user->id,
                'data' => $secret,
            ], [
                'accessibleFields' => [
                    'metadata_key_id' => true,
                    'user_id' => true,
                    'data' => true,
                ],
            ]);
            if (!empty($userMetadataPrivateKey->getErrors())) {
                if (Configure::read('debug')) {
                    Log::error(json_encode($userMetadataPrivateKey->getErrors()));
                }
                $msg = __('The OpenPGP key data is not valid.');
                /** @phpstan-ignore-next-line */
                throw new ValidationException($msg, $userMetadataPrivateKey, $metadataPrivateKeysTable);
            }
            if (!$metadataPrivateKeysTable->checkRules($userMetadataPrivateKey)) {
                if (Configure::read('debug')) {
                    Log::error(json_encode($userMetadataPrivateKey->getErrors()));
                }
                $msg = __('The OpenPGP key data is not valid.');
                /** @phpstan-ignore-next-line */
                throw new ValidationException($msg, $userMetadataPrivateKey, $metadataPrivateKeysTable);
            }
        } catch (\Exception $exception) {
            $msg = $exception->getMessage() . ' ';
            $msg .= __('The data could not be validated.') . ' ';
            $msg .= __('Metadata key could not be shared with user id: {0}.', $user->id);
            throw new MetadataKeyShareException($msg, 500, $exception);
        }

        // Save private key entity for the user
        try {
            $metadataPrivateKeysTable->save($userMetadataPrivateKey, ['checkRules' => false]);
        } catch (\Exception $exception) {
            $msg = $exception->getMessage() . ' ';
            $msg .= __('The data could not be saved.') . ' ';
            $msg .= __('Metadata key could not be shared with user id: {0}.', $user->id);
            throw new MetadataKeyShareException($msg, 500, $exception);
        }
    }

    /**
     * @inheritDoc
     */
    public function onFailure(\Exception $exception): void
    {
        Log::error($exception->getMessage());
        if (Configure::read('debug')) {
            Log::error($exception->getTraceAsString());
        }
    }

    /**
     * @param string $clearText private key object in json format
     * @return void
     */
    public function assertPrivateKey(string $clearText): void
    {
        if (empty($clearText)) {
            $msg = __('The metadata private key should not be empty.');
            throw new InternalErrorException($msg);
        }

        try {
            $decoded = json_decode($clearText, true, 2, JSON_THROW_ON_ERROR);
        } catch (\Exception $exception) {
            if (Configure::read('debug')) {
                Log::error($clearText);
            }
            $msg = __('The metadata private key cleartext data should be in JSON format.');
            throw new InternalErrorException($msg, 500, $exception);
        }
        if (!is_array($decoded) || empty($decoded)) {
            $msg = __('The metadata private key cleartext data should not be empty.');
            throw new InternalErrorException($msg);
        }

        $form = new MetadataCleartextPrivateKeyForm();
        if (!$form->validate($decoded)) {
            if (Configure::read('debug')) {
                Log::error(json_encode($form->getErrors()));
            }
            $msg = __('The metadata private key cleartext data is not valid.');
            throw new InternalErrorException($msg);
        }
    }

    /**
     * Get the OpenPGP Backend ready to decrypt with server key
     *
     * @param \App\Utility\OpenPGP\OpenPGPBackend $gpg for example OpenPGPBackendFactory::get()
     * @param string|null $createdBy uuid of user
     * @return \App\Utility\OpenPGP\OpenPGPBackend backend configured to use server keys
     * @throws \Cake\Http\Exception\InternalErrorException if the server key cannot be loaded
     */
    private function setKeyForVerify(OpenPGPBackend $gpg, ?string $createdBy = null): OpenPGPBackend
    {
        // Use server key if no user is defined in createdBy
        if ($createdBy === null) {
            return $this->setVerifyKeyWithServerKey($gpg);
        }

        // User key if createdBy is a user
        try {
            $usersTable = TableRegistry::getTableLocator()->get('Gpgkeys');
            /** @var \App\Model\Entity\Gpgkey $userKey */
            $userKey = $usersTable->find()
                ->where(['user_id' => $createdBy, 'deleted' => false])
                ->order(['created' => 'DESC'])
                ->firstOrFail();
        } catch (\Exception $exception) {
            $msg = __('The OpenPGP user key cannot be found.') . ' ';
            $msg .= $exception->getMessage();
            throw new InternalErrorException($msg, 500, $exception);
        }

        return $this->setVerifyKeyWithUserKey($gpg, $userKey);
    }
}
