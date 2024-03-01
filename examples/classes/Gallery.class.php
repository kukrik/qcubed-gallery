<?php

use QCubed as Q;
use QCubed\Plugin\FileHandler;
use QCubed\Event\Click;
use QCubed\Action\Ajax;
use QCubed\Action\Terminate;
use QCubed\Bootstrap as Bs;
use QCubed\Event\Change;
use QCubed\Event\EnterKey;
use QCubed\Event\Input;
use QCubed\Folder;
use QCubed\Project\Control\ControlBase;
use QCubed\Project\Control\FormBase as Form;
use QCubed\Project\Application;
use QCubed\Action\ActionParams;
use QCubed\Project\Control\Paginator;
use QCubed\Query\Condition\ConditionInterface as QQCondition;
use QCubed\Control\ListItem;
use QCubed\Query\QQ;
use QCubed\QString;

class Gallery extends Q\Control\Panel
{
    protected $strRootPath = APP_UPLOADS_DIR;
    /** @var string */
    protected $strRootUrl = APP_UPLOADS_URL;
    /** @var string */
    protected $strTempPath = APP_UPLOADS_TEMP_DIR;
    /** @var string */
    protected $strTempUrl = APP_UPLOADS_TEMP_URL;
    /** @var array */
    protected $tempFolders = ['thumbnail', 'medium', 'large'];
    /** @var array */
    protected $arrAllowed = array('jpg', 'jpeg', 'bmp', 'png', 'webp', 'gif');


    public $dlgModal1;
    public $dlgModal2;
    public $dlgModal3;
    public $dlgModal4;
    public $dlgModal5;
    public $dlgModal6;
    public $dlgToastr1;
    public $dlgToastr2;
    public $dlgToastr3;
    public $dlgToastr4;
    public $dlgToastr5;
    public $dlgToastr6;
    public $dlgToastr7;
    public $dlgToastr8;

    public $objUpload;
    public $btnAddFiles;
    public $btnAllStart;
    public $btnAllCancel;
    public $btnDone;
    public $btnGalleriesBack;

    protected $dtgGalleryList;
    protected $lstItemsPerPage;
    protected $txtTitleSlug;

    public $txtTitle;
    public $lstStatusGallery;
    public $txtDescription;
    public $txtAuthor;
    public $btnListUpdate;
    public $btnListDelete;

    public $txtFileName;
    public $txtFileDescription;
    public $txtFileAuthor;
    public $lstIsEnabled;
    public $btnSave;
    public $btnCancel;

    protected $intGalleriesList;
    protected $intAlbum;
    protected $intFolder;
    protected $objGalleriesList;
    protected $objAlbum;
    protected $objFolder;
    protected $oldCount;
    protected $oldPath;
    protected $intChangeFilesId = null;
    protected $intDeleteId = null;
    protected $strDateTimeFormat = 'd.m.Y H:i';
    protected $strTemplate = 'Gallery.tpl.php';

    public function __construct($objParentObject, $strControlId = null)
    {
        try {
            parent::__construct($objParentObject, $strControlId);
        } catch (\QCubed\Exception\Caller $objExc) {
            $objExc->IncrementOffset();
            throw $objExc;
        }

        $this->intGalleriesList = Application::instance()->context()->queryStringItem('id');
        $this->intAlbum = Application::instance()->context()->queryStringItem('album');
        $this->intFolder = Application::instance()->context()->queryStringItem('folder');
        if (strlen($this->intGalleriesList)) {
            $this->objGalleriesList = ListOfGalleries::load($this->intGalleriesList);
            $this->objAlbum = Albums::load($this->intAlbum);
            $this->objFolder = Folders::load($this->intFolder);
        } else {
            // does nothing
        }

        $this->createTable();
        $this->createInputs();
        $this->createButtons();
        $this->createObjects();
        $this->createModals();
        $this->createToastr();
        $this->checkGalleryAvailability();
    }

    protected function createTable()
    {
        $this->dtgGalleryList = new Q\Plugin\VauuTable($this);
        $this->dtgGalleryList->CssClass = "table vauu-table table-hover";

        $col = $this->dtgGalleryList->createCallableColumn('View', [$this, 'View_render']);
        $col->HtmlEntities = false;
        $col->CellStyler->Width = '8%';

        $col = $this->dtgGalleryList->createCallableColumn('Name', [$this, 'Name_render']);
        $col->HtmlEntities = false;
        $col->CellStyler->Width = '18%';

        $col = $this->dtgGalleryList->createCallableColumn('Author', [$this, 'Author_render']);
        $col->HtmlEntities = false;
        $col->CellStyler->Width = '18%';

        $col = $this->dtgGalleryList->createCallableColumn('Description', [$this, 'Description_render']);
        $col->HtmlEntities = false;
        $col->CellStyler->Width = '18%';

        $col = $this->dtgGalleryList->createCallableColumn('Status', [$this, 'IsEnabled_render']);
        $col->HtmlEntities = false;
        $col->CellStyler->Width = '20%';

        $col = $this->dtgGalleryList->createCallableColumn(t('Actions'), [$this, 'Change_render']);
        $col->HtmlEntities = false;
        $col->CellStyler->Width = '18%';

        $this->dtgGalleryList->Paginator = new Bs\Paginator($this);
        $this->dtgGalleryList->Paginator->LabelForPrevious = t('Previous');
        $this->dtgGalleryList->Paginator->LabelForNext = t('Next');
        $this->dtgGalleryList->ItemsPerPage = 10;

        $this->dtgGalleryList->SortColumnIndex = 1;
        //$this->dtgGalleryList->SortDirection = -1;
        $this->dtgGalleryList->UseAjax = true;
        $this->dtgGalleryList->setDataBinder('dtgGalleryList_Bind', $this);

        ////////////////////////////

        $this->lstItemsPerPage = new Q\Plugin\Select2($this);
        $this->lstItemsPerPage->addCssFile(QCUBED_GALLERY_ASSETS_URL . '/css/select2-web-vauu.css');
        $this->lstItemsPerPage->MinimumResultsForSearch = -1;
        $this->lstItemsPerPage->Theme = 'web-vauu';
        $this->lstItemsPerPage->Width = '100%';
        $this->lstItemsPerPage->SelectionMode = Q\Control\ListBoxBase::SELECTION_MODE_SINGLE;
        $this->lstItemsPerPage->SelectedValue = $this->dtgGalleryList->ItemsPerPage;
        $this->lstItemsPerPage->addItems(array(10, 25, 50, 100));
        $this->lstItemsPerPage->AddAction(new Q\Event\Change(), new Q\Action\AjaxControl($this,'lstItemsPerPage_Change'));
    }

