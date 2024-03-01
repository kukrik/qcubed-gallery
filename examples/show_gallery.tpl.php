<?php require(QCUBED_CONFIG_DIR . '/header.inc.php'); ?>
    <style>
        body, html {
            /*background-color: #ebe7e2;*/
            width: 100%;
            font-family: 'Open Sans', sans-serif;
            font-size: 12px;
            color: #000;
        }
        .nGY2 .toolbar .label .title {
            font-size: 14px;
            font-weight: normal;
            line-height: 1.5;
            margin: auto;
            vertical-align: middle;
            overflow: hidden;
        }
        .nGY2 .toolbar .label .description {
            color: #ffffff !important;
            font-size: 14px;
            font-weight: normal;
            line-height: 1.5;
            display: table-row;
            vertical-align: middle;
            overflow: hidden;
        }
        .nanogallery_viewertheme_dark_my_nanogallery .nGY2Viewer .toolbarBackground {
            /*background: rgba(0, 0, 0, 0.50) !important;*/
        }
    </style>

<?php $this->RenderBegin(); ?>

<div class="instructions">
    <h1 class="instruction_title" style="padding-bottom: 15px;">A simple example: Gallery</h1>
</div>
<div class="container" style="width: 70%">
    <div class="row" style="padding-top: 30px;">
        <h3><?= _r($this->lblTitle); ?></h3>
        <div style="margin-bottom: 25px;">
            <?= _r($this->objGallery); ?>
        </div>
        <p><?= _r($this->btnBack); ?></p>
    </div>
</div>

<?php $this->RenderEnd(); ?>

<?php require(QCUBED_CONFIG_DIR . '/footer.inc.php'); ?>