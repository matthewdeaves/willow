<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

class RobotsController extends AppController
{
    /**
     * Display robots.txt content with language-specific paths
     *
     * @return \Cake\Http\Response
     */
    public function index(): Response
    {
        $filePath = WWW_ROOT . 'robots.txt.template';

        if (!file_exists($filePath)) {
            return $this->response
                ->withType('text/plain')
                ->withStringBody('User-agent: *' . PHP_EOL . 'Disallow: /');
        }

        $robotsContent = file_get_contents($filePath);

        // Get language from URL or default to 'en'
        $lang = $this->request->getParam('lang');

        // If no language parameter (root robots.txt request), default to 'en'
        if (empty($lang)) {
            $lang = 'en';
        }

        // Replace language placeholder with actual language code
        $robotsContent = str_replace('{LANG}', $lang, $robotsContent);

        return $this->response
            ->withType('text/plain')
            ->withStringBody($robotsContent)
            ->withCache('-1 minute', '+1 day'); // Add cache headers
    }
}
