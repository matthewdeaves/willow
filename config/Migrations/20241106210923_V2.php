<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class V2 extends AbstractMigration
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

        $this->table('tags')
            ->addColumn('image', 'string', [
                'after' => 'description',
                'collation' => 'utf8mb4_unicode_ci',
                'default' => null,
                'length' => 255,
                'null' => true,
            ])
            ->addColumn('dir', 'string', [
                'after' => 'image',
                'collation' => 'utf8mb4_unicode_ci',
                'default' => null,
                'length' => 255,
                'null' => true,
            ])
            ->addColumn('alt_text', 'string', [
                'after' => 'dir',
                'collation' => 'utf8mb4_unicode_ci',
                'default' => null,
                'length' => 255,
                'null' => true,
            ])
            ->addColumn('keywords', 'string', [
                'after' => 'alt_text',
                'collation' => 'utf8mb4_unicode_ci',
                'default' => null,
                'length' => 255,
                'null' => true,
            ])
            ->addColumn('size', 'integer', [
                'after' => 'keywords',
                'default' => null,
                'length' => null,
                'null' => true,
                'signed' => true,
            ])
            ->addColumn('mime', 'string', [
                'after' => 'size',
                'collation' => 'utf8mb4_unicode_ci',
                'default' => null,
                'length' => 255,
                'null' => true,
            ])
            ->addColumn('name', 'string', [
                'after' => 'mime',
                'collation' => 'utf8mb4_unicode_ci',
                'default' => null,
                'length' => 255,
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

        $this->table('tags')
            ->removeColumn('image')
            ->removeColumn('dir')
            ->removeColumn('alt_text')
            ->removeColumn('keywords')
            ->removeColumn('size')
            ->removeColumn('mime')
            ->removeColumn('name')
            ->update();
    }
}
