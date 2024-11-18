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

        modalEl.addEventListener('show.bs.modal', function() {
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': csrfToken
                }
            })
            .then(response => response.text())
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
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                modal.hide();
                                if (options.reload) {
                                    window.location.reload();
                                }
                                if (typeof options.onSuccess === 'function') {
                                    options.onSuccess(data);
                                }
                            }
                        })
                        .catch(error => console.error('Error:', error));
                    });
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