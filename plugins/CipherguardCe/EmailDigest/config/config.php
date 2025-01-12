<?php
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
 * @since         3.0.0
 */
return [
    'cipherguard' => [
        'plugins' => [
            'emailDigest' => [
                'version' => '1.0.0',
                'batchSizeLimit' => filter_var(
                    env('CIPHERGUARD_PLUGINS_EMAIL_DIGEST_BATCH_SIZE_LIMIT', '100'),
                    FILTER_VALIDATE_INT
                ),
            ],
        ],
    ],
];