    protected function createInputs()
    {
        $this->txtTitle = new Bs\TextBox($this);
        $this->txtTitle->Placeholder = t('The title of the gallery');
        $this->txtTitle->Text = $this->objGalleriesList->Title ? $this->objGalleriesList->Title : null;
        $this->txtTitle->MaxLength = ListOfGalleries::TitleMaxLength;
        $this->txtTitle->CrossScripting = Bs\TextBox::XSS_HTML_PURIFIER;
        $this->txtTitle->setHtmlAttribute('autocomplete', 'off');
        $this->txtTitle->setHtmlAttribute('required', 'required');
        $this->txtTitle->setCssStyle('float', 'left');
        $this->txtTitle->setCssStyle('margin-right', '10px');
        $this->txtTitle->Width = '20%';
        $this->txtTitle->AddAction(new Q\Event\EnterKey(), new Q\Action\AjaxControl($this,'btnListUpdate_Click'));
        $this->txtTitle->addAction(new Q\Event\EnterKey(), new Q\Action\Terminate());

        $this->txtDescription = new Bs\TextBox($this->dtgGalleryList);
        $this->txtDescription->Placeholder = t('Brief description');
        $this->txtDescription->Text = $this->objGalleriesList->Description ? $this->objGalleriesList->Description : null;
        $this->txtDescription->TextMode = Q\Control\TextBoxBase::MULTI_LINE;
        $this->txtDescription->Rows = 2;
        $this->txtDescription->CrossScripting = Bs\TextBox::XSS_HTML_PURIFIER;
        $this->txtDescription->setHtmlAttribute('autocomplete', 'off');
        $this->txtDescription->setHtmlAttribute('required', 'required');
        $this->txtDescription->setCssStyle('float', 'left');
        $this->txtDescription->setCssStyle('margin-right', '10px');
        $this->txtDescription->Width = '20%';
        $this->txtDescription->AddAction(new Q\Event\EnterKey(), new Q\Action\AjaxControl($this,'btnListUpdate_Click'));
        $this->txtDescription->addAction(new Q\Event\EnterKey(), new Q\Action\Terminate());

        $this->txtAuthor = new Bs\TextBox($this);
        $this->txtAuthor->Placeholder = t("Author's name");
        $this->txtAuthor->Text = $this->objGalleriesList->Author ? $this->objGalleriesList->Author : null;
        $this->txtAuthor->MaxLength = ListOfGalleries::TitleMaxLength;
        $this->txtAuthor->CrossScripting = Bs\TextBox::XSS_HTML_PURIFIER;
        $this->txtAuthor->setHtmlAttribute('autocomplete', 'off');
        $this->txtAuthor->setCssStyle('float', 'left');
        $this->txtAuthor->setCssStyle('margin-right', '30px');
        $this->txtAuthor->Width = '20%';
        $this->txtAuthor->AddAction(new Q\Event\EnterKey(), new Q\Action\AjaxControl($this,'btnListUpdate_Click'));
        $this->txtAuthor->addAction(new Q\Event\EnterKey(), new Q\Action\Terminate());

        $this->lstStatusGallery = new Q\Plugin\RadioList($this);
        $this->lstStatusGallery->addItems([1 => t('Publiched'), 2 => t('Hidden')]);
        $this->lstStatusGallery->SelectedValue = $this->objGalleriesList->Status ? $this->objGalleriesList->Status : null;
        $this->lstStatusGallery->ButtonGroupClass = 'radio radio-orange radio-inline';
        $this->lstStatusGallery->setCssStyle('float', 'left');
        $this->lstStatusGallery->setCssStyle('margin-right', '30px');

        $this->txtFileName = new Bs\TextBox($this->dtgGalleryList);
        $this->txtFileName->setHtmlAttribute('required', 'required');
        $this->txtFileName->CrossScripting = Bs\TextBox::XSS_HTML_PURIFIER;

        $this->lstIsEnabled = new Q\Plugin\RadioList($this->dtgGalleryList);
        $this->lstIsEnabled->addItems([1 => t('Publiched'), 2 => t('Hidden')]);
        $this->lstIsEnabled->ButtonGroupClass = 'radio radio-orange radio-inline';

        $this->txtFileDescription = new Bs\TextBox($this->dtgGalleryList);
        $this->txtFileDescription->TextMode = Q\Control\TextBoxBase::MULTI_LINE;
        $this->txtFileDescription->Rows = 2;
        $this->txtFileDescription->CrossScripting = Bs\TextBox::XSS_HTML_PURIFIER;

        $this->txtFileAuthor = new Bs\TextBox($this->dtgGalleryList);
        $this->txtFileAuthor->CrossScripting = Bs\TextBox::XSS_HTML_PURIFIER;
    }

