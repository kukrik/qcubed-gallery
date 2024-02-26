<?php
namespace QCubed\Plugin;

use QCubed as Q;
use QCubed\Bootstrap as Bs;
use QCubed\Control\FormBase;
use QCubed\Exception\Caller;
use QCubed\Exception\InvalidCast;
use QCubed\ModelConnector\Param as QModelConnectorParam;
use QCubed\Project\Control\ControlBase;
use QCubed\Project\Application;
use QCubed\Type;

/**
 * Class NanoGalleryBase
 *
 * If want to will be overwritten when you update QCubed. To override, make your changes
 * to the NanoGallery.class.php file instead.
 *
 * Plugin "nanogallery2" is the tool of choice for beautiful galleries with eye-catching effects,
 * and user friendly lightbox for images and videos.
 *
 * Note: Video deployment is not covered here. If desired, the videos need to be developed further.
 *
 * NanoGallery takes full control over gallery and thumbnail design,
 * display animations and hover/touch effects.
 *
 *
 * @property string $TempUrl
 * @property string $Path
 * @property string $Description
 * @property string $Author
 *

 * @package QCubed\Plugin
 */

class NanoGalleryBase extends NanoGalleryBaseGen
{
    use Q\Control\DataBinderTrait;

    /** @var string */
    protected $strTempUrl = APP_UPLOADS_TEMP_URL;
    /** @var string */
    protected $strPath;
    /** @var string */
    protected $strDescription;
    /** @var string */
    protected $strAuthor;
    /** @var  callable */
    /** @var array DataSource from which the items are picked and rendered */
    protected $objDataSource;
    protected $nodeParamsCallback = null;

    /**
     * @param $objParentObject
     * @param $strControlId
     * @throws Caller
     */
    public function __construct($objParentObject, $strControlId = null)
    {
        try {
            parent::__construct($objParentObject, $strControlId);
        } catch (Caller  $objExc) {
            $objExc->incrementOffset();
            throw $objExc;
        }
        $this->registerFiles();
    }

    /**
     * @throws Caller
     */
    protected function registerFiles() {
        $this->AddJavascriptFile(QCUBED_NANOGALLERY_ASSETS_URL . "/nanogallery2/src/jquery-nanogallery2.core.js");
        $this->addCssFile(QCUBED_NANOGALLERY_ASSETS_URL . "/nanogallery2/src//css/jquery-nanogallery2.css");
        $this->AddCssFile(QCUBED_BOOTSTRAP_CSS); // make sure they know
    }

    /**
     * @param callable $callback
     * @return void
     */
    public function createNodeParams(callable $callback)
    {
        $this->nodeParamsCallback = $callback;
    }

    /**
     * Uses HTML callback to get each loop in the original array. Relies on the NodeParamsCallback
     * to return information on how to draw each node.
     *
     * @param mixed $objItem
     * @return string
     * @throws \Exception
     */
    public function getItem($objItem)
    {
        if (!$this->nodeParamsCallback) {
            throw new \Exception("Must provide an nodeParamsCallback");
        }
        $params = call_user_func($this->nodeParamsCallback, $objItem);

        $strPath = '';
        if (isset($params['path'])) {
            $strPath = $params['path'];
        }
        $strDescription = '';
        if (isset($params['description'])) {
            $strDescription = $params['description'];
        }
        $strAuthor = '';
        if (isset($params['author'])) {
            $strAuthor = $params['author'];
        }

        $vars = [
            'path' => $strPath,
            'descripton' => $strDescription,
            'author' => $strAuthor,
        ];
        return $vars;
    }

    /**
     * Fix up possible embedded reference to the form.
     */
    public function sleep()
    {
        $this->nodeParamsCallback = Q\Project\Control\ControlBase::sleepHelper($this->nodeParamsCallback);
        parent::sleep();
    }

    /**
     * The object has been unserialized, so fix up pointers to embedded objects.
     * @param FormBase $objForm
     */
    public function wakeup(FormBase $objForm)
    {
        parent::wakeup($objForm);
        $this->nodeParamsCallback = Q\Project\Control\ControlBase::wakeupHelper($objForm, $this->nodeParamsCallback);
    }

    /**
     * @throws Caller
     */
    public function dataBind()
    {
        // Run the DataBinder (if applicable)
        if (($this->objDataSource === null) && ($this->hasDataBinder()) && (!$this->blnRendered)) {
            try {
                $this->callDataBinder();
            } catch (Caller $objExc) {
                $objExc->incrementOffset();
                throw $objExc;
            }
        }
    }

    /**
     * Returns the HTML for the control.
     *
     * @return string
     */
    protected function getControlHtml()
    {
        $this->dataBind();
        $strParams = [];

        $strHtml = _nl('<div id="' . $this->ControlId . ' data-nanogallery2"></div>');

        if ($this->objDataSource) {
            foreach ($this->objDataSource as $objObject) {
                $strParams[] = $this->getItem($objObject);
            }
        }

        $this->Items = $this->convertToJson($strParams);

        $this->objDataSource = null;
        return $strHtml;
    }

    private function convertToJson($arrParams)
    {
        $strJson = [];

        for ($i = 0; $i < count($arrParams); $i++) {
            $strPath = $arrParams[$i]['path'];
            $strDescripton = $arrParams[$i]['descripton'];
            $strAuthor = $arrParams[$i]['author'];

            $strJson[] =
                [
                    'src' => '/large' . $strPath,
                    'srct' => '/thumbnail' . $strPath,
                    'title' => $strDescripton,
                    'descripton' => $strAuthor
                ];
        }

        return json_encode($strJson);
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
            case "Path": return $this->strPath;
            case "Description": return $this->strDescription;
            case "Author": return $this->strAuthor;
            case "DataSource": return $this->objDataSource;

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
            case "Path":
                try {
                    $this->strPath = Type::Cast($mixValue, Type::STRING);
                    $this->blnModified = true;
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->IncrementOffset();
                    throw $objExc;
                }
            case "Description":
                try {
                    $this->strDescription = Type::Cast($mixValue, Type::STRING);
                    $this->blnModified = true;
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->IncrementOffset();
                    throw $objExc;
                }
            case "Author":
                try {
                    $this->strAuthor = Type::Cast($mixValue, Type::STRING);
                    $this->blnModified = true;
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->IncrementOffset();
                    throw $objExc;
                }
            case "DataSource":
                $this->blnModified = true;
                $this->objDataSource = $mixValue;
                break;

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
