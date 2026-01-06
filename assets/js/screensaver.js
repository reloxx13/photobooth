/**
 * Screensaver module for Photobooth.
 * Exposes createScreensaver(deps) which returns the screensaver API.
 */
(function (window, $) {
    window.createScreensaver = function createScreensaver(deps) {
        const {
            config,
            environment,
            startPage,
            overlay,
            videoEl,
            imageEl,
            textTop,
            textCenter,
            textBottom,
            screensaverEnabled,
            screensaverMode,
            screensaverTimeoutMs,
            screensaverSwitchMs,
            urlSafe,
            galleryFallbackSource,
            photoboothTools
        } = deps;

        let screensaverTimeout;
        let screensaverSwitchTimeout;
        let screensaverFlip = false;
        let screensaverLastGallerySource = '';

        const api = {};

        api.resolveSource = function resolveSource() {
            const base = environment.publicFolders.api;
            switch (screensaverMode) {
                case 'video':
                    return config.screensaver.video_source;
                case 'image':
                    return config.screensaver.image_source;
                case 'folder':
                    return base + '/randomImg.php?dir=' + encodeURIComponent('screensavers') + '&t=' + Date.now();
                case 'gallery': {
                    const anchors = $('#galimages a');
                    if (anchors.length) {
                        const randomIndex = Math.floor(Math.random() * anchors.length);
                        return $(anchors[randomIndex]).attr('href');
                    }
                    return base + '/randomImg.php?dir=' + 'data/images' + '&t=' + Date.now();
                }
                default:
                    return '';
            }
        };

        api.hide = function hide() {
            if (!overlay.length) {
                return;
            }
            overlay.removeClass('screensaver-overlay--active');
            overlay.css('display', 'none');
            startPage.removeClass('stage--screensaver');
            clearInterval(screensaverSwitchTimeout);

            if (videoEl.length) {
                const vid = videoEl.get(0);
                vid.pause();
                vid.currentTime = 0;
                videoEl.attr('src', '');
            }
            imageEl.hide().attr('src', '');
            textTop.text('').hide();
            textCenter.text('').hide();
            textBottom.text('').hide();
        };

        api.toggleGalleryText = function toggleGalleryText() {
            const screensaverText = config.screensaver.text;
            const eventText = [config.event.textLeft, config.event.textRight].filter(Boolean).join(' ').trim();
            const showEvent = screensaverMode === 'gallery';
            const hasScreensaver = !!screensaverText;
            const hasEvent = showEvent && !!eventText;

            const position = config.screensaver.text_position || 'center';
            const showTop = position === 'top-center';
            const showCenter = position === 'center';
            const showBottom = position === 'bottom-center';

            const resetSlots = () => {
                textTop.removeClass('screensaver-overlay__text--center').hide().text('');
                textCenter.hide().text('');
                textBottom.hide().text('');
            };

            const setSlot = (text) => {
                resetSlots();
                if (showCenter) {
                    textCenter.text(text).show();
                    return;
                }
                if (showTop) {
                    textTop.text(text).show();
                }
                if (showBottom) {
                    textBottom.text(text).show();
                }
            };

            if (hasScreensaver && hasEvent) {
                if (screensaverFlip) {
                    setSlot(screensaverText);
                    if (showTop && showBottom) {
                        textBottom.text(eventText).show();
                    } else if (showCenter || showTop) {
                        textBottom.text(eventText).show();
                    } else {
                        textTop.text(eventText).show();
                    }
                } else {
                    setSlot(eventText);
                    if (showTop && showBottom) {
                        textBottom.text(screensaverText).show();
                    } else if (showCenter || showTop) {
                        textBottom.text(screensaverText).show();
                    } else {
                        textTop.text(screensaverText).show();
                    }
                }
            } else {
                const singleText = hasScreensaver ? screensaverText : hasEvent ? eventText : '';
                if (singleText) {
                    setSlot(singleText);
                } else {
                    resetSlots();
                }
            }

            screensaverFlip = !screensaverFlip;
        };

        api.stepScreensaver = function stepScreensaver() {
            const mode = overlay.data('mode') || screensaverMode;
            photoboothTools.console.logDev('Screensaver: step in mode \'' + mode + '\'');

            let nextSource = api.resolveSource();
            if (!nextSource && mode === 'gallery') {
                nextSource = galleryFallbackSource();
            }

            if (mode === 'gallery' || mode === 'folder') {
                let guard = 5;
                while (nextSource === screensaverLastGallerySource && guard > 0) {
                    nextSource = api.resolveSource();
                    guard--;
                }
                screensaverLastGallerySource = nextSource;
            }

            photoboothTools.console.logDev('Screensaver: next source \'' + nextSource + '\'');
            if (nextSource) {
                if (mode === 'folder') {
                    overlay.css('background-image', nextSource ? `url(${urlSafe(nextSource)})` : 'none');
                } else if (mode === 'gallery') {
                    imageEl
                        .one('error', function () {
                            const fallback = galleryFallbackSource();
                            if (fallback && fallback !== nextSource) {
                                screensaverLastGallerySource = fallback;
                                $(this).attr('src', urlSafe(fallback));
                            }
                        })
                        .attr('src', urlSafe(nextSource))
                        .show();
                }
            }
            if (mode === 'gallery') {
                api.toggleGalleryText();
            }
        };

        api.show = function show() {
            if (!screensaverEnabled || !overlay.length) {
                return;
            }
            const mode = screensaverMode;
            if (!startPage.hasClass('stage--active')) {
                api.resetTimer();
                return;
            }

            if (mode === 'gallery') {
                overlay.addClass('screensaver-overlay--gallery');
            } else {
                overlay.removeClass('screensaver-overlay--gallery');
            }

            const source = api.resolveSource();
            let finalSource = source;
            if (!source) {
                finalSource = galleryFallbackSource();
            }
            if (!finalSource) {
                api.resetTimer();
                return;
            }
            if (mode === 'gallery') {
                screensaverLastGallerySource = finalSource;
            }

            if (mode === 'video') {
                overlay.css('background-image', 'none');
                videoEl.attr('src', urlSafe(finalSource));
                videoEl.show();
                const vid = videoEl.get(0);
                vid.play().catch((err) => {
                    photoboothTools.console.logDev('Idle video play failed: ' + err);
                });
                imageEl.hide();
                api.toggleGalleryText();
            } else if (mode === 'gallery') {
                videoEl.hide();
                overlay.css('background-image', 'none');
                imageEl
                    .one('error', function () {
                        const fallback = galleryFallbackSource();
                        if (fallback && fallback !== finalSource) {
                            screensaverLastGallerySource = fallback;
                            $(this).attr('src', urlSafe(fallback));
                        }
                    })
                    .attr('src', urlSafe(finalSource))
                    .show();
                api.toggleGalleryText();
            } else {
                videoEl.hide();
                imageEl.hide();
                api.toggleGalleryText();
                overlay.css('background-image', finalSource ? `url(${urlSafe(finalSource)})` : 'none');
                overlay.css('background-size', 'cover');
            }

            startPage.addClass('stage--screensaver');
            overlay.addClass('screensaver-overlay--active');
            overlay.css('display', 'flex');

            clearInterval(screensaverSwitchTimeout);
            if ((mode === 'folder' || mode === 'gallery') && screensaverSwitchMs > 0) {
                screensaverSwitchTimeout = setInterval(function nextIdleFrame() {
                    api.stepScreensaver();
                }, screensaverSwitchMs);
            }
        };

        api.resetTimer = function resetTimer() {
            if (!screensaverEnabled) {
                return;
            }
            clearTimeout(screensaverTimeout);
            api.hide();
            screensaverTimeout = setTimeout(api.show, screensaverTimeoutMs);
        };

        return api;
    };
})(window, jQuery);
