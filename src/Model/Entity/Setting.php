<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Setting Entity
 *
 * @property int $id
 * @property string $key_name
 * @property string $value
 * @property string|null $group_name
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
        'key_name' => false,
        'value' => true,
        'group_name' => false,
        'is_numeric' => false,
        'created' => true,
        'modified' => true,
    ];
}
