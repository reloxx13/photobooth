<?php

require_once '../lib/boot.php';

use Photobooth\Image;
use Photobooth\Enum\FolderEnum;
use Photobooth\Enum\ImageFilterEnum;
use Photobooth\Processor\ImageProcessor;
use Photobooth\Utility\ImageUtility;
use Photobooth\Utility\PathUtility;
use Photobooth\Service\ApplicationService;
use Photobooth\Service\LanguageService;
use Photobooth\Service\LoggerService;

$logger = LoggerService::getInstance()->getLogger('main');
$logger->debug(basename($_SERVER['PHP_SELF']));

$languageService = LanguageService::getInstance();
$errorMessage = '';
$processor = null;
$database = null;
try {
    $vars['fileName'] = date('Ymd_His') . '.jpg';
    $vars['style'] = 'photo';
    $vars['imageFilter'] = $config['filters']['defaults'];
    $vars['isCollage'] = false;
    $vars['editSingleCollage'] = false;
    $vars['isChroma'] = false;
    $vars['srcImages'] = [];
    $vars['srcImages'][] = $vars['fileName'];
    $vars['singleImageFile'] = $vars['fileName'];
    $vars['tmpFile'] = FolderEnum::TEST->absolute() . DIRECTORY_SEPARATOR . $vars['fileName'];
    $vars['resultFile'] = $vars['tmpFile'];

    $imageHandler = new Image();
    $imageHandler->debugLevel = $config['dev']['loglevel'];
    $imageHandler->imageModified = false;

    $imageResource = $imageHandler->createFromImage(ImageUtility::getDemoImages(1)[0]);
    if (!$imageResource) {
        throw new \Exception('Error creating image resource.');
    }
    if (class_exists('Photobooth\Processor\ImageProcessor')) {
        $processor = new ImageProcessor($imageHandler, $logger, $database, $vars, $config);
        if (method_exists($processor, 'preImageProcessing')) {
            [$imageHandler, $vars, $config, $imageResource] = $processor->preImageProcessing($imageHandler, $vars, $config, $imageResource);
        }
    }
    $imageHandler->framePath = PathUtility::getPublicPath($config['picture']['frame']);

    // apply filter
    if ($vars['imageFilter'] !== ImageFilterEnum::PLAIN) {
        try {
            ImageUtility::applyFilter($vars['imageFilter'], $imageResource);
            $imageHandler->imageModified = true;
        } catch (\Exception $e) {
            throw new \Exception('Error applying image filter.');
        }
    }

    if ($config['picture']['flip'] !== 'off') {
        try {
            if ($config['picture']['flip'] === 'flip-horizontal') {
                imageflip($imageResource, IMG_FLIP_HORIZONTAL);
            } elseif ($config['picture']['flip'] === 'flip-vertical') {
                imageflip($imageResource, IMG_FLIP_VERTICAL);
            } elseif ($config['picture']['flip'] === 'flip-both') {
                imageflip($imageResource, IMG_FLIP_BOTH);
            }
            $imageHandler->imageModified = true;
        } catch (\Exception $e) {
            throw new \Exception('Error flipping image.');
        }
    }

    if ((int)$config['picture']['rotation'] !== 0) {
        $imageResource = $imageHandler->rotateResizeImage(
            image: $imageResource,
            degrees: (int)$config['picture']['rotation'],
        );
        if (!$imageResource) {
            throw new \Exception('Error resizing resource.');
        }
    }

    if ($config['picture']['polaroid_effect']) {
        $imageHandler->polaroidRotation = $config['picture']['polaroid_rotation'];
        $imageResource = $imageHandler->effectPolaroid($imageResource);
        if (!$imageResource) {
            throw new \Exception('Error applying polaroid effect.');
        }
    }

    $drawTextOnFrame = $config['textonpicture']['enabled'];

    if ($config['picture']['take_frame']) {
        if ($drawTextOnFrame) {
            $imageHandler->fontSize        = $config['textonpicture']['font_size'];
            $imageHandler->fontRotation    = $config['textonpicture']['rotation'];
            $imageHandler->fontLocationX   = $config['textonpicture']['locationx'];
            $imageHandler->fontLocationY   = $config['textonpicture']['locationy'];
            $imageHandler->fontColor       = $config['textonpicture']['font_color'];
            $imageHandler->fontPath        = $config['textonpicture']['font'];
            $imageHandler->textLine1       = $config['textonpicture']['line1'];
            $imageHandler->textLine2       = $config['textonpicture']['line2'];
            $imageHandler->textLine3       = $config['textonpicture']['line3'];
            $imageHandler->textLineSpacing = $config['textonpicture']['linespace'];
        }
        $imageHandler->frameExtend = $config['picture']['extend_by_frame'];
        if ($config['picture']['extend_by_frame']) {
            $imageHandler->frameExtendLeft = $config['picture']['frame_left_percentage'];
            $imageHandler->frameExtendRight = $config['picture']['frame_right_percentage'];
            $imageHandler->frameExtendBottom = $config['picture']['frame_bottom_percentage'];
            $imageHandler->frameExtendTop = $config['picture']['frame_top_percentage'];
        }
        $imageResource = $imageHandler->applyFrame($imageResource);
        if (!$imageResource) {
            throw new \Exception('Error applying frame to image resource.');
        }
    }

    if ($processor !== null && $processor instanceof ImageProcessor && method_exists($processor, 'postImageProcessing')) {
        [$imageHandler, $vars, $config, $imageResource] = $processor->postImageProcessing($imageHandler, $vars, $config, $imageResource);
    }

    $lineMetrics = null;

    if ($config['textonpicture']['enabled'] && !$drawTextOnFrame) {
        $imageHandler->fontSize = $config['textonpicture']['font_size'];
        $imageHandler->fontRotation = $config['textonpicture']['rotation'];
        $imageHandler->fontLocationX = $config['textonpicture']['locationx'];
        $imageHandler->fontLocationY = $config['textonpicture']['locationy'];
        $imageHandler->fontColor = $config['textonpicture']['font_color'];
        $imageHandler->fontPath = $config['textonpicture']['font'];
        $imageHandler->textLine1 = $config['textonpicture']['line1'];
        $imageHandler->textLine2 = $config['textonpicture']['line2'];
        $imageHandler->textLine3 = $config['textonpicture']['line3'];
        $imageHandler->textLineSpacing = $config['textonpicture']['linespace'];

        // Debug: measure bbox heights for line spacing
        $bbox1 = imagettfbbox($imageHandler->fontSize, $imageHandler->fontRotation, Photobooth\Utility\FontUtility::getFontPath($imageHandler->fontPath), $imageHandler->textLine1 ?: 'Ag');
        $bbox2 = imagettfbbox($imageHandler->fontSize, $imageHandler->fontRotation, Photobooth\Utility\FontUtility::getFontPath($imageHandler->fontPath), $imageHandler->textLine2 ?: 'Ag');
        $h1 = isset($bbox1[1], $bbox1[5]) ? abs($bbox1[5] - $bbox1[1]) : 0;
        $h2 = isset($bbox2[1], $bbox2[5]) ? abs($bbox2[5] - $bbox2[1]) : $h1;
        $lineMetrics = [
            'bbox_height_line1' => $h1,
            'bbox_height_line2' => $h2,
            'configured_linespace' => $imageHandler->textLineSpacing,
            'gap_suggestion' => $imageHandler->textLineSpacing - $h1
        ];

        $imageResource = $imageHandler->applyText($imageResource);
        if (!$imageResource) {
            throw new \Exception('Error applying text to image resource.');
        }
    }

    if (!$imageHandler->saveJpeg($imageResource, $vars['tmpFile'])) {
        throw new \Exception('Failed to save image.');
    }
    unset($imageResource);

} catch (\Exception $e) {
    $errorMessage = $e->getMessage();
    $logger->error($errorMessage);
}

