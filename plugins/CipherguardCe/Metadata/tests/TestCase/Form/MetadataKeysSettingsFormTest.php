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

namespace Cipherguard\Metadata\Test\TestCase\Form;

use App\Test\Lib\AppTestCaseV5;
use Cipherguard\Metadata\Form\MetadataKeysSettingsForm;
use Cipherguard\Metadata\Model\Dto\MetadataKeysSettingsDto;
use Cipherguard\Metadata\Service\Migration\MigrateAllV4ToV5ServiceCollector;
use Cipherguard\Metadata\Test\Factory\MetadataKeysSettingsFactory;

class MetadataKeysSettingsFormTest extends AppTestCaseV5
{
    /**
     * @var MetadataKeysSettingsForm $form
     */
    protected $form;

    public function setUp(): void
    {
        parent::setUp();
        $this->form = new MetadataKeysSettingsForm();
    }

    public function tearDown(): void
    {
        unset($this->form);
        MigrateAllV4ToV5ServiceCollector::clear();
        parent::tearDown();
    }

    public function getDefaultData(): array
    {
        return MetadataKeysSettingsFactory::getDefaultData();
    }

    public function testMetadataKeysSettingsForm_Success(): void
    {
        $this->assertTrue($this->form->execute($this->getDefaultData()));
    }

    public function testMetadataKeysSettingsForm_Error_Empty(): void
    {
        $this->assertFalse($this->form->execute([]));
        $errors = $this->form->getErrors();
        foreach (MetadataKeysSettingsDto::PROPS as $prop) {
            $this->assertTrue(isset($errors[$prop]['_empty']));
        }
    }

    public function testMetadataKeysSettingsForm_Error_NotBool(): void
    {
        $data = array_merge($this->getDefaultData(), [
            MetadataKeysSettingsDto::ALLOW_USAGE_OF_PERSONAL_KEYS => 'test',
            MetadataKeysSettingsDto::ZERO_KNOWLEDGE_KEY_SHARE => 'test',
        ]);
        $this->assertFalse($this->form->execute($data));
        $errors = $this->form->getErrors();
        $this->assertTrue(isset($errors[MetadataKeysSettingsDto::ALLOW_USAGE_OF_PERSONAL_KEYS]['boolean']));
        $this->assertTrue(isset($errors[MetadataKeysSettingsDto::ZERO_KNOWLEDGE_KEY_SHARE]['boolean']));
    }
}
