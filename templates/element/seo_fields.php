<div class="seo-fields-wrapper">
    <button type="button" class="btn btn-secondary" onclick="toggleSeoFields()">Toggle SEO Fields</button>
    <div id="seoFields" style="display: none;">
        <table>
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
                <th><?= __('Focus Keyword') ?></th>
                <td><?= h($article->focus_keyword) ?></td>
            </tr>
            <tr>
                <th><?= __('Featured Image Alt') ?></th>
                <td><?= h($article->featured_image_alt) ?></td>
            </tr>
            <tr>
                <th><?= __('Canonical URL') ?></th>
                <td><?= h($article->canonical_url) ?></td>
            </tr>
            <tr>
                <th><?= __('Schema Markup') ?></th>
                <td><?= h($article->schema_markup) ?></td>
            </tr>
            <tr>
                <th><?= __('Social Title') ?></th>
                <td><?= h($article->social_title) ?></td>
            </tr>
            <tr>
                <th><?= __('Social Description') ?></th>
                <td><?= h($article->social_description) ?></td>
            </tr>
            <tr>
                <th><?= __('Social Image') ?></th>
                <td><?= h($article->social_image) ?></td>
            </tr>
            <tr>
                <th><?= __('Readability Score') ?></th>
                <td><?= $article->readability_score !== null ? $this->Number->format($article->readability_score) : '' ?></td>
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