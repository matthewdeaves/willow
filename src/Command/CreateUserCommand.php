<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Log\LogTrait;
use Cake\ORM\Table;

/**
 * Command for creating a user in the database.
 */
class CreateUserCommand extends Command
{
    use LogTrait;

    /**
     * Builds the option parser for the command.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The console option parser.
     * @return \Cake\Console\ConsoleOptionParser The configured console option parser.
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $parser
            ->setDescription('Creates a user in the database')
            ->addOption('username', [
                'short' => 'u',
                'help' => 'Username for the user',
                'default' => null,
                'required' => true,
            ])
            ->addOption('password', [
                'short' => 'p',
                'help' => 'Password for the user',
                'default' => null,
                'required' => true,
            ])
            ->addOption('email', [
                'short' => 'e',
                'help' => 'Email for the user',
                'default' => null,
                'required' => true,
            ])
            ->addOption('is_admin', [
                'short' => 'a',
                'help' => 'Is the user an admin?',
                'default' => null,
                'required' => true,
            ]);

        return $parser;
    }

    /**
     * Executes the command to create a user.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console I/O.
     * @return int The exit code.
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $usersTable = $this->fetchTable('Users');

        if ($this->createUser($args, $io, $usersTable)) {
            return static::CODE_SUCCESS;
        }

        return static::CODE_ERROR;
    }

    /**
     * Creates a user with the provided arguments.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console I/O.
     * @param \Cake\ORM\Table $usersTable The users table.
     * @return bool True if the user was created successfully, false otherwise.
     */
    private function createUser(Arguments $args, ConsoleIo $io, Table $usersTable): bool
    {
        $data = [
            'username' => $args->getOption('username'),
            'password' => $args->getOption('password'),
            'confirm_password' => $args->getOption('password'),
            'email' => $args->getOption('email'),
            'is_admin' => (bool)$args->getOption('is_admin'),
        ];

        // don't log passwords
        $logData = $data;
        unset($logData['password'], $logData['confirm_password']);

        $this->log(
            __('Attempting to create user with data: {0}', [json_encode($logData)]),
            'info',
            ['group_name' => 'user_creation']
        );

        $user = $usersTable->newEmptyEntity();
        $user->setAccess('is_admin', true);
        $user = $usersTable->patchEntity($user, $data);

        if ($usersTable->save($user)) {
            $this->log(
                __(
                    'User created successfully: {0}',
                    [$user['username']]
                ),
                'info',
                ['group_name' => 'user_creation']
            );

            return true;
        } else {
            $this->log(__('Failed to create user: {0}. Errors: {1}', [
                $user['username'],
                json_encode($user->getErrors()),
            ]), 'error', ['group_name' => 'user_creation']);

            return false;
        }
    }
}
