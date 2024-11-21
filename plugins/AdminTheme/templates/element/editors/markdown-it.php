<script>
document.addEventListener('DOMContentLoaded', function() {
    const md = new markdownit({
        html: true,
        linkify: true,
        typographer: true
    });

    const editor = document.getElementById('article-markdown');
    const preview = document.getElementById('markdown-preview');
    const bodyTextarea = document.getElementById('article-body');

    if (editor && preview && bodyTextarea) {
        // Initial preview
        const initialRendered = md.render(editor.value);
        preview.innerHTML = initialRendered;
        bodyTextarea.value = initialRendered;

        // Live preview functionality
        editor.addEventListener('input', function() {
            const content = editor.value;
            const rendered = md.render(content);
            preview.innerHTML = rendered;
            bodyTextarea.value = rendered;
        });

        // Update preview when switching to preview tab
        $('#preview-tab').on('shown.bs.tab', function (e) {
            const rendered = md.render(editor.value);
            preview.innerHTML = rendered;
            bodyTextarea.value = rendered;
        });
    }
});
</script>