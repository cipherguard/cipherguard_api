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
 * @since         4.7.0
 */

namespace App\Service\Healthcheck\Gpg;

use App\Service\Healthcheck\HealthcheckServiceInterface;
use App\Utility\OpenPGP\OpenPGPBackendFactory;

class PublicKeyInKeyringGpgHealthcheck extends AbstractGpgHealthcheck
{
    /**
     * @inheritDoc
     */
    public function check(): HealthcheckServiceInterface
    {
        $fingerprint = $this->getServerKeyFingerprint();
        if (!$this->getGpgHome() || $fingerprint === null) {
            return $this;
        }
        $gpg = OpenPGPBackendFactory::get();
        if (!$gpg->isKeyInKeyring($fingerprint)) {
            return $this;
        }

        $this->status = true;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSuccessMessage(): string
    {
        return __('The server public key defined in the {0} (or environment variables) is in the keyring.', CONFIG . 'cipherguard.php');// phpcs:ignore
    }

    /**
     * @inheritDoc
     */
    public function getFailureMessage(): string
    {
        return __('The server public key defined in the {0} (or environment variables) is not in the keyring', CONFIG . 'cipherguard.php');// phpcs:ignore
    }

    /**
     * @inheritDoc
     */
    public function getHelpMessage()
    {
        return [
            __('Import the private server key in the keyring of the webserver user.'),
            __('you can try:'),
            'sudo su -s /bin/bash -c "gpg --home ' . $this->getGpgHome() . ' --import ' . $this->getPrivateServerKey() . '" ' . PROCESS_USER,// phpcs:ignore
        ];
    }

    /**
     * @inheritDoc
     */
    public function getLegacyArrayKey(): string
    {
        return 'gpgKeyPublicInKeyring';
    }
}
