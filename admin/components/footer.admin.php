    <div class="pageLoader w-full h-full fixed top-0 left-0 z-50 hidden place-items-center [&.isActive]:grid">
        <div class="w-full h-full left-0 top-0 z-10 absolute bg-black/60"></div>
        <div class="min-w-[115px] px-4 py-6 rounded-md bg-white shadow-md flex flex-col items-center justify-center relative z-20 text-center">
            <?= getLoader('sm') ?>
            <label class="text-xs text-brand-1 mt-4 font-bold"></label>
        </div>
    </div>

    <div id="text-positioner-modal" class="hidden fixed inset-0 z-50 items-center justify-center">
        <div class="absolute inset-0 bg-black/60"></div>
        <div class="relative z-10 bg-white rounded-xl shadow-2xl max-w-4xl w-[90%] h-[85%] flex flex-col">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
                <div>
                    <h2 class="text-lg font-bold text-brand-1">Position text on frame</h2>
                    <p class="text-xs text-gray-600">Drag the text to your desired spot on the frame, then save.</p>
                </div>
                <button type="button" class="close-modal text-2xl leading-none px-2 py-1 text-gray-500 hover:text-black">×</button>
            </div>
            <div class="flex-1 flex flex-col md:flex-row overflow-hidden">
                <div class="flex-1 bg-gray-50 flex flex-col items-center justify-center relative p-4">
                    <div class="relative max-h-full max-w-full border border-dashed border-gray-300 shadow-inner bg-white" id="text-positioner-stage">
                        <img class="block max-h-[70vh] max-w-full object-contain select-none" id="text-positioner-frame" alt="Frame preview" />
                        <div id="text-positioner-text-wrapper" class="absolute" style="left:0;top:0;transform-origin: top left;">
                            <div id="text-positioner-text" class="relative cursor-move select-none p-0 leading-none text-center text-white font-bold drop-shadow-lg whitespace-pre-line" style="left:0;top:0;transform-origin: top left; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0;">
                                Sample text
                            </div>
                        </div>
                    </div>
                    <div class="mt-2 text-xs bg-white/90 px-3 py-1 rounded shadow border border-gray-200 text-gray-700">
                        (X, Y): <span id="text-positioner-coords" class="font-mono">- , -</span>
                        <span id="text-positioner-line2" class="hidden font-mono"></span>
                        <span id="text-positioner-line3" class="hidden font-mono"></span>
                    </div>
                </div>
                <div class="w-full md:w-80 border-t md:border-t-0 md:border-l border-gray-200 p-4 space-y-3 z-20 bg-white/90 backdrop-blur">
                    <div class="grid grid-cols-1 gap-2">
                        <label class="text-xs font-semibold text-gray-600">Text lines</label>
                        <input id="text-positioner-line1" type="text" class="w-full border rounded px-2 py-1" />
                        <input id="text-positioner-line2" type="text" class="w-full border rounded px-2 py-1" />
                        <input id="text-positioner-line3" type="text" class="w-full border rounded px-2 py-1" />
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-gray-600" for="text-positioner-rotation">Rotation (°)</label>
                        <input id="text-positioner-rotation" type="number" class="w-full border rounded px-2 py-1" min="-180" max="180" step="1" />
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-gray-600" for="text-positioner-fontsize">Font size</label>
                        <input id="text-positioner-fontsize" type="number" class="w-full border rounded px-2 py-1" min="10" max="400" step="1" />
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-gray-600" for="text-positioner-linespace"><?= $languageService->translate('pictures:textonpicture_linespace') ?></label>
                        <input id="text-positioner-linespace" type="number" class="w-full border rounded px-2 py-1" min="0" max="800" step="1" />
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-gray-600" for="text-positioner-color">Font color</label>
                        <input id="text-positioner-color" type="color" class="w-full border rounded h-10" />
                    </div>
                    <div class="flex gap-3">
                        <button type="button" id="text-positioner-center-x" class="flex-1 border border-gray-300 rounded-full px-3 py-2 text-gray-700 hover:bg-gray-100">Center X</button>
                        <button type="button" id="text-positioner-center-y" class="flex-1 border border-gray-300 rounded-full px-3 py-2 text-gray-700 hover:bg-gray-100">Center Y</button>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" class="close-modal flex-1 border border-gray-300 rounded-full px-3 py-2 text-gray-700 hover:bg-gray-100">Cancel</button>
                        <button type="button" id="text-positioner-apply" class="flex-1 bg-brand-1 text-white rounded-full px-3 py-2 hover:bg-brand-2">Apply</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?= getToast() ?>
 </body>
</html>
