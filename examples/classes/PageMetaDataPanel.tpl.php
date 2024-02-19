<h3 class="vauu-title-3"><?php _p('Specific metadata of this page'); ?></h3>
<div class="form-horizontal">
    <div class="form-body">
        <div class="row">
            <div class="col-md-offset-3 col-md-9">
                <?= _r($this->lblInfo); ?>
            </div>
        </div>
        <div class="form-group">
            <?= _r($this->lblKeywords); ?>
            <div class="col-md-9">
                <?= _r($this->txtKeywords); ?>
            </div>
        </div>
        <div class="form-group">
            <?= _r($this->lblDescription); ?>
            <div class="col-md-9">
                <?= _r($this->txtDescription); ?>
            </div>
        </div>
        <div class="form-group">
            <?= _r($this->lblAuthor); ?>
            <div class="col-md-9">
                <?= _r($this->txtAuthor); ?>
            </div>
        </div>
        <div class="form-actions fluid">
            <div class="col-md-offset-3 col-md-9">
                <?= _r($this->btnSave); ?>
                <?= _r($this->btnSaving); ?>
                <?= _r($this->btnDelete); ?>
                <?= _r($this->btnCancel); ?>
            </div>
        </div>
    </div>
</div>