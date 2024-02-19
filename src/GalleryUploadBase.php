<?php

namespace QCubed\Plugin;

use QCubed\Exception\Caller;
use QCubed\Exception\InvalidCast;
use QCubed\Folder;
use QCubed\Type;


/**
 * Class GalleryUpload
 *
 * Note: the "upload" folder must already exist in /project/assets/ and this folder has 777 permissions.
 *
 * @property string $RootPath Default root path APP_UPLOADS_DIR. You may change the location of the file repository
 *                             at your own risk.
 * @property string $RootUrl Default root url APP_UPLOADS_URL. If necessary, the root url must be specified.
 *
 * Note: If you want to change TempPath, TempUrl and StoragePath, you have to rewrite the setup() function in the FileUpload class.
 * This class is located in the /project/includes/plugins folder.
 *
 * @property string $TempPath = Default temp path APP_UPLOADS_TEMP_DIR. If necessary, the temp dir must be specified.
 * @property string $TempUrl Default temp url APP_UPLOADS_TEMP_URL. If necessary, the temp url must be specified.
 * @property string $StoragePath Default dir named _files. This dir is generated together with the dirs
 *                               /thumbnail,  /medium,  /large when the corresponding page is opened for the first time.
 * @property string $FullStoragePath Please see the setup() function! Can only be changed in this function.
 *
 * @package QCubed\Plugin
 */

class GalleryUploadBase extends GalleryUploadBaseGen
{
    /** @var string[] */
    protected $strFormAttributes = array('enctype' => 'multipart/form-data');
    /** @var string */
    protected $strRootPath = APP_UPLOADS_DIR;
    /** @var string */
    protected $strRootUrl = APP_UPLOADS_URL;
    /** @var string */
    protected $strTempPath = APP_UPLOADS_TEMP_DIR;
    /** @var string */
    protected $strTempUrl = APP_UPLOADS_TEMP_URL;
    /** @var string */
    protected $strStoragePath = '_files';
    /** @var string */
    protected $strFullStoragePath;

    /**
     * @param $objParentObject
     * @param $strControlId
     * @throws Caller
     */
    public function __construct($objParentObject, $strControlId = null)
    {
        parent::__construct($objParentObject, $strControlId);

        $this->registerFiles();
        $this->setup();
    }

    /**
     * @throws Caller
     */
    protected function registerFiles() {
        $this->AddJavascriptFile(QCUBED_GALLERY_ASSETS_URL . "/js/qcubed.galleryupload.js");
        $this->addCssFile(QCUBED_GALLERY_ASSETS_URL . "/css/qcubed.uploadHandler.css");
        $this->AddCssFile(QCUBED_BOOTSTRAP_CSS); // make sure they know
    }

    /**
     * Returns the HTML for the control.
     *
     * @return string
     */
    protected function getControlHtml()
    {
        $strHtml = _nl('<div class="files"></div>');
        return $strHtml;
    }

    /**
     * @throws Caller
     */
    protected function setup()
    {
        $this->strFullStoragePath = $this->strTempPath . '/' . $this->strStoragePath;
        $strCreateDirs = ['/thumbnail', '/medium', '/large'];

        if (!is_dir($this->strRootPath)) {
            Folder::makeDirectory(QCUBED_PROJECT_DIR . '/assets/upload', 0777);
        }

        if (!is_dir($this->strFullStoragePath)) {
            Folder::makeDirectory($this->strFullStoragePath, 0777);
            foreach ($strCreateDirs as $strCreateDir) {
                Folder::makeDirectory($this->strFullStoragePath . $strCreateDir, 0777);
            }
        }

        if($_SERVER['REQUEST_METHOD'] == "POST") {exit;} // prevent loading entire page in the echo

        $isHttps = isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1)
            || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https';

        /** clean and check $strRootPath */
        $this->strRootPath = rtrim($this->strRootPath, '\\/');
        $this->strRootPath = str_replace('\\', '/', $this->strRootPath);

