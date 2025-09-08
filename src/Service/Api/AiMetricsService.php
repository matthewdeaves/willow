<?php
declare(strict_types=1);

namespace App\Service\Api;

use App\Model\Table\AiMetricsTable;
use App\Utility\SettingsManager;
use Cake\ORM\TableRegistry;
use Cake\Utility\Text;
use Exception;

/**
 * Centralized service for recording AI API metrics.
 *
 * This service provides a consistent interface for recording metrics across
 * different AI API services (Anthropic, Google Translate, etc.).
 */
class AiMetricsService
{
    /**
     * @var \App\Model\Table\AiMetricsTable
     */
    private AiMetricsTable $aiMetricsTable;

    public function __construct()
    {
        /** @var \App\Model\Table\AiMetricsTable $table */
        $table = TableRegistry::getTableLocator()->get('AiMetrics');
        $this->aiMetricsTable = $table;
    }

    /**
     * Records metrics for an AI API operation.
     *
     * @param string $taskType The type of task (e.g., 'google_translate', 'anthropic_seo')
     * @param int $executionTimeMs The execution time in milliseconds
     * @param bool $success Whether the operation was successful
     * @param string|null $errorMessage Error message if the operation failed
     * @param int|null $tokensUsed Number of tokens used (if applicable)
     * @param float|null $costUsd Cost in USD (if calculable)
     * @param string|null $modelUsed The model used (if applicable)
     * @return bool Whether the metrics were successfully recorded
     */
    public function recordMetrics(
        string $taskType,
        int $executionTimeMs,
        bool $success,
        ?string $errorMessage = null,
        ?int $tokensUsed = null,
        ?float $costUsd = null,
        ?string $modelUsed = null,
    ): bool {
        // Check if metrics tracking is enabled
        if (!SettingsManager::read('AI.enableMetrics', true)) {
            return true; // Silently skip if disabled
        }

        try {
            $metrics = $this->aiMetricsTable->newEntity([
                'id' => Text::uuid(),
                'task_type' => $taskType,
                'execution_time_ms' => $executionTimeMs,
                'tokens_used' => $tokensUsed,
                'cost_usd' => $costUsd,
                'success' => $success,
                'error_message' => $errorMessage,
                'model_used' => $modelUsed,
            ]);

            return (bool)$this->aiMetricsTable->save($metrics);
        } catch (Exception $e) {
            // Log the error but don't throw to avoid breaking the main operation
            error_log('Failed to record AI metrics: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Calculate estimated cost for Google Translate API operations.
     *
     * Based on Google Cloud Translation pricing:
     * - Basic edition: $20 per 1 million characters
     * - Advanced edition: $20 per 1 million characters (same base rate)
     *
     * @param int $characterCount Number of characters translated
     * @return float Estimated cost in USD
     */
    public function calculateGoogleTranslateCost(int $characterCount): float
    {
        // Google Translate pricing: $20 per 1,000,000 characters
        return $characterCount / 1000000 * 20.0;
    }

    /**
     * Count characters in an array of strings for cost calculation.
     *
     * @param array $strings Array of strings to count
     * @return int Total character count
     */
    public function countCharacters(array $strings): int
    {
        $totalChars = 0;
        foreach ($strings as $string) {
            $totalChars += mb_strlen($string ?? '', 'UTF-8');
        }

        return $totalChars;
    }

    /**
     * Get the current daily cost total from metrics.
     *
     * @return float Total cost for today in USD
     */
    public function getDailyCost(): float
    {
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));

        return $this->aiMetricsTable->getCostsByDateRange($today . ' 00:00:00', $tomorrow . ' 00:00:00');
    }

    /**
     * Check if daily cost limit has been reached.
     *
     * @return bool True if limit has been reached
     */
    public function isDailyCostLimitReached(): bool
    {
        $dailyLimit = (float)SettingsManager::read('AI.dailyCostLimit', 2.50);

        // If limit is 0, consider it unlimited
        if ($dailyLimit <= 0) {
            return false;
        }

        return $this->getDailyCost() >= $dailyLimit;
    }

    /**
     * Send cost alert if enabled and threshold is reached.
     *
     * @param float $currentCost Current daily cost
     * @param float $newCost Cost of new operation
     * @return void
     */
    public function checkCostAlert(float $currentCost, float $newCost): void
    {
        if (!SettingsManager::read('AI.enableCostAlerts', true)) {
            return;
        }

        $dailyLimit = (float)SettingsManager::read('AI.dailyCostLimit', 2.50);
        $alertThreshold = $dailyLimit * 0.8; // Alert at 80% of limit

        if ($currentCost < $alertThreshold && ($currentCost + $newCost) >= $alertThreshold) {
            // TODO: Implement email alert functionality
            error_log('AI Cost Alert: Daily spending has reached $' . ($currentCost + $newCost) . ' (80% of daily limit)');
        }
    }
}
