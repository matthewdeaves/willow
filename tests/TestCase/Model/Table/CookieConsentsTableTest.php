<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Entity\CookieConsent;
use App\Model\Table\CookieConsentsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\CookieConsentsTable Test Case
 */
class CookieConsentsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\CookieConsentsTable
     */
    protected $CookieConsents;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.CookieConsents',
        'app.Users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('CookieConsents') ? [] : ['className' => CookieConsentsTable::class];
        $this->CookieConsents = $this->getTableLocator()->get('CookieConsents', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->CookieConsents);
        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        $data = [
            'user_id' => 'not-an-integer',
            'session_id' => str_repeat('a', 256), // Exceeds maxLength
            'analytics_consent' => 'not-a-boolean',
            'functional_consent' => null,
            'marketing_consent' => null,
            'essential_consent' => null,
            'ip_address' => '',
            'user_agent' => '',
        ];

        $cookieConsent = $this->CookieConsents->newEntity($data);
        
        $this->assertTrue($cookieConsent->hasErrors('user_id'), 'user_id should require integer');
        $this->assertTrue($cookieConsent->hasErrors('session_id'), 'session_id should respect maxLength');
        $this->assertTrue($cookieConsent->hasErrors('analytics_consent'), 'analytics_consent should be boolean');
        $this->assertTrue($cookieConsent->hasErrors('functional_consent'), 'functional_consent should not be empty');
        $this->assertTrue($cookieConsent->hasErrors('marketing_consent'), 'marketing_consent should not be empty');
        $this->assertTrue($cookieConsent->hasErrors('essential_consent'), 'essential_consent should not be empty');
        $this->assertTrue($cookieConsent->hasErrors('ip_address'), 'ip_address should not be empty');
        $this->assertTrue($cookieConsent->hasErrors('user_agent'), 'user_agent should not be empty');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules(): void
    {
        $data = [
            'user_id' => 999999, // Non-existent user ID
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test User Agent',
            'analytics_consent' => true,
            'functional_consent' => true,
            'marketing_consent' => true,
            'essential_consent' => true,
        ];

        $cookieConsent = $this->CookieConsents->newEntity($data);
        $result = $this->CookieConsents->save($cookieConsent);
        
        $this->assertFalse($result);
        $this->assertTrue($cookieConsent->hasErrors());
    }

    /**
     * Test hasAnalyticsConsent method
     *
     * @return void
     */
    public function testHasAnalyticsConsent(): void
    {
        $cookieConsent = $this->CookieConsents->newEntity([
            'analytics_consent' => true,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test User Agent',
        ]);
        $this->assertTrue($this->CookieConsents->hasAnalyticsConsent($cookieConsent));

        $cookieConsent->set('analytics_consent', false);
        $this->assertFalse($this->CookieConsents->hasAnalyticsConsent($cookieConsent));

        $this->assertFalse($this->CookieConsents->hasAnalyticsConsent(null));
    }

    /**
     * Test hasFunctionalConsent method
     *
     * @return void
     */
    public function testHasFunctionalConsent(): void
    {
        $cookieConsent = $this->CookieConsents->newEntity([
            'functional_consent' => true,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test User Agent',
        ]);
        $this->assertTrue($this->CookieConsents->hasFunctionalConsent($cookieConsent));

        $cookieConsent->set('functional_consent', false);
        $this->assertFalse($this->CookieConsents->hasFunctionalConsent($cookieConsent));

        $this->assertFalse($this->CookieConsents->hasFunctionalConsent(null));
    }

    /**
     * Test hasMarketingConsent method
     *
     * @return void
     */
    public function testHasMarketingConsent(): void
    {
        $cookieConsent = $this->CookieConsents->newEntity([
            'marketing_consent' => true,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test User Agent',
        ]);
        $this->assertTrue($this->CookieConsents->hasMarketingConsent($cookieConsent));

        $cookieConsent->set('marketing_consent', false);
        $this->assertFalse($this->CookieConsents->hasMarketingConsent($cookieConsent));

        $this->assertFalse($this->CookieConsents->hasMarketingConsent(null));
    }

    /**
     * Test getLatestConsent method
     *
     * @return void
     */
    public function testGetLatestConsent(): void
    {
        // First create a test record
        $data = [
            'session_id' => 'test-session-id',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test User Agent',
            'analytics_consent' => true,
            'functional_consent' => true,
            'marketing_consent' => true,
            'essential_consent' => true,
        ];
        
        $consent = $this->CookieConsents->newEntity($data);
        $this->CookieConsents->save($consent);

        // Test with session ID
        $result = $this->CookieConsents->getLatestConsent('test-session-id', null);
        $this->assertInstanceOf(CookieConsent::class, $result);

        // Test with no parameters
        $result = $this->CookieConsents->getLatestConsent(null, null);
        $this->assertNull($result);

        // Test with non-existent session ID
        $result = $this->CookieConsents->getLatestConsent('non-existent-session', null);
        $this->assertNull($result);
    }

    /**
     * Test hasConsentRecord method
     *
     * @return void
     */
    public function testHasConsentRecord(): void
    {
        // First create a test record
        $data = [
            'session_id' => 'test-session-id',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test User Agent',
            'analytics_consent' => true,
            'functional_consent' => true,
            'marketing_consent' => true,
            'essential_consent' => true,
        ];
        
        $consent = $this->CookieConsents->newEntity($data);
        $this->CookieConsents->save($consent);

        $this->assertTrue(
            $this->CookieConsents->hasConsentRecord('test-session-id', null),
            'Should find consent record by session ID'
        );

        $this->assertFalse(
            $this->CookieConsents->hasConsentRecord('non-existent-session', null),
            'Should not find non-existent consent record'
        );

        $this->assertFalse(
            $this->CookieConsents->hasConsentRecord(null, null),
            'Should return false when no identifiers provided'
        );
    }

    /**
     * Test getAllowedConsentTypes method
     *
     * @return void
     */
    public function testGetAllowedConsentTypes(): void
    {
        $cookieConsent = $this->CookieConsents->newEntity([
            'analytics_consent' => true,
            'functional_consent' => true,
            'marketing_consent' => true,
            'essential_consent' => true,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Test User Agent',
        ]);

        $allowed = $this->CookieConsents->getAllowedConsentTypes($cookieConsent);
        $this->assertContains('essential', $allowed);
        $this->assertContains('analytics', $allowed);
        $this->assertContains('functional', $allowed);
        $this->assertContains('marketing', $allowed);
        $this->assertEquals(4, count($allowed));

        // Test with null consent
        $allowed = $this->CookieConsents->getAllowedConsentTypes(null);
        $this->assertContains('essential', $allowed);
        $this->assertEquals(1, count($allowed));
    }
}