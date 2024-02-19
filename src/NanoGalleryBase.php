<?php

namespace QCubed\Plugin;

use QCubed as Q;
use QCubed\Control;
use QCubed\Bootstrap as Bs;
use QCubed\Control\DataRepeater;
use QCubed\Exception\Caller;
use QCubed\Exception\InvalidCast;
use QCubed\ModelConnector\Param as QModelConnectorParam;
use QCubed\Project\Control\ControlBase;
use QCubed\Project\Application;
use QCubed\Type;

/**
 * Class NanoGallery
 *
 * Note: the "upload" folder must already exist in /project/assets/ and this folder has 777 permissions.
 *

 *
 * @package QCubed\Plugin
 */

class NanoGalleryBase extends DataRepeater
{
    /** @var string */
    protected $strTempUrl = APP_UPLOADS_TEMP_URL;

    /**
     * @param $objParentObject
     * @param $strControlId
     * @throws Caller
     */
    public function __construct($objParentObject, $strControlId = null)
    {
        parent::__construct($objParentObject, $strControlId);

        $this->registerFiles();
    }

    /**
     * @throws Caller
     */
    protected function registerFiles() {
        // $this->AddJavascriptFile(QCUBED_GALLERYMANAGER_ASSETS_URL . "/js/qcubed.fileupload.js");
        // $this->addCssFile(QCUBED_GALLERYMANAGER_ASSETS_URL . "/css/qcubed.fileupload.css");
        $this->AddCssFile(QCUBED_BOOTSTRAP_CSS); // make sure they know
    }



    public function validate() {return true;}

    public function parsePostData() {}

    /**
     * Returns the HTML for the control.
     *
     * @return string
     */
    protected function getControlHtml()
    {

    }

    /**
     * @param $strName
     * @return array|bool|callable|float|int|mixed|string|null
     * @throws Caller
     */
    public function __get($strName)
    {
        switch ($strName) {
            case "TempUrl": return $this->strTempUrl;

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
            case "TempUrl":
                try {
                    $this->strTempUrl = Type::Cast($mixValue, Type::STRING);
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
