<div id="video-gallery" class="video-picker">
    <!-- Search Form -->
    <?php if (!$this->request->getQuery('gallery_only')): ?>
    <div class="row mb-3">
        <div class="col-md-8">
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fab fa-youtube"></i>
                </span>
                <input type="text" 
                       class="form-control" 
                       id="videoSearch" 
                       placeholder="<?= __('Search YouTube videos...') ?>"
                       value="<?= h($searchTerm ?? '') ?>"
                       autocomplete="off">
            </div>
        </div>
        <?php if (($channelId ?? 'your-api-key-here') !== 'your-api-key-here'): ?>
        <div class="col-md-4">
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" 
                       type="checkbox" 
                       id="channelFilter" 
                       <?= ($filterByChannel ?? false) ? 'checked' : '' ?>>
                <label class="form-check-label" for="channelFilter">
                    <?= __('Show only channel videos') ?>
                </label>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <!-- Video Gallery Content -->

    <div class="video-results row g-3">
        <?php if (!empty($videos ?? [])): ?>
            <?php foreach ($videos as $video): ?>
                <div class="col-md-4">
                    <div class="card h-100 video-picker-card">
                        <div class="video-thumbnail-wrapper">
                            <img src="<?= h($video['thumbnail']) ?>" 
                                 class="card-img-top" 
                                 alt="<?= h($video['title']) ?>">
                            <div class="video-play-overlay">
                                <i class="fab fa-youtube fa-2x text-white"></i>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="card-title"><?= h($video['title']) ?></h6>
                            <p class="card-text small text-muted">
                                <?= $this->Text->truncate(
                                    $video['description'],
                                    100,
                                    ['exact' => false]
                                ) ?>
                            </p>
                        </div>
                        <div class="card-footer bg-transparent border-top-0 pt-0">
                            <button type="button" 
                                    class="btn btn-primary btn-sm w-100 select-video" 
                                    data-video-id="<?= h($video['id']) ?>"
                                    data-title="<?= h($video['title']) ?>">
                                <i class="fas fa-plus me-2"></i>
                                <?= __('Insert Video') ?>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Empty State -->
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fab fa-youtube fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted"><?= __('No videos found') ?></h5>
                    <?php if (!empty($searchTerm ?? '')): ?>
                        <p class="text-muted">
                            <?= __('No videos match your search for "{0}"', h($searchTerm)) ?>
                        </p>
                        <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('videoSearch').value = ''; document.getElementById('videoSearch').dispatchEvent(new Event('input'));">
                            <?= __('Clear Search') ?>
                        </button>
                    <?php else: ?>
                        <p class="text-muted">
                            <?= __('Search for YouTube videos to embed in your content.') ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.video-picker-card {
    transition: transform 0.2s, box-shadow 0.2s;
    cursor: pointer;
}

.video-picker-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.video-thumbnail-wrapper {
    position: relative;
    overflow: hidden;
}

.video-play-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    opacity: 0.8;
    transition: opacity 0.2s;
}

.video-picker-card:hover .video-play-overlay {
    opacity: 1;
}

.select-video:hover {
    transform: none !important;
}
</style>