document.addEventListener('DOMContentLoaded', function() {
    // Disable automatic highlighting
    hljs.configure({ ignoreUnescapedHTML: true });
    
    const md = new markdownit({
        html: true,
        linkify: true,
        typographer: true,
        highlight: function (str, lang) {
            if (lang && hljs.getLanguage(lang)) {
                try {
                    const highlighted = hljs.highlight(str, { language: lang, ignoreIllegals: true }).value;
                    return `<pre><code class="hljs language-${lang}">${highlighted}</code></pre>`;
                } catch (__) {}
            }
            return `<pre><code class="hljs">${md.utils.escapeHtml(str)}</code></pre>`;
        }
    });

    // Custom render rule for links
    const defaultRender = md.renderer.rules.link_open || function(tokens, idx, options, env, self) {
        return self.renderToken(tokens, idx, options);
    };

    md.renderer.rules.link_open = function(tokens, idx, options, env, self) {
        const aIndex = tokens[idx].attrIndex('target');
        if (aIndex < 0) {
            tokens[idx].attrPush(['target', '_blank']); 
        } else {
            tokens[idx].attrs[aIndex][1] = '_blank';    
        }

        const relIndex = tokens[idx].attrIndex('rel');
        if (relIndex < 0) {
            tokens[idx].attrPush(['rel', 'noopener']); 
        } else {
            tokens[idx].attrs[relIndex][1] = 'noopener';
        }

        return defaultRender(tokens, idx, options, env, self);
    };

    const editor = document.getElementById('article-markdown');
    const preview = document.getElementById('markdown-preview');
    const bodyTextarea = document.getElementById('article-body');

    let updateTimeout = null;

    function cleanupHighlight(element) {
        element.querySelectorAll('pre code').forEach(block => {
            // Remove all highlight.js related attributes
            block.removeAttribute('data-highlighted');
            block.className = block.className.replace(/hljs-.*\s?/g, '');
            if (block.classList.contains('hljs')) {
                const lang = Array.from(block.classList)
                    .find(cls => cls.startsWith('language-'));
                block.className = `hljs ${lang || ''}`.trim();
            }
        });
    }

    function updatePreview() {
        if (!editor || !preview || !bodyTextarea) return;

        const content = editor.value;
        const rendered = md.render(content);
        
        // First, update the HTML
        preview.innerHTML = rendered;
        bodyTextarea.value = rendered;
        
        // Clean up any existing highlighting
        cleanupHighlight(preview);
    }

    if (editor && preview && bodyTextarea) {
        // Initial preview
        updatePreview();

        // Live preview functionality with debouncing
        editor.addEventListener('input', function() {
            clearTimeout(updateTimeout);
            updateTimeout = setTimeout(updatePreview, 300);
        });

        // Update preview when switching to preview tab
        $('#preview-tab').on('shown.bs.tab', function (e) {
            updatePreview();
        });
    }
});