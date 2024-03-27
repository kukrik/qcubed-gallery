<div class="form-horizontal">
    <div class="row">
        <div class="buttons-heading">
            <?= _r($this->btnAddAlbum); ?>
            <?= _r($this->txtTitle); ?>
            <?= _r($this->btnSave); ?>
            <?= _r($this->btnCancel); ?>
            <div class="clearfix"></div>
        </div>
    </div>
    <div class="table-body">
        <div class="row">
            <div class="col-md-1"><?= _r($this->lstItemsPerPage); ?></div>
            <div class="col-md-3" style="margin-top: -7px;"><?= _r($this->txtFilter); ?></div>
            <div class="col-md-8" style="text-align: right; margin-bottom: 15px;"><?= _r($this->dtgGalleries->Paginator); ?></div>
        </div>
        <?= _r($this->dtgGalleries); ?>
    </div>
</div>










