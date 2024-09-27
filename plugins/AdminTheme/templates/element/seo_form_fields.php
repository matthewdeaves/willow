<div class="accordion" id="seoAccordion">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingSeoFields">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#seoFields" aria-expanded="false" aria-controls="seoFields">
                SEO Fields
            </button>
        </h2>
        <div id="seoFields" class="accordion-collapse collapse" aria-labelledby="headingSeoFields" data-bs-parent="#seoAccordion">
            <div class="accordion-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <?= $this->Form->control('meta_title', [
                            'class' => 'form-control',
                            'label' => 'Meta Title'
                        ]) ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <?= $this->Form->control('focus_keyword', [
                            'class' => 'form-control',
                            'label' => 'Focus Keyword'
                        ]) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <?= $this->Form->control('meta_description', [
                            'type' => 'textarea',
                            'class' => 'form-control',
                            'label' => 'Meta Description'
                        ]) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <?= $this->Form->control('meta_keywords', [
                            'class' => 'form-control',
                            'label' => 'Meta Keywords'
                        ]) ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <?= $this->Form->control('canonical_url', [
                            'class' => 'form-control',
                            'label' => 'Canonical URL'
                        ]) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <?= $this->Form->control('featured_image_alt', [
                            'class' => 'form-control',
                            'label' => 'Featured Image Alt Text'
                        ]) ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <?= $this->Form->control('social_image', [
                            'class' => 'form-control',
                            'label' => 'Social Media Image URL'
                        ]) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <?= $this->Form->control('social_title', [
                            'class' => 'form-control',
                            'label' => 'Social Media Title'
                        ]) ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <?= $this->Form->control('social_description', [
                            'type' => 'textarea',
                            'class' => 'form-control',
                            'label' => 'Social Media Description'
                        ]) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <?= $this->Form->control('schema_markup', [
                            'type' => 'textarea',
                            'class' => 'form-control',
                            'label' => 'Schema Markup'
                        ]) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <?= $this->Form->control('readability_score', [
                            'type' => 'number',
                            'class' => 'form-control',
                            'label' => 'Readability Score'
                        ]) ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <?= $this->Form->control('word_count', [
                            'type' => 'number',
                            'class' => 'form-control',
                            'label' => 'Word Count'
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>