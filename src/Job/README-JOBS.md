Queue-Based Processing

Heavy operations are processed asynchronously using CakePHP Queue:

// Queue an AI job
$this->queue->push(ArticleSeoUpdateJob::class, [
    'article_id' => $article->id,
    'locale' => 'en'
]);

// Background job example
class ArticleSeoUpdateJob implements JobInterface {
    public function execute(array $data): void {
        $seoGenerator = new SeoContentGenerator();
        $seoGenerator->generateSeoContent($data['article_id']);
    }
}
Key background jobs:

ProcessImageJob - Image resizing and optimization
ImageAnalysisJob - AI-powered image analysis and alt text generation
ArticleSeoUpdateJob - AI-generated SEO content and meta descriptions
ArticleTagUpdateJob - Automatic tag generation based on content analysis
ArticleSummaryUpdateJob - AI-powered content summarization
TranslateArticleJob - Multi-language content translation
TranslateI18nJob - Interface translation for all supported languages
TranslateImageGalleryJob - Gallery metadata translation
TranslateTagJob - Tag translation across languages
CommentAnalysisJob - AI-powered comment moderation and spam detection
GenerateGalleryPreviewJob - Gallery preview image generation
SendEmailJob - Asynchronous email delivery