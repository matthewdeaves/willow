document.addEventListener('DOMContentLoaded', function() {
    // Image insertion handler
    document.getElementById('insertImageBtn')?.addEventListener('click', function() {
        WillowModal.show('/admin/images/imageSelect', {
            title: 'Select Image',
            closeable: true,
            dialogClass: 'modal-lg',
            handleForm: false,
            onContentLoaded: function() {
                const mediaSelect = new MediaSelect('image');
                mediaSelect.init();
            }
        });
    });

    // Video insertion handler
    document.getElementById('insertVideoBtn')?.addEventListener('click', function() {
        WillowModal.show('/admin/videos/video_select', {
            title: 'Insert YouTube Video',
            closeable: true,
            dialogClass: 'modal-lg',
            handleForm: false,
            onContentLoaded: function() {
                const mediaSelect = new MediaSelect('video');
                mediaSelect.init();
            }
        });
    });
});