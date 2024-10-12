<?php
declare(strict_types=1);

namespace App\Test\TestCase\Utility;

use App\Utility\SettingsManager;
use Cake\TestSuite\TestCase;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use Cake\Console\ConsoleOutput;

class SettingsManagerTest extends TestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = ['app.Settings'];

    public function setUp(): void
    {
        parent::setUp();
        //$this->disableErrorHandlerMiddleware();
        SettingsManager::initialize();
        Cache::clear(SettingsManager::getCacheConfig());
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Cache::clear(SettingsManager::getCacheConfig());
    }

    public function testReadAIEnabled(): void
    {
        $settingsTable = TableRegistry::getTableLocator()->get('Settings');
        $settingsTable->saveOrFail($settingsTable->newEntity([
            'category' => 'AI',
            'key_name' => 'enabled',
            'value' => 1,
            'type' => 'bool'
        ]));

        $result = SettingsManager::read('AI.enabled');
        $this->assertIsBool($result);
        $this->assertTrue($result);

        $settingsTable->updateAll(['value' => 'false'], ['category' => 'AI', 'key_name' => 'enabled']);
        $result = SettingsManager::read('AI.enabled', null, true); // Force refresh
        $this->assertFalse($result);
    }

    public function testReadAICategory(): void
    {
        $settingsTable = TableRegistry::getTableLocator()->get('Settings');
        $settingsTable->saveOrFail($settingsTable->newEntity([
            'category' => 'AI',
            'key_name' => 'enabled',
            'value' => 1,
            'type' => 'bool'
        ]));
        $settingsTable->saveOrFail($settingsTable->newEntity([
            'category' => 'AI',
            'key_name' => 'api_key',
            'value' => 'test_api_key',
            'type' => 'text'
        ]));

        $result = SettingsManager::read('AI');

        $output = new ConsoleOutput();
        $output->write(print_r($result));


        $this->assertIsArray($result);
        $this->assertArrayHasKey('enabled', $result);
        $this->assertArrayHasKey('api_key', $result);
        $this->assertTrue($result['enabled']);
        $this->assertEquals('test_api_key', $result['api_key']);
    }

    public function testReadImageSizes(): void
    {
        $settingsTable = TableRegistry::getTableLocator()->get('Settings');
        $imageSizes = [
            'thumbnail' => 150,
            'medium' => 300,
            'large' => 1024
        ];

        foreach ($imageSizes as $key => $value) {
            $settingsTable->saveOrFail($settingsTable->newEntity([
                'category' => 'ImageSizes',
                'key_name' => $key,
                'value' => (string)$value,
                'type' => 'numeric'
            ]));
        }

        $result = SettingsManager::read('ImageSizes');
        
        $this->assertIsArray($result);
        foreach ($imageSizes as $key => $value) {
            $this->assertArrayHasKey($key, $result);
            $this->assertIsInt($result[$key]);
            $this->assertEquals($value, $result[$key]);
        }
    }

    public function testReadNonExistentSetting(): void
    {
        $result = SettingsManager::read('NonExistent.Setting', 'default_value');
        $this->assertEquals('default_value', $result);
    }

    public function testCaching(): void
    {
        $settingsTable = TableRegistry::getTableLocator()->get('Settings');
        $settingsTable->saveOrFail($settingsTable->newEntity([
            'category' => 'Test',
            'key_name' => 'cached_value',
            'value' => 'initial',
            'type' => 'text'
        ]));

        $initialResult = SettingsManager::read('Test.cached_value');
        $this->assertEquals('initial', $initialResult);

        $settingsTable->updateAll(['value' => 'updated'], ['category' => 'Test', 'key_name' => 'cached_value']);

        $cachedResult = SettingsManager::read('Test.cached_value');
        $this->assertEquals('initial', $cachedResult, 'Should return cached value');

        SettingsManager::clearCache();
        $refreshedResult = SettingsManager::read('Test.cached_value');
        $this->assertEquals('updated', $refreshedResult, 'Should return updated value after cache clear');
    }

    public function testReadWithDifferentTypes(): void
    {
        $settingsTable = TableRegistry::getTableLocator()->get('Settings');
        $settings = [
            ['category' => 'Types', 'key_name' => 'string_value', 'value' => 'test', 'type' => 'text'],
            ['category' => 'Types', 'key_name' => 'int_value', 'value' => '42', 'type' => 'numeric'],
            ['category' => 'Types', 'key_name' => 'float_value', 'value' => '3.14', 'type' => 'numeric'],
            ['category' => 'Types', 'key_name' => 'bool_value', 'value' => 1, 'type' => 'bool'],
        ];

        foreach ($settings as $setting) {
            $settingsTable->saveOrFail($settingsTable->newEntity($setting));
        }

        $this->assertIsString(SettingsManager::read('Types.string_value'));
        $this->assertEquals('test', SettingsManager::read('Types.string_value'));

        $this->assertIsInt(SettingsManager::read('Types.int_value'));
        $this->assertEquals(42, SettingsManager::read('Types.int_value'));

        $this->assertIsFloat(SettingsManager::read('Types.float_value'));
        $this->assertEquals(3.14, SettingsManager::read('Types.float_value'));

        $this->assertIsBool(SettingsManager::read('Types.bool_value'));
        $this->assertTrue(SettingsManager::read('Types.bool_value'));
    }
}