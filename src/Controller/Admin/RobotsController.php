<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;

class RobotsController extends AppController
{
    /**
     * Default content for robots.txt with language placeholder
     */
    private string $defaultContent = <<<EOT
User-agent: *
Allow: /{LANG}/
Allow: /{LANG}/articles/*
Allow: /{LANG}/pages/*
Allow: /{LANG}/sitemap.xml

Disallow: /admin/
Disallow: /{LANG}/users/login
Disallow: /{LANG}/users/register
Disallow: /{LANG}/users/forgot-password
Disallow: /{LANG}/users/reset-password/*
Disallow: /{LANG}/users/confirm-email/*
Disallow: /{LANG}/users/edit/*
Disallow: /{LANG}/cookie-consents/edit

# Prevent indexing of non-existent listing pages
Disallow: /{LANG}/articles$
Disallow: /{LANG}/pages$

Sitemap: /{LANG}/sitemap.xml
EOT;

    /**
     * Edit the robots.txt content
     *
     * @return \Cake\Http\Response|null|void
     */
    public function edit(): ?Response
    {
        $filePath = WWW_ROOT . 'robots.txt.template';

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
    public function reset(): ?Response
    {
        $filePath = WWW_ROOT . 'robots.txt.template';

        if (file_put_contents($filePath, $this->defaultContent) !== false) {
            $this->Flash->success(__('The robots.txt file has been reset to default.'));
        } else {
            $this->Flash->error(__('Could not reset the robots.txt file. Please try again.'));
        }

        return $this->redirect(['action' => 'edit']);
    }
}
