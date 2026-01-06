<?php
declare(strict_types=1);

namespace App\Service\Api;

use App\Service\Api\Anthropic\AnthropicApiService;
use App\Service\Api\OpenRouter\OpenRouterApiService;
use App\Utility\SettingsManager;

/**
 * AiServiceFactory
 *
 * Factory class for creating AI provider instances based on application settings.
 * Supports switching between Anthropic (direct API) and OpenRouter providers.
 */
class AiServiceFactory
{
    /**
     * Create the appropriate AI provider based on settings.
     *
     * @return \App\Service\Api\AiProviderInterface The configured AI provider.
     */
    public static function createProvider(): AiProviderInterface
    {
        $provider = SettingsManager::read('Anthropic.provider', 'anthropic');

        return match ($provider) {
            'openrouter' => new OpenRouterApiService(),
            default => new AnthropicApiService(),
        };
    }

    /**
     * Get the currently configured provider name.
     *
     * @return string The provider identifier ('anthropic' or 'openrouter').
     */
    public static function getProviderName(): string
    {
        return SettingsManager::read('Anthropic.provider', 'anthropic');
    }

    /**
     * Check if the configured provider is properly set up.
     *
     * @return bool True if the provider has valid credentials.
     */
    public static function isConfigured(): bool
    {
        $provider = static::createProvider();

        return $provider->isConfigured();
    }
}
