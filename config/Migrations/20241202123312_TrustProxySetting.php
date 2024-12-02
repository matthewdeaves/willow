<?php
declare(strict_types=1);

use Cake\Utility\Text;
use Migrations\AbstractMigration;

class TrustProxySetting extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        $this->table('settings')
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 1,
                'category' => 'Security',
                'key_name' => 'trustProxy',
                'value' => 0,
                'value_type' => 'bool',
                'value_obscure' => 0,
                'description' => 'Enable this setting if Willow CMS is deployed behind a proxy or load balancer that modifies request headers. When enabled, the application will trust the `X-Forwarded-For` and `X-Real-IP` headers to determine the original client IP address. Use this setting with caution, as it can expose Willow CMS to IP spoofing if untrusted proxies are allowed.',
                'data' => null,
                'column_width' => 2,
            ])
            ->save();
    }
}
