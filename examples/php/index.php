<?php

require_once('../qcubed.inc.php');

use QCubed\Plugin\UploadHandler;

$options = array(
    //'ImageResizeQuality' => 75, // Defult 85
    //'ImageResizeFunction' => 'imagecopyresized', // Default imagecopyresampled
    //'ImageResizeSharpen' => false, // Default true
    //'TempFolders' =>  ['thumbnail', 'medium', 'large'], // Please read the UploadHandler description and manual
    //'ResizeDimensions' => [320, 480, 1500], // Please read the UploadHandler description and manual
    //'DestinationPath' => null, // Please read the UploadHandler description and manual
    'AcceptFileTypes' => ['gif', 'jpg', 'jpeg', 'png', 'pdf', 'docx', 'mp4'], // Default null
    'DestinationPath' => !empty($_SESSION["name"]) ? $_SESSION["name"] : null, // Default null
    //'MaxFileSize' => 1024 * 1024 * 2 // 2 MB // Default null
    //'UploadExists' => 'overwrite', // increment || overwrite Default 'increment'
);

class CustomUploadHandler extends UploadHandler
{
    protected function uploadInfo()
    {
        parent::uploadInfo();

        if ($this->options['FileError'] == 0) {
            $obj = new Files();
            $obj->setName(basename($this->options['FileName']));
            $obj->setPath($this->getRelativePath($this->options['FileName']));
            $obj->setDescription(null);
            $obj->setExtension($this->getExtension($this->options['FileName']));
            $obj->setMimeType($this->getMimeType($this->options['FileName']));
            $obj->setSize($this->options['FileSize']);
            $obj->setMtime(filemtime($this->options['FileName']));
            $obj->setDimensions($this->getDimensions($this->options['FileName']));
            $obj->save(true);
        }
    }
}

$objHandler = new CustomUploadHandler($options);
















