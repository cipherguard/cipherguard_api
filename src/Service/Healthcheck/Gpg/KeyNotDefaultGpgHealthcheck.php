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

class KeyNotDefaultGpgHealthcheck extends AbstractGpgHealthcheck
{
    protected bool $isGpgkeyDefined = false;

    /**
     * @inheritDoc
     */
    public function check(): HealthcheckServiceInterface
    {
        $fingerprint = $this->getServerKeyFingerprint();
        $this-> isGpgkeyDefined = !is_null($fingerprint);
        if (!$this->isGpgkeyDefined) {
            return $this;
        }

        $default = '2FC8945833C51946E937F9FED47B0811573EE67E';
        $this->status = ($fingerprint !== $default);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSuccessMessage(): string
    {
        return __('The server OpenPGP key is not the default one.');
    }

    /**
     * @inheritDoc
     */
    public function getFailureMessage(): string
    {
        if ($this->isGpgkeyDefined) {
            return __('Do not use the default OpenPGP key for the server.');
        }

        return __('The server OpenPGP key is not set.');
    }

    /**
     * @inheritDoc
     */
    public function getHelpMessage()
    {
        return [
            __('Create a key, export it and add the fingerprint to {0}', CONFIG . 'cipherguard.php'),
            __('See. https://www.cipherguard.com/help/tech/install#toc_gpg'),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getLegacyArrayKey(): string
    {
        return 'gpgKeyNotDefault';
    }
}
