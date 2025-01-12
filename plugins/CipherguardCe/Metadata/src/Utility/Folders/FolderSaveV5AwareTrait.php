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

namespace Cipherguard\Metadata\Utility\Folders;

use Cake\ORM\TableRegistry;
use Cipherguard\Metadata\Model\Dto\MetadataFolderDto;

/**
 * Trait ResourcesVersionValidationServiceTrait
 *
 * A utility trait to set the validation options when creating or patching resources
 * based on v4 or v5 format
 */
trait FolderSaveV5AwareTrait
{
    /**
     * Returns options array to use while saving folder entity.
     *
     * @param \Cipherguard\Metadata\Model\Dto\MetadataFolderDto $folderDto DTO.
     * @return array
     */
    public function getOptionsForFolderSave(MetadataFolderDto $folderDto): array
    {
        return [
            'accessibleFields' => $this->getAccessibleFields($folderDto),
            'validate' => $this->getValidator($folderDto),
        ];
    }

    /**
     * Accessible fields array for resource save.
     *
     * @param \Cipherguard\Metadata\Model\Dto\MetadataFolderDto $folderDto DTO.
     * @return array
     */
    private function getAccessibleFields(MetadataFolderDto $folderDto): array
    {
        $isV5 = $folderDto->isV5();
        $fields = [];

        if ($isV5) {
            $metadataFields = MetadataFolderDto::V5_META_PROPS;
        } else {
            $metadataFields = MetadataFolderDto::V4_META_PROPS;
        }

        foreach ($metadataFields as $metadataField) {
            $fields[$metadataField] = true;
        }

        return $fields;
    }

    /**
     * Returns validator method to use (V4 or V5) while saving folder entity.
     *
     * @param \Cipherguard\Metadata\Model\Dto\MetadataFolderDto $folderDto DTO.
     * @return string
     */
    protected function getValidator(MetadataFolderDto $folderDto): string
    {
        $isV5 = $folderDto->isV5();
        if ($isV5) {
            $validator = 'v5';
            /** @var \Cipherguard\Folders\Model\Table\FoldersTable $foldersTable */
            $foldersTable = TableRegistry::getTableLocator()->get('Cipherguard/Folders.Folders');
            /** @var \Cake\ORM\RulesChecker $rules */
            $rules = $foldersTable->rulesChecker();
            $foldersTable->buildRulesV5($rules);
        } else {
            $validator = 'default';
        }

        return $validator;
    }
}
