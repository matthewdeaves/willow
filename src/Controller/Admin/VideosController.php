<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Google_Client;
use Google_Service_YouTube;
use App\Utility\SettingsManager;

class VideosController extends AppController
{
    /**
     * Video selection interface with YouTube API integration
     *
     * @return void
     */
    public function videoSelect()
    {
        $this->viewBuilder()->setLayout('ajax');
        
        $searchTerm = $this->request->getQuery('search');
        $videos = [];
        
        if ($searchTerm) {
            $client = new Google_Client();
            $apiKey = SettingsManager::read('Google.youtubeApiKey', env('YOUTUBE_API_KEY'));
            $client->setDeveloperKey($apiKey);
            
            $youtube = new Google_Service_YouTube($client);
            
            try {
                $searchResponse = $youtube->search->listSearch('snippet', [
                    'q' => $searchTerm,
                    'maxResults' => 12,
                    'type' => 'video'
                ]);
                
                foreach ($searchResponse['items'] as $searchResult) {
                    $videos[] = [
                        'id' => $searchResult['id']['videoId'],
                        'title' => $searchResult['snippet']['title'],
                        'thumbnail' => $searchResult['snippet']['thumbnails']['medium']['url'],
                        'description' => $searchResult['snippet']['description']
                    ];
                }
            } catch (\Exception $e) {
                $this->Flash->error(__('Error fetching videos: {0}', $e->getMessage()));
            }
        }
        
        $this->set(compact('videos', 'searchTerm'));
    }
}
