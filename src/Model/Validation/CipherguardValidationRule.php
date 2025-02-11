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
 * @since         3.6.0
 */

namespace App\Model\Validation;

use Cake\Validation\ValidationRule;

abstract class CipherguardValidationRule extends ValidationRule
{
    /**
     * The rule
     *
     * @param mixed $value Value to validate
     * @param mixed $context Context
     * @return bool
     */
    abstract public function rule($value, $context): bool;

    /**
     * The error displayed
     *
     * @param mixed $value Value to validate
     * @param mixed $context Context
     * @return string
     */
    abstract public function defaultErrorMessage($value, $context): string;

    /**
     * Set the error message
     *
     * @param string $msg Message to be displayed
     * @return void
     */
    public function setErrorMessage(string $msg): void
    {
        $this->_message = $msg;
    }

    /**
     * Construct
     *
     * @psalm-suppress RedundantPropertyInitializationCheck
     * @param array $validator Validation parameters
     */
    public function __construct(array $validator = [])
    {
        parent::__construct($validator);

        $this->_rule = function ($value, $context) {
            $result = $this->rule($value, $context);
            $msg = $this->_message ?? $this->defaultErrorMessage($value, $context);

            return $result ? true : $msg;
        };
    }
}
