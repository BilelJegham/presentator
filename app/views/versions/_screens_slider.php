<?php
use yii\helpers\Html;
use common\models\Version;
use common\models\Screen;
use common\models\ScreenComment;
use common\components\helpers\CFileHelper;

/**
 * $model                \common\models\Version
 * $activeScreenId       integer|null
 * $unreadCommentTargets array
 */

// set default values
$unreadCommentTargets = isset($unreadCommentTargets) ? $unreadCommentTargets : [];
$activeScreenId       = isset($activeScreenId) ? $activeScreenId : null;

if ($model->type == Version::TYPE_TABLET) {
    $type = 'tablet';
    $device = "ipad";    
} elseif ($model->type == Version::TYPE_MOBILE) {
    $type = 'mobile';
    $device = "iphone-x";
} else {
    $type = 'desktop';
    $device = "macbook";
}

$generalSlideStyles = [];
if ($model->subtype && !empty(Version::SUBTYPES[$model->subtype])) {
    $generalSlideStyles['width']  = Version::SUBTYPES[$model->subtype][0] . 'px';
    $generalSlideStyles['height'] = Version::SUBTYPES[$model->subtype][1] . 'px';
}

$totalScreens = count($model->screens);
$isGuest      = Yii::$app->user->isGuest;
?>

<div id="version_slider_<?= $model->id ?>"
    class="version-slider <?= $type ?>"
    data-version-id="<?= $model->id ?>"
