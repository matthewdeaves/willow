<?php
/**
 * @var \App\View\AppView $this
 * @var string $videoId
 * @var string $width
 * @var string $height
 * @var string $title
 * @var string $thumbnailUrl
 */
?>
<div class="youtube-embed" 
     data-video-id="<?= h($videoId) ?>"
     data-title="<?= h($title) ?>"
     style="width: <?= h($width) ?>px; max-width: 100%;">
    <div class="youtube-placeholder" style="position: relative;">
        <img src="<?= h($thumbnailUrl) ?>" 
             alt="<?= h($title) ?>"
             class="img-fluid youtube-thumbnail"
             style="width: <?= h($width) ?>px; height: <?= h($height) ?>px; object-fit: cover;">
        <div class="youtube-consent-overlay" 
             style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; 
                    background: rgba(0,0,0,0.7); display: flex; 
                    flex-direction: column; align-items: center; 
                    justify-content: center; color: white; text-align: center; 
                    padding: 20px;">
            <h4><?= h($title) ?></h4>
            <p class="mb-3"><?= __('This content is hosted by YouTube. By showing the external content you accept the privacy policy of YouTube.') ?></p>
            <button class="btn btn-primary youtube-consent-btn" 
                    data-video-id="<?= h($videoId) ?>"
                    onclick="loadYouTubeVideo(this)">
                <?= __('Load video') ?>
            </button>
        </div>
    </div>
</div>