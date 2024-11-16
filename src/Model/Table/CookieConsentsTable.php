<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\CookieConsent;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * CookieConsents Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @method \App\Model\Entity\CookieConsent newEmptyEntity()
 * @method \App\Model\Entity\CookieConsent newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\CookieConsent> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CookieConsent get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\CookieConsent findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\CookieConsent patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\CookieConsent> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\CookieConsent|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\CookieConsent saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\CookieConsent>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CookieConsent>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\CookieConsent>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CookieConsent> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\CookieConsent>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CookieConsent>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\CookieConsent>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CookieConsent> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CookieConsentsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('cookie_consents');
        $this->setDisplayField('ip_address');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('user_id')
            ->allowEmptyString('user_id');

        $validator
            ->scalar('session_id')
            ->maxLength('session_id', 255)
            ->allowEmptyString('session_id');

        $validator
            ->boolean('analytics_consent')
            ->notEmptyString('analytics_consent');

        $validator
            ->boolean('functional_consent')
            ->notEmptyString('functional_consent');

        $validator
            ->boolean('marketing_consent')
            ->notEmptyString('marketing_consent');

        $validator
            ->boolean('essential_consent')
            ->notEmptyString('essential_consent');

        $validator
            ->scalar('ip_address')
            ->maxLength('ip_address', 45)
            ->requirePresence('ip_address', 'create')
            ->notEmptyString('ip_address');

        $validator
            ->scalar('user_agent')
            ->maxLength('user_agent', 255)
            ->requirePresence('user_agent', 'create')
            ->notEmptyString('user_agent');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }

    /**
     * Check if analytics cookies are allowed based on consent.
     *
     * @param \App\Model\Entity\CookieConsent|null $cookieConsent The cookie consent entity
     * @return bool True if analytics cookies are allowed
     */
    public function hasAnalyticsConsent(?CookieConsent $cookieConsent): bool
    {
        if ($cookieConsent === null) {
            return false;
        }

        return (bool)$cookieConsent->analytics_consent;
    }

    /**
     * Check if functional cookies are allowed based on consent.
     *
     * @param \App\Model\Entity\CookieConsent|null $cookieConsent The cookie consent entity
     * @return bool True if functional cookies are allowed
     */
    public function hasFunctionalConsent(?CookieConsent $cookieConsent): bool
    {
        if ($cookieConsent === null) {
            return false;
        }

        return (bool)$cookieConsent->functional_consent;
    }

    /**
     * Check if marketing cookies are allowed based on consent.
     *
     * @param \App\Model\Entity\CookieConsent|null $cookieConsent The cookie consent entity
     * @return bool True if marketing cookies are allowed
     */
    public function hasMarketingConsent(?CookieConsent $cookieConsent): bool
    {
        if ($cookieConsent === null) {
            return false;
        }

        return (bool)$cookieConsent->marketing_consent;
    }

    /**
     * Get the latest consent record for a session or user.
     *
     * @param string|null $sessionId The session ID
     * @param string|null $userId The user ID
     * @return \App\Model\Entity\CookieConsent|null
     */
    public function getLatestConsent(?string $sessionId = null, ?string $userId = null): ?CookieConsent
    {
        $query = $this->find();

        if ($userId !== null) {
            $query->where(['user_id' => $userId]);
        } elseif ($sessionId !== null) {
            $query->where(['session_id' => $sessionId]);
        } else {
            return null;
        }

        return $query->order(['created' => 'DESC'])->first();
    }

    /**
     * Check if any consent record exists for a session or user.
     *
     * @param string|null $sessionId The session ID
     * @param string|null $userId The user ID
     * @return bool
     */
    public function hasConsentRecord(?string $sessionId = null, ?string $userId = null): bool
    {
        return $this->getLatestConsent($sessionId, $userId) !== null;
    }

    /**
     * Get all consent types that are currently allowed.
     *
     * @param \App\Model\Entity\CookieConsent|null $cookieConsent The cookie consent entity
     * @return array<string> Array of allowed consent types
     */
    public function getAllowedConsentTypes(?CookieConsent $cookieConsent): array
    {
        $allowed = ['essential'];

        if ($cookieConsent === null) {
            return $allowed;
        }

        if ($this->hasAnalyticsConsent($cookieConsent)) {
            $allowed[] = 'analytics';
        }

        if ($this->hasFunctionalConsent($cookieConsent)) {
            $allowed[] = 'functional';
        }

        if ($this->hasMarketingConsent($cookieConsent)) {
            $allowed[] = 'marketing';
        }

        return $allowed;
    }
}