$pageTitle = 'Picture test - ' . ApplicationService::getInstance()->getTitle();
include PathUtility::getAbsolutePath('admin/components/head.admin.php');
include PathUtility::getAbsolutePath('admin/helper/index.php');
?>

<div class="w-full h-screen grid place-items-center absolute bg-brand-2 px-6 py-12 overflow-x-hidden overflow-y-auto">
    <div class="w-full flex items-center justify-center flex-col">
        <div class="w-full max-w-xl rounded-lg p-8 bg-white flex flex-col shadow-xl">
            <div class="w-full flex flex-col items-center justify-center text-2xl font-bold text-brand-1 mb-2">
                <?=$languageService->translate('pictureTest')?>
            </div>
            <?php
                    if (empty($errorMessage)) {
                        echo '<div class="border border-solid border-black">';
                        echo '<img src="' . PathUtility::getPublicPath($vars['tmpFile']) . '" alt="Test Image">';
                        echo '</div>';
                        if ($lineMetrics !== null) {
                            echo '<pre class="mt-4 text-xs bg-gray-100 p-3 border">' . htmlspecialchars(json_encode($lineMetrics, JSON_PRETTY_PRINT), ENT_QUOTES) . '</pre>';
                        }
                    } else {
                        echo '<div class="flex flex-col gap-2">';
                        echo '<div class="flex flex-col justify-between p-2 rounded-sm bg-red-300 text-red-800 border-2 border-red-800"><div class="col-span-1">' . $errorMessage . '</div></div>';
                        echo '</div>';
                    }
?>
        </div>

        <div class="w-full max-w-xl my-12 border-b border-solid border-white border-opacity-20">
        </div>

        <div class="w-full max-w-xl rounded-lg py-8 bg-white flex flex-col shadow-xl relative">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 px-4 ">
            <?php
echo getMenuBtn(PathUtility::getPublicPath('admin'), 'admin_panel', $config['icons']['admin']);
echo getMenuBtn(PathUtility::getPublicPath('test'), 'testMenu', $config['icons']['admin']);
?>
            </div>
        </div>
    </div>
</div>

<?php
include PathUtility::getAbsolutePath('admin/components/footer.scripts.php');
include PathUtility::getAbsolutePath('admin/components/footer.admin.php');
