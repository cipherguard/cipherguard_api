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
 * @since         2.0.0
 */
namespace App\Utility;

use Ramsey\Uuid\Uuid;

class UuidFactory
{
    public const CIPHERGUARD_SEED = 'd5447ca1-950f-459d-8b20-86ddfdd0f922';

    /**
     * Return a UUID v4 or v5
     * Needed because CakePHP Text::uuid is not cryptographically secure
     * But also do not provide uuid5
     *
     * @param string|null $seed optional, used to create uuid5
     * @return string uuid4|uuid5
     * @throws \Exception
     */
    public static function uuid(?string $seed = null): string
    {
        if (is_null($seed)) {
            // Generate a version 4 (random) UUID object
            // uses random_bytes on php7
            // uses openssl_random_bytes on php5
            try {
                $uuid4 = Uuid::uuid4();

                return $uuid4->toString();
            } catch (\Throwable $e) {
                throw new \Exception('Cannot generate a random UUID, some dependencies are missing.');
            }
        } else {
            // Generate a version 5 (name-based and hashed with SHA1) UUID object
            $uuid5 = Uuid::uuid5(UuidFactory::CIPHERGUARD_SEED, $seed);

            return $uuid5->toString();
        }
    }
}
