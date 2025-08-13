<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ProductsFixture
 */
class ProductsFixture extends TestFixture
{
    /**
     * Table schema
     *
     * @var array
     */
    protected array $schema = [
        'id' => ['type' => 'uuid', 'null' => false],
        'user_id' => ['type' => 'uuid', 'null' => true],
        'article_id' => ['type' => 'uuid', 'null' => true],
        'title' => ['type' => 'string', 'length' => 255, 'null' => false],
        'slug' => ['type' => 'string', 'length' => 255, 'null' => false],
        'description' => ['type' => 'text', 'null' => true],
        'manufacturer' => ['type' => 'string', 'length' => 255, 'null' => true],
        'model_number' => ['type' => 'string', 'length' => 255, 'null' => true],
        'price' => ['type' => 'decimal', 'precision' => 10, 'scale' => 2, 'null' => true],
        'currency' => ['type' => 'string', 'length' => 3, 'null' => false, 'default' => 'USD'],
        'image' => ['type' => 'string', 'length' => 255, 'null' => true],
        'alt_text' => ['type' => 'string', 'length' => 255, 'null' => true],
        'is_published' => ['type' => 'boolean', 'null' => false, 'default' => false],
        'featured' => ['type' => 'boolean', 'null' => false, 'default' => false],
        'verification_status' => ['type' => 'string', 'length' => 50, 'null' => false, 'default' => 'pending'],
        'reliability_score' => ['type' => 'decimal', 'precision' => 3, 'scale' => 2, 'null' => true],
        'view_count' => ['type' => 'integer', 'null' => false, 'default' => 0],
        'created' => ['type' => 'datetime', 'null' => false],
        'modified' => ['type' => 'datetime', 'null' => false],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ],
    ];

    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                // Approved product
                'id' => '0cab0d79-877c-4e97-81c3-472cefa099a5',
                'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d0f',
                'article_id' => '263a5364-a1bc-401c-9e44-49c23d066a0f',
                'title' => 'Approved Test Product',
                'slug' => 'approved-test-product',
                'description' => 'This is an approved test product for integration testing.',
                'manufacturer' => 'Test Manufacturer Corp',
                'model_number' => 'TEST-MODEL-001',
                'price' => 199.99,
                'currency' => 'USD',
                'image' => 'test-product-image.jpg',
                'alt_text' => 'Test product image',
                'is_published' => 1,
                'featured' => 1,
                'verification_status' => 'approved',
                'reliability_score' => 8.5,
                'view_count' => 150,
                'created' => '2025-08-04 18:34:21',
                'modified' => '2025-08-04 18:34:21',
            ],
            [
                // Pending product for admin review
                'id' => 'pending-fixture-product-001',
                'user_id' => '6509480c-e7e6-4e65-9c38-1423a8d09d02',
                'article_id' => null,
                'title' => 'Pending Review Product Fixture',
                'slug' => 'pending-review-product-fixture',
                'description' => 'This product is awaiting admin review and verification.',
                'manufacturer' => 'Pending Manufacturer',
                'model_number' => 'PEND-001',
                'price' => 89.99,
                'currency' => 'USD',
                'image' => 'pending-product.jpg',
                'alt_text' => 'Pending product image',
                'is_published' => 0,
                'featured' => 0,
                'verification_status' => 'pending',
                'reliability_score' => 0.0,
                'view_count' => 0,
                'created' => '2025-08-05 10:15:30',
                'modified' => '2025-08-05 10:15:30',
            ],
            [
                // Rejected product
                'id' => 'rejected-fixture-product-001',
                'user_id' => '6509480c-e7e6-4e65-9c38-8574a8d09d02',
                'article_id' => null,
                'title' => 'Rejected Product Fixture',
                'slug' => 'rejected-product-fixture',
                'description' => 'This product was rejected during verification.',
                'manufacturer' => 'Rejected Corp',
                'model_number' => 'REJ-001',
                'price' => 25.00,
                'currency' => 'USD',
                'image' => 'rejected-product.jpg',
                'alt_text' => 'Rejected product image',
                'is_published' => 0,
                'featured' => 0,
                'verification_status' => 'rejected',
                'reliability_score' => 2.1,
                'view_count' => 5,
                'created' => '2025-08-03 14:22:15',
                'modified' => '2025-08-04 09:30:45',
            ],
            [
                // Another pending product with different user
                'id' => 'pending-fixture-product-002',
                'user_id' => '6509480c-e7e6-34hy-9c38-8574a8d09d02',
                'article_id' => null,
                'title' => 'Second Pending Product',
                'slug' => 'second-pending-product',
                'description' => 'Another product awaiting verification by admin team.',
                'manufacturer' => 'Secondary Manufacturer',
                'model_number' => 'SEC-002',
                'price' => 129.50,
                'currency' => 'USD',
                'image' => 'second-pending.jpg',
                'alt_text' => 'Second pending product image',
                'is_published' => 0,
                'featured' => 1,
                'verification_status' => 'pending',
                'reliability_score' => 0.0,
                'view_count' => 2,
                'created' => '2025-08-05 16:45:22',
                'modified' => '2025-08-05 16:45:22',
            ],
        ];
        parent::init();
    }
}
