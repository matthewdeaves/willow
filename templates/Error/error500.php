<?php
/**
 * @var \App\View\AppView $this
 * @var string $message
 * @var string $url
 * @var \Throwable $error
 */
use Cake\Core\Configure;
use Cake\Error\Debugger;

$this->layout = 'error';

if (Configure::read('debug')) :
    $this->layout = 'dev_error';

    $this->assign('title', $message);
    $this->assign('templateName', 'error500.php');

    $this->start('file');
?>
<?php if (!empty($error->getFile())) : ?>
    <strong><?= __('Error in: ') ?></strong>
    <?= $this->Html->link(sprintf('%s, line %s', Debugger::trimPath($error->getFile()), $error->getLine()), Debugger::editorUrl($error->getFile(), $error->getLine())); ?>
<?php endif; ?>
<?php
    echo $this->element('auto_table_warning');
    $this->end();
endif;
?>
<h2><?= __('An Internal Error Has Occurred.') ?></h2>
<p class="error">
    <strong><?= __('Error') ?>: </strong>
    <?= h($message) ?>
</p>
<?php
if (Configure::read('debug')):
    echo $this->element('auto_table_warning');
    echo $this->element('exception_stack_trace');
endif;
?>