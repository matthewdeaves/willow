<?php use Cake\Routing\Router; ?>
<?php if (!isset($cookieConsent)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    WillowModal.show('<?= Router::url(['_name' => 'cookie-consent']) ?>', {
        title: '<?= __('Cookie Preferences') ?>',
        static: true,
        closeable: false,
        reload: true,
        dialogClass: 'modal-dialog-scrollable'
    });
});
</script>
<?php endif; ?>