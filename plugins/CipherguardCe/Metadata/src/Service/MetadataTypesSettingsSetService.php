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

use App\Utility\UserAccessControl;
use Cake\Event\EventDispatcherTrait;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cipherguard\Metadata\Model\Dto\MetadataTypesSettingsDto;

class MetadataTypesSettingsSetService
{
    use EventDispatcherTrait;
    use LocatorAwareTrait;

    public const AFTER_METADATA_SETTINGS_SET_SUCCESS_EVENT_NAME = 'MetadataSettings.afterSettingSet.success';

    /**
     * Validates and save the metadata settings
     *
     * @param \App\Utility\UserAccessControl $uac user access control
     * @param array $data Data provided in the payload
     * @return \Cipherguard\Metadata\Model\Dto\MetadataTypesSettingsDto dto
     * @throws \Cake\Http\Exception\UnauthorizedException When user role is not admin.
     * @throws \App\Error\Exception\CustomValidationException When there are validation errors.
     * @throws \Cake\Http\Exception\InternalErrorException|\Exception When unable to save the entity.
     * @throws \App\Error\Exception\FormValidationException if the data does not validate
     */
    public function saveSettings(UserAccessControl $uac, array $data): MetadataTypesSettingsDto
    {
        $uac->assertIsAdmin();

        $dto = (new MetadataTypesSettingsAssertService())->assert($data);

        /** @var \App\Model\Table\OrganizationSettingsTable $orgSettingsTable */
        $orgSettingsTable = $this->fetchTable('OrganizationSettings');
        $orgSettingsTable->createOrUpdateSetting(
            MetadataTypesSettingsGetService::ORG_SETTING_PROPERTY,
            $dto->toJson(),
            $uac
        );

        $this->dispatchEvent(
            static::AFTER_METADATA_SETTINGS_SET_SUCCESS_EVENT_NAME,
            compact('dto', 'uac'),
            $this
        );

        return $dto;
    }
}
