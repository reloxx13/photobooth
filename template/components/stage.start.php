<?php
use Photobooth\Utility\PathUtility;

?>
<!-- Start Page -->
<div class="stage stage--start rotarygroup" data-stage="start">
    <?php include PathUtility::getAbsolutePath('template/components/start.logo.php'); ?>
    <div class="stage-inner">
        <?php if ($config['event']['enabled'] || $config['start_screen']['title_visible']): ?>
            <div class="names<?= ($config['ui']['decore_lines']) ? ' names--decoration' : '' ?>">
                <div class="names-inner">
                    <?php if ($config['event']['enabled']): ?>
                        <h1>
                            <?= $config['event']['textLeft'] ?>
                            <i class="fa <?= $config['event']['symbol'] ?>" aria-hidden="true"></i>
                            <?= $config['event']['textRight'] ?>
                            <?php if ($config['start_screen']['title_visible']): ?>
                            <br>
                            <?= $config['start_screen']['title'] ?>
                            <?php endif; ?>
                        </h1>
                        <?php if ($config['start_screen']['subtitle_visible']): ?>
                            <h2><?= $config['start_screen']['subtitle'] ?></h2>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php if ($config['start_screen']['title_visible']): ?>
                        <h1><?= $config['start_screen']['title'] ?></h1>
                        <?php endif; ?>
                        <?php if ($config['start_screen']['subtitle_visible']): ?>
                        <h2><?= $config['start_screen']['subtitle'] ?></h2>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
<?php
if ($config['ui']['selfie_mode']) {
    include PathUtility::getAbsolutePath('template/components/selfieAction.php');
} else {
    include PathUtility::getAbsolutePath('template/components/actionBtn.php');
}
?>
    </div>
    <?php
    $idleMode = $config['idle']['mode'] ?? 'image';
    $idleImageSource = $config['idle']['image_source'] ?? '';
    $idleVideoSource = $config['idle']['video_source'] ?? '';

    $idleSource = '';
    if ($idleMode === 'image' && $idleImageSource) {
        $idleSource = PathUtility::getPublicPath($idleImageSource);
    } elseif ($idleMode === 'video' && $idleVideoSource) {
        $idleSource = PathUtility::getPublicPath($idleVideoSource);
    }
?>
    <div
        id="idle-overlay"
        class="idle-overlay"
        data-mode="<?= $idleMode ?>"
        data-source="<?= $idleSource ?>"
        style="display: none;"
    >
        <div id="idle-text-top" class="idle-overlay__text idle-overlay__text--top"></div>
        <img id="idle-image" class="idle-overlay__image" alt="idle">
        <video id="idle-video" loop muted playsinline></video>
        <div id="idle-text-bottom" class="idle-overlay__text idle-overlay__text--bottom"></div>
    </div>
    <?php include PathUtility::getAbsolutePath('template/components/github-corner.php'); ?>
</div>
