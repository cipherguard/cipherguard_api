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

namespace App\Test\TestCase\Notification\Email;

use App\Notification\Email\Email;
use App\Notification\Email\EmailSubscriptionManager;
use App\Test\Factory\UserFactory;
use Cake\Event\Event;
use Cake\TestSuite\TestCase;

class EmailSubscriptionManagerTest extends TestCase
{
    use SubscribedEmailRedactorMockTrait;

    /**
     * @var EmailSubscriptionManager
     */
    private $sut;

    public function setUp(): void
    {
        $this->sut = new EmailSubscriptionManager();
        parent::setUp();
    }

    public function testThatNewSubscriptionRegisterAllSubscribedEventsForRedactor()
    {
        $expectedRedactors = [
            $this->createSubscribedRedactor(
                ['event_name'],
                new Email(UserFactory::make()->getEntity(), 'test', [], 'test')
            ),
            $this->createSubscribedRedactor(
                ['event_name'],
                new Email(UserFactory::make()->getEntity(), 'test', [], 'test')
            ),
        ];
        $this->sut->addNewSubscription($expectedRedactors[0]);
        $this->sut->addNewSubscription($expectedRedactors[1]);

        $this->assertEquals($expectedRedactors, $this->sut->getSubscriptionsForEvent(new Event('event_name')));
    }

    public function testThatGetSubscribedEventsReturnAllEventsSubscribed()
    {
        $expectedRedactors = [
            $this->createSubscribedRedactor(
                ['event_name'],
                new Email(UserFactory::make()->getEntity(), 'test', [], 'test')
            ),
            $this->createSubscribedRedactor(
                ['event_name1'],
                new Email(UserFactory::make()->getEntity(), 'test', [], 'test')
            ),
        ];
        $this->sut->addNewSubscription($expectedRedactors[0]);
        $this->sut->addNewSubscription($expectedRedactors[1]);

        $this->assertEquals(['event_name', 'event_name1'], $this->sut->getSubscribedEvents());
    }
}
