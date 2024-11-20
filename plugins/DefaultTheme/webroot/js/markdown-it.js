document.addEventListener('DOMContentLoaded', function() {
    // Initialize markdown-it with syntax highlighting
    const md = window.markdownit({
        html: true,        // Enable HTML tags in source
        linkify: true,     // Autoconvert URL-like text to links
        typographer: true, // Enable some language-neutral replacement + quotes beautification
        breaks: true,      // Convert \n in paragraphs into <br>
        highlight: function (str, lang) {
            if (lang && hljs.getLanguage(lang)) {
                try {
                    return '<pre class="hljs"><code>' +
                        hljs.highlight(str, { language: lang, ignoreIllegals: true }).value +
                        '</code></pre>';
                } catch (__) {}
            }
            return '<pre class="hljs"><code>' + md.utils.escapeHtml(str) + '</code></pre>';
        }
    }).enable([
        'heading',        // Enable header parsing
        'image',         // Enable image parsing
        'link',          // Enable link parsing
        'emphasis',      // Enable emphasis parsing
        'list',          // Enable list parsing
        'code'           // Enable code block parsing
    ]);

    // Get the article content
    const articleBody = document.getElementById('article-body-content');
    if (!articleBody) return;

    // Function to decode HTML entities
    function decodeHtml(html) {
        const txt = document.createElement("textarea");
        txt.innerHTML = html;
        return txt.value;
    }

    const markdownContent = decodeHtml(articleBody.innerHTML);
    
    // Render the markdown and update the content
    articleBody.innerHTML = md.render(markdownContent);
});