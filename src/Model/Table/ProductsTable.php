<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Behavior\ImageValidationTrait;
use Cake\Log\LogTrait;
use Cake\ORM\Behavior\Translate\TranslateTrait;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class ProductsTable extends Table
{
    use ImageValidationTrait;
    use LogTrait;
    use TranslateTrait;

    /**
     * Initialize hook
     *
     * @param array $config The configuration settings provided to this table
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('products');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Slug', [
            'slug' => 'slug',
            'displayField' => 'title',
        ]);

        // Core relationships
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);

        $this->belongsTo('Articles', [
            'foreignKey' => 'article_id',
            'joinType' => 'LEFT',
            'propertyName' => 'article',
        ]);

        // Many-to-many with Tags (unified tagging system)
        $this->belongsToMany('Tags', [
            'foreignKey' => 'product_id',
            'targetForeignKey' => 'tag_id',
            'joinTable' => 'products_tags',
        ]);
    }

    /**
     * Default validation rules
     *
     * @param \Cake\Validation\Validator $validator Validator instance
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->uuid('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('title')
            ->maxLength('title', 255)
            ->requirePresence('title', 'create')
            ->notEmptyString('title');

        // $validator
        //     ->scalar('slug')
        //     ->maxLength('slug', 191)
        //     ->requirePresence('slug', 'create')
        //     ->notEmptyString('slug')
        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        $validator
            ->scalar('manufacturer')
            ->maxLength('manufacturer', 255)
            ->allowEmptyString('manufacturer');

        $validator
            ->scalar('model_number')
            ->maxLength('model_number', 255)
            ->allowEmptyString('model_number');

        $validator
            ->decimal('price')
            ->allowEmptyString('price');

        $validator
            ->boolean('is_published')
            ->notEmptyString('is_published');

        $validator
            ->boolean('featured')
            ->notEmptyString('featured');

        return $validator;
    }

    /**
     * Get published products with optional filtering
     */
    public function getPublishedProducts(array $options = []): Query
    {
        $query = $this->find()
            ->where(['Products.is_published' => true])
            ->contain(['Users', 'Tags', 'Articles'])
            ->orderBy(['Products.created' => 'DESC']);

        // Apply filters
        if (!empty($options['tag'])) {
            $query->matching('Tags', function ($q) use ($options) {
                return $q->where(['Tags.slug' => $options['tag']]);
            });
        }

        if (!empty($options['manufacturer'])) {
            $query->where(['Products.manufacturer LIKE' => '%' . $options['manufacturer'] . '%']);
        }

        if (!empty($options['featured'])) {
            $query->where(['Products.featured' => true]);
        }

        return $query;
    }

    /**
     * Get products by verification status
     */
    public function getProductsByStatus(string $status): Query
    {
        return $this->find()
            ->where(['verification_status' => $status])
            ->contain(['Users', 'Tags'])
            ->orderBy(['created' => 'ASC']);
    }

    /**
     * Search products across title, description, manufacturer
     */
    public function searchProducts(string $term): Query
    {
        return $this->find()
            ->where([
                'OR' => [
                    'Products.title LIKE' => "%{$term}%",
                    'Products.description LIKE' => "%{$term}%",
                    'Products.manufacturer LIKE' => "%{$term}%",
                    'Products.model_number LIKE' => "%{$term}%",
                ],
            ])
            ->contain(['Tags', 'Users'])
            ->where(['Products.is_published' => true]);
    }

    /**
     * Get products with same tags (for related products)
     */
    public function getRelatedProducts(string $productId, int $limit = 5): array
    {
        $product = $this->get($productId, ['contain' => ['Tags']]);

        if (empty($product->tags)) {
            return [];
        }

        $tagIds = array_map(fn($tag) => $tag->id, $product->tags);

        return $this->find()
            ->matching('Tags', function ($q) use ($tagIds) {
                return $q->where(['Tags.id IN' => $tagIds]);
            })
            ->where([
                'Products.id !=' => $productId,
                'Products.is_published' => true,
            ])
            ->limit($limit)
            ->toArray();
    }

    /**
     * Increment view count
     */
    public function incrementViewCount(string $productId): bool
    {
        return $this->updateAll(
            ['view_count = view_count + 1'],
            ['id' => $productId],
        );
    }
}
