<?php
namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

class LoadDefaultSettingsCommand extends Command
{

    protected array $defaultSettings = [
        ['key_name' => 'host', 'value' => 'rabbitmq', 'group_name' => 'RabbitMQ', 'is_numeric' => 0],
        ['key_name' => 'port', 'value' => '5672', 'group_name' => 'RabbitMQ', 'is_numeric' => 1],
        ['key_name' => 'user', 'value' => 'admin', 'group_name' => 'RabbitMQ', 'is_numeric' => 0],
        ['key_name' => 'password', 'value' => 'password', 'group_name' => 'RabbitMQ', 'is_numeric' => 0],
        ['key_name' => 'tiny', 'value' => '10', 'group_name' => 'ImageSizes', 'is_numeric' => 1],
        ['key_name' => 'small', 'value' => '50', 'group_name' => 'ImageSizes', 'is_numeric' => 1],
        ['key_name' => 'medium', 'value' => '100', 'group_name' => 'ImageSizes', 'is_numeric' => 1],
        ['key_name' => 'large', 'value' => '300', 'group_name' => 'ImageSizes', 'is_numeric' => 1],
        ['key_name' => 'extra-large', 'value' => '400', 'group_name' => 'ImageSizes', 'is_numeric' => 1],
        ['key_name' => 'massive', 'value' => '500', 'group_name' => 'ImageSizes', 'is_numeric' => 1],
    ];

    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser
            ->setDescription('Loads default settings into the database')
            ->addOption('delete-existing', [
                'help' => 'Delete ALL existing settings before loading defaults',
                'boolean' => true,
                'required' => false,
            ])
            ->addOption('update-existing', [
                'help' => 'Update existing settings with default values',
                'boolean' => true,
                'required' => false,
            ])
            ->setEpilog('Example usage: 
                cake load_default_settings
                cake load_default_settings --delete-existing
                cake load_default_settings --update-existing
                cake load_default_settings --delete-existing --update-existing
            ');
        return $parser;
    }


    public function execute(Arguments $args, ConsoleIo $io)
    {
        $settingsTable = $this->fetchTable('Settings');

        if ($args->getOption('delete-existing')) {
            $confirm = $io->askChoice('Are you sure you want to delete all existing settings? This action cannot be undone. (yes/no)', ['yes', 'no'], 'no');
            if ($confirm === 'yes') {
                $settingsTable->deleteAll([]);
                $io->success('All existing settings have been deleted.');
            } else {
                $io->out('Deletion cancelled. Proceeding with loading default settings...');
            }
        }

        $this->loadDefaultSettings($args, $io, $settingsTable);

        $io->success('Default settings loaded successfully');
        return static::CODE_SUCCESS;
    }

    private function loadDefaultSettings(Arguments $args, ConsoleIo $io, $settingsTable): void
    {
        foreach ($this->defaultSettings as $setting) {
            $existingSetting = $settingsTable->find()
                ->where(['key_name' => $setting['key_name'], 'group_name' => $setting['group_name']])
                ->first();

            if ($existingSetting) {
                if ($args->getOption('update-existing')) {
                    $existingSetting = $settingsTable->patchEntity($existingSetting, $setting);
                    if ($settingsTable->save($existingSetting)) {
                        $io->out("Updated setting: {$setting['key_name']} in group {$setting['group_name']}");
                    } else {
                        $io->error("Failed to update setting: {$setting['key_name']} in group {$setting['group_name']}");
                    }
                } else {
                    $io->out("Setting already exists: {$setting['key_name']} in group {$setting['group_name']}");
                }
            } else {
                $entity = $settingsTable->newEmptyEntity();
                $entity->setAccess('key_name', true);
                $entity->setAccess('group_name', true);
                $entity->setAccess('is_numeric', true);
                $entity = $settingsTable->patchEntity($entity, $setting);
                if ($settingsTable->save($entity)) {
                    $io->out("Saved setting: {$setting['key_name']} in group {$setting['group_name']}");
                } else {
                    $io->error("Failed to save setting: {$setting['key_name']} in group {$setting['group_name']}");
                }
            }
        }
    }
}