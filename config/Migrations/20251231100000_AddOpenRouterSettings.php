<?php
declare(strict_types=1);

use Cake\Utility\Text;
use Migrations\AbstractMigration;

/**
 * AddOpenRouterSettings migration.
 *
 * Adds settings to support OpenRouter as an alternative AI provider.
 * OpenRouter allows using Anthropic Claude models through their API gateway,
 * which may offer different pricing and rate limits.
 */
class AddOpenRouterSettings extends AbstractMigration
{
    /**
     * Change Method.
     *
     * @return void
     */
    public function change(): void
    {
        $this->table('settings')
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 0,
                'category' => 'Anthropic',
                'key_name' => 'provider',
                'value' => 'anthropic',
                'value_type' => 'select',
                'value_obscure' => false,
                'description' => 'Choose which AI provider to use for Claude models. "Anthropic (Direct)" connects directly to Anthropic\'s API, while "OpenRouter" routes requests through OpenRouter.ai which may offer different pricing tiers and rate limits. Both options use Claude models.',
                'data' => '{"anthropic": "Anthropic (Direct)", "openrouter": "OpenRouter"}',
                'column_width' => 4,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 2,
                'category' => 'Anthropic',
                'key_name' => 'openRouterApiKey',
                'value' => '',
                'value_type' => 'text',
                'value_obscure' => true,
                'description' => 'Your OpenRouter API key. Required only when using OpenRouter as the AI provider. Get your key from https://openrouter.ai/keys',
                'data' => null,
                'column_width' => 12,
            ])
            ->save();

        // Update the ordering of the existing apiKey setting to come after provider
        $this->execute("UPDATE settings SET ordering = 1 WHERE category = 'Anthropic' AND key_name = 'apiKey'");
    }
}
