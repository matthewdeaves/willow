<div class="seo-fields-wrapper">
    <button type="button" class="btn btn-secondary mb-3" onclick="toggleSeoFields()">
        <?= __('Toggle SEO Fields') ?>
    </button>
    <div id="seoFields" class="table-responsive" style="display: none;">
        <table class="table table-bordered">
            <tr>
                <th><?= __('Meta Title') ?></th>
                <td><?= h($article->meta_title) ?></td>
            </tr>
            <tr>
                <th><?= __('Meta Description') ?></th>
                <td><?= h($article->meta_description) ?></td>
            </tr>
            <tr>
                <th><?= __('Meta Keywords') ?></th>
                <td><?= h($article->meta_keywords) ?></td>
            </tr>
            <tr>
                <th><?= __('Facebook Description') ?></th>
                <td><?= h($article->facebook_description) ?></td>
            </tr>
            <tr>
                <th><?= __('LinkedIn Description') ?></th>
                <td><?= h($article->linkedin_description) ?></td>
            </tr>
            <tr>
                <th><?= __('Twitter Description') ?></th>
                <td><?= h($article->twitter_description) ?></td>
            </tr>
            <tr>
                <th><?= __('Instagram Description') ?></th>
                <td><?= h($article->instagram_description) ?></td>
            </tr>
            <tr>
                <th><?= __('Word Count') ?></th>
                <td><?= $article->word_count !== null ? $this->Number->format($article->word_count) : '' ?></td>
            </tr>
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