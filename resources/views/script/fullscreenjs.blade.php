<script>
    // 1. Toggle Fullscreen Mode
    function togglePageFullscreen() {
        const docEl = document.documentElement;
        const isFullscreen = document.fullscreenElement ||
                            document.webkitFullscreenElement ||
                            document.mozFullScreenElement ||
                            document.msFullscreenElement;

        if (!isFullscreen) {
            enterFullscreen(docEl);
            localStorage.setItem('keep_fullscreen', 'true');
        } else {
            exitFullscreen();
            localStorage.setItem('keep_fullscreen', 'false');
        }
    }

    // Request native fullscreen
    function enterFullscreen(element) {
        if (element.requestFullscreen) {
            element.requestFullscreen().catch(() => {});
        } else if (element.webkitRequestFullscreen) {
            element.webkitRequestFullscreen();
        } else if (element.mozRequestFullScreen) {
            element.mozRequestFullScreen();
        } else if (element.msRequestFullscreen) {
            element.msRequestFullscreen();
        }
    }

    // Exit native fullscreen
    function exitFullscreen() {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }
    }

    // 2. Update UI (Button Icons & Labels)
    function updateFullscreenUI() {
        const isFullscreen = document.fullscreenElement ||
                            document.webkitFullscreenElement ||
                            document.mozFullScreenElement ||
                            document.msFullscreenElement;

        document.querySelectorAll('.zoom-icon').forEach(icon => {
            icon.className = isFullscreen ? 'ti ti-minimize me-1 zoom-icon' : 'ti ti-maximize me-1 zoom-icon';
        });

        document.querySelectorAll('.zoom-text').forEach(text => {
            text.innerText = isFullscreen ? 'Exit Zoom' : 'Zoom';
        });

        // If user manually exits via ESC key, update storage state
        if (!isFullscreen && !window.restoreClickPending) {
            localStorage.setItem('keep_fullscreen', 'false');
        }
    }

    // 3. Auto-Restore Fullscreen After Refresh On First Click
    function checkFullscreenRestore() {
        const shouldBeFullscreen = localStorage.getItem('keep_fullscreen') === 'true';

        if (shouldBeFullscreen) {
            window.restoreClickPending = true;

            // One-time listener: triggers fullscreen instantly on user's first click anywhere on page
            const restoreHandler = () => {
                enterFullscreen(document.documentElement);
                window.restoreClickPending = false;
                document.removeEventListener('click', restoreHandler);
                document.removeEventListener('keydown', restoreHandler);
            };

            document.addEventListener('click', restoreHandler, { once: true });
            document.addEventListener('keydown', restoreHandler, { once: true });
        }
    }

    // Event Listeners
    document.addEventListener('fullscreenchange', updateFullscreenUI);
    document.addEventListener('webkitfullscreenchange', updateFullscreenUI);
    document.addEventListener('mozfullscreenchange', updateFullscreenUI);
    document.addEventListener('MSFullscreenChange', updateFullscreenUI);

    // Check state on page refresh
    document.addEventListener('DOMContentLoaded', checkFullscreenRestore);
</script>
