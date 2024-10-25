<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class Settingsdata extends AbstractMigration
{
    /**
     * Up Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-up-method
     * @return void
     */
    public function up(): void
    {

        $this->table('settings')
            ->addColumn('data', 'text', [
                'after' => 'value_obscure',
                'collation' => 'utf8mb4_unicode_ci',
                'default' => null,
                'length' => null,
                'null' => true,
            ])
            ->update();
    }

    /**
     * Down Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-down-method
     * @return void
     */
    public function down(): void
    {

        $this->table('settings')
            ->removeColumn('data')
            ->update();
    }
}
