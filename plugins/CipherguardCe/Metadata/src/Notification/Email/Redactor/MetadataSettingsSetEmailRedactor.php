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

namespace Cipherguard\Metadata\Notification\Email\Redactor;

use App\Model\Entity\User;
use App\Model\Table\AvatarsTable;
use App\Notification\Email\Email;
use App\Notification\Email\EmailCollection;
use App\Notification\Email\SubscribedEmailRedactorInterface;
use App\Notification\Email\SubscribedEmailRedactorTrait;
use App\Utility\Purifier;
use Cake\Event\Event;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cipherguard\Locale\Service\LocaleService;
use Cipherguard\Metadata\Model\Dto\MetadataSettingsDto;
use Cipherguard\Metadata\Service\MetadataTypesSettingsSetService;

/**
 * @property \App\Model\Table\UsersTable $Users
 */
class MetadataSettingsSetEmailRedactor implements SubscribedEmailRedactorInterface
{
    use LocatorAwareTrait;
    use SubscribedEmailRedactorTrait;

    public const EMAIL_TEMPLATE = 'Cipherguard/Metadata.Admin/metadata_settings_update';

    /**
     * @var \App\Model\Table\UsersTable
     */
    protected $Users;

    /**
     * JwtAuthenticationAttackEmailRedactor constructor.
     */
    public function __construct()
    {
        /** @phpstan-ignore-next-line */
        $this->Users = $this->fetchTable('Users');
    }

    /**
     * Return the list of events to which the redactor is subscribed
     * and when it must create emails to be sent.
     *
     * @return array
     */
    public function getSubscribedEvents(): array
    {
        return [
            MetadataTypesSettingsSetService::AFTER_METADATA_SETTINGS_SET_SUCCESS_EVENT_NAME,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getNotificationSettingPath(): ?string
    {
        return null;
    }

    /**
     * @param \Cake\Event\Event $event User register event
     * @return \App\Notification\Email\EmailCollection
     */
    public function onSubscribedEvent(Event $event): EmailCollection
    {
        $emailCollection = new EmailCollection();
        /** @var \App\Utility\UserAccessControl $uac */
        $uac = $event->getData('uac');
        /** @var \Cipherguard\Metadata\Model\Dto\MetadataSettingsDto $dto */
        $dto = $event->getData('dto');

        $modifier = $this->Users->findFirstForEmail($uac->getId());
        $admins = $this->Users
            ->findAdmins()
            ->contain(['Profiles' => AvatarsTable::addContainAvatar()])
            ->find('notDisabled')
            ->find('locale');

        foreach ($admins as $recipient) {
            $email = $this->createEmail($recipient, $modifier, $dto);
            $emailCollection->addEmail($email);
        }

        return $emailCollection;
    }

    /**
     * @param \App\Model\Entity\User $recipient Admin being notified
     * @param \App\Model\Entity\User $modifier Admin who performed the action
     * @param \Cipherguard\Metadata\Model\Dto\MetadataSettingsDto $dto settings DTO
     * @return \App\Notification\Email\Email
     */
    private function createEmail(
        User $recipient,
        User $modifier,
        MetadataSettingsDto $dto
    ): Email {
        if ($recipient->id === $modifier->id) {
            $subject = $this->getSubjectForModifier($recipient);
        } else {
            $subject = $this->getSubjectForOtherAdmin($recipient, $modifier);
        }
        $settings = $dto->toHumanReadableArray(); // No purifying needed, hardcoded [key:value]

        return new Email(
            $recipient,
            $subject,
            [
                'body' => compact('recipient', 'modifier', 'settings', 'subject'),
                'title' => $subject,
            ],
            static::EMAIL_TEMPLATE
        );
    }

    /**
     * @param \App\Model\Entity\User $recipient User to include in the subject
     * @param \App\Model\Entity\User $modifier User performing the action
     * @return string
     */
    private function getSubjectForOtherAdmin(User $recipient, User $modifier): string
    {
        $modifierFirstName = Purifier::clean($modifier['profile']['first_name']);

        return (new LocaleService())->translateString(
            $recipient->locale,
            function () use ($modifierFirstName) {
                return __('{0} edited the metadata settings.', $modifierFirstName);
            }
        );
    }

    /**
     * @param \App\Model\Entity\User $recipient User performing the setting change
     * @return string
     */
    private function getSubjectForModifier(User $recipient): string
    {
        return (new LocaleService())->translateString(
            $recipient->locale,
            function () {
                return __('You edited the metadata settings.');
            }
        );
    }
}
