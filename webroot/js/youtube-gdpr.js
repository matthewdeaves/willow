function loadYouTubeVideo(button) {
    const container = button.closest('.youtube-embed');
    const videoId = button.dataset.videoId;
    const width = container.style.width;
    const height = container.querySelector('.youtube-thumbnail').style.height;

    // Create iframe with privacy-enhanced mode
    const iframe = document.createElement('iframe');
    iframe.setAttribute('src', `https://www.youtube-nocookie.com/embed/${videoId}`);
    iframe.setAttribute('frameborder', '0');
    iframe.setAttribute('allowfullscreen', '');
    iframe.style.width = width;
    iframe.style.height = height;

    // Replace the placeholder with the iframe
    container.innerHTML = '';
    container.appendChild(iframe);

    // Store consent in localStorage
    localStorage.setItem('youtube_consent', 'granted');
}

// Optional: Check for existing consent and auto-load videos
document.addEventListener('DOMContentLoaded', function() {
    if (localStorage.getItem('youtube_consent') === 'granted') {
        document.querySelectorAll('.youtube-consent-btn').forEach(button => {
            loadYouTubeVideo(button);
        });
    }
});