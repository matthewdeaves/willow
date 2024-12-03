<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Http\Response;

class RobotsController extends AppController
{
    /**
     * Default content for robots.txt
     */
    private string $defaultContent = <<<EOT
User-agent: *
Allow: /
Allow: /articles/
Allow: /pages/
Allow: /tags/
Allow: /sitemap.xml

Disallow: /admin/
Disallow: /users/login
Disallow: /users/register
Disallow: /users/forgot-password
Disallow: /users/reset-password/
Disallow: /users/confirm-email/
Disallow: /users/edit/
Disallow: /cookie-consents/edit

Sitemap: /sitemap.xml
EOT;

    /**
     * Edit the robots.txt content
     *
     * @return \Cake\Http\Response|null|void
     */
    public function edit()
    {
        $filePath = WWW_ROOT . 'robots.txt';

        // Check if the file exists, if not create it with default content
        if (!file_exists($filePath)) {
            file_put_contents($filePath, $this->defaultContent);
        }

        if ($this->request->is(['post', 'put'])) {
            $robotsContent = $this->request->getData('robotsContent');
            if (file_put_contents($filePath, $robotsContent) !== false) {
                $this->Flash->success(__('The robots.txt file has been updated.'));
                return $this->redirect(['action' => 'edit']);
            }
            $this->Flash->error(__('Could not save the robots.txt file. Please try again.'));
        }

        $robotsContent = file_get_contents($filePath);
        $this->set(compact('robotsContent'));
    }

    /**
     * Reset the robots.txt content to default
     *
     * @return \Cake\Http\Response|null
     */
    public function reset()
    {
        $filePath = WWW_ROOT . 'robots.txt';

        if (file_put_contents($filePath, $this->defaultContent) !== false) {
            $this->Flash->success(__('The robots.txt file has been reset to default.'));
        } else {
            $this->Flash->error(__('Could not reset the robots.txt file. Please try again.'));
        }

        return $this->redirect(['action' => 'edit']);
    }
}