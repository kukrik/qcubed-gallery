<?php
$url = (isset($_SERVER['HTTPS']) ? "https" : "http") . '://' . $_SERVER['HTTP_HOST'] . QCUBED_URL_PREFIX;
?>
<?php if ($_ITEM->Status == 1) { ?>
    <li>
        <div class="date"><?php _p($_ITEM->PostDate->qFormat('DD.MM.YYYY') ); ?></div>
        <div class="text"><a href=" <?php _p('show_gallery.php?id=' . $_ITEM->Id); ?> "><?php _p($_ITEM->Title); ?></a> <?php _p('(' . Galleries::countByListGalleryId($_ITEM->Id, 2) . t(' pictures') . ')'); ?></div>
    </li>
<?php } ?>
