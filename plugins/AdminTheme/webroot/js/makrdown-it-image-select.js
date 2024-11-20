$(document).ready(function() {
    function bindImageInsertEvents() {
        $('.insert-image').off('click').on('click', function() {
            var imageSrc = $(this).data('src');
            var imageId = $(this).data('id');
            var imageAlt = $(this).data('alt');
            var imageSize = $('#' + imageId + '_size').val();
            
            // Get the textarea element
            var textarea = document.getElementById('article-body');
            
            // You can choose either format:
            
            // Option 1: Markdown syntax
            var markdownImage = '![' + imageAlt + '](/files/Images/image/' + imageSize + '/' + imageSrc + ')';
            
            // Option 2: HTML syntax (which markdown-it will also render correctly)
            var htmlImage = '<img src="/files/Images/image/' + imageSize + '/' + 
                          imageSrc + '" alt="' + imageAlt + '" class="img-fluid" />';

            // Insert at cursor position or at end if no cursor
            var startPos = textarea.selectionStart;
            var endPos = textarea.selectionEnd;
            var textToInsert = markdownImage; // or htmlImage if you prefer

            textarea.value = textarea.value.substring(0, startPos) + 
                           textToInsert + 
                           textarea.value.substring(endPos);

            // Trigger the input event to update preview
            textarea.dispatchEvent(new Event('input'));

            // Update cursor position after insert
            var newCursorPos = startPos + textToInsert.length;
            textarea.setSelectionRange(newCursorPos, newCursorPos);

            // Close the modal
            $('.modal').modal('hide');

            return false;
        });
    }

    function loadImages(url) {
        $.ajax({
            url: url,
            type: 'GET',
            data: { gallery_only: true },
            success: function(response) {
                $('#image-gallery').html(response);
                bindImageInsertEvents();
                bindPaginationEvents();
            },
            error: function(xhr, status, error) {
                console.error("<?= __('Error loading images:') ?>", error);
            }
        });
    }

    function bindPaginationEvents() {
        $('.pagination a').off('click').on('click', function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            loadImages(url);
        });
    }

    function bindSearchBoxEvents() {
        const searchInput = document.getElementById('imageSearch');
        let debounceTimer;

        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                const searchTerm = this.value.trim();
                let url = '/admin/images/trumbowygSelect';
                if (searchTerm.length > 0) {
                    url += '?search=' + encodeURIComponent(searchTerm);
                }
                loadImages(url);
            }, 300);
        });
    }
    
    bindImageInsertEvents();
    bindPaginationEvents();
    bindSearchBoxEvents();
});