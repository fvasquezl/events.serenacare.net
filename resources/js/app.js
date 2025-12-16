import mediaSlideshow from './media-slideshow.js';

// Register Alpine components before Alpine starts
document.addEventListener('alpine:init', () => {
    Alpine.data('mediaSlideshow', mediaSlideshow);
});

// YouTube IFrame API ready callback
window.onYouTubeIframeAPIReady = function() {
    window.ytApiReady = true;
};
