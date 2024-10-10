<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Setting Entity
 *
 * @property string $id
 * @property string $category
 * @property string|null $subcategory
 * @property string $key_name
 * @property string|null $value
 * @property bool|null $is_numeric
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 */
class Setting extends Entity
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
        'category' => true,
        'subcategory' => true,
        'key_name' => true,
        'value' => true,
        'is_numeric' => true,
        'created' => true,
        'modified' => true,
    ];
}
