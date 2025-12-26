<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Behavior\Translate\TranslateTrait;
use Cake\ORM\Entity;

/**
 * Tag Entity
 *
 * @property string $id
 * @property string $title
 * @property string|null $slug
 * @property string|null $description
 * @property string|null $name
 * @property string|null $parent_id
 * @property int|null $lft
 * @property int|null $rght
 * @property bool|null $main_menu
 * @property string|null $image
 * @property string|null $dir
 * @property string|null $alt_text
 * @property string|null $keywords
 * @property int|null $size
 * @property string|null $mime
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 * @property string|null $facebook_description
 * @property string|null $linkedin_description
 * @property string|null $twitter_description
 * @property string|null $instagram_description
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\Article[] $articles
 * @property \App\Model\Entity\Tag|null $parent_tag
 * @property \App\Model\Entity\Tag[] $child_tags
 */
class Tag extends Entity
{
    use SeoEntityTrait;
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
        'title' => true,
        'slug' => true,
        'description' => true,
        'created' => true,
        'modified' => true,
        'articles' => true,
        // SEO fields (managed by SeoEntityTrait)
        'meta_title' => true,
        'meta_description' => true,
        'meta_keywords' => true,
        'facebook_description' => true,
        'linkedin_description' => true,
        'twitter_description' => true,
        'instagram_description' => true,
        'dir' => true,
        'alt_text' => true,
        'keywords' => true,
        'size' => true,
        'mime' => true,
        'name' => true,
        'image' => true,
        'parent_id' => true,
        'main_menu' => true,
        'lft' => true,
        'rght' => true,
    ];
}