        $permissions = fileperms($this->strRootPath);
        $permissions = substr(sprintf('%o', $permissions), -4);
        if (!Folder::isWritable($this->strRootPath)) {
            throw new Caller('Root path "' . $this->strRootPath . '" not writable or not found and
            it has the following directory permissions: ' . $permissions . '. Please set 0755 or 0777 permissions to the
            directory or create a directory "upload" into the location "/project/assets" and grant permissions 0755 or 0777!');
        };

        if (!Folder::isWritable($this->strRootPath)) {
            throw new Caller('Root path "' . $this->strRootPath . '" not writable or not found and
            it has the following directory permissions: ' . $permissions . '. Please set 0755 or 0777 permissions to the
            directory or create a directory "upload" into the location "/project/assets" and grant permissions 0755 or 0777!');
        };

        if (!Folder::isWritable($this->strFullStoragePath) && isset($this->strFullStoragePath)) {
            throw new Caller('Storage path "' . $this->strTempPath . '/' . $this->strStoragePath .
                '" not writable or not found." Please set permissions to the 0777 directory "/project/tmp", the "_files" folder and subfolders!');
        }

        clearstatcache();
        /** clean $strRootUrl */
        $this->strRootUrl = $this->cleanPath($this->strRootUrl);
        /** clean $strTempUrl */
        $this->strTempUrl = $this->cleanPath($this->strTempUrl);
        /** Server hostname. Can set manually if wrong. Don't change! */
        $strHttpHost = $_SERVER['HTTP_HOST'];

        $this->strRootUrl = $isHttps ? 'https' : 'http' . '://' . $strHttpHost . (!empty($this->strRootUrl) ? '/' . $this->strRootUrl : '');
        $this->strTempUrl = $isHttps ? 'https' : 'http' . '://' . $strHttpHost . (!empty($this->strTempUrl) ? '/' . $this->strTempUrl : '');
    }
    /**
     * Clean path
     * @param string $path
     * @return string
     */
    protected function cleanPath($path)
    {
        $path = trim($path);
        $path = trim($path, '\\/');
        $path = str_replace(array('../', '..\\'), '', $path);
        if ($path == '..') {
            $path = '';
        }
        return str_replace('\\', '/', $path);
    }
    /**
     * @param $strName
     * @return array|bool|callable|float|int|mixed|string|null
     * @throws Caller
     */
    public function __get($strName)
    {
        switch ($strName) {
            case "RootPath": return $this->strRootPath;
            case "RootUrl": return $this->strRootUrl;
            case "TempPath": return $this->strTempPath;
            case "TempUrl": return $this->strTempUrl;
            case "StoragePath": return $this->strStoragePath;

            default:
                try {
                    return parent::__get($strName);
                } catch (Caller $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
        }
    }
    /**
     * @param $strName
     * @param $mixValue
     * @return void
     * @throws Caller
     * @throws InvalidCast
     */
    public function __set($strName, $mixValue)
    {
        switch ($strName) {
            case "RootPath":
                try {
                    $this->strRootPath = Type::Cast($mixValue, Type::STRING);
                    $this->blnModified = true;
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->IncrementOffset();
                    throw $objExc;
                }
            case "RootUrl":
                try {
                    $this->strRootUrl = Type::Cast($mixValue, Type::STRING);
                    $this->blnModified = true;
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->IncrementOffset();
                    throw $objExc;
                }
            case "TempPath":
                try {
                    $this->strTempPath = Type::Cast($mixValue, Type::STRING);
                    $this->blnModified = true;
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->IncrementOffset();
                    throw $objExc;
                }
            case "StoragePath":
                try {
                    $this->strStoragePath = Type::Cast($mixValue, Type::STRING);
                    $this->blnModified = true;
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->IncrementOffset();
                    throw $objExc;
                }
            case "FullStoragePath":
                try {
                    $this->strFullStoragePath = Type::Cast($mixValue, Type::STRING);
                    $this->blnModified = true;
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->IncrementOffset();
                    throw $objExc;
                }

            default:
                try {
                    parent::__set($strName, $mixValue);
                    break;
                } catch (Caller $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
        }
    }
}
