<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

/**
 * AddOpenRouterModelColumn migration.
 *
 * Adds an openrouter_model column to the aiprompts table to allow
 * configuring different models for OpenRouter vs Anthropic providers.
 */
class AddOpenRouterModelColumn extends AbstractMigration
{
    /**
     * Up Method.
     *
     * @return void
     */
    public function up(): void
    {
        $table = $this->table('aiprompts');
        $table->addColumn('openrouter_model', 'string', [
            'default' => null,
            'limit' => 100,
            'null' => true,
            'after' => 'model',
        ]);
        $table->update();

        // Set default OpenRouter model values based on existing Anthropic models
        $modelDefaults = [
            'claude-3-haiku-20240307' => 'anthropic/claude-3-haiku',
            'claude-3-5-sonnet-20241022' => 'anthropic/claude-3.5-sonnet',
            'claude-sonnet-4-5-20250929' => 'anthropic/claude-sonnet-4.5',
            'claude-sonnet-4-5' => 'anthropic/claude-sonnet-4.5',
            'claude-opus-4-5-20251101' => 'anthropic/claude-opus-4.5',
            'claude-opus-4-5' => 'anthropic/claude-opus-4.5',
        ];

        foreach ($modelDefaults as $anthropicModel => $openrouterModel) {
            $this->execute(sprintf(
                "UPDATE aiprompts SET openrouter_model = '%s' WHERE model = '%s' AND openrouter_model IS NULL",
                $openrouterModel,
                $anthropicModel,
            ));
        }

        // For any remaining rows without openrouter_model, set a sensible default
        $this->execute(
            "UPDATE aiprompts SET openrouter_model = 'anthropic/claude-3.5-sonnet' WHERE openrouter_model IS NULL"
        );
    }

    /**
     * Down Method.
     *
     * @return void
     */
    public function down(): void
    {
        $table = $this->table('aiprompts');
        $table->removeColumn('openrouter_model');
        $table->update();
    }
}
