<script src="https://cdn.jsdelivr.net/npm/markdown-it@14.1.0/dist/markdown-it.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const md = new markdownit({
        html: true,
        linkify: true,
        typographer: true
    });

    const editor = document.getElementById('article-body');
    const preview = document.getElementById('markdown-preview');

    if (editor && preview) {
        // Initial preview
        preview.innerHTML = md.render(editor.value);

        // Live preview functionality
        editor.addEventListener('input', function() {
            const content = editor.value;
            const rendered = md.render(content);
            preview.innerHTML = rendered;
        });

        // Update preview when switching to preview tab
        $('#preview-tab').on('shown.bs.tab', function (e) {
            preview.innerHTML = md.render(editor.value);
        });
    }
});
</script>