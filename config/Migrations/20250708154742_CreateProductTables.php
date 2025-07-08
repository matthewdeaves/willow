<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateProductTables extends BaseMigration
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
        $this->table('attributes', ['id' => false, 'primary_key' => ['attribute_id']])
            ->addColumn('attribute_id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => false,
            ])
            ->addIndex(
                $this->index('name')
                    ->setName('name')
                    ->setType('unique')
            )
            ->create();

        $this->table('connectors', ['id' => false, 'primary_key' => ['connector_id']])
            ->addColumn('connector_id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => false,
            ])
            ->addIndex(
                $this->index('name')
                    ->setName('name')
                    ->setType('unique')
            )
            ->create();

        $this->table('product_attributes', ['id' => false, 'primary_key' => ['product_attribute_id']])
            ->addColumn('product_attribute_id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('product_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('attribute_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('value', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addIndex(
                $this->index('product_id')
                    ->setName('product_id')
            )
            ->addIndex(
                $this->index('attribute_id')
                    ->setName('attribute_id')
            )
            ->create();

        $this->table('product_connectors', ['id' => false, 'primary_key' => ['product_connector_id']])
            ->addColumn('product_connector_id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('product_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('connector_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('position', 'string', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index('product_id')
                    ->setName('product_id')
            )
            ->addIndex(
                $this->index('connector_id')
                    ->setName('connector_id')
            )
            ->create();

        $this->table('product_links', ['id' => false, 'primary_key' => ['product_link_id']])
            ->addColumn('product_link_id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('product_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('url', 'string', [
                'default' => null,
                'limit' => 2083,
                'null' => false,
            ])
            ->addColumn('last_verification_date', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index('product_id')
                    ->setName('product_id')
            )
            ->create();

        $this->table('product_usages', ['id' => false, 'primary_key' => ['product_usage_id']])
            ->addColumn('product_usage_id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('product_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('usage_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('value', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addIndex(
                $this->index('product_id')
                    ->setName('product_id')
            )
            ->addIndex(
                $this->index('usage_id')
                    ->setName('usage_id')
            )
            ->create();

        $this->table('products', ['id' => false, 'primary_key' => ['product_id']])
            ->addColumn('product_id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('price_usd', 'decimal', [
                'default' => null,
                'null' => true,
                'precision' => 10,
                'scale' => 2,
                'signed' => true,
            ])
            ->addColumn('category_rating', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('comments', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->create();

        $this->table('usages', ['id' => false, 'primary_key' => ['usage_id']])
            ->addColumn('usage_id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => true,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => false,
            ])
            ->addIndex(
                $this->index('name')
                    ->setName('name')
                    ->setType('unique')
            )
            ->create();

        $this->table('product_attributes')
            ->addForeignKey(
                $this->foreignKey('product_id')
                    ->setReferencedTable('products')
                    ->setReferencedColumns('product_id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('product_attributes_ibfk_1')
            )
            ->addForeignKey(
                $this->foreignKey('attribute_id')
                    ->setReferencedTable('attributes')
                    ->setReferencedColumns('attribute_id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('product_attributes_ibfk_2')
            )
            ->update();

        $this->table('product_connectors')
            ->addForeignKey(
                $this->foreignKey('product_id')
                    ->setReferencedTable('products')
                    ->setReferencedColumns('product_id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('product_connectors_ibfk_1')
            )
            ->addForeignKey(
                $this->foreignKey('connector_id')
                    ->setReferencedTable('connectors')
                    ->setReferencedColumns('connector_id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('product_connectors_ibfk_2')
            )
            ->update();

        $this->table('product_links')
            ->addForeignKey(
                $this->foreignKey('product_id')
                    ->setReferencedTable('products')
                    ->setReferencedColumns('product_id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('product_links_ibfk_1')
            )
            ->update();

        $this->table('product_usages')
            ->addForeignKey(
                $this->foreignKey('product_id')
                    ->setReferencedTable('products')
                    ->setReferencedColumns('product_id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('product_usages_ibfk_1')
            )
            ->addForeignKey(
                $this->foreignKey('usage_id')
                    ->setReferencedTable('usages')
                    ->setReferencedColumns('usage_id')
                    ->setOnDelete('CASCADE')
                    ->setOnUpdate('NO_ACTION')
                    ->setName('product_usages_ibfk_2')
            )
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
        $this->table('product_attributes')
            ->dropForeignKey(
                'product_id'
            )
            ->dropForeignKey(
                'attribute_id'
            )->save();

        $this->table('product_connectors')
            ->dropForeignKey(
                'product_id'
            )
            ->dropForeignKey(
                'connector_id'
            )->save();

        $this->table('product_links')
            ->dropForeignKey(
                'product_id'
            )->save();

        $this->table('product_usages')
            ->dropForeignKey(
                'product_id'
            )
            ->dropForeignKey(
                'usage_id'
            )->save();

        $this->table('attributes')->drop()->save();
        $this->table('connectors')->drop()->save();
        $this->table('product_attributes')->drop()->save();
        $this->table('product_connectors')->drop()->save();
        $this->table('product_links')->drop()->save();
        $this->table('product_usages')->drop()->save();
        $this->table('products')->drop()->save();
        $this->table('usages')->drop()->save();
    }
}
