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
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\Article[] $articles
 */
class Tag extends Entity
{
    use TranslateTrait;

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
    ];

    /**
     * Retrieves the URL for an image by removing the 'webroot/' prefix from the directory path.
     *
     * This method constructs the image URL by concatenating the directory and image name,
     * and then removes the 'webroot/' part from the path to generate a relative URL.
     *
     * @return string The relative URL of the image.
     */
    protected function _getImageUrl(): string
    {
        return str_replace('webroot/', '', $this->dir . $this->image);
    }
}