>
    <div class="version-slider-panel control-panel">
        <nav class="panel-menu">
            <div class="ctrl-wrapper ctrl-left">
                <ul>
                    <li id="slider_prev_handle"
                        class="ctrl-item slider-nav-handle slider-prev"
                        data-cursor-tooltip="<?= Yii::t('app', 'Previous screen') ?>"
                    >
                        <i class="ion ion-md-arrow-back"></i>
                    </li>

                    <?php if ($totalScreens > 0): ?>
                        <li class="ctrl-item screen-info ctrl-text hint">
                            <span class="txt active-slide-title"></span>
                            <span class="slide-counter">
                                (<span class="active-slide-order"></span>&nbsp;<?= Yii::t('app', 'of') ?>&nbsp;<?= $totalScreens ?>)
                            </span>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="ctrl-wrapper ctrl-center">
                <ul>
                    <li id="panel_preview_handle" class="ctrl-item preview-handle active" data-cursor-tooltip="<?= Yii::t('app', 'Preview mode') ?>" data-cursor-tooltip-class="hotspots-mode-tooltip">
                        <i class="ion ion-md-eye"></i>
                    </li>
                    <li id="panel_hotspots_handle" class="ctrl-item hotspots-handle active" data-cursor-tooltip="<?= Yii::t('app', 'Hotspots mode') ?>" data-cursor-tooltip-class="hotspots-mode-tooltip">
                        <i class="ion ion-md-expand"></i>
                    </li>
                    <li id="panel_comments_handle" class="ctrl-item comments-handle" data-cursor-tooltip="<?= Yii::t('app', 'Comments mode') ?>" data-cursor-tooltip-class="comments-mode-tooltip">
                        <i class="ion ion-md-chatboxes"></i>
                        <span class="bubble comments-counter">0</span>
                    </li>
                    <li id="panel_settings_handle" class="ctrl-item settings-handle" data-cursor-tooltip="<?= Yii::t('app', 'Screen settings') ?>">
                        <i class="ion ion-md-settings"></i>
                    </li>
                <ul>
            </div>

            <div class="ctrl-wrapper ctrl-right">
                <ul>
                    <li class="ctrl-item resolved-comments-toggle-wrapper">
                        <div class="form-group">
                            <input type="checkbox" id="resolved_comments_toggle">
                            <label for="resolved_comments_toggle">
                                <span class="txt"><?= Yii::t('app', 'Show resolved') ?></span>
                                (<span class="resolved-comments-counter">0</span>)
                            </label>
                        </div>
                    </li>

                    <?php if ($model->type == Version::TYPE_DESKTOP): ?>
                        <li id="panel_toggle_screen_fit_handle"  class="ctrl-item toggle-screen-fit-handle" data-cursor-tooltip="<?= Yii::t('app', 'Fit to screen') ?>"><i class="ion ion-md-grid"></i></li>
                    <?php endif ?>

                    <li id="slider_next_handle"
                        class="ctrl-item slider-nav-handle slider-next"
                        data-cursor-tooltip="<?= Yii::t('app', 'Next screen') ?>"
                    >
                        <i class="ion ion-md-arrow-forward"></i>
                    </li>
                </ul>
            </div>
        </nav>
    </div>

    <div class="version-slider-content">
        <div class="close-handle-wrapper">
            <span class="close-handle close-screen-edit"><i class="ion ion-ios-close"></i></span>
        </div>

        <div class="slider-items">
            <?php foreach ($model->screens as $i => $screen): ?>
                <?php
                    if ($activeScreenId === null && $i === 0) {
                        $isActive = true;
                    } else {
                        $isActive = $activeScreenId !== null && $activeScreenId == $screen->id;
                    }

                    // alignment
                    if ($screen->alignment == Screen::ALIGNMENT_LEFT) {
                        $align = 'left';
                    } elseif ($screen->alignment == Screen::ALIGNMENT_RIGHT) {
                        $align = 'right';
                    } else {
                        $align = 'center';
                    }

                    // background color
                    $background = ($screen->background ? $screen->background : '#eff2f8');

                    // image dimensions
                    $originalWidth  = 0;
                    $originalHeight = 0;
                    if (file_exists(CFileHelper::getPathFromUrl($screen->imageUrl))) {
                        list($originalWidth, $originalHeight) = getimagesize(CFileHelper::getPathFromUrl($screen->imageUrl));
                    }

                    // scaling
                    $scaleFactor = $model->getScaleFactor($originalWidth);
                    $width       = $originalWidth / $scaleFactor;
                    $height      = $originalHeight / $scaleFactor;

                    // hotspots
                    $hotspots = $screen->hotspots ? json_decode($screen->hotspots, true) : [];
                ?>
                      <div class="marvel-device <?= $device?>">
                         <div class="top-bar"></div>
                        <div class="inner"></div>
                        <div class="overflow">
                            <div class="shadow"></div>
                        </div>
                        <div class="speaker"></div>
                        <div class="sensors"></div>
                        <div class="more-sensors"></div>
                        <div class="sleep"></div>
                        <div class="volume"></div>
                        <div class="camera"></div>
                        <div class="screen">
                     
                            <div class="slider-item screen <?= $isActive ? 'active' : ''?>"
                                data-original-scale-factor="<?= $scaleFactor ?>"
                                data-scale-factor="<?= $scaleFactor ?>"
                                data-screen-id="<?= $screen->id ?>"
                                data-alignment="<?= $align ?>"
                                data-title="<?= Html::encode($screen->title) ?>"
                                style="<?= Html::cssStyleFromArray(array_merge($generalSlideStyles, ['background' => $background])) ?>"
                            >
                            
                                <figure class="img-wrapper hotspot-layer-wrapper">
                                    <img class="img lazy-load hotspot-layer"
                                        alt="<?= Html::encode($screen->title) ?>"
                                        width="<?= $width ?>px"
                                        height="<?= $height ?>px"
                                        data-original-width="<?= $originalWidth ?>"
                                        data-original-height="<?= $originalHeight ?>"
                                        data-src="<?= $screen->imageUrl ?>"
                                        data-priority="<?= $isActive ? 'high' : 'medium' ?>"
                                    >

                                    <!-- Hotspots -->
                                    <div id="hotspots_wrapper">
                                        <?php foreach ($hotspots as $id => $spot): ?>
                                            <?= $this->render('_hotspot_item', [
                                                'id'           => $id,
                                                'spot'         => $spot,
                                                'scaleFactor'  => $scaleFactor,
                                                'showControls' => true,
                                                'maxX'         => $width,
                                                'maxY'         => $height,
                                            ]); ?>
                                        <?php endforeach ?>
                                    </div>

                                    <!-- Comment targets -->
                                    <div id="comment_targets_list" class="comment-targets-list">
                                        <?php foreach ($screen->screenComments as $comment): ?>
                                            <?php if (!$comment->replyTo): // we make use of the already eager loaded screenComments relation ?>
                                                <?php
                                                    $isResolved = $comment->status == ScreenComment::STATUS_RESOLVED;
                                                    $isUnread   = in_array($comment->id, $unreadCommentTargets);
                                                ?>
                                                <?= $this->render('_comment_item', [
                                                    'comment'     => $comment,
                                                    'scaleFactor' => $scaleFactor,
                                                    'isUnread'    => $isUnread,
                                                    'isResolved'  => $isResolved,
                                                    'maxX'        => $width,
                                                    'maxY'        => $height,
                                                ]); ?>
                                            <?php endif ?>
                                        <?php endforeach ?>
                                    </div>
                                </figure>
                            </div>
                            <div class="home"></div>
                     <div class="bottom-bar"></div>
                    </div>
                </div>
            <?php endforeach ?>
        </div>

        <?= $this->render('_hotspots_popover', ['screens' => $model->screens]); ?>

        <?= $this->render('_comments_popover'); ?>

        <div id="hotspots_bulk_panel" class="fixed-panel hotspots-bulk-panel" style="display: none;">
            <span class="close hotspots-bulk-reset"><i class="ion ion-md-close"></i></span>

            <div class="table-wrapper">
                <div class="table-cell min-width">
                    <button id="hotspots_bulk_screens_select" type="button" class="btn btn-sm btn-primary btn-ghost hotspots-bulk-screens-btn">
                        <?= Yii::t('app', 'Duplicate on screen') ?>
                        <i class="ion ion-md-arrow-dropdown m-l-5"></i>

                        <div id="hotspots_bulk_screens_popover" class="popover hotspots-bulk-screens-popover bottom-left">
                            <div class="popover-thumbs-wrapper">
                                <?php foreach ($model->screens as $screen): ?>
                                    <div class="box popover-thumb" data-screen-id="<?= $screen->id ?>" data-cursor-tooltip="<?= Html::encode($screen->title) ?>">
                                        <div class="content">
                                            <figure class="featured">
                                                <img class="img lazy-load"
                                                    alt="<?= Html::encode($screen->title) ?>"
                                                    data-src="<?= $screen->getThumbUrl('small') ?>"
                                                    data-priority="low"
                                                >
                                            </figure>
                                        </div>
                                    </div>
                                <?php endforeach ?>
                            </div>
                        </div>
                    </button>
                </div>
                <div class="table-cell min-width p-l-15 p-r-15"><?= Yii::t('app', 'or') ?></div>
                <div class="table-cell min-width">
                    <a href="#" id="hotspots_bulk_delete" class="danger-link"><?= Yii::t('app', 'Delete selected') ?></a>
                </div>
                <div class="table-cell text-right">
                    <a href="#" class="hotspots-bulk-reset"><?= Yii::t('app', 'Reset selection') ?></a>
                </div>
            </div>
        </div>
    </div>
</div>
