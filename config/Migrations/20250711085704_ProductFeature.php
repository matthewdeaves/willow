<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class ProductFeature extends BaseMigration
{
    /**
     * Up Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-up-method
     * @return void
     */
    public function up(): void
    {
        $this->table('products')
            ->addColumn('feature', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
                'after' => 'comments',
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
        $this->table('products')
            ->removeColumn('feature')
            ->update();
            
    }
}
