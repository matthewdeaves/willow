<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\Product;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Entity\Product Test Case
 */
class ProductTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Entity\Product
     */
    protected $Product;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->Product = new Product();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Product);

        parent::tearDown();
    }
}
