<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\AiService;
use Cake\Queue\Job\Message;
use Interop\Queue\Processor;

/**
 * ImageGallerySeoUpdateJob
 *
 * This job is responsible for updating the SEO metadata of an image gallery using AI.
 * It processes messages from the queue to update various SEO-related fields of a gallery.
 */
class ImageGallerySeoUpdateJob extends AbstractJob
{
    /**
     * Instance of the AI service.
     *
     * @var \App\Service\Api\AiService
     */
    private AiService $aiService;

    /**
     * Constructor to allow dependency injection for testing
     *
     * @param \App\Service\Api\AiService|null $aiService
     */
    public function __construct(?AiService $aiService = null)
    {
        $this->aiService = $aiService ?? new AiService();
    }

    /**
     * Get the human-readable job type name for logging
     *
     * @return string The job type description
     */
    protected static function getJobType(): string
    {
        return 'gallery SEO update';
    }

    /**
     * Executes the job to update gallery SEO metadata.
     *
     * @param \Cake\Queue\Job\Message $message The message containing gallery data
     * @return string|null Returns Processor::ACK on success, Processor::REJECT on failure
     */
    public function execute(Message $message): ?string
    {
        $id = $message->getArgument('id');
        $name = $message->getArgument('name');

        if (!$this->validateArguments($message, ['id', 'name'])) {
            return Processor::REJECT;
        }

        return $this->executeWithErrorHandling($id, function () use ($id, $name) {
            $galleriesTable = $this->getTable('ImageGalleries');
            $gallery = $galleriesTable->get($id, ['contain' => ['Images']]);

            // Prepare gallery context for AI analysis
            $imageCount = is_array($gallery->images) ? count($gallery->images) : 0;
            $galleryContext = sprintf(
                'Gallery: %s. Description: %s. Contains %d images.',
                $name,
                $gallery->description ?: 'No description provided',
                $imageCount,
            );

            $seoResult = $this->aiService->generateGallerySeo(
                (string)$name,
                $galleryContext,
            );

            if ($seoResult) {
                $emptyFields = $galleriesTable->emptySeoFields($gallery);
                array_map(fn($field) => $gallery->{$field} = $seoResult[$field], $emptyFields);

                return $galleriesTable->save($gallery, ['noMessage' => true]);
            }

            return false;
        }, $name);
    }
}
