document.addEventListener('DOMContentLoaded', function() {
    // Initialize markdown-it
    const md = window.markdownit({
        html: true,        // Enable HTML tags in source
        linkify: true,     // Autoconvert URL-like text to links
        typographer: true  // Enable some language-neutral replacement + quotes beautification
    });

    // Get the article content
    const articleBody = document.getElementById('article-body-content');
    const markdownContent = articleBody.textContent;
    
    // Render the markdown and update the content
    articleBody.innerHTML = md.render(markdownContent);
});