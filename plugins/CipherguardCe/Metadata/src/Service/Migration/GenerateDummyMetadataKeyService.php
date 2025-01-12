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
namespace Cipherguard\Metadata\Service\Migration;

use App\Error\Exception\CustomValidationException;
use App\Service\OpenPGP\GenerateOpenPGPKeyService;
use App\Service\OpenPGP\OpenPGPCommonServerOperationsTrait;
use App\Service\OpenPGP\OpenPGPCommonUserOperationsTrait;
use App\Utility\OpenPGP\OpenPGPBackendFactory;
use Cake\Core\Configure;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\Log\Log;
use Cake\Routing\Router;
use Cipherguard\Metadata\Form\MetadataCleartextPrivateKeyForm;
use Cipherguard\Metadata\Model\Entity\MetadataKey;
use Cipherguard\Metadata\Service\MetadataKeyCreateService;
use Cipherguard\Metadata\Service\MetadataKeyShareDefaultService;
use Cipherguard\Metadata\Test\Utility\GpgMetadataKeysTestTrait;

class GenerateDummyMetadataKeyService extends MetadataKeyShareDefaultService
{
    use GpgMetadataKeysTestTrait;
    use OpenPGPCommonUserOperationsTrait;
    use OpenPGPCommonServerOperationsTrait;

    /**
     * Constructor - prevent use in production
     */
    public function __construct()
    {
        if (!Configure::read('debug') || !Configure::read('cipherguard.selenium.active')) {
            throw new ForbiddenException();
        }
    }

    /**
     * @param bool $verbose default false
     * @return \Cipherguard\Metadata\Model\Entity\MetadataKey
     * @throws \Exception if process fails
     */
    public function generate(bool $verbose = false): MetadataKey
    {
        $key = (new GenerateOpenPGPKeyService())->generateMetadataKey($verbose);

        $data = [
            'armored_key' => $key['public_key'],
            'fingerprint' => $key['fingerprint'],
            'created_by' => null,
            'modified_by' => null,
            'metadata_private_keys' => [],
        ];

        $userKeys = $this->preparePrivateKeyDataForAllUsers($key);
        if (!empty($userKeys)) {
            $data['metadata_private_keys'] = $userKeys;
        }

        $serverKey = $this->preparePrivateKeyDataForServer($key);
        if (!empty($serverKey)) {
            $data['metadata_private_keys'][] = $serverKey;
        }

        try {
            return (new MetadataKeyCreateService())->buildAndSaveEntity($data);
        } catch (CustomValidationException $exception) {
            Log::error(json_encode($exception->getErrors()));
            throw $exception;
        }
    }

    /**
     * @param array $key dto
     * @return array data
     */
    protected function preparePrivateKeyDataForServer(array $key): array
    {
        $data = [];
        $gpg = OpenPGPBackendFactory::get();
        try {
            $gpg->clearKeys();
            $gpg = $this->setEncryptKeyWithServerKey($gpg);
            $gpg = $this->setSignKeyWithServerKey($gpg);
            $msg = $gpg->encrypt(json_encode([
                'object_type' => MetadataCleartextPrivateKeyForm::CIPHERGUARD_METADATA_PRIVATE_KEY,
                'domain' => Router::url('/', true),
                'armored_key' => $key['private_key'],
                'fingerprint' => $key['fingerprint'],
                'passphrase' => $key['passphrase'],
            ]), true);
            $data = [
                'data' => $msg,
                'user_id' => null,
                'created_by' => null,
                'modified_by' => null,
            ];
        } catch (\Exception $exception) {
            $msg = __('Could not prepare private key for server key.')
                . ' ' . $exception->getMessage();
            Log::error($msg);
        }

        return $data;
    }

    /**
     * @param array $key dto
     * @return array data
     */
    protected function preparePrivateKeyDataForAllUsers(array $key): array
    {
        $data = [];
        $userTable = $this->fetchTable('Users');
        /** @var \App\Model\Entity\User[] $users */
        $users = $userTable->find('activeNotDeleted')->contain('Gpgkeys')->all();
        $gpg = OpenPGPBackendFactory::get();
        foreach ($users as $user) {
            try {
                $gpg->clearKeys();
                if (!isset($user->gpgkey)) {
                    throw new NotFoundException(__('User key not found.'));
                }
                $gpg = $this->setEncryptKeyWithUserKey($gpg, $user->gpgkey);
                $gpg = $this->setSignKeyWithServerKey($gpg);
                $msg = $gpg->encrypt(json_encode([
                    'object_type' => MetadataCleartextPrivateKeyForm::CIPHERGUARD_METADATA_PRIVATE_KEY,
                    'domain' => Router::url('/', true),
                    'armored_key' => $key['private_key'],
                    'fingerprint' => $key['fingerprint'],
                    'passphrase' => $key['passphrase'],
                ]), true);
                $data[] = [
                    'data' => $msg,
                    'user_id' => $user->id,
                    'created_by' => null,
                    'modified_by' => null,
                ];
            } catch (\Exception $exception) {
                $msg = __('Could not prepare private key for user id {0}.', $user->id)
                    . ' ' . $exception->getMessage();
                Log::error($msg);
            }
        }

        return $data;
    }
}