    public function createButtons()
    {
        $this->btnAddFiles = new Q\Plugin\BsFileControl($this, 'files');
        $this->btnAddFiles->Text = t(' Add files');
        $this->btnAddFiles->Glyph = 'fa fa-upload';
        $this->btnAddFiles->Multiple = true;
        $this->btnAddFiles->CssClass = 'btn btn-orange fileinput-button';
        $this->btnAddFiles->setCssStyle('float', 'left');
        $this->btnAddFiles->setCssStyle('margin-right', '10px');
        $this->btnAddFiles->addWrapperCssClass('center-button');
        $this->btnAddFiles->UseWrapper = false;
        $this->btnAddFiles->addAction(new Q\Event\Click(), new Q\Action\AjaxControl($this, 'uploadStart_Click'));

        $this->btnAllStart = new Bs\Button($this);
        $this->btnAllStart->Text = t('Start upload');
        $this->btnAllStart->CssClass = 'btn btn-darkblue all-start disabled';
        $this->btnAllStart->setCssStyle('float', 'left');
        $this->btnAllStart->setCssStyle('margin-right', '10px');
        $this->btnAllStart->addWrapperCssClass('center-button');
        $this->btnAllStart->PrimaryButton = true;
        $this->btnAllStart->UseWrapper = false;

        $this->btnAllCancel = new Bs\Button($this);
        $this->btnAllCancel->Text = t('Cancel all uploads');
        $this->btnAllCancel->CssClass = 'btn btn-warning all-cancel disabled';
        $this->btnAllCancel->setCssStyle('float', 'left');
        $this->btnAllCancel->setCssStyle('margin-right', '10px');
        $this->btnAllCancel->addWrapperCssClass('center-button');
        $this->btnAllCancel->UseWrapper = false;

        $this->btnDone = new Bs\Button($this);
        $this->btnDone->Text = t('Done');
        $this->btnDone->CssClass = 'btn btn-success pull-right done';
        $this->btnDone->UseWrapper = false;
        $this->btnDone->addAction(new Q\Event\Click(), new Q\Action\AjaxControl($this, 'btnDone_Click'));

        $this->btnGalleriesBack = new Bs\Button($this);
        $this->btnGalleriesBack->Text = t('Back to list');
        $this->btnGalleriesBack->CssClass = 'btn btn-default back-to-list';
        $this->btnGalleriesBack->setCssStyle('float', 'left');
        $this->btnGalleriesBack->setCssStyle('margin-right', '10px');
        $this->btnGalleriesBack->addWrapperCssClass('center-button');
        $this->btnGalleriesBack->UseWrapper = false;
        $this->btnGalleriesBack->addAction(new Q\Event\Click(), new Q\Action\AjaxControl($this,'btnListBack_Click'));

        $this->btnListUpdate = new Q\Plugin\Button($this);
        $this->btnListUpdate->Text = t('Update');
        $this->btnListUpdate->CssClass = 'btn btn-orange';
        $this->btnListUpdate->setCssStyle('float', 'left');
        $this->btnListUpdate->setCssStyle('margin-right', '10px');
        $this->btnListUpdate->addWrapperCssClass('center-button');
        $this->btnListUpdate->PrimaryButton = true;
        $this->btnListUpdate->CausesValidation = true;
        $this->btnListUpdate->addAction(new Q\Event\Click(), new Q\Action\AjaxControl($this,'btnListUpdate_Click'));

        $this->btnListDelete = new Q\Plugin\Button($this);
        $this->btnListDelete->Text = t('Delete');
        $this->btnListDelete->CssClass = 'btn btn-default';
        $this->btnListDelete->setCssStyle('float', 'left');
        $this->btnListDelete->addWrapperCssClass('center-button');
        $this->btnListDelete->CausesValidation = false;
        $this->btnListDelete->addAction(new Q\Event\Click(), new Q\Action\AjaxControl($this, 'btnListDelete_Click'));

        $this->btnSave = new Bs\Button($this->dtgGalleryList);
        $this->btnSave->Text = 'Save';
        $this->btnSave->CssClass = 'btn btn-orange';
        $this->btnSave->addAction(new Q\Event\Click(), new Q\Action\AjaxControl($this, 'btnSave_Click'));
        $this->btnSave->PrimaryButton = true;

        $this->btnCancel = new Bs\Button($this->dtgGalleryList);
        $this->btnCancel->Text = 'Cancel';
        $this->btnCancel->addAction(new Q\Event\Click(), new Q\Action\AjaxControl($this, 'btnCancel_Click'));
        $this->btnCancel->CausesValidation = false;
    }

