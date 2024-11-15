<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Behavior\Translate\TranslateTrait;
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
 * @property string|null $slug
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 * @property string|null $facebook_description
 * @property string|null $linkedin_description
 * @property string|null $twitter_description
 * @property string|null $instagram_description
 * @property int|null $word_count
 * @property string $kind
 * @property string|null $parent_id
 * @property int|null $lft
 * @property int|null $rght
 * @property bool $published
 * @property bool $is_published
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Tag[] $tags
 */
class Article extends Entity
{
    use TranslateTrait;
    use ImageUrlTrait;

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
        'lead' => true,
        'featured' => true,
        'slug' => true,
        'body' => true,
        'summary' => true,
        'created' => true,
        'modified' => true,
        'meta_title' => true,
        'meta_description' => true,
        'meta_keywords' => true,
        'facebook_description' => true,
        'linkedin_description' => true,
        'twitter_description' => true,
        'instagram_description' => true,
        'word_count' => true,
        'kind' => true,
        'parent_id' => true,
        'lft' => true,
        'rght' => true,
        'published' => true,
        'is_published' => true,
        'tags' => true,
        'images' => true,
        'image' => true,
        'dir' => true,
        'name' => true,
        'size' => true,
        'mime' => true,
    ];
}
