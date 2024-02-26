<?php

namespace QCubed\Plugin;

use QCubed as Q;
use QCubed\Control;
use QCubed\Exception\Caller;
use QCubed\Exception\InvalidCast;
use QCubed\ModelConnector\Param as QModelConnectorParam;
use QCubed\Project\Control\ControlBase;
use QCubed\Project\Application;
use QCubed\Type;

/**
 * Class NanoGalleryBaseGen
 *
 * @see NanoGalleryBase
 * @package QCubed\Plugin
 */

/**
 * * ### GALLERY SETTINGS ###
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
 * @property object $ViewerToolbar Display options for the lightbox main toolbar.
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
 *
 * ### Lightbox tools ###
 * @property object $ViewerTools Tools in the top corners of the lightbox, over the media.
 *                               String 'topLeft'. Toolbar positioned in the top left corner. Default value:
 *                                      viewerTools : { topLeft: 'pageCounter, playPauseButton'}
 *                               String 'topRight'. Toolbar positioned in the top right corner. Default value:
 *                                      viewerTools : { topRight: 'rotateLeft, rotateRight, zoomButton, closeButton'}.
 *                                      viewerTools : {playPauseButton, zoomButton, rotateLeftButton, rotateRightButton, fullscreenButton, shareButton, downloadButton, closeButton'}.
 *
 * @property array $Items Default: null. It outputs the values between the img tags. The output must look like a standard format. Example:
 *                        ...,items: [
                                        { src: 'img_01.jpg', srct: 'img_01t.jpg', title: 'Title Image 1' },
                                        { src: 'img_02.jpg', srct: 'img_02t.jpg', title: 'Title Image 2' },
                                        { src: 'img_03.jpg', srct: 'img_03t.jpg', title: 'Title Image 3' }
                                    ]
 * @property boolean $LocationHash Default: true.
 *
 * @see https://nanogallery2.nanostudio.org/documentation.html
 *
 * @link https://nanogallery2.nanostudio.org/ or https://github.com/nanostudio-org/nanogallery2
 * @package QCubed\Plugin
 */

class NanoGalleryBaseGen extends Q\Control\Panel
{
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

    /** @var object */
    protected $objViewerToolbar = null;
    /** @var object */
    protected $objViewerTools = null;

    /** @var array */
    protected $strItems = null;
    /** @var boolean */
    protected $blnLocationHash = null;

    protected function makeJqOptions()
    {
        $jqOptions = parent::MakeJqOptions();
        if (!is_null($val = $this->ItemsBaseURL)) {$jqOptions['itemsBaseURL'] = $val;}
        if (!is_null($val = $this->GalleryDisplayMode)) {$jqOptions['galleryDisplayMode'] = $val;}
        if (!is_null($val = $this->GalleryMaxRows)) {$jqOptions['galleryMaxRows'] = $val;}
        if (!is_null($val = $this->GalleryMaxItems)) {$jqOptions['galleryMaxItems'] = $val;}
        if (!is_null($val = $this->GalleryDisplayTransition)) {$jqOptions['galleryDisplayTransition'] = $val;}
        if (!is_null($val = $this->GalleryDisplayTransitionDuration)) {$jqOptions['galleryDisplayTransitionDuration'] = $val;}

        if (!is_null($val = $this->ThumbnailWidth)) {$jqOptions['thumbnailWidth'] = $val;}
        if (!is_null($val = $this->ThumbnailHeight)) {$jqOptions['thumbnailHeight'] = $val;}
        if (!is_null($val = $this->ThumbnailAlignment)) {$jqOptions['thumbnailAlignment'] = $val;}
        if (!is_null($val = $this->ThumbnailGutterWidth)) {$jqOptions['thumbnailGutterWidth'] = $val;}
        if (!is_null($val = $this->ThumbnailGutterHeight)) {$jqOptions['thumbnailGutterHeight'] = $val;}
        if (!is_null($val = $this->ThumbnailBorderVertical)) {$jqOptions['thumbnailBorderVertical'] = $val;}
        if (!is_null($val = $this->ThumbnailBorderHorizontal)) {$jqOptions['thumbnailBorderHorizontal'] = $val;}

        if (!is_null($val = $this->ImageTransition)) {$jqOptions['imageTransition'] = $val;}
        if (!is_null($val = $this->SlideshowAutoStart)) {$jqOptions['slideshowAutoStart'] = $val;}
        if (!is_null($val = $this->SlideshowDelay)) {$jqOptions['slideshowDelay'] = $val;}
        if (!is_null($val = $this->ViewerHideToolsDelay)) {$jqOptions['viewerHideToolsDelay'] = $val;}
        if (!is_null($val = $this->ViewerFullscreen)) {$jqOptions['viewerFullscreen'] = $val;}

        if (!is_null($val = $this->ViewerToolbar)) {$jqOptions['viewerToolbar'] = $val;}
        if (!is_null($val = $this->ViewerTools)) {$jqOptions['viewerTools'] = $val;}

        if (!is_null($val = $this->Items)) {$jqOptions['items'] = $val;}
        if (!is_null($val = $this->LocationHash)) {$jqOptions['locationHash'] = $val;}
        return $jqOptions;
    }

