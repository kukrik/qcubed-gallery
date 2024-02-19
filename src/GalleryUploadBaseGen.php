<?php

namespace QCubed\Plugin;

use QCubed as Q;
use QCubed\Control;
use QCubed\Bootstrap as Bs;
use QCubed\Exception\Caller;
use QCubed\Exception\InvalidCast;
use QCubed\ModelConnector\Param as QModelConnectorParam;
use QCubed\Project\Control\ControlBase;
use QCubed\Project\Application;
use QCubed\Type;

/**
 * Class GalleryUploadBaseGen
 *
 * @see GalleryUploadBase
 * @package QCubed\Plugin
 */

/**
 * @property string $Language	Default: empty. Optional language selection, default is set to English (en).
 * @property boolean $MultipleUploads Default: false. Optional, must be set to true to allow multiple file uploads.
 * @property boolean $ShowIcons Default: false. If necessary, if you do not want to display the preview, you can turn on
 *                              only the display of icons.
 * @property array $AcceptFileTypes Default: null. The output form of the array looks like this:
 *                                 '['gif', 'jpg', 'jpeg', 'png', 'pdf']'. If necessary, specify the allowed file types.
 *                                  When empty (default), all file types are allowed.
 * @property integer $MaxNumberOfFiles Default: null. If necessary, limit the upper limit of uploaded files
 * @property integer $MaxFileSize Default: null. The maximum allowed file size in bytes, for example (1024 * 1024 * 5, // 5 MB)
 * @property integer $MinFileSize Default: null, The minimum allowed file size in bytes. No minimal file size
 * @property boolean $ChunkUpload Default: true. Chunked upload is on by default. Can switch to normal upload if needed
 * @property integer $MaxChunkSize Default: '1024 * 1024 (1 MB)'. If possible, the size of bytes can be increased
 * @property string $LimitConcurrentUploads Default: '2'. Limited to 2 simultaneous uploads by default. Can be increased if necessary
 * @property string $Url Default: null. If necessary, you can refer to the file processing php
 * @property integer $PreviewMaxWidth Default: '80' (maximum preview width limit)
 * @property integer $PreviewMaxHeight Default: '80' (maximum preview height limit)
 * @property boolean $WithCredentials Default: false. XMLHttpRequest can make cross-origin requests, using the same CORS
 *                                    policy as fetch. Just like fetch, it doesn’t send cookies and HTTP-authorization
 *                                    to another origin by default. To enable them, set xhr.withCredentials to true
 *
 * @package QCubed\Plugin
 */

class GalleryUploadBaseGen extends Q\Control\Panel
{
    /** @var string */
    protected $strLanguage = null;
    /** @var boolean */
    protected $blnMultipleUploads = null;
    /** @var boolean */
    protected $blnShowIcons = null;
    /** @var array */
    protected $arrAcceptFileTypes = null;
    /** @var integer */
    protected $intMaxNumberOfFiles = null;
    /** @var integer */
    protected $intMaxFileSize = null;
    /** @var integer */
    protected $intMinFileSize = null;
    /** @var boolean */
    protected $blnChunkUpload = null;
    /** @var integer */
    protected $intMaxChunkSize = null;
    /** @var integer */
    protected $intLimitConcurrentUploads = null;
    /** @var string */
    protected $strUrl = null;
    /** @var string */
    protected $intPreviewMaxWidth = null;
    /** @var string */
    protected $intPreviewMaxHeight = null;
    /** @var boolean */
    protected $blnWithCredentials = null;

    protected function makeJqOptions()
    {
        $jqOptions = parent::MakeJqOptions();
        if (!is_null($val = $this->Language)) {$jqOptions['language'] = $val;}
        if (!is_null($val = $this->MultipleUploads)) {$jqOptions['multipleUploads'] = $val;}
        if (!is_null($val = $this->ShowIcons)) {$jqOptions['showIcons'] = $val;}
        if (!is_null($val = $this->AcceptFileTypes)) {$jqOptions['acceptFileTypes'] = $val;}
        if (!is_null($val = $this->MaxNumberOfFiles)) {$jqOptions['maxNumberOfFiles'] = $val;}
        if (!is_null($val = $this->MaxFileSize)) {$jqOptions['maxFileSize'] = $val;}
        if (!is_null($val = $this->MinFileSize)) {$jqOptions['minFileSize'] = $val;}
        if (!is_null($val = $this->ChunkUpload)) {$jqOptions['chunkUpload'] = $val;}
        if (!is_null($val = $this->MaxChunkSize)) {$jqOptions['maxChunkSize'] = $val;}
        if (!is_null($val = $this->LimitConcurrentUploads)) {$jqOptions['limitConcurrentUploads'] = $val;}
        if (!is_null($val = $this->Url)) {$jqOptions['url'] = $val;}
        if (!is_null($val = $this->PreviewMaxWidth)) {$jqOptions['previewMaxWidth'] = $val;}
        if (!is_null($val = $this->PreviewMaxHeight)) {$jqOptions['previewMaxHeight'] = $val;}
        if (!is_null($val = $this->WithCredentials)) {$jqOptions['withCredentials'] = $val;}
        return $jqOptions;
    }

