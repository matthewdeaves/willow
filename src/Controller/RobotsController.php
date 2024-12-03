<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\AppController;

class RobotsController extends AppController
{
    /**
     * Display robots.txt content
     *
     * @return \Cake\Http\Response
     */
    public function index(): \Cake\Http\Response
    {
        $filePath = WWW_ROOT . 'robots.txt';
        $robotsContent = file_exists($filePath) ? file_get_contents($filePath) : '';

        return $this->response
            ->withType('text/plain')
            ->withStringBody($robotsContent);
    }
}