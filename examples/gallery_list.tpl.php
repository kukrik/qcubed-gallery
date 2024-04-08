<?php require('header.inc.php'); ?>

<?php // require(QCUBED_CONFIG_DIR . '/header.inc.php'); ?>

<?php $this->RenderBegin(); ?>

    <div class="page-container">
        <div class="page-content">
            <div class="row">
                <div class="col-md-12">
                    <?= _r($this->nav); ?>
                </div>
            </div>
        </div>
    </div>

<?php $this->RenderEnd(); ?>

<?php require('footer.inc.php'); ?>

<?php // require(QCUBED_CONFIG_DIR . '/footer.inc.php'); ?>