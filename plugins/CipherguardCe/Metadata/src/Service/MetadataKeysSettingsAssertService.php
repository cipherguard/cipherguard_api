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
namespace Cipherguard\Metadata\Service;

use App\Error\Exception\FormValidationException;
use Cipherguard\Metadata\Form\MetadataKeysSettingsForm;
use Cipherguard\Metadata\Model\Dto\MetadataKeysSettingsDto;

class MetadataKeysSettingsAssertService
{
    /**
     * Validates the setting and return them
     *
     * @param array $data untrusted input
     * @return \Cipherguard\Metadata\Model\Dto\MetadataKeysSettingsDto dto
     * @throws \App\Error\Exception\FormValidationException if the data does not validate
     */
    public function assert(array $data): MetadataKeysSettingsDto
    {
        $form = new MetadataKeysSettingsForm();
        if (!$form->execute($data)) {
            throw new FormValidationException(__('Could not validate the settings.'), $form);
        }

        // TODO build rules
        // if ZERO_KNOWLEDGE_KEY_SHARE && metadata private key exist in settings
        //  then metadata private key must be available for the server

        return new MetadataKeysSettingsDto($form->getData());
    }
}
