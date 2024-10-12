<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;
use Cake\Utility\Text;

/**
 * SettingsFixture
 */
class SettingsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => Text::uuid(),
                'category' => 'ImageSizes',
                'key_name' => 'massive',
                'value' => '800',
                'type' => 'numeric',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'ImageSizes',
                'key_name' => 'extra-large',
                'value' => '500',
                'type' => 'numeric',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'ImageSizes',
                'key_name' => 'large',
                'value' => '400',
                'type' => 'numeric',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'ImageSizes',
                'key_name' => 'medium',
                'value' => '300',
                'type' => 'numeric',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'ImageSizes',
                'key_name' => 'small',
                'value' => '200',
                'type' => 'numeric',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'ImageSizes',
                'key_name' => 'tiny',
                'value' => '100',
                'type' => 'numeric',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'ImageSizes',
                'key_name' => 'teeny',
                'value' => '50',
                'type' => 'numeric',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'ImageSizes',
                'key_name' => 'micro',
                'value' => '10',
                'type' => 'numeric',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'Email',
                'key_name' => 'reply_email',
                'value' => 'noreply@example.com',
                'type' => 'text',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'SEO',
                'key_name' => 'siteStrapline',
                'value' => 'Welcome to Willow CMS',
                'type' => 'text',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'AI',
                'key_name' => 'anthropicApiKey',
                'value' => 'your-api-key-here',
                'type' => 'text',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'AI',
                'key_name' => 'enabled',
                'value' => '0',
                'type' => 'bool',
            ],
        ];
        parent::init();
    }
}
