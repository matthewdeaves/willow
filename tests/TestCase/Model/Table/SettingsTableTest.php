<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\SettingsTable;
use ArrayObject;
use Cake\Event\EventInterface;
use Cake\TestSuite\TestCase;
use Cake\Validation\Validator;

/**
 * App\Model\Table\SettingsTable Test Case
 */
class SettingsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\SettingsTable
     */
    protected $SettingsTable;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Settings',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Settings') ? [] : ['className' => SettingsTable::class];
        $this->SettingsTable = $this->getTableLocator()->get('Settings', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->SettingsTable);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\SettingsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $validator = new Validator();
        $validator = $this->SettingsTable->validationDefault($validator);

        // Test valid data
        $validData = [
            'category' => 'TestCategory',
            'key_name' => 'TestKey',
            'type' => 'numeric',
            'value' => '100',
        ];
        $errors = $validator->validate($validData);
        $this->assertEmpty($errors, 'Validation should pass for valid numeric data');

        $validStringData = [
            'category' => 'TestCategory',
            'key_name' => 'TestKey',
            'type' => 'text',
            'value' => 'Test Value',
        ];
        $errors = $validator->validate($validStringData);
        $this->assertEmpty($errors, 'Validation should pass for valid string data');

        // Test missing required fields
        $missingCategory = $validData;
        unset($missingCategory['category']);
        $errors = $validator->validate($missingCategory);
        $this->assertArrayHasKey('category', $errors, 'Validation should fail when category is missing');

        $missingKeyName = $validData;
        unset($missingKeyName['key_name']);
        $errors = $validator->validate($missingKeyName);
        $this->assertArrayHasKey('key_name', $errors, 'Validation should fail when key_name is missing');

        $missingValue = $validData;
        unset($missingValue['value']);
        $errors = $validator->validate($missingValue);
        $this->assertArrayHasKey('value', $errors, 'Validation should fail when value is missing');

        // Test empty values
        $emptyCategory = $validData;
        $emptyCategory['category'] = '';
        $errors = $validator->validate($emptyCategory);
        $this->assertArrayHasKey('category', $errors, 'Validation should fail when category is empty');

        $emptyKeyName = $validData;
        $emptyKeyName['key_name'] = '';
        $errors = $validator->validate($emptyKeyName);
        $this->assertArrayHasKey('key_name', $errors, 'Validation should fail when key_name is empty');

        $emptyValueNumeric = $validData;
        $emptyValueNumeric['value'] = '';
        $errors = $validator->validate($emptyValueNumeric);
        $this->assertArrayHasKey('value', $errors, 'Validation should fail when value is empty for numeric setting');

        $emptyValueString = $validStringData;
        $emptyValueString['value'] = '';
        $errors = $validator->validate($emptyValueString);
        $this->assertArrayHasKey('value', $errors, 'Validation should fail when value is empty for string setting');

        // Test invalid numeric type
        $invalidIsNumeric = $validData;
        $invalidIsNumeric['is_numeric'] = 'not_a_boolean';
        $errors = $validator->validate($invalidIsNumeric);
        $this->assertArrayHasKey('is_numeric', $errors, 'Validation should fail when is_numeric is not a boolean');

        // Test max length for string fields
        $longCategory = $validData;
        $longCategory['category'] = str_repeat('a', 256);
        $errors = $validator->validate($longCategory);
        $this->assertArrayHasKey('category', $errors, 'Validation should fail when category exceeds max length');

        $longKeyName = $validData;
        $longKeyName['key_name'] = str_repeat('a', 256);
        $errors = $validator->validate($longKeyName);
        $this->assertArrayHasKey('key_name', $errors, 'Validation should fail when key_name exceeds max length');

        // Test subcategory (optional field)
        $withSubcategory = $validData;
        $withSubcategory['subcategory'] = 'TestSubcategory';
        $errors = $validator->validate($withSubcategory);
        $this->assertEmpty($errors, 'Validation should pass with valid subcategory');

        $longSubcategory = $validData;
        $longSubcategory['subcategory'] = str_repeat('a', 256);
        $errors = $validator->validate($longSubcategory);
        $this->assertArrayHasKey('subcategory', $errors, 'Validation should fail when subcategory exceeds max length');
    }

    /**
     * Test beforeSave method
     *
     * @return void
     * @uses \App\Model\Table\SettingsTable::beforeSave()
     */
    public function testBeforeSave(): void
    {
        $numericSetting = $this->SettingsTable->newEntity([
            'category' => 'TestCategory',
            'key_name' => 'numeric_setting',
            'value' => '100',
            'is_numeric' => true,
        ]);
        $this->SettingsTable->save($numericSetting);

        $event = $this->getMockBuilder(EventInterface::class)->getMock();
        $options = new ArrayObject();

        // Test saving a non-numeric value to a numeric setting
        $numericSetting->value = 'not_a_number';
        $result = $this->SettingsTable->beforeSave($event, $numericSetting, $options);
        $this->assertFalse($result, 'Should not save non-numeric value to numeric setting');
        $this->assertNotEmpty($numericSetting->getError('value'), 'Should set an error on the value field');

        // Test saving a numeric value to a numeric setting
        $numericSetting->value = '200';
        $result = $this->SettingsTable->beforeSave($event, $numericSetting, $options);
        $this->assertTrue($result, 'Should save numeric value to numeric setting');

        // Test saving an empty value to a non-numeric setting
        $nonNumericSetting = $this->SettingsTable->newEntity([
            'category' => 'TestCategory',
            'key_name' => 'non_numeric_setting',
            'value' => 'test',
            'is_numeric' => false,
        ]);
        $this->SettingsTable->save($nonNumericSetting);

        $nonNumericSetting->value = '';
        $result = $this->SettingsTable->beforeSave($event, $nonNumericSetting, $options);
        $this->assertFalse($result, 'Should not save empty value to non-numeric setting');
        $this->assertNotEmpty($nonNumericSetting->getError('value'), 'Should set an error on the value field');
    }

    /**
     * Test getSettingValue method
     *
     * @return void
     * @uses \App\Model\Table\SettingsTable::getSettingValue()
     */
    public function testGetSettingValue(): void
    {
        // Test getting all settings for a category
        $imageSizes = $this->SettingsTable->getSettingValue('ImageSizes');
        $this->assertIsArray($imageSizes, 'Should return an array of image sizes');
        $this->assertArrayHasKey('massive', $imageSizes, 'Should contain "massive" key');
        $this->assertEquals('800', $imageSizes['massive'], 'Should have correct value for "massive"');

        // Test getting a specific setting
        $replyEmail = $this->SettingsTable->getSettingValue('Email', 'reply_email');
        $this->assertEquals('noreply@example.com', $replyEmail, 'Should return correct reply email');

        // Test getting a non-existent setting
        $nonExistent = $this->SettingsTable->getSettingValue('NonExistent', 'non_existent_key');
        $this->assertNull($nonExistent, 'Should return null for non-existent setting');

        // Test getting a non-existent category
        $nonExistentCategory = $this->SettingsTable->getSettingValue('NonExistentCategory');
        $this->assertEmpty($nonExistentCategory, 'Should return empty array for non-existent category');
    }
}