    public function getJqSetupFunction()
    {
        return 'nanogallery2';
    }

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

            case 'ViewerToolbar': return $this->objViewerToolbar;
            case 'ViewerTools': return $this->objViewerTools;

            case 'Items': return $this->strItems;
            case 'LocationHash': return $this->blnLocationHash;

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
            case 'ItemsBaseURL':
                try {
                    $this->strItemsBaseURL = Type::Cast($mixValue, Type::STRING);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'itemsBaseURL', $this->strItemsBaseURL);
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'GalleryDisplayMode':
                try {
                    $this->strGalleryDisplayMode = Type::Cast($mixValue, Type::STRING);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'galleryDisplayMode', $this->strGalleryDisplayMode);
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'GalleryMaxRows':
                try {
                    $this->intGalleryMaxRows = Type::Cast($mixValue, Type::INTEGER);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'galleryMaxRows', $this->intGalleryMaxRows);
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'GalleryMaxItems':
                try {
                    $this->intGalleryMaxItems = Type::Cast($mixValue, Type::INTEGER);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'galleryMaxItems', $this->intGalleryMaxItems);
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'GalleryDisplayTransition':
                try {
                    $this->strGalleryDisplayTransition = Type::Cast($mixValue, Type::STRING);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'galleryDisplayTransition', $this->strGalleryDisplayTransition);
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'GalleryDisplayTransitionDuration':
                try {
                    $this->intGalleryDisplayTransitionDuration = Type::Cast($mixValue, Type::INTEGER);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'galleryDisplayTransitionDuration', $this->intGalleryDisplayTransitionDuration);
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }

            case 'ThumbnailWidth':
                try {
                    $this->intThumbnailWidth = Type::Cast($mixValue, Type::INTEGER);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'thumbnailWidth', $this->intThumbnailWidth);
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'ThumbnailHeight':
                try {
                    $this->intThumbnailHeight = Type::Cast($mixValue, Type::INTEGER);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'thumbnailHeight', $this->intThumbnailHeight);
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'ThumbnailAlignment':
                try {
                    $this->strThumbnailAlignment = Type::Cast($mixValue, Type::STRING);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'thumbnailAlignment', $this->strThumbnailAlignment);
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'ThumbnailGutterWidth':
                try {
                    $this->intThumbnailGutterWidth = Type::Cast($mixValue, Type::INTEGER);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'thumbnailGutterWidth', $this->intThumbnailGutterWidth);
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'ThumbnailGutterHeight ':
                try {
                    $this->intThumbnailGutterHeight  = Type::Cast($mixValue, Type::INTEGER);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'thumbnailGutterHeight ', $this->intThumbnailGutterHeight );
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'ThumbnailBorderVertical':
                try {
                    $this->intThumbnailBorderVertical = Type::Cast($mixValue, Type::INTEGER);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'thumbnailBorderVertical', $this->intThumbnailBorderVertical);
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'ThumbnailBorderHorizontal':
                try {
                    $this->intThumbnailBorderHorizontal = Type::Cast($mixValue, Type::INTEGER);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'thumbnailBorderHorizontal', $this->intThumbnailBorderHorizontal);
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }

            case 'ImageTransition ':
                try {
                    $this->strImageTransition  = Type::Cast($mixValue, Type::STRING);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'imageTransition ', $this->strImageTransition );
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'SlideshowAutoStart':
                try {
                    $this->blnSlideshowAutoStart = Type::Cast($mixValue, Type::BOOLEAN);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'slideshowAutoStart', $this->blnSlideshowAutoStart);
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'SlideshowDelay':
                try {
                    $this->intSlideshowDelay = Type::Cast($mixValue, Type::INTEGER);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'slideshowDelay', $this->intSlideshowDelay);
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'ViewerHideToolsDelay':
                try {
                    $this->intViewerHideToolsDelay = Type::Cast($mixValue, Type::INTEGER);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'viewerHideToolsDelay', $this->intViewerHideToolsDelay);
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'ViewerFullscreen':
                try {
                    $this->blnViewerFullscreen = Type::Cast($mixValue, Type::BOOLEAN);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'viewerFullscreen', $this->blnViewerFullscreen);
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }

            case 'ViewerToolbar':
                try {
                    $this->objViewerToolbar = Type::Cast($mixValue, Type::ARRAY_TYPE);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'viewerToolbar', $this->objViewerToolbar);
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'objViewerTools':
                try {
                    $this->objViewerTools = Type::Cast($mixValue, Type::ARRAY_TYPE);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'viewerTools', $this->objViewerTools);
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }

            case 'Items':
                try {
                    $this->strItems = Type::Cast($mixValue, Type::STRING);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'items', $this->strItems);
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
            case 'LocationHash':
                try {
                    $this->blnLocationHash = Type::Cast($mixValue, Type::BOOLEAN);
                    $this->addAttributeScript($this->getJqSetupFunction(), 'option', 'locationHash', $this->blnLocationHash);
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


