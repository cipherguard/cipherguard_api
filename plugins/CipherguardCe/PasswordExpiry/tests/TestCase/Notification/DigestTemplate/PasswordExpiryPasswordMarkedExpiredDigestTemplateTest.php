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
 * @since         4.5.0
 */

namespace Cipherguard\PasswordExpiry\Test\TestCase\Notification\DigestTemplate;

use App\Test\Factory\UserFactory;
use App\Utility\UuidFactory;
use Cake\TestSuite\TestCase;
use Cipherguard\EmailDigest\EmailDigestPlugin;
use Cipherguard\EmailDigest\Service\EmailDigestService;
use Cipherguard\EmailDigest\Test\Factory\EmailQueueFactory;
use Cipherguard\Locale\LocalePlugin;
use Cipherguard\PasswordExpiry\Notification\Email\PasswordExpiryPasswordMarkedExpiredEmailRedactor;
use Cipherguard\PasswordExpiry\PasswordExpiryPlugin;

class PasswordExpiryPasswordMarkedExpiredDigestTemplateTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->loadPlugins([
            EmailDigestPlugin::class => [],
            LocalePlugin::class => [],
            PasswordExpiryPlugin::class => [],
        ]);
    }

    public function testPasswordExpiryExpiredPasswordDigestTemplate(): void
    {
        $recipient = 'foo@cipherguard.test';
        [$operator1, $operator2, $operator10] = UserFactory::make(3)
            ->withAvatarNull()
            ->getEntities();

        $factory = EmailQueueFactory::make()
            ->setRecipient($recipient)
            ->setTemplate(PasswordExpiryPasswordMarkedExpiredEmailRedactor::TEMPLATE)
            ->setField('template_vars.body.resourceId', UuidFactory::uuid());

        $emails[] = $factory
            ->setField('template_vars.body.operator', $operator1->toArray())
            ->getEntity();

        $emails = array_merge($emails, $factory
            ->setTimes(2)
            ->setField('template_vars.body.operator', $operator2->toArray())
            ->getEntities());

        $emails = array_merge($emails, $factory
            ->setTimes(10)
            ->setField('template_vars.body.operator', $operator10->toArray())
            ->getEntities());

        $this->assertCount(13, $emails);

        $digests = (new EmailDigestService())->createEmailDigests($emails);
        $this->assertCount(3, $digests);
    }
}
