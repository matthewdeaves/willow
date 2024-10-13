<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Slug Entity
 *
 * @property int $id
 * @property int $article_id
 * @property string $slug
 * @property \Cake\I18n\DateTime $created
 *
 * @property \App\Model\Entity\Article $article
 */
class Slug extends Entity
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
        'article_id' => true,
        'slug' => true,
        'created' => true,
        'article' => true,
    ];
}
