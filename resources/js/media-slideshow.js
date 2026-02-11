/**
 * Alpine.js component for media slideshow with YouTube support
 * and varied CSS transitions between slides.
 */

const TRANSITIONS = [
    { name: 'fade',        videoSafe: true },
    { name: 'zoom-in',     videoSafe: false },
    { name: 'zoom-out',    videoSafe: false },
    { name: 'slide-right', videoSafe: false },
    { name: 'slide-left',  videoSafe: false },
    { name: 'ken-burns',   videoSafe: false },
];

export default function mediaSlideshow(mediaItems, slideshowId) {
    return {
        media: mediaItems,
        currentIndex: 0,
        previousIndex: -1,
        transitionCycleIndex: 0,
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
            this.$watch('currentIndex', (newIndex, oldIndex) => {
                this.previousIndex = oldIndex;
                this.handleIndexChange(newIndex);
            });

            // Start with first element
            if (this.media.length > 0) {
                this.handleIndexChange(0);
            }
        },

        /**
         * Pick the next transition, cycling sequentially.
         * Falls back to fade when either slide is a video.
         */
        pickTransition(enterIndex, exitIndex) {
            const enterItem = this.media[enterIndex];
            const exitItem = exitIndex >= 0 ? this.media[exitIndex] : null;
            const hasVideo = enterItem?.type === 'video' || exitItem?.type === 'video';

            if (hasVideo) {
                return TRANSITIONS[0]; // fade
            }

            const transition = TRANSITIONS[this.transitionCycleIndex % TRANSITIONS.length];
            this.transitionCycleIndex++;
            return transition;
        },

        /**
         * Apply CSS animation classes to the entering and exiting slides.
         */
        applyTransition(transition) {
            const slides = this.$el.querySelectorAll('[data-slide]');
            if (!slides.length) {
                return;
            }

            slides.forEach((slide) => {
                const idx = parseInt(slide.dataset.slide, 10);

                // Remove all previous animation classes
                slide.className = slide.className
                    .replace(/\banim-[\w-]+\b/g, '')
                    .replace(/\bslide-(enter|exit|idle)\b/g, '')
                    .trim();

                if (idx === this.currentIndex) {
                    slide.classList.add('slide-enter', `anim-${transition.name}-enter`);
                    slide.style.opacity = '';
                } else if (idx === this.previousIndex) {
                    slide.classList.add('slide-exit', `anim-${transition.name}-exit`);
                    slide.style.opacity = '';
                } else {
                    slide.classList.add('slide-idle');
                }
            });

            // Apply Ken Burns slow zoom on entering image
            if (transition.name === 'ken-burns') {
                this.applyKenBurns();
            }
        },

        /**
         * Apply Ken Burns slow zoom effect to the current image slide.
         */
        applyKenBurns() {
            const current = this.media[this.currentIndex];
            if (!current || current.type === 'video') {
                return;
            }

            this.$nextTick(() => {
                const slides = this.$el.querySelectorAll('[data-slide]');
                slides.forEach((slide) => {
                    const idx = parseInt(slide.dataset.slide, 10);
                    if (idx === this.currentIndex) {
                        const duration = (current.time_offset || 5) + 's';
                        slide.style.setProperty('--ken-burns-duration', duration);
                        const img = slide.querySelector('img');
                        if (img) {
                            img.classList.add('anim-ken-burns');
                        }
                    }
                });
            });
        },

        /**
         * Reset slides and restart from the beginning (e.g., on Livewire refresh).
         */
        forceRefresh() {
            clearTimeout(this.timer);
            this.previousIndex = -1;
            this.currentIndex = 0;
            this.transitionCycleIndex = 0;

            // Clear all animation classes
            const slides = this.$el.querySelectorAll('[data-slide]');
            slides.forEach((slide) => {
                slide.className = slide.className
                    .replace(/\banim-[\w-]+\b/g, '')
                    .replace(/\bslide-(enter|exit|idle)\b/g, '')
                    .trim();
                const img = slide.querySelector('img');
                if (img) {
                    img.classList.remove('anim-ken-burns');
                }
            });

            this.handleIndexChange(0);
        },

        handleIndexChange(index) {
            const current = this.media[index];
            if (!current) {
                return;
            }

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

            // Apply transition
            const transition = this.pickTransition(index, this.previousIndex);
            this.$nextTick(() => {
                this.applyTransition(transition);
            });

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
                            event.target.setPlaybackQuality('hd1080');
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
            if (!current) {
                return;
            }

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

        prevMedia() {
            clearTimeout(this.timer);
            this.currentIndex = (this.currentIndex - 1 + this.media.length) % this.media.length;
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