    public function getJqSetupFunction()
    {
        return 'galleryUpload';
    }

    public function __get($strName)
    {
        switch ($strName) {
            case 'Language': return t($this->strLanguage);
            case 'MultipleUploads': return $this->blnMultipleUploads;
            case 'ShowIcons': return $this->blnShowIcons;
            case 'AcceptFileTypes': return $this->arrAcceptFileTypes;
            case 'MaxNumberOfFiles': return $this->intMaxNumberOfFiles;
            case 'MaxFileSize': return $this->intMaxFileSize;
            case 'MinFileSize': return $this->intMinFileSize;
            case 'ChunkUpload': return $this->blnChunkUpload;
            case 'MaxChunkSize': return $this->intMaxChunkSize;
            case 'LimitConcurrentUploads': return $this->intLimitConcurrentUploads;
            case 'Url': return $this->strUrl;
            case 'PreviewMaxWidth': return $this->intPreviewMaxWidth;
            case 'PreviewMaxHeight': return $this->intPreviewMaxHeight;
            case 'WithCredentials': return $this->blnWithCredentials;

            default:
                try {
                    return parent::__get($strName);
                } catch (Caller $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
        }
    }

    public function __set($strName, $mixValue)
    {
        switch ($strName) {
            case 'Language':
                try {
                    $this->strLanguage = Type::Cast($mixValue, Type::STRING);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'language', $this->strLanguage);
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }

            case 'MultipleUploads':
                try {
                    $this->blnMultipleUploads = Type::Cast($mixValue, Type::BOOLEAN);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'multipleUploads', $this->blnMultipleUploads);
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }

            case 'ShowIcons':
                try {
                    $this->blnShowIcons = Type::Cast($mixValue, Type::STRING);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'showIcons', $this->blnShowIcons);
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }

            case 'AcceptFileTypes':
                try {
                    $this->arrAcceptFileTypes = Type::Cast($mixValue, Type::ARRAY_TYPE);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'acceptFileTypes', $this->arrAcceptFileTypes);
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }

            case 'MaxNumberOfFiles':
                try {
                    $this->intMaxNumberOfFiles = Type::Cast($mixValue, Type::INTEGER);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'maxNumberOfFiles', $this->intMaxNumberOfFiles);
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }

            case 'MaxFileSize':
                try {
                    $this->intMaxFileSize = Type::Cast($mixValue, Type::INTEGER);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'maxFileSize', $this->intMaxFileSize);
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }

            case 'MinFileSize':
                try {
                    $this->intMinFileSize = Type::Cast($mixValue, Type::INTEGER);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'minFileSize', $this->intMinFileSize);
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }

            case 'ChunkUpload':
                try {
                    $this->blnChunkUpload = Type::Cast($mixValue, Type::BOOLEAN);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'chunkUpload', $this->blnChunkUpload);
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }

            case 'MaxChunkSize':
                try {
                    $this->intMaxChunkSize = Type::Cast($mixValue, Type::INTEGER);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'maxChunkSize', $this->intMaxChunkSize);
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }

            case 'LimitConcurrentUploads':
                try {
                    $this->intLimitConcurrentUploads = Type::Cast($mixValue, Type::INTEGER);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'limitConcurrentUploads', $this->intLimitConcurrentUploads);
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }

            case 'Url':
                try {
                    $this->strUrl = Type::Cast($mixValue, Type::STRING);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'url', $this->strUrl);
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }

            case 'PreviewMaxWidth':
                try {
                    $this->intPreviewMaxWidth = Type::Cast($mixValue, Type::INTEGER);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'previewMaxWidth', $this->intPreviewMaxWidth);
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }

            case 'PreviewMaxHeight':
                try {
                    $this->intPreviewMaxHeight = Type::Cast($mixValue, Type::INTEGER);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'previewMaxHeight', $this->intPreviewMaxHeight);
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }

            case 'WithCredentials':
                try {
                    $this->blnWithCredentials = Type::Cast($mixValue, Type::BOOLEAN);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'withCredentials', $this->blnWithCredentials);
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
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

    /**
     * If this control is attachable to a codegenerated control in a ModelConnector, this function will be
     * used by the ModelConnector designer dialog to display a list of options for the control.
     * @return QModelConnectorParam[]
     **/
    public static function getModelConnectorParams()
    {
        return array_merge(parent::GetModelConnectorParams(), array());
    }
}