    public function createObjects()
    {
        $this->objUpload = new Q\Plugin\GalleryUpload($this);
        $this->objUpload->Language = 'et'; // Default en
        //$this->objUpload->ShowIcons = true; // Default false
        $this->objUpload->AcceptFileTypes = ['jpg', 'jpeg', 'bmp', 'png', 'webp', 'gif']; // Default null
        //$this->objUpload->MaxNumberOfFiles = 5; // Default null
        //$this->objUpload->MaxFileSize = 1024 * 1024 * 2; // 2 MB // Default null
        //$this->objUpload->MinFileSize = 500000; // 500 kb // Default null
        //$this->objUpload->ChunkUpload = false; // Default true
        //$this->objUpload->MaxChunkSize = 1024 * 1024 * 10; // 10 MB // Default 5 MB
        //$this->objUpload->LimitConcurrentUploads = 5; // Default 2
        $this->objUpload->Url = 'php/'; // Default null
        //$this->objUpload->PreviewMaxWidth = 120; // Default 80
        //$this->objUpload->PreviewMaxHeight = 120; // Default 80
        //$this->objUpload->WithCredentials = true; // Default false

        if ($this->txtTitle->Text) {
            $this->txtTitleSlug = new Q\Plugin\Label($this);
            $url = (isset($_SERVER['HTTPS']) ? "https" : "http") . '://' . $_SERVER['HTTP_HOST'] . QCUBED_URL_PREFIX .
                $this->objGalleriesList->Path;
            $this->txtTitleSlug->Text = Q\Html::renderLink($url, $url, ["target" => "_blank"]);
            $this->txtTitleSlug->HtmlEntities = false;
            $this->txtTitleSlug->setCssStyle('font-weight', 400);
        } else {
            $this->txtTitleSlug = new Q\Plugin\Label($this);
            $this->txtTitleSlug->Text = t('Uncompleted link...');
            $this->txtTitleSlug->setCssStyle('color', '#999;');
        }
    }

    public function createModals()
    {
        $this->dlgModal1 = new Bs\Modal($this);
        $this->dlgModal1->Title = t('Tip');
        $this->dlgModal1->Text = t('<p style="margin-top: 15px;">The gallery cannot be updated without a name!</p>');
        $this->dlgModal1->HeaderClasses = 'btn-darkblue';
        $this->dlgModal1->addCloseButton(t("I close the window"));

        $this->dlgModal2 = new Bs\Modal($this);
        $this->dlgModal2->Text = t('<p style="line-height: 25px; margin-bottom: 2px;">Cannot update a gallery with the same name!</p>');
        $this->dlgModal2->Title = t("Warning");
        $this->dlgModal2->HeaderClasses = 'btn-danger';
        $this->dlgModal2->addCloseButton(t("I understand"));

        $this->dlgModal3 = new Bs\Modal($this);
        $this->dlgModal3->Text = '<p style="line-height: 25px; margin-bottom: 2px;">Are you sure you want to permanently delete this file?</p>
                                <p style="line-height: 25px; margin-bottom: -3px;">Can\'t undo it afterwards!</p>';
        $this->dlgModal3->Title = 'Warning';
        $this->dlgModal3->HeaderClasses = 'btn-danger';
        $this->dlgModal3->addButton("I accept", 'This file has been permanently deleted', false, false, null,
            ['class' => 'btn btn-orange']);
        $this->dlgModal3->addCloseButton(t("I'll cancel"));
        $this->dlgModal3->addAction(new \QCubed\Event\DialogButton(), new \QCubed\Action\AjaxControl($this, 'deleteItem_Click'));

        $this->dlgModal4 = new Bs\Modal($this);
        $this->dlgModal4->Text = t('<p style="line-height: 15px; margin-bottom: 2px;">File cannot be updated without name!</p>');
        $this->dlgModal4->Title = t("Tip");
        $this->dlgModal4->HeaderClasses = 'btn-darkblue';
        $this->dlgModal4->addCloseButton(t("I close the window"));

        $this->dlgModal5 = new Bs\Modal($this);
        $this->dlgModal5->Text = t('<p style="line-height: 25px; margin-bottom: 2px;">Cannot create a file with the same name!</p>');
        $this->dlgModal5->Title = t("Warning");
        $this->dlgModal5->HeaderClasses = 'btn-danger';
        $this->dlgModal5->addCloseButton(t("I understand"));

        $this->dlgModal6 = new Bs\Modal($this);
        $this->dlgModal6->Text = '<p style="line-height: 25px; margin-bottom: 2px;">Are you sure you want to permanently delete this gallery?</p>
                                <p style="line-height: 25px; margin-bottom: -3px;">Can\'t undo it afterwards!</p>';
        $this->dlgModal6->Title = 'Warning';
        $this->dlgModal6->HeaderClasses = 'btn-danger';
        $this->dlgModal6->addButton("I accept", 'This file has been permanently deleted', false, false, null,
            ['class' => 'btn btn-orange']);
        $this->dlgModal6->addCloseButton(t("I'll cancel"));
        $this->dlgModal6->addAction(new \QCubed\Event\DialogButton(), new \QCubed\Action\AjaxControl($this, 'deleteGallery_Click'));
    }

