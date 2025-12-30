/* globals photoboothTools */
$(function () {
    // adminRangeInput
    $(document).on('input', '.adminRangeInput', function () {
        document.querySelector('#' + this.name.replace('[', '\\[').replace(']', '\\]') + '-value span').innerHTML =
            this.value;
    });

    // Localization of toggle button text
    $('.adminCheckbox').on('click', function () {
        if ($(this).find('input').is(':checked')) {
            $('.adminCheckbox-true', this).removeClass('hidden');
            $('.adminCheckbox-false', this).addClass('hidden');
        } else {
            $('.adminCheckbox-true', this).addClass('hidden');
            $('.adminCheckbox-false', this).removeClass('hidden');
        }
    });

    // Text positioner
    const frameImg = $('#text-positioner-frame');
    const textBlock = $('#text-positioner-text');
    const textWrapper = $('#text-positioner-text-wrapper');
    const modal = $('#text-positioner-modal');
    const coordsLabel = $('#text-positioner-coords');
    const rotationInput = $('#text-positioner-rotation');
    const fontSizeInput = $('#text-positioner-fontsize');
    const lineSpaceInput = $('#text-positioner-linespace');
    const colorInput = $('#text-positioner-color');
    const lineInputs = [$('#text-positioner-line1'), $('#text-positioner-line2'), $('#text-positioner-line3')];
    let renderWidth = 0;
    let renderHeight = 0;
    let naturalWidth = 0;
    let naturalHeight = 0;
    let fontFaceStyle = null;
    let imgOffset = { left: 0, top: 0 }; // relative to stage
    let imgOffsetAbs = { left: 0, top: 0 }; // absolute page coords

    // Opens the modal using data attributes on the CTA button
    $(document).on('click', '.open-text-positioner', function(e) {
        e.preventDefault();

        const frameUrl = $(this).data('frame');
        const line1 = $('input[name="textonpicture[line1]"]').val();
        const line2 = $('input[name="textonpicture[line2]"]').val();
        const line3 = $('input[name="textonpicture[line3]"]').val();
        const x = parseInt($('input[name="textonpicture[locationx]"]').val(), 10) || 0;
        const y = parseInt($('input[name="textonpicture[locationy]"]').val(), 10) || 0;
        const rotation = parseInt($('input[name="textonpicture[rotation]"]').val(), 10) || 0;
        const fontSize = parseInt($('input[name="textonpicture[font_size]"]').val(), 10) || 80;
        const lineSpace = parseInt($('input[name="textonpicture[linespace]"]').val(), 10) || 90;
        const color = $('input[name="textonpicture[font_color]"]').val() || '#ffffff';
        const fontPath = $(this).data('font') || $('input[name="textonpicture[font]"]').val();

        const lines = [line1, line2, line3];
        renderLines(lines);
        lineInputs[0].val(line1);
        lineInputs[1].val(line2);
        lineInputs[2].val(line3);

        rotationInput.val(rotation);
        fontSizeInput.val(fontSize);
        lineSpaceInput.val(lineSpace);
        colorInput.val(color);
        applyFont(fontPath);

        frameImg.off('load.positioner').on('load.positioner', function() {
            naturalWidth = frameImg[0].naturalWidth;
            naturalHeight = frameImg[0].naturalHeight;

            refreshFrameMetrics();

            updateStyles(fontSize, rotation, color, lineSpace);

            const displayCoords = toDisplayCoords(x, y);
            placeText(displayCoords.x, displayCoords.y);
        });

        frameImg.attr('src', frameUrl);
        if (frameImg[0].complete) {
            frameImg.trigger('load');
        }

        modal.removeClass('hidden').addClass('flex');
    });

    $(window).on('resize', function() {
        if (!modal.hasClass('hidden')) {
            refreshFrameMetrics();
            updateStyles(fontSizeInput.val(), rotationInput.val(), colorInput.val(), lineSpaceInput.val());
        }
    });

    // Drag handling
    let isDragging = false;
    let dragOffset = { x: 0, y: 0 };

    textBlock.on('mousedown touchstart', function(e) {
        e.preventDefault();
        isDragging = true;
        const pageX = e.pageX || (e.originalEvent.touches && e.originalEvent.touches[0].pageX);
        const pageY = e.pageY || (e.originalEvent.touches && e.originalEvent.touches[0].pageY);
        const currentX = parseFloat(textBlock.data('x')) || 0;
        const currentY = parseFloat(textBlock.data('y')) || 0;
        dragOffset = {
            x: pageX - imgOffsetAbs.left - currentX,
            y: pageY - imgOffsetAbs.top - currentY
        };
    });

    $(document).on('mousemove touchmove', function(e) {
        if (!isDragging) {
            return;
        }
        const pageX = e.pageX || (e.originalEvent.touches && e.originalEvent.touches[0].pageX);
        const pageY = e.pageY || (e.originalEvent.touches && e.originalEvent.touches[0].pageY);
        const localX = pageX - imgOffsetAbs.left - dragOffset.x;
        const localY = pageY - imgOffsetAbs.top - dragOffset.y;

        const clampedX = Math.max(0, Math.min(renderWidth, localX));
        const clampedY = Math.max(0, Math.min(renderHeight, localY));

        placeText(clampedX, clampedY);
    });

    $(document).on('mouseup touchend', function() {
        isDragging = false;
    });

    // Input adjustments
    rotationInput.on('input', function() {
        updateStyles(fontSizeInput.val(), this.value, colorInput.val(), lineSpaceInput.val());
    });

    fontSizeInput.on('input', function() {
        updateStyles(this.value, rotationInput.val(), colorInput.val(), lineSpaceInput.val());
    });

    lineSpaceInput.on('input', function() {
        updateStyles(fontSizeInput.val(), rotationInput.val(), colorInput.val(), this.value);
    });

    colorInput.on('input', function() {
        updateStyles(fontSizeInput.val(), rotationInput.val(), this.value, lineSpaceInput.val());
    });

    lineInputs.forEach((input, idx) => {
        input.on('input', function() {
            const lines = lineInputs.map((el) => el.val());
            renderLines(lines);
            $('input[name="textonpicture[line' + (idx + 1) + ']"]').val(this.value);
        });
    });

    $('#text-positioner-center-x').on('click', function() {
        if (!renderWidth) {
            return;
        }
        const { scaleX } = getScale();
        const textW = textBlock.outerWidth() * scaleX;
        const targetX = Math.max(0, (renderWidth - textW) / 2);
        const currentY = parseFloat(textBlock.data('y')) || 0;
        placeText(targetX, currentY);
    });

    $('#text-positioner-center-y').on('click', function() {
        if (!renderHeight) {
            return;
        }
        const { scaleY } = getScale();
        const textH = textBlock.outerHeight() * scaleY;
        const targetY = Math.max(0, (renderHeight - textH) / 2);
        const currentX = parseFloat(textBlock.data('x')) || 0;
        placeText(currentX, targetY);
    });

    // Apply
    $('#text-positioner-apply').on('click', function() {
        const displayX = parseFloat(textBlock.data('x')) || 0;
        const displayY = parseFloat(textBlock.data('y')) || 0;
        const realCoords = toRealCoords(displayX, displayY);

        $('input[name="textonpicture[locationx]"]').val(Math.round(realCoords.x));
        $('input[name="textonpicture[locationy]"]').val(Math.round(realCoords.y));
        $('input[name="textonpicture[rotation]"]').val(parseInt(rotationInput.val(), 10) || 0);
        $('input[name="textonpicture[font_size]"]').val(parseInt(fontSizeInput.val(), 10) || 80);
        $('input[name="textonpicture[linespace]"]').val(parseInt(lineSpaceInput.val(), 10) || 90);
        $('input[name="textonpicture[font_color]"]').val(colorInput.val());

        closeModal();
    });

    // Close buttons & backdrop
    $(document).on('click', '.close-modal', function() {
        closeModal();
    });

    $(document).on('click', '#text-positioner-modal', function(e) {
        if (e.target.id === 'text-positioner-modal') {
            closeModal();
        }
    });

    function placeText(x, y) {
        textWrapper.css({
            left: imgOffset.left + x + 'px',
            top: imgOffset.top + y + 'px'
        });
        textBlock.data('x', x);
        textBlock.data('y', y);
        const real = toRealCoords(x, y);
        coordsLabel.text(Math.round(real.x) + ', ' + Math.round(real.y));
    }

    function updateStyles(size, rotation, color, linespace) {
        const { scaleX, scaleY } = getScale();
        const rawSize = parseFloat(size) || 0;
        const rawLineSpace = parseFloat(linespace) || 0;
        const displaySize = rawSize; // raw pixels; wrapper handles scale

        textWrapper.css({
            transform: 'rotate(' + rotation + 'deg) scale(' + scaleX + ',' + scaleY + ')',
            transformOrigin: 'top left'
        });

        const lines = textBlock.children('span');
        lines.css({
            display: 'block',
            fontSize: displaySize + 'px',
            color: color,
            lineHeight: 1,
            marginTop: 0
        });

        // Align with PHP: the top-left anchor of each next line is offset by `textLineSpacing`.
        // Because the DOM flows lines one after another, we mimic this by subtracting the
        // rendered height of the previous line from the configured spacing.
        let prevHeight = 0;
        lines.each(function(idx) {
            const h = $(this).outerHeight();
            if (idx > 0) {
                const gap = Math.max(0, rawLineSpace - prevHeight);
                $(this).css('margin-top', gap + 'px');
            }
            prevHeight = h;
        });
    }

    function getScale() {
        const nw = frameImg[0]?.naturalWidth || 0;
        const nh = frameImg[0]?.naturalHeight || 0;
        if (!nw || !nh) {
            return { scaleX: 1, scaleY: 1 };
        }
        const currentW = frameImg.width() || nw;
        const currentH = frameImg.height() || nh;
        const scaleX = currentW / nw;
        const scaleY = currentH / nh;
        return { scaleX, scaleY };
    }

    function renderLines(lines) {
        const allEmpty = lines.every((l) => !l);
        const content = allEmpty ? ['Sample text'] : lines;
        textBlock.empty();
        content.forEach((line) => {
            const span = $('<span>').text(line || '');
            textBlock.append(span);
        });
    }

    function closeModal() {
        modal.addClass('hidden').removeClass('flex');
    }

    function toRealCoords(displayX, displayY) {
        if (!naturalWidth || !naturalHeight || !renderWidth || !renderHeight) {
            return { x: displayX, y: displayY };
        }
        return {
            x: (displayX / renderWidth) * naturalWidth,
            y: (displayY / renderHeight) * naturalHeight
        };
    }

    function toDisplayCoords(realX, realY) {
        if (!naturalWidth || !naturalHeight || !renderWidth || !renderHeight) {
            return { x: realX, y: realY };
        }
        return {
            x: (realX / naturalWidth) * renderWidth,
            y: (realY / naturalHeight) * renderHeight
        };
    }

    function applyFont(fontPath) {
        if (!fontPath) {
            textBlock.css('font-family', '');
            return;
        }

        if (fontFaceStyle) {
            fontFaceStyle.remove();
        }

        fontFaceStyle = document.createElement('style');
        fontFaceStyle.id = 'text-positioner-font-face';
        fontFaceStyle.innerHTML = `
            @font-face {
                font-family: 'TextPositionerFont';
                src: url('${fontPath}');
            }
        `;
        document.head.appendChild(fontFaceStyle);
        textBlock.css('font-family', 'TextPositionerFont, sans-serif');
    }

    function refreshFrameMetrics() {
        if (!frameImg[0] || !frameImg[0].naturalWidth || !frameImg[0].naturalHeight) {
            return;
        }

        const prevRenderWidth = renderWidth || frameImg[0].naturalWidth;
        const prevRenderHeight = renderHeight || frameImg[0].naturalHeight;

        // Let CSS (max-height/max-width) decide the display size; use the actual rendered size for scaling.
        frameImg.css({ width: '', height: '' });
        renderWidth = frameImg.width() || frameImg[0].naturalWidth;
        renderHeight = frameImg.height() || frameImg[0].naturalHeight;

        const stageOffsetAbs = $('#text-positioner-stage').offset();
        imgOffsetAbs = frameImg.offset();
        imgOffset = {
            left: imgOffsetAbs.left - stageOffsetAbs.left,
            top: imgOffsetAbs.top - stageOffsetAbs.top
        };

        // Keep current placement stable when the frame resizes (e.g., window resize).
        const currentDisplayX = parseFloat(textBlock.data('x')) || 0;
        const currentDisplayY = parseFloat(textBlock.data('y')) || 0;
        const currentReal = {
            x: (currentDisplayX / prevRenderWidth) * frameImg[0].naturalWidth,
            y: (currentDisplayY / prevRenderHeight) * frameImg[0].naturalHeight
        };
        const refreshedDisplay = toDisplayCoords(currentReal.x, currentReal.y);
        textBlock.data('x', refreshedDisplay.x);
        textBlock.data('y', refreshedDisplay.y);
        placeText(refreshedDisplay.x, refreshedDisplay.y);
    }
});

// eslint-disable-next-line no-unused-vars
const shellCommand = function ($mode, $filename = '') {
    const command = {
        mode: $mode,
        filename: $filename
    };

    photoboothTools.console.log('Run' + $mode);

    jQuery
        .post('../api/shellCommand.php', command)
        .done(function (result) {
            photoboothTools.console.log($mode, 'result: ', result);
        })
        .fail(function (xhr, status, result) {
            photoboothTools.console.log($mode, 'result: ', result);
        });
};
