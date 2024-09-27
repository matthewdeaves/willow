<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\ORM\Entity;

/**
 * User Entity
 *
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string|null $email
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\Article[] $articles
 */
class User extends Entity
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
        'username' => true,
        'password' => true,
        'email' => true,
        'created' => true,
        'modified' => true,
        'articles' => true,
        'profile' => true,
        'picture_dir' => true,
        'picture_size' => true,
        'picture_type' => true,
        'is_admin' => false,
        'is_disabled' => false,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var list<string>
     */
    protected array $_hidden = [
        'password',
    ];
    
    /**
     * Sets the password for the user entity.
     *
     * This method hashes the password using the DefaultPasswordHasher if the provided
     * password is not empty. If the password is empty, it returns the original password value.
     * This ensures that passwords are stored securely in the database and prevents
     * overwriting existing passwords with empty values during updates.
     *
     * @param string $password The plain text password to be hashed.
     * @return string|null The hashed password if the input is not empty, or the original password value.
     */
    protected function _setPassword(string $password): ?string
    {
        if (strlen($password) > 0) {
            return (new DefaultPasswordHasher())->hash($password);
        }

        return $this->getOriginal('password');
    }

    /**
     * Checks for errors when attempting to lock an account.
     *
     * This method evaluates the provided data to determine if there are any errors
     * related to locking an account, specifically for admin users. It checks if an
     * admin user is trying to remove their own admin status or disable their own account.
     *
     * @param array $data An associative array containing account data. Expected keys are:
     *  - 'is_admin' (bool): Indicates if the user is an admin.
     *  - 'is_disabled' (bool): Indicates if the account is disabled.
     *
     * @return string|false Returns an error message if an admin user attempts to remove
     * their own admin status or disable their own account. Returns false if no errors are found.
     */
    public function lockAccountError(array $data): ?string
    {
        if (isset($data['is_admin']) && !$data['is_admin'] && $this->is_admin) {
            return __('You cannot remove your own admin status.');
        }
        if (isset($data['is_disabled']) && $data['is_disabled'] && $this->is_admin) {
            return __('You cannot disable your own account.');
        }
        return null;
    }
}
