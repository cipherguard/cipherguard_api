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
 * @since         2.12.0
 */
namespace App\Notification\Email;

use Exception;

class EmailSenderException extends Exception
{
    /**
     * @var \App\Notification\Email\Email
     */
    private $email;

    /**
     * @var array
     */
    private $options;

    /**
     * @param \App\Notification\Email\Email $email Email entity
     * @param array $options Options.
     */
    public function __construct(Email $email, array $options)
    {
        parent::__construct('Failed to send email');
        $this->email = $email;
        $this->options = $options;
    }

    /**
     * @return \App\Notification\Email\Email
     */
    public function getEmail(): Email
    {
        return $this->email;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
