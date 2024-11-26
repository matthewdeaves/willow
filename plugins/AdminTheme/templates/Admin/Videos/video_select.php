<!-- templates/Admin/Videos/video_select.php -->
<div class="mb-3">
    <input type="text" 
           id="videoSearch" 
           class="form-control" 
           placeholder="<?= __('Search YouTube videos...') ?>" 
           value="<?= h($searchTerm) ?>">
</div>

<div id="video-gallery" class="row g-3">
    <?php if (empty($videos) && empty($searchTerm)): ?>
        <div class="col-12 text-center">
            <p class="text-muted"><?= __('Search for YouTube videos to embed') ?></p>
        </div>
    <?php elseif (empty($videos)): ?>
        <div class="col-12 text-center">
            <p class="text-muted"><?= __('No videos found matching your search') ?></p>
        </div>
    <?php else: ?>
        <?php foreach ($videos as $video): ?>
            <div class="col-md-4">
                <div class="card h-100">
                    <img src="<?= h($video['thumbnail']) ?>" 
                         class="card-img-top" 
                         alt="<?= h($video['title']) ?>">
                    <div class="card-body">
                        <h6 class="card-title"><?= h($video['title']) ?></h6>
                        <p class="card-text small text-muted">
                            <?= $this->Text->truncate(
                                $video['description'],
                                100,
                                ['exact' => false]
                            ) ?>
                        </p>
                        <button type="button" 
                                class="btn btn-primary btn-sm select-video" 
                                data-video-id="<?= h($video['id']) ?>"
                                data-video-title="<?= h($video['title']) ?>">
                            <?= __('Select Video') ?>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>