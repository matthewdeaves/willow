<div class="accordion" id="seoAccordion">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingSeoFields">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#seoFields" aria-expanded="false" aria-controls="seoFields">
                <?= __('SEO Fields') ?>
            </button>
        </h2>
        <div id="seoFields" class="accordion-collapse collapse" aria-labelledby="headingSeoFields" data-bs-parent="#seoAccordion">
            <div class="accordion-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <?= $this->Form->control('meta_title', [
                            'class' => 'form-control',
                            'label' => __('Meta Title')
                        ]) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <?= $this->Form->control('meta_description', [
                            'type' => 'textarea',
                            'class' => 'form-control',
                            'label' => __('Meta Description')
                        ]) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <?= $this->Form->control('meta_keywords', [
                            'class' => 'form-control',
                            'label' => __('Meta Keywords')
                        ]) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <?= $this->Form->control('facebook_description', [
                            'type' => 'textarea',
                            'class' => 'form-control',
                            'label' => __('Facebook Description')
                        ]) ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <?= $this->Form->control('linkedin_description', [
                            'type' => 'textarea',
                            'class' => 'form-control',
                            'label' => __('LinkedIn Description')
                        ]) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <?= $this->Form->control('twitter_description', [
                            'type' => 'textarea',
                            'class' => 'form-control',
                            'label' => __('Twitter Description')
                        ]) ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <?= $this->Form->control('instagram_description', [
                            'type' => 'textarea',
                            'class' => 'form-control',
                            'label' => __('Instagram Description')
                        ]) ?>
                    </div>
                </div>
                <?php if (!isset($hideWordCount)) : ?>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <?= $this->Form->control('word_count', [
                            'type' => 'number',
                            'class' => 'form-control',
                            'label' => __('Word Count')
                        ]) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>