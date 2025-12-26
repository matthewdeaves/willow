<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Image Entity
 *
 * @property string $id
 * @property string|null $name
 * @property string|null $image
 * @property string|null $dir
 * @property string|null $alt_text
 * @property string|null $keywords
 * @property int|null $size
 * @property string|null $mime
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\ImageGallery[] $image_galleries
 */
class Image extends Entity
{
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
        'name' => true,
        'alt_text' => true,
        'keywords' => true,
        'image' => true,
        'dir' => true,
        'size' => true,
        'mime' => true,
        'created' => true,
        'modified' => true,
    ];
}
