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
namespace App\Test\Lib\Utility;

trait ObjectTrait
{
    /**
     * Override the phpunit Assert::assertObjectHasAttribute to assert that an object has a specified attribute.
     * We override the parent method to take care of \Cake\ORM\Entity objects for which the properties are declared
     * on the fly and cannot be tested with the php ReflectionObject used by the phpunit
     * PHPUnit\Framework\Constraint::ObjectHasAttribute class.
     *
     * @param string $attributeName
     * @param object $object
     * @param string $message
     */
    public static function assertObjectHasAttribute(string $attributeName, $object, string $message = ''): void
    {
        if (is_a($object, 'Cake\ORM\Entity')) {
            $objectProperties = $object->toArray();
            self::assertTrue(array_key_exists($attributeName, $objectProperties), 'Missing attribute ' . $attributeName);
        } else {
            parent::assertObjectHasAttribute($attributeName, $object, $message);
        }
    }

    /**
     * Override the phpunit Assert::assertObjectHasAttribute to assert that an object has a specified attribute.
     * We override the parent method to take care of \Cake\ORM\Entity objects for which the properties are declared
     * on the fly and cannot be tested with the php ReflectionObject used by the phpunit
     * PHPUnit\Framework\Constraint::ObjectHasAttribute class.
     *
     * @param string $attributeName
     * @param object $object
     * @param string $message
     */
    public static function assertObjectNotHasAttribute(string $attributeName, $object, string $message = ''): void
    {
        if (is_a($object, 'Cake\ORM\Entity')) {
            $objectProperties = $object->toArray();
            self::assertFalse(array_key_exists($attributeName, $objectProperties));
        } else {
            parent::assertObjectNotHasAttribute($attributeName, $object, $message);
        }
    }

    /**
     * Asserts that an object has specified attributes.
     *
     * @param array $attributesNames
     * @param object $object
     */
    public function assertObjectHasAttributes($attributesNames, $object)
    {
        foreach ($attributesNames as $attributeName) {
            $this->assertObjectHasAttribute($attributeName, $object);
        }
    }

    /**
     * Asserts that an object doesn't have a list of attributes.
     *
     * @param array $attributesNames The list of attributes the object should have.
     * @param object $object The object to test
     * @return void
     */
    public function assertObjectNotHasAttributes(array $attributesNames, object $object): void
    {
        foreach ($attributesNames as $attributeName) {
            $this->assertObjectNotHasAttribute($attributeName, $object);
        }
    }
}
