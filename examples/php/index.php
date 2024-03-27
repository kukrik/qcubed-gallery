<?php

require_once('../qcubed.inc.php');

use QCubed\Plugin\FileHandler;

$options = array(
    //'ImageResizeQuality' => 75, // Defult 85
    //'ImageResizeFunction' => 'imagecopyresized', // Default imagecopyresampled
    //'ImageResizeSharpen' => false, // Default true
    //'TempFolders' =>  ['thumbnail', 'medium', 'large'], // Please read the FileHandler description and manual
    //'ResizeDimensions' => [320, 480, 1500], // Please read the FileHandler description and manual
    //'DestinationPath' => null, // Please read the FileHandler description and manual
    'AcceptFileTypes' => ['jpg', 'jpeg', 'bmp', 'png', 'webp', 'gif'], // Default null
    'DestinationPath' => !empty($_SESSION["path"]) ? $_SESSION["path"] : null, // Default null
    //'MaxFileSize' => 1024 * 1024 * 2 // 2 MB // Default null
    //'UploadExists' => 'overwrite', // increment || overwrite Default 'increment'
);

class CustomUploadHandler extends FileHandler
{
    protected function uploadInfo()
    {
        parent::uploadInfo();

        if ($this->options['FileError'] == 0) {
            $objFile = new Files();
            $objFile->setFolderId($_SESSION['folderId']);
            $objFile->setName(basename($this->options['FileName']));
            $objFile->setPath($this->getRelativePath($this->options['FileName']));
            $objFile->setType("file");
            $objFile->setDescription(null);
            $objFile->setExtension($this->getExtension($this->options['FileName']));
            $objFile->setMimeType($this->getMimeType($this->options['FileName']));
            $objFile->setSize($this->options['FileSize']);
            $objFile->setMtime(filemtime($this->options['FileName']));
            $objFile->setDimensions($this->getDimensions($this->options['FileName']));
            $objFile->setWidth($this->getImageWidth($this->options['FileName']));
            $objFile->setHeight($this->getImageHeight($this->options['FileName']));
            $objFile->setActivitiesLocked(1);
            $objFile->save(true);

            $objGallery = new Galleries();
            $objGallery->setAlbumId($_SESSION['albumId']);
            $objGallery->setFolderId($_SESSION['folderId']);
            $objGallery->setListId($_SESSION['listId']);
            $objGallery->setFileId($objFile->getId());
            $objGallery->setName(basename($this->options['FileName']));
            $objGallery->setPath($this->getRelativePath($this->options['FileName']));
            $objGallery->setStatus(1);
            $objGallery->setPostDate(QCubed\QDateTime::Now());
            $objGallery->save();
        }

        $objFolder = Folders::loadById($_SESSION['folderId']);
        if ($objFolder->getLockedFile() == 0) {
            $objFolder->setLockedFile(1);
            $objFolder->save();
        }
    }
}

$objHandler = new CustomUploadHandler($options);
















