<?php
namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

class CreateUserCommand extends Command
{
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


    public function execute(Arguments $args, ConsoleIo $io)
    {
        $usersTable = $this->fetchTable('Users');

        $io->out('Creating new user...');
        if ($this->createUser($args, $io, $usersTable)) {
            $io->success('Created');

            return static::CODE_SUCCESS;
        }

        return static::CODE_ERROR;
    }

    private function createUser(Arguments $args, ConsoleIo $io, $usersTable): bool
    {
        $users = $usersTable->find();

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
