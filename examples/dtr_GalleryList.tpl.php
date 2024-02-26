<?php
$url = (isset($_SERVER['HTTPS']) ? "https" : "http") . '://' . $_SERVER['HTTP_HOST'] . QCUBED_URL_PREFIX;
?>
<li>
    <div class="date"><?php _p($_ITEM->PostDate->qFormat('DD.MM.YYYY') ); ?></div>
    <div class="text"><a href=" <?php _p($url . $_ITEM->Path); ?> "><?php _p($_ITEM->Title); ?></a></div>
</li>
