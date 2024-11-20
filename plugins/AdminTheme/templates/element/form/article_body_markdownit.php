<ul class="nav nav-tabs" id="editorTabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="editor-tab" data-bs-toggle="tab" href="#editor" role="tab" 
            aria-controls="editor" aria-selected="true">
            <?= __('Body Editor') ?>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="preview-tab" data-bs-toggle="tab" href="#preview" role="tab" 
            aria-controls="preview" aria-selected="false">
            <?= __('Body Preview') ?>
        </a>
    </li>
</ul>
<div class="tab-content" id="editorTabContent">
    <div class="tab-pane show active" id="editor" role="tabpanel" aria-labelledby="editor-tab">
        <div class="mb-3">
            <?php echo $this->Form->control('body',
                [
                    'id' => 'article-body',
                    'rows' => '30',
                    'class' => 'my-3 form-control' . ($this->Form->isFieldError('body') ? ' is-invalid' : ''),
                    'label' => false,
                ]); ?>
                <?php if ($this->Form->isFieldError('body')): ?>
                <div class="invalid-feedback">
                    <?= $this->Form->error('body') ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="tab-pane" id="preview" role="tabpanel" aria-labelledby="preview-tab">
        <div class="mb-3">
            <div class="card-body border rounded my-3" id="markdown-preview">

            </div>
        </div>
    </div>
</div>