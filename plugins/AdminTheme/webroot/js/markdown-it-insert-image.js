document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('insertImageBtn').addEventListener('click', function() {
        WillowModal.show('/admin/images/imageSelect', {
            title: 'Select Image',
            closeable: true,
            dialogClass: 'modal-lg',
            handleForm: false, // Changed to false since we're handling clicks directly
            onShown: function() {
                // This will be called after the modal is fully shown
                MarkdownImageSelect.bindEvents();
            },
            onContentLoaded: function() {
                // Optional: if you need to do anything right after content loads
                console.log('Image selector loaded');
            },
            onError: function(error) {
                // Handle any errors that occur
                console.error('Error in image selector:', error);
                alert('Error loading image selector');
            }
        });
    });
});