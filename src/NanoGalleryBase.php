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
 * NanoGallery takes full control over gallery and thumbnail design, display animations and hover/touch effects.
 *
 * ### GALLERY SETTINGS ###
 * @property string $ItemsBaseURL Default ''. Global path to medias (big images, thumbnail images, videos).
 *                                If defined, the value will be added to the URLs of medias, which don't start with
 *                                a protocole (HTTP, HTTPS, ...).
 *                                Example: itemsBaseURL: 'https://nanogallery2.nanostudio.org/samples/'
 *
 * @property string $GalleryDisplayMode Default 'fullContent'. Define how many thumbnails should be displayed in the gallery.
 * @property integer $GalleryMaxRows Default 2. Maximum number of rows to display. Only for galleryDisplayMode: 'rows'.
 *                                   Not supported in cascading layout.
 * @property integer $GalleryMaxItems Default 0. Maximum number of items per album. 0 = display all.
 * @property string $GalleryDisplayTransition Default 'none'. Transition for displaing the gallery. Applied on the
 *                                   whole gallery. Possible values: 'none', 'rotateX', 'slideUp'.
 * @property integer $GalleryDisplayTransitionDuration Default 1000. Duration of the gallery display transition, in milliseconds.
 *
 * ### THUMBNAIL LAYOUT ###
 * @property integer $ThumbnailWidth Default 300. Thumbnails image width in pixels, or 'auto'. Use 'auto' for
 *                                   a gallery with justified layout.
 * @property integer ThumbnailHeight Default 200. Thumbnails image height in pixels, or 'auto'. Use 'auto' for
 *                                   a gallery with cascading/masonry layout.
 * @property string $ThumbnailAlignment Default 'fillWidth'. Horizontal thumbnail alignement in the available width.
 *                                   This option is ignored for the justified layout.
 *                                   Possible values: left, right, center, justified (property thumbnailGutterWidth ignored),
 *                                   fillWidth (thumbnails are downscaled to use the full available width).
 * @property integer $ThumbnailGutterWidth Default 2. Sets the horizontal and vertical gutter space between thumbnails.
 * @property integer $ThumbnailGutterHeight
 * @property integer $ThumbnailBorderVertical Default 2. Set the horizontal (left and right) / vertical (top and bottom) thumbnail border
 * @property integer $ThumbnailBorderHorizontal
 *
 * ### THUMBNAIL LABELS ###
 * @property array $ThumbnailLabel The label is composed by a title and a description. Set these settings with the thumbnailLabel property.
 *                                  String 'position': 'overImageOnBottom'. Position of the label on the thumbnail.
 *                                  Possible values: 'overImage', 'onBottom'
 *                                  Depreciated values (v3+): 'overImageOnBottom', 'overImageOnTop', 'overImageOnMiddle'
 *                                  String 'align': 'center'. Horizontal text alignment.
 *                                  Possible values: 'right', 'center', 'left'
 *                                  String 'valign': 'bottom'. Vertical text alignment.
 *                                  Possible values: 'top', 'middle', 'bottom'
 *                                  Boolean 'display': true. Displays or hides the label (title and description).
 *                      ˇ           Boolean 'hideIcons': true. Hides or displays the icons beside the title.
 *                                  Integer 'titleMaxLength': 0. Title maximum length to display.
 *                                  Boolean 'titleMultiLine': false. Title can be multiline (not supported with position:'onBottom').
 *                                  String 'title': null. Variable to set the image title (undescores are replaced by spaces).
 *                                  Possible values:
 *                                                  - '%filename': use the filename without path
 *                                                  - '%filenameNoExt': use the filename without path and without extension
 *                                  String 'titleFontSize': null. Set the title font size. Example: titleFontSize: '2em'
 *                                  Boolean 'displayDescription': false. Displays or hides the description.
 *                                  Integer 'descriptionMaxLength': 0. Description maximum length to display.
 *                                  Boolean 'descriptionMultiLine': false. Description can be multiline (not supported with position:'onBottom').
 *                                  String 'descriptionFontSize': null. Set the description font size.
 *                                                              Example: descriptionFontSize: '0.8em'
 *
 *
 *
 * ### THUMBNAIL DISPLAY ANIMATION ###
 * @property string $ThumbnailDisplayTransition Default 15. Interval in ms between the display of 2 thumbnails (in ms).
 * @property string $ThumbnailDisplayOrder Default ''. Thumbnail's display order. Possible values: '', 'random'.
 * @property integer $ThumbnailDisplayTransitionDuration Default 'fadeIn'. Transition used to display each individual thumbnail.
 *
 * ### LIGHTBOX: MEDIA DISPLAY ###
 * ### General settings ###
 * @property string $ImageTransition Default 'swipe2'. Display transition from one media to the next one.
 *                                  Possible values: 'slideAppear', 'swipe', 'swipe2'.
 * @property boolean $SlideshowAutoStart Default false. Starts automatically the slideshow when a media is displayed in lightbox.
 * @property integer $SlideshowDelay Default 3000. Duration of the photo display in slideshow mode (in ms).
 *                                  The delay starts when an image is fully downloaded.
 * @property integer $ViewerHideToolsDelay Default 4000. Delay of inactivity before hiding tools and labels.
 *                                  Use value -1 to disable this feature.
 * @property boolean $ViewerFullscreen Default false. Displays the lightbox directly in fullscreen (on supported browser).
 *
 * ### Lightbox toolbar ###
 * @property array $ViewerToolbar Display options for the lightbox main toolbar.
 *                                 Boolean 'display' : false. Displays/hides the main toolbar.
 *                                 String 'position': 'bottom'. Vertical position. Possible values: 'top', 'bottom'.
 *                                 String 'align': 'center'. Horizontal alignement. Possible values: 'left', 'right', 'center'.
 *                                 Boolean 'fullWidth': false. Toolbar is as width as the screen.
 *                                 Integer 'autoMinimize': 800. Breakpoint (in pixels) for switching between minimized
 *                                          and standard toolbar. If the width is lower than this value, the toolbar is
 *                                          switched to minimized.
 *                                 String 'standard'. List of items (tools/labels) to display in the standard toolbar
 *                                          (comma separated). For this toolbar, 'minimizeButton' is additionally available.
 *                                          Default value: viewerToolbar: { standard :'minimizeButton, label'}
 *                                 String 'minimized'. List of items to display in the minimized toolbar (comma separated).
 *                                          For this toolbar, 'minimizeButton' is additionally available.
 *                                          Default value: viewerToolbar: { minimized :'minimizeButton, label, fullscreenButton'}
 * ### Lightbox tools ###
 * @property array $ViewerTools Tools in the top corners of the lightbox, over the media.
 *                               String 'topLeft'. Toolbar positioned in the top left corner. Default value:
 *                                      viewerTools : { topLeft: 'pageCounter, playPauseButton'}
 *                               String 'topRight'. Toolbar positioned in the top right corner. Default value:
 *                                      viewerTools : { topRight: 'rotateLeft, rotateRight, zoomButton, closeButton'}.
 *                                      viewerTools : {playPauseButton, zoomButton, rotateLeftButton, rotateRightButton,
 *                                                    fullscreenButton, shareButton, downloadButton, closeButton'}.
 *
 *                              Possible tools: 'previousButton', 'nextButton', 'rotateLeft', 'rotateRight', 'pageCounter',
 *                                              'playPauseButton', 'fullscreenButton', 'infoButton', 'linkOriginalButton',
 *                                               'closeButton', 'downloadButton', 'zoomButton', 'shareButton', 'label'
 *                                               (image title and description), 'shoppingcart', 'customN'To add custom
 *                                               elements in a toolbar, use the label customN, where N is an integer (e.g.
 *                                               custom1, custom2...).
 *
 * @property boolean $LocationHash Default: true. Enables hash tracking. This will activate browser Back/Forward navigation
 *                                  (browser history support) and Deep Linking of images and photo albums.
 *                                  Must be enabled to allow sharing of images/albums.
 *                                  Note: only one gallery per HTML page should use this feature.
 *
 * @property boolean $AllowHTMLinData Default: false. To enable HTML tags, set the option allowHTMLinData: true.
 *                                  Be aware that this could lead to XSS (cross site scripting) vulnerability.
 *
 * @see https://nanogallery2.nanostudio.org/documentation.html
 * @link https://nanogallery2.nanostudio.org/ or https://github.com/nanostudio-org/nanogallery2
 *
 * @property string $TempUrl
 * @property string $ListTitle
 * @property string $ListDescription
 * @property string $ListAuthor
 *
 *
 * @package QCubed\Plugin
 */

class NanoGalleryBase extends Q\Control\Panel
{
    use Q\Control\DataBinderTrait;

    /** @var string */
    protected $strItemsBaseURL = null;
    /** @var string */
    protected $strGalleryDisplayMode = null;
    /** @var integer */
    protected $intGalleryMaxRows = null;
    /** @var integer */
    protected $intGalleryMaxItems = null;
    /** @var string */
    protected $strGalleryDisplayTransition = null;
    /** @var integer */
    protected $intGalleryDisplayTransitionDuration = null;

    /** @var integer */
    protected $intThumbnailWidth = null;
    /** @var integer */
    protected $intThumbnailHeight = null;
    /** @var string */
    protected $strThumbnailAlignment = null;
    /** @var integer */
    protected $intThumbnailGutterWidth = null;
    /** @var integer */
    protected $intThumbnailGutterHeight = null;
    /** @var integer */
    protected $intThumbnailBorderVertical = null;
    /** @var integer */
    protected $intThumbnailBorderHorizontal = null;

    /** @var string */
    protected $strImageTransition = null;
    /** @var boolean */
    protected $blnSlideshowAutoStart = null;
    /** @var integer */
    protected $intSlideshowDelay = null;
    /** @var integer */
    protected $intViewerHideToolsDelay = null;
    /** @var boolean */
    protected $blnViewerFullscreen = null;
    /** @var array */
    protected $arrThumbnailLabel = null;
    /** @var array */
    protected $arrViewerToolbar = null;
    /** @var array */
    protected $arrViewerTools = null;

    /** @var boolean */
    protected $blnLocationHash = null;
    /** @var boolean */
    protected $blnAllowHTMLinData = null;

    /** @var string */
    protected $strTempUrl = APP_UPLOADS_TEMP_URL;
    /** @var string */
    protected $strListTitle;
    /** @var string */
    protected $strListDescription;
    /** @var string */
    protected $strListAuthor;
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
        $this->AddJavascriptFile(QCUBED_NANOGALLERY_ASSETS_URL . "/nanogallery2/dist/jquery.nanogallery2.js");
        $this->addCssFile(QCUBED_NANOGALLERY_ASSETS_URL . "/nanogallery2/src/css/nanogallery2.css");
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
        $intStatus = '';
        if (isset($params['status'])) {
            $intStatus = $params['status'];
        }

        $vars = [
            'path' => $strPath,
            'descripton' => $strDescription,
            'author' => $strAuthor,
            'status' => $intStatus,
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
        $strHtml = "";

        if ($this->objDataSource) {
            foreach ($this->objDataSource as $objObject) {
                $strParams[] = $this->getItem($objObject);
            }
        }

        $strHtml .= "<div data-nanogallery2 ='";
        $strHtml .= json_encode($this->makePHPOptions(),JSON_UNESCAPED_SLASHES);
        $strHtml .= "'>";
        $strHtml .= $this->renderImageUrl($strParams);
        $strHtml .= "</div>";

        $this->objDataSource = null;
        return $strHtml;
    }

    protected function renderImageUrl($arrParams)
    {
        $strHtml = '';

        for ($i = 0; $i < count($arrParams); $i++) {
            $strPath = $arrParams[$i]['path'];
            $strDescripton = $arrParams[$i]['descripton'];
            $strAuthor = $arrParams[$i]['author'];
            $intStatus = $arrParams[$i]['status'];

            if ($intStatus == 1) {
                $strHtml .= '<a href="';
                $strHtml .= '/large' . $strPath . '"';
                $strHtml .= ' data-ngthumb="';
                $strHtml .= '/thumbnail' . $strPath . '"';

                if ($strAuthor || (!$strAuthor && $this->strListAuthor)) {
                    $strHtml .= ' data-ngdesc="';
                    $strHtml .= $strAuthor ? $strAuthor : $this->strListAuthor;
                    $strHtml .= '">';
                } else {
                    $strHtml .= '>';
                }

                if ($strDescripton || (!$strDescripton && $this->strListDescription)) {
                    $strHtml .= $strDescripton ? $strDescripton : $this->strListDescription;
                }
                $strHtml .= '</a>';
            } else {
                $strHtml .= '';
            }
        }

        return $strHtml;
    }

    protected function makePHPOptions()
    {
        $phpOptions = [];
        if (!is_null($val = $this->ItemsBaseURL)) {$phpOptions['itemsBaseURL'] = $val;}
        if (!is_null($val = $this->GalleryDisplayMode)) {$phpOptions['galleryDisplayMode'] = $val;}
        if (!is_null($val = $this->GalleryMaxRows)) {$phpOptions['galleryMaxRows'] = $val;}
        if (!is_null($val = $this->GalleryMaxItems)) {$phpOptions['galleryMaxItems'] = $val;}
        if (!is_null($val = $this->GalleryDisplayTransition)) {$phpOptions['galleryDisplayTransition'] = $val;}
        if (!is_null($val = $this->GalleryDisplayTransitionDuration)) {$phpOptions['galleryDisplayTransitionDuration'] = $val;}

        if (!is_null($val = $this->ThumbnailWidth)) {$phpOptions['thumbnailWidth'] = $val;}
        if (!is_null($val = $this->ThumbnailHeight)) {$phpOptions['thumbnailHeight'] = $val;}
        if (!is_null($val = $this->ThumbnailAlignment)) {$phpOptions['thumbnailAlignment'] = $val;}
        if (!is_null($val = $this->ThumbnailGutterWidth)) {$phpOptions['thumbnailGutterWidth'] = $val;}
        if (!is_null($val = $this->ThumbnailGutterHeight)) {$phpOptions['thumbnailGutterHeight'] = $val;}
        if (!is_null($val = $this->ThumbnailBorderVertical)) {$phpOptions['thumbnailBorderVertical'] = $val;}
        if (!is_null($val = $this->ThumbnailBorderHorizontal)) {$phpOptions['thumbnailBorderHorizontal'] = $val;}

        if (!is_null($val = $this->ImageTransition)) {$phpOptions['imageTransition'] = $val;}
        if (!is_null($val = $this->SlideshowAutoStart)) {$phpOptions['slideshowAutoStart'] = $val;}
        if (!is_null($val = $this->SlideshowDelay)) {$phpOptions['slideshowDelay'] = $val;}
        if (!is_null($val = $this->ViewerHideToolsDelay)) {$phpOptions['viewerHideToolsDelay'] = $val;}
        if (!is_null($val = $this->ViewerFullscreen)) {$phpOptions['viewerFullscreen'] = $val;}

        if (!is_null($val = $this->ThumbnailLabel)) {$phpOptions['thumbnailLabel'] = $val;}
        if (!is_null($val = $this->ViewerToolbar)) {$phpOptions['viewerToolbar'] = $val;}
        if (!is_null($val = $this->ViewerTools)) {$phpOptions['viewerTools'] = $val;}

        if (!is_null($val = $this->LocationHash)) {$phpOptions['locationHash'] = $val;}
        if (!is_null($val = $this->AllowHTMLinData)) {$phpOptions['allowHTMLinData'] = $val;}
        return $phpOptions;
    }

    /**
     * @param $strName
     * @return array|bool|callable|float|int|mixed|string|null
     * @throws Caller
     */
    public function __get($strName)
    {
        switch ($strName) {
            case 'ItemsBaseURL': return $this->strItemsBaseURL;
            case 'GalleryDisplayMode': return $this->strGalleryDisplayMode;
            case 'GalleryMaxRows': return $this->intGalleryMaxRows;
            case 'GalleryMaxItems': return $this->intGalleryMaxItems;
            case 'GalleryDisplayTransition': return $this->strGalleryDisplayTransition;
            case 'GalleryDisplayTransitionDuration': return $this->intGalleryDisplayTransitionDuration;

            case 'ThumbnailWidth': return $this->intThumbnailWidth;
            case 'ThumbnailHeight': return $this->intThumbnailHeight;
            case 'ThumbnailAlignment': return $this->strThumbnailAlignment;
            case 'ThumbnailGutterWidth': return $this->intThumbnailGutterWidth;
            case 'ThumbnailGutterHeight': return $this->intThumbnailGutterHeight;
            case 'ThumbnailBorderVertical': return $this->intThumbnailBorderVertical;
            case 'ThumbnailBorderVertical': return $this->intThumbnailBorderVertical;
            case 'ThumbnailBorderHorizontal': return $this->intThumbnailBorderHorizontal;

            case 'ImageTransition': return $this->strImageTransition;
            case 'SlideshowAutoStart': return $this->blnSlideshowAutoStart;
            case 'SlideshowDelay': return $this->intSlideshowDelay;
            case 'ViewerHideToolsDelay': return $this->intViewerHideToolsDelay;
            case 'ViewerFullscreen': return $this->blnViewerFullscreen;

            case 'ThumbnailLabel': return $this->arrThumbnailLabel;
            case 'ViewerToolbar': return $this->arrViewerToolbar;
            case 'ViewerTools': return $this->arrViewerTools;

            case 'LocationHash': return $this->blnLocationHash;
            case 'AllowHTMLinData': return $this->blnAllowHTMLinData;

            case "TempUrl": return $this->strTempUrl;
            case "ListTitle": return $this->strListTitle;
            case "ListDescription": return $this->strListDescription;
            case "ListAuthor": return $this->strListAuthor;
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
            case 'ItemsBaseURL':
                try {
                    $this->strItemsBaseURL = Type::Cast($mixValue, Type::STRING);
                    $this->blnModified = true;
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'GalleryDisplayMode':
                try {
                    $this->strGalleryDisplayMode = Type::Cast($mixValue, Type::STRING);
                    $this->blnModified = true;
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'GalleryMaxRows':
                try {
                    $this->intGalleryMaxRows = Type::Cast($mixValue, Type::INTEGER);
                    $this->blnModified = true;
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'GalleryMaxItems':
                try {
                    $this->intGalleryMaxItems = Type::Cast($mixValue, Type::INTEGER);
                    $this->blnModified = true;
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'GalleryDisplayTransition':
                try {
                    $this->strGalleryDisplayTransition = Type::Cast($mixValue, Type::STRING);
                    $this->blnModified = true;
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'GalleryDisplayTransitionDuration':
                try {
                    $this->intGalleryDisplayTransitionDuration = Type::Cast($mixValue, Type::INTEGER);
                    $this->blnModified = true;
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }

            case 'ThumbnailWidth':
                try {
                    $this->intThumbnailWidth = $mixValue; // Type::Cast($mixValue, Type::INTEGER);
                    $this->blnModified = true;
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'ThumbnailHeight':
                try {
                    $this->intThumbnailHeight = $mixValue; // Type::Cast($mixValue, Type::INTEGER);
                    $this->blnModified = true;
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'ThumbnailAlignment':
                try {
                    $this->strThumbnailAlignment = Type::Cast($mixValue, Type::STRING);
                    $this->blnModified = true;
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'ThumbnailGutterWidth':
                try {
                    $this->intThumbnailGutterWidth = Type::Cast($mixValue, Type::INTEGER);
                    $this->blnModified = true;
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'ThumbnailGutterHeight':
                try {
                    $this->intThumbnailGutterHeight = Type::Cast($mixValue, Type::INTEGER);
                    $this->blnModified = true;
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'ThumbnailBorderVertical':
                try {
                    $this->intThumbnailBorderVertical = Type::Cast($mixValue, Type::INTEGER);
                    $this->blnModified = true;
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'ThumbnailBorderVertical':
                try {
                    $this->intThumbnailBorderVertical = Type::Cast($mixValue, Type::INTEGER);
                    $this->blnModified = true;
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'ThumbnailBorderHorizontal':
                try {
                    $this->intThumbnailBorderHorizontal = Type::Cast($mixValue, Type::INTEGER);
                    $this->blnModified = true;
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }

            case 'ImageTransition':
                try {
                    $this->strImageTransition = Type::Cast($mixValue, Type::STRING);
                    $this->blnModified = true;
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'SlideshowAutoStart':
                try {
                    $this->blnSlideshowAutoStart = Type::Cast($mixValue, Type::BOOLEAN);
                    $this->blnModified = true;
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'SlideshowDelay':
                try {
                    $this->intSlideshowDelay = Type::Cast($mixValue, Type::INTEGER);
                    $this->blnModified = true;
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'ViewerHideToolsDelay':
                try {
                    $this->intViewerHideToolsDelay = Type::Cast($mixValue, Type::INTEGER);
                    $this->blnModified = true;
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'ViewerFullscreen':
                try {
                    $this->blnViewerFullscreen = Type::Cast($mixValue, Type::BOOLEAN);
                    $this->blnModified = true;
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }

            case 'ThumbnailLabel':
                try {
                    $this->arrThumbnailLabel = Type::Cast($mixValue, Type::ARRAY_TYPE);
                    $this->blnModified = true;
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'ViewerToolbar':
                try {
                    $this->arrViewerToolbar = Type::Cast($mixValue, Type::ARRAY_TYPE);
                    $this->blnModified = true;
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'ViewerTools':
                try {
                    $this->arrViewerTools = Type::Cast($mixValue, Type::ARRAY_TYPE);
                    $this->blnModified = true;
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }

          case 'LocationHash':
                try {
                    $this->blnLocationHash = Type::Cast($mixValue, Type::BOOLEAN);
                    $this->blnModified = true;
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'AllowHTMLinData':
                try {
                    $this->blnAllowHTMLinData = Type::Cast($mixValue, Type::BOOLEAN);
                    $this->blnModified = true;
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }

            case "TempUrl":
                try {
                    $this->strTempUrl = Type::Cast($mixValue, Type::STRING);
                    $this->blnModified = true;
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->IncrementOffset();
                    throw $objExc;
                }
            case "ListTitle":
                try {
                    $this->strListTitle = Type::Cast($mixValue, Type::STRING);
                    $this->blnModified = true;
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->IncrementOffset();
                    throw $objExc;
                }
            case "ListDescription":
                try {
                    $this->strListDescription = Type::Cast($mixValue, Type::STRING);
                    $this->blnModified = true;
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->IncrementOffset();
                    throw $objExc;
                }
            case "ListAuthor":
                try {
                    $this->strListAuthor = Type::Cast($mixValue, Type::STRING);
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
