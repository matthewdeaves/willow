<?php
// plugins/ContactManager/src/Model/Table/ContactsTable.php
// plugins/ContactManager/src/Model/Entity/Contact.php:
namespace ContactManager\Model\Entity;

use Cake\ORM\Entity;

class Contact extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array
     */
    protected $_accessible = [
        'first_name' => true,
        'last_name' => true,
        'email' => true,
        'contact_num' => true,
        'address' => true,
        'city' => true,
        'state' => true,
        'country' => true,
    ];
}
