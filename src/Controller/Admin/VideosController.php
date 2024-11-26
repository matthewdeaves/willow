<?php
// src/Controller/Admin/VideosController.php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Utility\SettingsManager;
use Exception;
use Google_Client;
use Google_Service_YouTube;

class VideosController extends AppController
{
    /**
     * Video selection interface with YouTube API integration
     *
     * @return void
     */
    public function videoSelect(): void
    {
        $this->viewBuilder()->setLayout('ajax');

        $searchTerm = $this->request->getQuery('search');
        $filterByChannel = filter_var($this->request->getQuery('channel_filter'), FILTER_VALIDATE_BOOLEAN);
        $videos = [];

        if ($searchTerm || $filterByChannel) {
            $client = new Google_Client();
            $apiKey = SettingsManager::read('Google.youtubeApiKey', env('YOUTUBE_API_KEY'));
            $client->setDeveloperKey($apiKey);

            $youtube = new Google_Service_YouTube($client);

            try {
                $searchParams = [
                    'q' => $searchTerm,
                    'maxResults' => 12,
                    'type' => 'video',
                ];

                if ($filterByChannel) {
                    $channelId = SettingsManager::read('Google.youtubeChannelId', env('YOUTUBE_CHANNEL_ID'));
                    if ($channelId) {
                        $searchParams['channelId'] = $channelId;
                    }
                }

                $searchResponse = $youtube->search->listSearch('snippet', $searchParams);

                foreach ($searchResponse['items'] as $searchResult) {
                    $videos[] = [
                        'id' => $searchResult['id']['videoId'],
                        'title' => $searchResult['snippet']['title'],
                        'thumbnail' => $searchResult['snippet']['thumbnails']['medium']['url'],
                        'description' => $searchResult['snippet']['description'],
                    ];
                }
            } catch (Exception $e) {
                $this->Flash->error(__('Error fetching videos: {0}', $e->getMessage()));
            }
        }

        $channelId = SettingsManager::read('Google.youtubeChannelId', env('YOUTUBE_CHANNEL_ID'));
        $this->set(compact('videos', 'searchTerm', 'filterByChannel', 'channelId'));
    }
}
