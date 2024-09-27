<?php
declare(strict_types=1);

namespace App\Log\Engine;

use Cake\Log\Engine\BaseLog;
use Cake\ORM\TableRegistry;
use Stringable;

/**
 * DatabaseLog
 *
 * A logging engine that writes log messages to a database table.
 * This class extends the BaseLog class and implements the log method
 * to save log entries into the 'SystemLogs' table.
 */
class DatabaseLog extends BaseLog
{
    /**
     * Logs a message to the database.
     *
     * This method constructs a log entry with the provided level, message,
     * and context, and saves it to the 'SystemLogs' table. If a 'group_name'
     * is provided in the context, it is used; otherwise, 'general' is used
     * as the default group name.
     *
     * @param mixed $level The log level. Expected to be a string, but not type-hinted for compatibility.
     * @param \Stringable|string $message The log message to be recorded.
     * @param array $context Additional context information for the log entry.
     *                       This can include a 'group_name' key to categorize
     *                       the log entry.
     * @return void
     */
    public function log(mixed $level, string|Stringable $message, array $context = []): void
    {
        $group = $context['group_name'] ?? 'general';

        $data = [
            'level' => $level,
            'message' => $message,
            'context' => json_encode($context),
            'created' => date('Y-m-d H:i:s'),
            'group_name' => $group,
        ];

        $logsTable = TableRegistry::getTableLocator()->get('SystemLogs');
        $logEntry = $logsTable->newEntity($data);
        $logsTable->save($logEntry);
    }
}
