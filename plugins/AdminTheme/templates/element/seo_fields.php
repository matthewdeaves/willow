<div class="accordion mb-3" id="seoAccordion">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingSeoFields">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#seoFields" aria-expanded="false" aria-controls="seoFields">
                <?= __('SEO Fields') ?>
            </button>
        </h2>
        <div id="seoFields" class="accordion-collapse collapse" aria-labelledby="headingSeoFields" data-bs-parent="#seoAccordion">
            <div class="accordion-body">

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
    </div>
</div>