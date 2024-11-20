<?php use App\Utility\SettingsManager; ?>

<?php if(SettingsManager::read('Editing.editor') == 'trumbowyg') : ?>
<?= $this->element('editors/trumbowyg'); ?>
<?php endif; ?>

<?php if(SettingsManager::read('Editing.editor') == 'markdownit') : ?>
<?= $this->element('editors/markdown-it'); ?>
<?php endif; ?>