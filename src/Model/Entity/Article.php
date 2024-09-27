<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Article Entity
 *
 * @property string $id
 * @property string $user_id
 * @property string $title
 * @property string|null $body
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Tag[] $tags
 */
class Article extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'user_id' => true,
        'title' => true,
        'slug' => true,
        'body' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
        'tags' => true,
        'meta_title' => true,
        'meta_description' => true,
        'meta_keywords' => true,
        'focus_keyword' => true,
        'featured_image_alt' => true,
        'canonical_url' => true,
        'schema_markup' => true,
        'social_title' => true,
        'social_description' => true,
        'social_image' => true,
        'readability_score' => true,
        'word_count' => true,
        'is_page' => true,
        'parent_id' => true,
        'lft' => true,
        'rght' => true,
        'published' => true,
        'is_published' => true,
    ];
}
