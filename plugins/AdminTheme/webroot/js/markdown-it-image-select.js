const MarkdownImageSelect = {
    init: function() {
        document.getElementById('insertImageBtn').addEventListener('click', function() {
            WillowModal.show('/admin/images/imageSelect', {
                title: modalTitle, // This should be set in your view as a global var
                closeable: true,
                dialogClass: 'modal-lg',
                handleForm: false,
                onSuccess: function(data) {
                    // Not used since we're handling clicks directly
                }
            });
        });
    },

    bindEvents: function() {
        this.bindImageInsertEvents();
        this.bindPaginationEvents();
        this.bindSearchBoxEvents();
    },

    bindImageInsertEvents: function() {
        $('.insert-image').off('click').on('click', function() {
            var imageSrc = $(this).data('src');
            var imageId = $(this).data('id');
            var imageAlt = $(this).data('alt');
            var imageName = $(this).data('name');
            var imageSize = $('#' + imageId + '_size').val();
            
            var altText = imageAlt || imageName;

            // Get the textarea element
            var textarea = document.getElementById('article-body');
            
            // Create markdown syntax for the image
            var markdownImage = '![' + altText + '](/files/Images/image/' + imageSize + '/' + imageSrc + ')';
    
            // Insert at cursor position or at end if no cursor
            var startPos = textarea.selectionStart;
            var endPos = textarea.selectionEnd;
    
            textarea.value = textarea.value.substring(0, startPos) + 
                            markdownImage + 
                            textarea.value.substring(endPos);
    
            // Trigger the input event to update preview
            textarea.dispatchEvent(new Event('input'));
    
            // Update cursor position after insert
            var newCursorPos = startPos + markdownImage.length;
            textarea.setSelectionRange(newCursorPos, newCursorPos);
    
            // Close the modal
            const modal = bootstrap.Modal.getInstance(document.querySelector('.modal'));
            if (modal) {
                modal.hide();
            }
    
            return false;
        });
    },

    loadImages: function(url) {
        $.ajax({
            url: url,
            type: 'GET',
            data: { gallery_only: true },
            success: function(response) {
                $('#dynamicModalContent').html(response);
                MarkdownImageSelect.bindEvents();
            },
            error: function(xhr, status, error) {
                console.error("Error loading images:", error);
            }
        });
    },

    bindPaginationEvents: function() {
        $('.pagination a').off('click').on('click', function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            MarkdownImageSelect.loadImages(url);
        });
    },

    bindSearchBoxEvents: function() {
        const searchInput = document.getElementById('imageSearch');
        if (!searchInput) return;

        let debounceTimer;
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const searchTerm = this.value.trim();
                let url = '/admin/images/imageSelect';
                if (searchTerm.length > 0) {
                    url += '?search=' + encodeURIComponent(searchTerm);
                }
                MarkdownImageSelect.loadImages(url);
            }, 300);
        });
    }
};

// Initialize when document is ready
$(document).ready(function() {
    MarkdownImageSelect.init();
});

// Add a listener for when modal content is loaded
document.addEventListener('shown.bs.modal', function(event) {
    if (event.target.id === 'dynamicModal') {
        MarkdownImageSelect.bindEvents();
    }
});