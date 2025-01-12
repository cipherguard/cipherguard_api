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

namespace App\Service\Healthcheck\Ssl;

class PeerValidSslHealthcheck extends AbstractBaseSslHealthcheck
{
    /**
     * @inheritDoc
     */
    protected function getClientOptions(): array
    {
        return [
            'ssl_verify_peer' => true,
            'ssl_verify_host' => false,
            'ssl_allow_self_signed' => true,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getSuccessMessage(): string
    {
        return __('SSL peer certificate validates.');
    }

    /**
     * @inheritDoc
     */
    public function getFailureMessage(): string
    {
        return __('SSL peer certificate does not validate.');
    }

    /**
     * @inheritDoc
     */
    public function getLegacyArrayKey(): string
    {
        return 'peerValid';
    }
}
