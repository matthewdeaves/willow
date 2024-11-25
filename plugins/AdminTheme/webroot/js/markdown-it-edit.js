document.addEventListener('DOMContentLoaded', function() {
    const md = new markdownit({
        html: true,
        linkify: true,
        typographer: true
    });

    // Custom render rule for links
    const defaultRender = md.renderer.rules.link_open || function(tokens, idx, options, env, self) {
        return self.renderToken(tokens, idx, options);
    };

    md.renderer.rules.link_open = function(tokens, idx, options, env, self) {
        // Add a target="_blank" attribute to all links
        const aIndex = tokens[idx].attrIndex('target');

        if (aIndex < 0) {
            tokens[idx].attrPush(['target', '_blank']); // add new attribute
        } else {
            tokens[idx].attrs[aIndex][1] = '_blank';    // replace value of existing attr
        }

        // Add rel="noopener" for security when using target="_blank"
        const relIndex = tokens[idx].attrIndex('rel');
        if (relIndex < 0) {
            tokens[idx].attrPush(['rel', 'noopener']); 
        } else {
            tokens[idx].attrs[relIndex][1] = 'noopener';
        }

        // pass token to default renderer
        return defaultRender(tokens, idx, options, env, self);
    };

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