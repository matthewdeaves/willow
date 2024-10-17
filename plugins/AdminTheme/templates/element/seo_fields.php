<div class="seo-fields-wrapper">
    <button type="button" class="btn btn-secondary mb-3" onclick="toggleSeoFields()">
        <?= __('Toggle SEO Fields') ?>
    </button>
    <div id="seoFields" class="table-responsive" style="display: none;">
        <table class="table table-bordered">
            <tr>
                <th><?= __('Meta Title') ?></th>
                <td><?= h($model->meta_title) ?></td>
            </tr>
            <tr>
                <th><?= __('Meta Description') ?></th>
                <td><?= h($model->meta_description) ?></td>
            </tr>
            <tr>
                <th><?= __('Meta Keywords') ?></th>
                <td><?= h($model->meta_keywords) ?></td>
            </tr>
            <tr>
                <th><?= __('Facebook Description') ?></th>
                <td><?= h($model->facebook_description) ?></td>
            </tr>
            <tr>
                <th><?= __('LinkedIn Description') ?></th>
                <td><?= h($model->linkedin_description) ?></td>
            </tr>
            <tr>
                <th><?= __('Twitter Description') ?></th>
                <td><?= h($model->twitter_description) ?></td>
            </tr>
            <tr>
                <th><?= __('Instagram Description') ?></th>
                <td><?= h($model->instagram_description) ?></td>
            </tr>
            <?php if (!isset($hideWordCount)) : ?>
            <tr>
                <th><?= __('Word Count') ?></th>
                <td><?= $model->word_count !== null ? $this->Number->format($model->word_count) : '' ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<script>
function toggleSeoFields() {
    var seoFields = document.getElementById('seoFields');
    if (seoFields.style.display === 'none') {
        seoFields.style.display = 'block';
    } else {
        seoFields.style.display = 'none';
    }
}
</script>