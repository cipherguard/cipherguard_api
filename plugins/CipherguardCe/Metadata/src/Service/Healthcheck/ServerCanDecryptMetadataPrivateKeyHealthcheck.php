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

namespace Cipherguard\Metadata\Service\Healthcheck;

use App\Service\Healthcheck\HealthcheckServiceCollector;
use App\Service\Healthcheck\HealthcheckServiceInterface;
use App\Service\OpenPGP\OpenPGPCommonServerOperationsTrait;
use App\Utility\OpenPGP\OpenPGPBackendFactory;
use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Query;

class ServerCanDecryptMetadataPrivateKeyHealthcheck implements HealthcheckServiceInterface
{
    use LocatorAwareTrait;
    use OpenPGPCommonServerOperationsTrait;

    /**
     * Status of this health check if it is passed or failed.
     *
     * @var bool
     */
    private bool $status = false;

    /**
     * @var string|null
     */
    private ?string $errorMessage = null;

    /**
     * @inheritDoc
     */
    public function check(): HealthcheckServiceInterface
    {
        try {
            $metadataPrivateKeysTable = $this->fetchTable('Cipherguard/Metadata.MetadataPrivateKeys');
            /** @var \Cipherguard\Metadata\Model\Entity\MetadataPrivateKey $serverMetadataPrivateKey */
            $serverMetadataPrivateKey = $metadataPrivateKeysTable
                ->find()
                ->contain('MetadataKeys')
                ->innerJoinWith('MetadataKeys', function (Query $q) {
                    $expr = $q->newExpr()->isNull('MetadataKeys.deleted');

                    return $q->where([$expr]);
                })
                ->where(['MetadataPrivateKeys.user_id IS' => null])
                ->order(['MetadataPrivateKeys.created' => 'DESC'])
                ->firstOrFail();
        } catch (\PDOException | RecordNotFoundException $exception) {
            $this->errorMessage = __('No server metadata private key found.');
            if (Configure::read('debug')) {
                $this->errorMessage .= ' ' . $exception->getMessage();
            }

            // No metadata private key found
            return $this;
        }

        // Try to decrypt it
        try {
            $openpgp = OpenPGPBackendFactory::get();
            $openpgp->clearKeys();
            $openpgp = $this->setDecryptKeyWithServerKey($openpgp);
            if ($serverMetadataPrivateKey->metadata_key->modified_by === null) {
                $this->setVerifyKeyWithServerKey($openpgp);
                $openpgp->decrypt($serverMetadataPrivateKey->data, true);
            } else {
                // TODO verify with user key
                $openpgp->decrypt($serverMetadataPrivateKey->data, false);
            }

            // mark as succeed if able to decrypt
            $this->status = true;
        } catch (\Exception $exception) {
            // failure
            $this->errorMessage = __('Unable to decrypt the metadata private key data.') . ' ';
            $this->errorMessage .= $exception->getMessage();
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function domain(): string
    {
        return HealthcheckServiceCollector::DOMAIN_METADATA;
    }

    /**
     * @inheritDoc
     */
    public function isPassed(): bool
    {
        return $this->status;
    }

    /**
     * @inheritDoc
     */
    public function level(): string
    {
        return HealthcheckServiceCollector::LEVEL_ERROR;
    }

    /**
     * @inheritDoc
     */
    public function getSuccessMessage(): string
    {
        return __('The server is able to decrypt the metadata private key.');
    }

    /**
     * @inheritDoc
     */
    public function getFailureMessage(): string
    {
        if (is_null($this->errorMessage)) {
            $this->errorMessage = __('Unable to decrypt the metadata private key.');
        }

        return $this->errorMessage;
    }

    /**
     * @inheritDoc
     */
    public function getHelpMessage()
    {
        return null;
    }

    /**
     * CLI Option for this check.
     *
     * @return string
     */
    public function cliOption(): string
    {
        return HealthcheckServiceCollector::DOMAIN_METADATA;
    }

    /**
     * @inheritDoc
     */
    public function getLegacyArrayKey(): string
    {
        return 'canDecryptMetadataPrivateKey';
    }
}