    public function createToastr()
    {
        $this->dlgToastr1 = new Q\Plugin\Toastr($this);
        $this->dlgToastr1->AlertType = Q\Plugin\Toastr::TYPE_SUCCESS;
        $this->dlgToastr1->PositionClass = Q\Plugin\Toastr::POSITION_TOP_CENTER;
        $this->dlgToastr1->Message = t('<strong>Well done!</strong> Gallery update was successful.');
        $this->dlgToastr1->ProgressBar = true;

        $this->dlgToastr2 = new Q\Plugin\Toastr($this);
        $this->dlgToastr2->AlertType = Q\Plugin\Toastr::TYPE_ERROR;
        $this->dlgToastr2->PositionClass = Q\Plugin\Toastr::POSITION_TOP_CENTER;
        $this->dlgToastr2->Message = t('<strong>Sorry!</strong> Failed to update gallery.');
        $this->dlgToastr2->ProgressBar = true;

        $this->dlgToastr3 = new Q\Plugin\Toastr($this);
        $this->dlgToastr3->AlertType = Q\Plugin\Toastr::TYPE_SUCCESS;
        $this->dlgToastr3->PositionClass = Q\Plugin\Toastr::POSITION_TOP_CENTER;
        $this->dlgToastr3->Message = t('<strong>Well done!</strong> File changed successfully.');
        $this->dlgToastr3->ProgressBar = true;

        $this->dlgToastr4 = new Q\Plugin\Toastr($this);
        $this->dlgToastr4->AlertType = Q\Plugin\Toastr::TYPE_ERROR;
        $this->dlgToastr4->PositionClass = Q\Plugin\Toastr::POSITION_TOP_CENTER;
        $this->dlgToastr4->Message = t('<strong>Sorry!</strong> Failed to change file.');
        $this->dlgToastr4->ProgressBar = true;

        $this->dlgToastr5 = new Q\Plugin\Toastr($this);
        $this->dlgToastr5->AlertType = Q\Plugin\Toastr::TYPE_SUCCESS;
        $this->dlgToastr5->PositionClass = Q\Plugin\Toastr::POSITION_TOP_CENTER;
        $this->dlgToastr5->Message = t('<strong>Well done!</strong> File deleted successfully.');
        $this->dlgToastr5->ProgressBar = true;

        $this->dlgToastr6 = new Q\Plugin\Toastr($this);
        $this->dlgToastr6->AlertType = Q\Plugin\Toastr::TYPE_ERROR;
        $this->dlgToastr6->PositionClass = Q\Plugin\Toastr::POSITION_TOP_CENTER;
        $this->dlgToastr6->Message = t('<strong>Sorry!</strong> Failed to delete file.');
        $this->dlgToastr6->ProgressBar = true;

        $this->dlgToastr7 = new Q\Plugin\Toastr($this);
        $this->dlgToastr7->AlertType = Q\Plugin\Toastr::TYPE_SUCCESS;
        $this->dlgToastr7->PositionClass = Q\Plugin\Toastr::POSITION_TOP_CENTER;
        $this->dlgToastr7->Message = t('<strong>Well done!</strong> The folder and files have been successfully deleted.');
        $this->dlgToastr7->ProgressBar = true;

        $this->dlgToastr8 = new Q\Plugin\Toastr($this);
        $this->dlgToastr8->AlertType = Q\Plugin\Toastr::TYPE_ERROR;
        $this->dlgToastr8->PositionClass = Q\Plugin\Toastr::POSITION_TOP_CENTER;
        $this->dlgToastr8->Message = t('<strong>Sorry!</strong> Failed to delete folder and files.');
        $this->dlgToastr8->ProgressBar = true;
    }

