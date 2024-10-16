<?php
/**
 * @var \App\View\AppView $this
 * @var string $message
 * @var string $url
 * @var \Throwable $error
 */
use Cake\Core\Configure;

$this->layout = 'error';

if (Configure::read('debug')) :
    $this->layout = 'dev_error';

    $this->assign('title', $message);
    $this->assign('templateName', 'error429.php');

    $this->start('file');
    echo $this->element('auto_table_warning');
    $this->end();
endif;
?>
<h2><?= __('Too Many Requests') ?></h2>
<p class="error">
    <strong><?= __('Error') ?>: </strong>
    <?= __('You have exceeded the rate limit. Please try again later.') ?>
</p>
<?php
if (Configure::read('debug')):
    echo $this->element('exception_stack_trace');
endif;
?>