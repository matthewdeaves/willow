<div class="accordion mb-3" id="seoAccordion">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingSeoFields">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#seoFields" aria-expanded="false" aria-controls="seoFields">
                <?= __('SEO Fields') ?>
            </button>
        </h2>
        <div id="seoFields" class="accordion-collapse collapse" aria-labelledby="headingSeoFields" data-bs-parent="#seoAccordion">
            <div class="accordion-body">
                <?= $this->element('form/field', ['name' => 'meta_title']) ?>
                <?= $this->element('form/field', ['name' => 'meta_description']) ?>
                <?= $this->element('form/field', ['name' => 'meta_keywords']) ?>
                <?= $this->element('form/field', ['name' => 'facebook_description']) ?>
                <?= $this->element('form/field', ['name' => 'linkedin_description']) ?>
                <?= $this->element('form/field', ['name' => 'instagram_description']) ?>
                <?= $this->element('form/field', ['name' => 'twitter_description']) ?>
                <?php if (!isset($hideWordCount)) : ?>
                    <?= $this->element('form/field', ['name' => 'word_count', 'type' => 'number']) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