    protected function checkGalleryAvailability()
    {
        $blnCheckGallery = Galleries::countByFolderId($this->intFolder);
        if ($blnCheckGallery) {
            Application::executeJavaScript("
                $('.fileinfo-wrapper').removeClass('hidden');
                $('.table-gallery').removeClass('hidden');
            ");
        } else {
            Application::executeJavaScript("
                $('.fileinfo-wrapper').addClass('hidden');
                $('.table-gallery').addClass('hidden');
            ");
        }
    }

    protected function btnDone_Click(ActionParams $params)
    {
        $this->dtgGalleryList->refresh();

        unset($_SESSION['path']);
        unset($_SESSION['listId']);
        unset($_SESSION['albumId']);
        unset($_SESSION['folderId']);
    }

    protected function uploadStart_Click(ActionParams $params)
    {
        $_SESSION['path'] = $this->objGalleriesList->getPath();
        $_SESSION['listId'] = $this->objGalleriesList->getId();
        $_SESSION['albumId'] = $this->intAlbum;
        $_SESSION['folderId'] = $this->intFolder;
    }

    protected function btnListUpdate_Click(ActionParams $params)
    {
        $parts = pathinfo($this->strRootPath . $this->objGalleriesList->getPath());
        $folders = glob($parts['dirname'] . '/*', GLOB_NOSORT);
        $oldTitle = $this->objGalleriesList->getTitle(); //$this->txtTitle->Text;
        $this->oldPath = $this->objGalleriesList->getPath();

        if (!$this->txtTitle->Text) {
            $this->dlgModal1->showDialogBox();
        } else if ((strlen($this->txtTitle->Text) == strlen($this->objGalleriesList->getTitle())) &&
            (strlen($this->txtAuthor->Text) !== strlen($this->objGalleriesList->getAuthor()) ||
            strlen($this->txtDescription->Text) !==  strlen($this->objGalleriesList->getDescription()) ||
            $this->lstStatusGallery->SelectedValue !== $this->objGalleriesList->getStatus())) {
            $this->updateGalleries($this->intGalleriesList);
        } else if (in_array($parts['dirname'] . '/' . QString::sanitizeForUrl(trim($this->txtTitle->Text)), $folders)) {
            $this->dlgModal2->showDialogBox();
            $this->txtTitle->Text = $oldTitle;
        } else {
           $this->updateGalleries($this->intGalleriesList);
        }
    }

    protected function updateGalleries($listId)
    {
        $parts = pathinfo($this->strRootPath . $this->objGalleriesList->getPath());

        if (!Galleries::countByListId($listId)) {
            if (is_dir($this->strRootPath . $this->objGalleriesList->getPath())) {
                $newPath = $parts['dirname'] . '/' . QString::sanitizeForUrl(trim($this->txtTitle->Text));
                $this->rename($this->strRootPath . $this->objGalleriesList->getPath(), $newPath);

                foreach ($this->tempFolders as $tempFolder) {
                    if (is_dir($this->strTempPath . '/_files/' . $tempFolder . $this->objGalleriesList->getPath())) {
                        $this->rename($this->strTempPath . '/_files/' . $tempFolder . $this->objGalleriesList->getPath(), $this->strTempPath . '/_files/' . $tempFolder . $this->getRelativePath($newPath));
                    }
                }

                $objList = ListOfGalleries::loadById($listId);
                $objList->setTitle($this->txtTitle->Text);
                $objList->setPath($this->getRelativePath($newPath));
                $objList->setTitleSlug(QString::sanitizeForUrl(trim($this->txtTitle->Text)));
                $objList->setDescription($this->txtDescription->Text);
                $objList->setAuthor($this->txtAuthor->Text);
                $objList->setStatus($this->lstStatusGallery->SelectedValue);
                $objList->setPostUpdateDate(Q\QDateTime::Now());
                $objList->save();

                $objFolder = Folders::loadById($this->objGalleriesList->getFolderId());
                $objFolder->setName($this->txtTitle->Text);
                $objFolder->setPath($this->getRelativePath($newPath));
                $objFolder->setMtime(time());
                $objFolder->save();
            }
        } else {
            if (is_dir($this->strRootPath . $this->objGalleriesList->getPath())) {
                $newPath = $parts['dirname'] . '/' . QString::sanitizeForUrl(trim($this->txtTitle->Text));
                $this->rename($this->strRootPath . $this->objGalleriesList->getPath(), $newPath);

                foreach ($this->tempFolders as $tempFolder) {
                    if (is_dir($this->strTempPath . '/_files/' . $tempFolder . $this->objGalleriesList->getPath())) {
                        $this->rename($this->strTempPath . '/_files/' . $tempFolder . $this->objGalleriesList->getPath(), $this->strTempPath . '/_files/' . $tempFolder . $this->getRelativePath($newPath));
                    }
                }

                $objList = ListOfGalleries::loadById($listId);
                $objList->setTitle($this->txtTitle->Text);
                $objList->setPath($this->getRelativePath($newPath));
                $objList->setTitleSlug(QString::sanitizeForUrl(trim($this->txtTitle->Text)));
                $objList->setDescription($this->txtDescription->Text);
                $objList->setAuthor($this->txtAuthor->Text);
                $objList->setStatus($this->lstStatusGallery->SelectedValue);
                $objList->setPostUpdateDate(Q\QDateTime::Now());
                $objList->save();

                $objFolder = Folders::loadById($this->objGalleriesList->getFolderId());
                $objFolder->setName($this->txtTitle->Text);
                $objFolder->setPath($this->getRelativePath($newPath));
                $objFolder->setMtime(time());
                $objFolder->save();

                // Changing file paths from here

                $tempItems = $this->fullListIds($listId);

                foreach ($tempItems as $temp) {
                    $objGallery = Galleries::loadById($temp->getId());
                    $replacePath = str_replace(basename($this->oldPath), QString::sanitizeForUrl(trim($this->txtTitle->Text)), $objGallery->getPath());

                    $objGallery->setPath($replacePath);
                    $objGallery->setPostUpdateDate(Q\QDateTime::Now());
                    $objGallery->save();
                }

                foreach ($tempItems as $temp) {
                    $objFile = Files::loadById($temp->getFileId());
                    $replacePath = str_replace(basename($this->oldPath), QString::sanitizeForUrl(trim($this->txtTitle->Text)), $objFile->getPath());

                    $objFile->setPath($replacePath);
                    $objFile->setMtime(time());
                    $objFile->save();
                }
            }

            if (is_dir($this->strRootPath . $this->getRelativePath($newPath))) {
                $this->dlgToastr1->notify();
            } else {
                $this->dlgToastr2->notify();
            }
        }

        $this->txtTitle->refresh();
        $this->txtAuthor->refresh();
        $this->txtDescription->refresh();

        if ($this->txtTitle->Text) {
            $url = (isset($_SERVER['HTTPS']) ? "https" : "http") . '://' . $_SERVER['HTTP_HOST'] . QCUBED_URL_PREFIX .
                $this->objAlbum->getPath() . '/' . QString::sanitizeForUrl(trim($this->txtTitle->Text));
            $this->txtTitleSlug->Text = Q\Html::renderLink($url, $url, ["target" => "_blank"]);
            $this->txtTitleSlug->HtmlEntities = false;
            $this->txtTitleSlug->setCssStyle('font-weight', 400);
        } else {
            $this->txtTitleSlug->Text = t('Uncompleted link...');
            $this->txtTitleSlug->setCssStyle('color', '#999;');
        }
    }

    protected function btnListBack_Click(ActionParams $params)
    {
        Application::redirect('gallery_list.php?id=' . $this->intAlbum);
    }

    protected function btnListDelete_Click(ActionParams $params)
    {
        $this->dlgModal6->showDialogBox();
    }

    protected function deleteGallery_Click(ActionParams $params)
    {
        $tempItems = $this->fullListIds($this->intGalleriesList);

        $this->dlgModal6->hideDialogBox();

        foreach ($tempItems as $tempItem) {
            if (is_file($this->strRootPath . $tempItem->getPath())) {
                unlink($this->strRootPath . $tempItem->getPath());
            }

            foreach ($this->tempFolders as $tempFolder) {
                if (is_file($this->strTempPath . '/_files/' . $tempFolder . $tempItem->getPath())) {
                    unlink($this->strTempPath . '/_files/' . $tempFolder . $tempItem->getPath());
                }
            }

            $objFile = Files::loadById($tempItem->getFileId());
            $objFile->delete();

            $objGallery = Galleries::loadById($tempItem->getid());
            $objGallery->delete();
        }

        $this->dtgGalleryList->refresh();

        $objFolder = Folders::load($this->intFolder);
        if (is_dir($this->strRootPath . $objFolder->getPath())) {
            rmdir($this->strRootPath . $objFolder->getPath());
        }

        foreach ($this->tempFolders as $tempFolder) {
            if (is_dir($this->strTempPath . '/_files/' . $tempFolder . $objFolder->getPath())) {
                rmdir($this->strTempPath . '/_files/' . $tempFolder . $objFolder->getPath());
            }
        }

        $objFolder->delete();

        $objList = ListOfGalleries::load($this->intGalleriesList);
        $objList->delete();

        if (is_dir($this->strRootPath . $objFolder->getPath())) {
            $this->dlgToastr8->notify();
        } else {
            $this->dlgToastr7->notify();
        }

        Application::executeJavaScript("
            $('.fileinput-button').addClass('disabled');
            $('.back-to-list').addClass('disabled');
            $('.fileinfo-wrapper').addClass('hidden');
            $('.table-gallery').addClass('hidden');
            setTimeout(function() {
                window.location.href = 'gallery_list.php?id={$this->intAlbum}'
            }, 5000)
        ");
    }

    protected function lstItemsPerPage_Change(ActionParams $params)
    {
        $this->dtgGalleryList->ItemsPerPage = $this->lstItemsPerPage->SelectedName;
        $this->dtgGalleryList->refresh();
    }

    public function dtgGalleryList_Bind()
    {
        $this->dtgGalleryList->TotalItemCount = Galleries::countByFolderId($this->intFolder);

        $objClauses = array();
        if ($objClause = $this->dtgGalleryList->OrderByClause)
            $objClauses[] = $objClause;
        if ($objClause = $this->dtgGalleryList->LimitClause)
            $objClauses[] = $objClause;

        $this->dtgGalleryList->DataSource = Galleries::loadArrayByFolderId($this->intFolder, $objClauses);
    }

    public function View_render(Galleries $objGalleries)
    {
        $strHtm = '<span class="preview">';
        $strHtm .= '<img src="' . $this->strTempUrl . '/_files/thumbnail' . $objGalleries->Path . '">';
        $strHtm .= '</span>';
        return $strHtm;
    }

    public function Name_render(Galleries $objGalleries)
    {
        if ($objGalleries->Id == $this->intChangeFilesId) {
            return $this->txtFileName->render(false);
        } else {
            // return QCubed::truncate($objGalleries->Name, 25);
            return wordwrap($objGalleries->Name, 25, "\n", true);
        }
    }

    public function IsEnabled_render(Galleries $objGalleries)
    {
        if ($objGalleries->Id == $this->intChangeFilesId) {
            return $this->lstIsEnabled->render(false);
        } else {
            return $objGalleries->StatusObject;
        }
    }

    public function Description_render(Galleries $objGalleries)
    {
        if ($objGalleries->Id == $this->intChangeFilesId) {
            return $this->txtFileDescription->render(false);
        } else {
            return $objGalleries->Description;
        }
    }

    public function Author_render(Galleries $objGalleries)
    {
        if ($objGalleries->Id == $this->intChangeFilesId) {
            return $this->txtFileAuthor->render(false);
        } else {
            return $objGalleries->Author;
        }
    }

    public function Change_render(Galleries $objGalleries)
    {
        if ($objGalleries->Id == $this->intChangeFilesId) {
            return $this->btnSave->render(false) . ' ' . $this->btnCancel->render(false);
        } else {
            $btnChangeId = 'btnChange' . $objGalleries->Id;
            $btnChange = $this->Form->getControl($btnChangeId);
            if (!$btnChange) {
                $btnChange = new Bs\Button($this->dtgGalleryList, $btnChangeId);
                $btnChange->Text = t('Change');
                $btnChange->ActionParameter = $objGalleries->Id;
                $btnChange->CssClass = 'btn btn-orange';
                $btnChange->addAction(new Q\Event\Click(), new Q\Action\AjaxControl($this, 'btnChange_Click'));
                $btnChange->CausesValidation = false;
            }
            $btnDeleteId = 'btnDelete' . $objGalleries->Id;
            $btnDelete = $this->Form->getControl($btnDeleteId);
            if (!$btnDelete) {
                $btnDelete = new Bs\Button($this->dtgGalleryList, $btnDeleteId);
                $btnDelete->Text = 'Delete';
                $btnDelete->ActionParameter = $objGalleries->Id;
                $btnDelete->addAction(new Q\Event\Click(), new Q\Action\AjaxControl($this, 'btnDelete_Click'));
                $btnDelete->CausesValidation = false;
            }

            if ($this->intChangeFilesId) {
                $btnChange->Enabled = false;
                $btnDelete->Enabled = false;
            } else {
                $btnChange->Enabled = true;
                $btnDelete->Enabled = true;
            }

            return $btnChange->render(false) . ' ' . $btnDelete->render(false);
        }
    }

    protected function btnChange_Click(ActionParams $params)
    {
        $this->intChangeFilesId = intval($params->ActionParameter);
        $objFile = Galleries::load($this->intChangeFilesId);

        $this->txtFileName->Text = pathinfo(APP_UPLOADS_DIR . $objFile->getPath(), PATHINFO_FILENAME);
        $this->lstIsEnabled->SelectedValue = $objFile->getStatus();
        $this->txtFileDescription->Text = $objFile->getDescription();
        $this->txtFileAuthor->Text = $objFile->getAuthor();
        Application::executeControlCommand($this->txtFileName->ControlId, 'focus');

        $this->dtgGalleryList->refresh();
    }

    protected function btnDelete_Click(ActionParams $params)
    {
        $this->intDeleteId = intval($params->ActionParameter);
        $this->dlgModal3->showDialogBox();
    }

    protected function deleteItem_Click(ActionParams $params)
    {
        $objGallery = Galleries::load($this->intDeleteId);

        if (is_file($this->strRootPath . $objGallery->getPath())) {
            unlink($this->strRootPath . $objGallery->getPath());

            foreach ($this->tempFolders as $tempFolder) {
                if (is_file($this->strTempPath . '/_files/' . $tempFolder . $objGallery->getPath())) {
                    unlink($this->strTempPath . '/_files/' . $tempFolder . $objGallery->getPath());
                }
            }

            $objFile = Files::loadById($objGallery->getFileId());
            $objFile->delete();
            $objGallery->delete();

            $this->dtgGalleryList->refresh();
            $this->dlgModal3->hideDialogBox();
        }

        if (is_file($this->strRootPath . $objGallery->getPath())) {
            $this->dlgToastr6->notify();
        } else {
            $this->dlgToastr5->notify();
        }

        if (Galleries::countByListId($this->intGalleriesList) == 0) {

            Application::executeJavaScript("
                $('.fileinput-button').removeClass('disabled');
                $('.back-to-list').removeClass('disabled');
                $('.fileinfo-wrapper').removeClass('hidden');
                $('.table-gallery').addClass('hidden');
            ");

            $objList = ListOfGalleries::loadById($this->intGalleriesList);
            $objList->setAuthor('');
            $objList->setDescription('');
            $objList->setStatus(2);
            $objList->save();

            $this->txtAuthor->Text = '';
            $this->txtDescription->Text = '';
            $this->lstStatusGallery->SelectedValue = 2;
            $this->dtgGalleryList->refresh();
        }
    }

    protected function btnSave_Click(ActionParams $params)
    {
        $intGallery = Galleries::load($this->intChangeFilesId);
        $parts = pathinfo($this->strRootPath . $intGallery->getPath());
        $files = glob($parts['dirname'] . '/*', GLOB_NOSORT);

        if (!$this->txtFileName->Text) {
            $this->dlgModal4->showDialogBox();
            $this->txtFileName->Text = $this->getFileName($intGallery->getName());
        } else if ((strlen($this->txtFileName->Text) == strlen($this->getFileName($intGallery->getName()))) &&
            (strlen($this->txtFileAuthor->Text) !== strlen($intGallery->getAuthor()) ||
                strlen($this->txtFileDescription->Text) !==  strlen($intGallery->getDescription()) ||
                $this->lstIsEnabled->SelectedValue !== $intGallery->getStatus())) {
            $this->updateFile($intGallery);
        } else if (in_array($parts['dirname'] . '/' . trim($this->txtFileName->Text) . '.' . strtolower($parts['extension']), $files)) {
            $this->dlgModal5->showDialogBox();
            $this->txtFileName->Text = $this->getFileName($intGallery->getName());
        } else {
            $this->updateFile($intGallery);
        }
    }

    protected function updateFile($intGallery)
    {
        $parts = pathinfo($this->strRootPath . $intGallery->getPath());
        $files = glob($parts['dirname'] . '/*', GLOB_NOSORT);
        $newPath = $parts['dirname'] . '/' . trim($this->txtFileName->Text) . '.' . strtolower($parts['extension']);

        if (is_file($this->strRootPath . $intGallery->getPath())) {
            if (!in_array($parts['dirname'] . '/' . trim($this->txtFileName->Text) . '.' . strtolower($parts['extension']), $files)) {
                $this->rename($this->strRootPath . $intGallery->getPath(), $newPath);

                foreach ($this->tempFolders as $tempFolder) {
                    if (is_file($this->strTempPath . '/_files/' . $tempFolder . $intGallery->getPath())) {
                        $this->rename($this->strTempPath . '/_files/' . $tempFolder . $intGallery->getPath(), $this->strTempPath . '/_files/' . $tempFolder . $this->getRelativePath($newPath));
                    }
                }
            }

            $objGallery = Galleries::loadById($intGallery->getId());
            $objGallery->setName(basename($newPath));
            $objGallery->setPath($this->getRelativePath($newPath));
            $objGallery->setDescription($this->txtFileDescription->Text);
            $objGallery->setAuthor($this->txtFileAuthor->Text);
            $objGallery->setStatus($this->lstIsEnabled->SelectedValue);
            $objGallery->setPostUpdateDate(Q\QDateTime::Now());
            $objGallery->save();

            $objFile = Files::loadById($intGallery->getFileId());
            $objFile->setName(basename($newPath));
            $objFile->setPath($this->getRelativePath($newPath));
            $objFile->setMtime(time());
            $objFile->save();
        }

        if (is_file($newPath)) {
            $this->dlgToastr3->notify();
        } else {
            $this->dlgToastr4->notify();
        }

        $this->intChangeFilesId = null;
        $this->dtgGalleryList->refresh();
    }

    protected function btnCancel_Click(ActionParams $params)
    {
        $this->intChangeFilesId = null;
        $this->dtgGalleryList->refresh();
    }

    /**
     * Get file path without RootPath
     * @param $path
     * @return string
     */
    protected function getRelativePath($path)
    {
        return substr($path, strlen($this->strRootPath));
    }

    /**
     * Get filename without extension
     * @param $filename
     * @return string
     */
    protected function getFileName($filename)
    {
        return substr($filename, 0, strrpos($filename, "."));
    }

    public function rename($old, $new)
    {
        return (!file_exists($new) && file_exists($old)) ? rename($old, $new) : null;
    }

    /**
     * Find all members IDs of a gallery equal to the member ID of the gallery list.
     *
     * @param int $listId
     * @return array
     */
    protected function fullListIds($listId)
    {
        $objFiles = Galleries::loadAll();
        $descendantIds = [];

        foreach ($objFiles as $objFile) {
            if ($objFile->ListId == $listId) {
                $descendantIds[] = $objFile;
            }
        }

        return $descendantIds;
    }
}