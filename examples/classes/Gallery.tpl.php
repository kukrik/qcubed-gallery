<div class="form-horizontal">
    <div class="row">
        <div class="upload-heading">
            <div class="fileupload-buttons">
                <?= _r($this->btnAddFiles); ?>
                <?= _r($this->btnAllStart); ?>
                <?= _r($this->btnAllCancel); ?>
                <?= _r($this->btnGalleriesBack); ?>
            </div>
            <div class="clearfix"></div>
            <div class="fileinfo-wrapper">
                <?= _r($this->txtTitle); ?>
                <?= _r($this->txtAuthor); ?>
                <?= _r($this->txtDescription); ?>
                <?= _r($this->lstStatusGallery); ?>
                <?= _r($this->btnListUpdate); ?>
                <?= _r($this->btnListDelete); ?>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
    <div id="alert-wrapper"></div>
    <div class="alert-multi-wrapper"></div>
    <div class="upload-wrapper">
        <?= _r($this->objUpload); ?>
        <div class="fileupload-donebar hidden">
            <?= _r($this->btnDone); ?>
        </div>


    </div>
    <div class="table-body table-gallery">
        <div class="row">
            <div class="col-md-1"><?= _r($this->lstItemsPerPage); ?></div>
            <div class="col-md-7"><?= _r($this->txtTitleSlug); ?></div>
            <div class="col-md-4" style="text-align: right; margin-bottom: 10px;"><?= _r($this->dtgGalleryList->Paginator); ?></div>
        </div>
        <?= _r($this->dtgGalleryList); ?>
    </div>
</div>










