<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\Table;

/**
 * Command for creating a user in the database.
 */
class CreateUserCommand extends Command
{
    /**
     * Builds the option parser for the command.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The console option parser.
     * @return \Cake\Console\ConsoleOptionParser The configured console option parser.
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
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
                'help' => 'email for the user',
                'default' => null,
                'required' => true,
            ])
            ->addOption('is_admin', [
                'short' => 'a',
                'help' => 'Username for the user',
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

        $io->out('Creating new user...');
        if ($this->createUser($args, $io, $usersTable)) {
            $io->success('Created');

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

        $user = $usersTable->newEmptyEntity();
        $user->setAccess('is_admin', true);
        $user = $usersTable->patchEntity($user, $data);

        if ($usersTable->save($user)) {
            $io->out("Created User: {$user['username']}");

            return true;
        } else {
            $io->error("Failed to create User: {$user['username']}");

            return false;
        }
    }
}
