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
                'value_type' => 'numeric',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'ImageSizes',
                'key_name' => 'extra-large',
                'value' => '500',
                'value_type' => 'numeric',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'ImageSizes',
                'key_name' => 'large',
                'value' => '400',
                'value_type' => 'numeric',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'ImageSizes',
                'key_name' => 'medium',
                'value' => '300',
                'value_type' => 'numeric',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'ImageSizes',
                'key_name' => 'small',
                'value' => '200',
                'value_type' => 'numeric',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'ImageSizes',
                'key_name' => 'tiny',
                'value' => '100',
                'value_type' => 'numeric',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'ImageSizes',
                'key_name' => 'teeny',
                'value' => '50',
                'value_type' => 'numeric',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'ImageSizes',
                'key_name' => 'micro',
                'value' => '10',
                'value_type' => 'numeric',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'Email',
                'key_name' => 'reply_email',
                'value' => 'noreply@example.com',
                'value_type' => 'text',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'SEO',
                'key_name' => 'siteStrapline',
                'value' => 'Welcome to Willow CMS',
                'value_type' => 'text',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'AI',
                'key_name' => 'anthropicApiKey',
                'value' => 'your-api-key-here',
                'value_type' => 'text',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'AI',
                'key_name' => 'enabled',
                'value' => '0',
                'value_type' => 'bool',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'Comments',
                'key_name' => 'articlesEnabled',
                'value' => '1',
                'value_type' => 'bool',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'Comments',
                'key_name' => 'pagesEnabled',
                'value' => '1',
                'value_type' => 'bool',
            ],
            [
                'id' => Text::uuid(),
                'category' => 'Users',
                'key_name' => 'registrationEnabled',
                'value' => '1',
                'value_type' => 'bool',
            ],
        ];
        parent::init();
    }
}
