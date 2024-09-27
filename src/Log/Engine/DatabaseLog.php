<?php
namespace App\Log\Engine;

use Cake\Log\Engine\BaseLog;
use Cake\ORM\TableRegistry;

class DatabaseLog extends BaseLog
{
    public function log($level, $message, array $context = []): void
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
