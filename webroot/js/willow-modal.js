/**
 * WillowModal - Dynamic Bootstrap Modal Manager
 * ==========================================
 * 
 * A utility for dynamically creating and managing Bootstrap modals with AJAX content loading
 * and form handling capabilities.
 * 
 * Features:
 * - Dynamic modal creation and cleanup
 * - AJAX content loading
 * - Automatic form submission handling
 * - Support for submit button data attributes
 * - Comprehensive event callbacks
 * - CSRF token support
 * - Configurable modal options
 * 
 * Basic Usage:
 * ```javascript
 * WillowModal.show('/path/to/content', {
 *     title: 'Modal Title',
 *     closeable: true
 * });
 * ```
 * 
 * @namespace WillowModal
 */

/**
 * Creates and displays a Bootstrap modal with content loaded from a URL
 * 
 * @method show
 * @memberof WillowModal
 * 
 * @param {string} url - The URL from which to load the modal content
 * @param {Object} [options={}] - Configuration options for the modal
 * 
 * @param {string} [options.title] - The title to display in the modal header
 * @param {string} [options.dialogClass] - Additional CSS classes for the modal dialog
 * @param {boolean} [options.closeable=false] - Whether to show a close button in the header
 * @param {boolean} [options.static=false] - If true, modal won't close on backdrop click or Escape key
 * @param {boolean} [options.handleForm=true] - Whether to automatically handle form submissions
 * @param {boolean} [options.reload=false] - Whether to reload the page after successful form submission
 * 
 * @param {Function} [options.onShown] - Callback fired when modal is fully shown
 * @param {Function} [options.onContentLoaded] - Callback fired after content is loaded
 * @param {Function} [options.onSuccess] - Callback fired after successful form submission
 * @param {Function} [options.onError] - Callback fired on any error (loading/submission)
 * @param {Function} [options.onHidden] - Callback fired when modal is fully hidden
 * 
 * @returns {bootstrap.Modal} The Bootstrap modal instance
 * 
 * @example
 * // Basic modal with content
 * WillowModal.show('/users/edit/1', {
 *     title: 'Edit User',
 *     closeable: true
 * });
 * 
 * @example
 * // Form handling with callbacks
 * WillowModal.show('/users/add', {
 *     title: 'Add New User',
 *     handleForm: true,
 *     onSuccess: (data) => {
 *         console.log('User added:', data);
 *     },
 *     onError: (error) => {
 *         console.error('Failed:', error);
 *     }
 * });
 * 
 * Technical Details:
 * -----------------
 * 
 * Modal Structure:
 * The modal is created using Bootstrap's modal component structure and inserted
 * into the document body. It includes:
 * - Modal dialog with configurable classes
 * - Header with optional title and close button
 * - Body container for AJAX content
 * 
 * Form Handling:
 * When handleForm is true (default):
 * 1. Captures all form submissions within the modal
 * 2. Collects form data including:
 *    - All form fields
 *    - Data attributes from clicked submit buttons
 *    - Submit button name/value if present
 * 3. Submits via AJAX with CSRF token
 * 4. Handles success/error responses
 * 
 * CSRF Protection:
 * Requires a global csrfToken variable to be defined.
 * Automatically includes the token in all AJAX requests.
 * 
 * Cleanup:
 * The modal automatically removes itself from the DOM when closed.
 * 
 * Error Handling:
 * - Logs errors to console
 * - Calls onError callback if provided
 * - Handles both network and application errors
 * 
 * Dependencies:
 * - Bootstrap 5.x
 * - Modern browser with fetch API support
 */
window.WillowModal = {
    show: function(url, options = {}) {
        const modalHtml = `
            <div class="modal fade" id="dynamicModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered ${options.dialogClass || ''}">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5">${options.title || ''}</h1>
                            ${options.closeable ? '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>' : ''}
                        </div>
                        <div class="modal-body">
                            <div id="dynamicModalContent"></div>
                        </div>
                    </div>
                </div>
            </div>`;

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        const modalEl = document.getElementById('dynamicModal');
        const modal = new bootstrap.Modal(modalEl, {
            backdrop: options.static ? 'static' : true,
            keyboard: !options.static
        });

        // Add shown.bs.modal event listener before showing the modal
        modalEl.addEventListener('shown.bs.modal', function() {
            if (typeof options.onShown === 'function') {
                options.onShown();
            }
        });

        modalEl.addEventListener('show.bs.modal', function() {
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': csrfToken
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(html => {
                document.getElementById('dynamicModalContent').innerHTML = html;
                
                // Handle form submission if present
                const form = modalEl.querySelector('form');
                if (form && options.handleForm !== false) {
                    let lastClickedButton = null;

                    // Handle any button clicks within the form
                    form.addEventListener('click', function(e) {
                        if (e.target.matches('button[type="submit"]')) {
                            lastClickedButton = e.target;
                        }
                    });

                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        
                        const formData = new FormData(form);
                        
                        // Add any data attributes from the clicked button to the form data
                        if (lastClickedButton) {
                            Object.entries(lastClickedButton.dataset).forEach(([key, value]) => {
                                formData.append(key, value);
                            });
                            // Also add the button's name and value if present
                            if (lastClickedButton.name && lastClickedButton.value) {
                                formData.append(lastClickedButton.name, lastClickedButton.value);
                            }
                        }

                        fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-Token': csrfToken
                            },
                            body: formData
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                modal.hide();
                                if (options.reload) {
                                    window.location.reload();
                                }
                                if (typeof options.onSuccess === 'function') {
                                    options.onSuccess(data);
                                }
                            } else {
                                console.error('Form submission failed:', data);
                                if (typeof options.onError === 'function') {
                                    options.onError(data);
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            if (typeof options.onError === 'function') {
                                options.onError(error);
                            }
                        });
                    });
                }

                // Call onContentLoaded callback if provided
                if (typeof options.onContentLoaded === 'function') {
                    options.onContentLoaded();
                }
            })
            .catch(error => {
                console.error('Error loading modal content:', error);
                if (typeof options.onError === 'function') {
                    options.onError(error);
                }
            });
        });

        modalEl.addEventListener('hidden.bs.modal', function() {
            modalEl.remove();
            if (typeof options.onHidden === 'function') {
                options.onHidden();
            }
        });

        modal.show();
        return modal;
    }
};