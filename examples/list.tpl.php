<?php require(QCUBED_CONFIG_DIR . '/header.inc.php'); ?>

<style>
   body {font-size: 14px;}
    /*p, */footer {font-size: medium;}
    footer {margin-top: 35px;}
    footer span {color: #ffffff;}

   .gallery-archive ul {
       margin: 0;
       width: 100%;
       padding: 0;
       list-style-type: none !important;
   }
   .gallery-archive li {
       width: 100%;
       margin: 0;
       padding: 15px 20px;
   }
   .gallery-archive li div.date {
       width: 6%;
       display: block;
       float: left;
   }
   .gallery-archive li div.text {
       width: 94%;
       display: block;
       float: left;
   }
</style>

<?php $this->RenderBegin(); ?>

<div class="instructions">
    <h1 class="instruction_title" style="padding-bottom: 15px;">A simple example: List of galleries</h1>
    <p>Here we try to show what the gallery list looks like and how we can see the pictures.
        By default, the gallery list is empty, please go to the gallery manager (gallerymanager.php)
        and create an album and gallery and upload images.</p>
</div>
<div class="container">
    <div class="row" style="padding-top: 30px;">
        <div class="col-lg-12">
            <div class="gallery-archive">
                <ul>
                    <?= _r($this->dtrGalleryList); ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php $this->RenderEnd(); ?>

<?php require(QCUBED_CONFIG_DIR . '/footer.inc.php'); ?>