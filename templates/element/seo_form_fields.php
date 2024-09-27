<div class="seo-fields-wrapper">
    <button type="button" class="btn btn-secondary" onclick="toggleSeoFields()">Toggle SEO Fields</button>
    <div id="seoFields" style="display: none;">
        <?php
        echo $this->Form->control('meta_title', ['label' => 'Meta Title']);
        echo $this->Form->control('meta_description', ['type' => 'textarea', 'label' => 'Meta Description']);
        echo $this->Form->control('meta_keywords', ['label' => 'Meta Keywords']);
        echo $this->Form->control('focus_keyword', ['label' => 'Focus Keyword']);
        echo $this->Form->control('featured_image_alt', ['label' => 'Featured Image Alt Text']);
        echo $this->Form->control('canonical_url', ['label' => 'Canonical URL']);
        echo $this->Form->control('schema_markup', ['type' => 'textarea', 'label' => 'Schema Markup']);
        echo $this->Form->control('social_title', ['label' => 'Social Media Title']);
        echo $this->Form->control('social_description', ['type' => 'textarea', 'label' => 'Social Media Description']);
        echo $this->Form->control('social_image', ['label' => 'Social Media Image URL']);
        echo $this->Form->control('readability_score', ['type' => 'number', 'label' => 'Readability Score']);
        echo $this->Form->control('word_count', ['type' => 'number', 'label' => 'Word Count']);
        ?>
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