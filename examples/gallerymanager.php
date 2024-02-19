<?php
require('qcubed.inc.php');

error_reporting(E_ALL); // Error engine - always ON!
ini_set('display_errors', TRUE); // Error display - OFF in production env or real server
ini_set('log_errors', TRUE); // Error logging

use QCubed\Project\Application;

if (Albums::countAll() !== 0) {
    foreach (Albums::loadAll() as $value) {
        if (strlen($value->Id)) {
            $objAlbum = Albums::load($value->Id);
            Application::redirect('gallery_list.php' . '?id=' . $objAlbum->Id);
        }
    }
} else {
    Application::redirect('album_create.php');
}
