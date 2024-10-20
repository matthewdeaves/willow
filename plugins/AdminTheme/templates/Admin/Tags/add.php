<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Tag $tag
 * @var \Cake\Collection\CollectionInterface|string[] $articles
 */
?>
<div class="container-fluid mt-4">
    <div class="row">
        <?php
        echo $this->element('actions_card', [
            'modelName' => 'Tag',
            'controllerName' => 'Tags',
            'entity' => $tag
        ]);
        ?>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><?= __('Add Tag') ?></h3>
                </div>
                <div class="card-body">
                    <?= $this->Form->create($tag, ['class' => 'needs-validation', 'novalidate' => true]) ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <?= $this->Form->control('title', [
                                'class' => 'form-control' . ($this->Form->isFieldError('title') ? ' is-invalid' : ''),
                                'required' => true
                            ]) ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <?= $this->Form->control('slug', [
                                'class' => 'form-control' . ($this->Form->isFieldError('slug') ? ' is-invalid' : ''),
                                'required' => true
                            ]) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <?= $this->Form->control('description', [
                                'type' => 'textarea',
                                'rows' => '3',
                                'class' => 'form-control' . ($this->Form->isFieldError('description') ? ' is-invalid' : ''),
                            ]) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <?= $this->Form->label('articles._ids', 'Associated Articles') ?>
                            <div class="input-group">
                                <input type="text" class="form-control" id="articleSearch" placeholder="Search articles...">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Select Articles</button>
                                <ul class="dropdown-menu dropdown-menu-end" style="max-height: 300px; overflow-y: auto;">
                                    <?php foreach ($articles as $value => $label): ?>
                                        <li>
                                            <label class="dropdown-item">
                                                <input type="checkbox" name="articles[_ids][]" value="<?= h($value) ?>" class="me-2">
                                                <?= h($label) ?>
                                            </label>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?= $this->element('seo_form_fields', ['hideWordCount' => true]) ?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mt-4 mb-3">
                                <?= $this->Form->button(__('Add Tag'), [
                                    'class' => 'btn btn-primary'
                                ]) ?>
                            </div>
                        </div>
                    </div>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('articleSearch');
    const dropdownItems = document.querySelectorAll('.dropdown-menu li');

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        dropdownItems.forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
});
</script>