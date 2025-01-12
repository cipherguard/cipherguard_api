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

namespace Cipherguard\PasswordExpiry\Test\TestCase\Form;

use Cake\TestSuite\TestCase;
use Cipherguard\PasswordExpiry\Form\PasswordExpirySettingsForm;
use Cipherguard\PasswordExpiry\Model\Dto\PasswordExpirySettingsDto;

/**
 * @see \Cipherguard\PasswordExpiry\Service\Settings\PasswordExpirySetSettingsService
 */
class PasswordExpirySettingsFormTest extends TestCase
{
    private PasswordExpirySettingsForm $form;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->form = new PasswordExpirySettingsForm();
    }

    /**
     * @inheritDoc
     */
    public function tearDown(): void
    {
        unset($this->form);

        parent::tearDown();
    }

    public function passwordExpirySettingsFormDataProvider(): array
    {
        return [
            [
                'inputData' => [],
                'expectedResult' => false,
            ],
            [
                'inputData' => [
                    PasswordExpirySettingsDto::AUTOMATIC_EXPIRY => true,
                    PasswordExpirySettingsDto::AUTOMATIC_UPDATE => false,
                ],
                'expectedResult' => false,
            ],
            [
                'inputData' => [
                    PasswordExpirySettingsDto::AUTOMATIC_EXPIRY => false,
                    PasswordExpirySettingsDto::AUTOMATIC_UPDATE => true,
                ],
                'expectedResult' => false,
            ],
            [
                'inputData' => [
                    PasswordExpirySettingsDto::AUTOMATIC_EXPIRY => false,
                    PasswordExpirySettingsDto::AUTOMATIC_UPDATE => false,
                ],
                'expectedResult' => false,
            ],
            [
                'inputData' => [
                    PasswordExpirySettingsDto::AUTOMATIC_EXPIRY => true,
                    PasswordExpirySettingsDto::AUTOMATIC_UPDATE => true,
                ],
                'expectedResult' => true,
            ],
        ];
    }

    /**
     * @dataProvider passwordExpirySettingsFormDataProvider
     */
    public function testPasswordExpirySettingsForm(array $data, bool $expectedResult)
    {
        $this->assertSame($expectedResult, $this->form->validate($data));
    }
}
