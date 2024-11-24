// In markdown-it-insert-image.js
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('insertImageBtn').addEventListener('click', function() {
        WillowModal.show('/admin/images/imageSelect', {
            title: 'Select Image',
            closeable: true,
            dialogClass: 'modal-lg',
            handleForm: false,
            onContentLoaded: function() {
                // Initialize immediately after content loads
                MarkdownImageSelect.init();
            },
            onShown: function() {
                // Re-initialize after modal is fully visible
                // This ensures elements are accessible in the DOM
                MarkdownImageSelect.init();
            }
        });
    });
});