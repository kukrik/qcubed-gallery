<div class="form-horizontal">
    <div class="form-body">
        <div class="row" style="margin-bottom: -20px;">
            <div class="fileupload-buttonbar">
                <?= _r($this->btnAddFiles); ?>
                <?= _r($this->btnAllStart); ?>
                <?= _r($this->btnAllCancel); ?>
            </div>
            <div class="upload-wrapper hidden">
                <div id="alert-wrapper"></div>
                <div class="alert-multi-wrapper"></div>
                <?= _r($this->objUpload); ?>
                <div class="fileupload-donebar hidden">
                    <?= _r($this->btnDone); ?>
                </div>
            </div>
            <div class="fileinfo-wrapper">
                <div class="buttons">
                    <?= _r($this->txtTitle); ?>
                    <?= _r($this->txtAuthor); ?>
                    <?= _r($this->txtDescription); ?>
                    <?= _r($this->lstStatusGallery); ?>
                </div>
                <div class="buttons">
                    <?= _r($this->btnBackToList); ?>
                    <?= _r($this->btnListDelete); ?>
                    <?= _r($this->btnListUpdate); ?>
                </div>
                <div class="table-body">
                    <div class="col-md-1"><?= _r($this->lstItemsPerPage); ?></div>
                    <div class="col-md-7"><?= _r($this->txtTitleSlug); ?></div>
                    <div class="col-md-4" style="text-align: right; margin-bottom: 10px;"><?= _r($this->dtgGalleryList->Paginator); ?></div>
                    <?= _r($this->dtgGalleryList); ?>
                </div>
            </div>
        </div>
    </div>
</div>












