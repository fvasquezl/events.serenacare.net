/**
 * Alpine.js component for media slideshow with YouTube support
 */
export default function mediaSlideshow(mediaItems, slideshowId) {
    return {
        media: mediaItems,
        currentIndex: 0,
        timer: null,
        slideshowId: slideshowId,
        currentPlayer: null,
        currentVideoIndex: null,

        init() {
            // Load YouTube API if not already loaded
            if (!window.YT) {
                const tag = document.createElement('script');
                tag.src = 'https://www.youtube.com/iframe_api';
                const firstScriptTag = document.getElementsByTagName('script')[0];
                firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
            }

            // Watch for index changes
            this.$watch('currentIndex', (newIndex) => {
                this.handleIndexChange(newIndex);
            });

            // Start with first element
            if (this.media.length > 0) {
                this.handleIndexChange(0);
            }
        },

        handleIndexChange(index) {
            const current = this.media[index];
            if (!current) return;

            // Destroy previous player if exists
            if (this.currentPlayer) {
                try {
                    this.currentPlayer.destroy();
                } catch (e) {
                    // Ignore errors when destroying
                }
                this.currentPlayer = null;
                this.currentVideoIndex = null;
            }

            if (current.type === 'video' && current.youtube_id) {
                // Wait for DOM update
                this.$nextTick(() => {
                    this.createYouTubePlayer(current.youtube_id, index);
                });
            } else {
                // For images, schedule next
                this.scheduleNext();
            }
        },

        createYouTubePlayer(videoId, index) {
            const containerId = this.slideshowId + '-container-' + index;
            const container = document.getElementById(containerId);

            if (!container) {
                // Retry after brief delay
                setTimeout(() => this.createYouTubePlayer(videoId, index), 200);
                return;
            }

            // Clear container
            container.innerHTML = '';

            // Create player div
            const playerDiv = document.createElement('div');
            const playerId = this.slideshowId + '-player-' + index;
            playerDiv.id = playerId;
            playerDiv.style.width = '100%';
            playerDiv.style.height = '100%';
            container.appendChild(playerDiv);

            const self = this;

            const createPlayer = () => {
                self.currentVideoIndex = index;
                self.currentPlayer = new YT.Player(playerId, {
                    videoId: videoId,
                    width: '100%',
                    height: '100%',
                    playerVars: {
                        autoplay: 1,
                        mute: 1,
                        controls: 0,
                        rel: 0,
                        showinfo: 0,
                        modestbranding: 1,
                        playsinline: 1
                    },
                    events: {
                        onReady: function(event) {
                            event.target.playVideo();
                        },
                        onStateChange: function(event) {
                            // State 0 = video ended
                            if (event.data === YT.PlayerState.ENDED) {
                                self.nextMedia();
                            }
                        },
                        onError: function(event) {
                            console.error('YouTube player error:', event.data);
                            self.nextMedia();
                        }
                    }
                });
            };

            if (window.YT && window.YT.Player) {
                createPlayer();
            } else {
                // Wait for API to be ready
                const checkApi = setInterval(() => {
                    if (window.YT && window.YT.Player) {
                        clearInterval(checkApi);
                        createPlayer();
                    }
                }, 100);
            }
        },

        scheduleNext() {
            clearTimeout(this.timer);
            const current = this.media[this.currentIndex];
            if (!current) return;

            // Only schedule timer for images
            // Videos use onStateChange event
            if (current.type !== 'video') {
                this.timer = setTimeout(() => {
                    this.nextMedia();
                }, (current.time_offset || 5) * 1000);
            }
        },

        nextMedia() {
            clearTimeout(this.timer);
            this.currentIndex = (this.currentIndex + 1) % this.media.length;
        },

        destroy() {
            clearTimeout(this.timer);
            if (this.currentPlayer) {
                try {
                    this.currentPlayer.destroy();
                } catch (e) {
                    // Ignore
                }
            }
        }
    };
}
