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
 * @since         3.3.0
 */
namespace Cipherguard\JwtAuthentication\Service\AccessToken;

use Cipherguard\JwtAuthentication\Error\Exception\AccessToken\InvalidJwtKeyPairException;
use Firebase\JWT\JWT;

class JwksGetService extends JwtAbstractService
{
    public const PUBLIC_KEY_PATH = self::JWT_CONFIG_DIR . 'jwt.pem';

    protected string $keyPath = self::PUBLIC_KEY_PATH;

    /**
     * @return string[]
     * @throws \Cipherguard\JwtAuthentication\Error\Exception\AccessToken\InvalidJwtKeyPairException if the public key file is not found or not readable.
     */
    public function getPublicKey(): array
    {
        $details = $this->getDetails();

        return [
            'kty' => 'RSA',
            'alg' => JwtTokenCreateService::JWT_ALG,
            'use' => 'sig',
            'e' => JWT::urlsafeB64Encode($details['rsa']['e']),
            'n' => JWT::urlsafeB64Encode($details['rsa']['n']),
        ];
    }

    /**
     * @return int
     */
    public function getSecretKeySize(): int
    {
        $details = $this->getDetails();

        return $details['bits'] ?? 0;
    }

    /**
     * @return array
     * @throws \Cipherguard\JwtAuthentication\Error\Exception\AccessToken\InvalidJwtKeyPairException if the public key file is not parsable.
     */
    private function getDetails(): array
    {
        $pubKey = $this->readKeyFileContent();
        $res = openssl_pkey_get_public($pubKey);
        if ($res === false) {
            throw new InvalidJwtKeyPairException(__('The JWT public key could not be extracted.'));
        }
        $details = openssl_pkey_get_details($res);
        if ($details === false) {
            throw new InvalidJwtKeyPairException(__('The JWT public key details could not be read.'));
        }

        return $details;
    }

    /**
     * @return string
     * @throws \Cipherguard\JwtAuthentication\Error\Exception\AccessToken\InvalidJwtKeyPairException if the public key file is not found or not readable.
     */
    public function getRawPublicKey(): string
    {
        return $this->readKeyFileContent();
    }
}
