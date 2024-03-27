<?php

use QCubed as Q;
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

class GalleryList extends Q\Control\Panel
{
    protected $strRootPath = APP_UPLOADS_DIR;
    /** @var string */
    protected $strRootUrl = APP_UPLOADS_URL;
    /** @var string */
    protected $strTempPath = APP_UPLOADS_TEMP_DIR;
    /** @var string */
    protected $strTempUrl = APP_UPLOADS_TEMP_URL;
    /** @var array */
    public $tempFolders = ['thumbnail', 'medium', 'large'];

    public $dlgModal1;
    public $dlgModal2;
    public $dlgToastr1;

    public $btnAddAlbum;
    public $btnSave;
    public $btnCancel;
    public $lblTitle;
    public $txtTitle;

    protected $dtgGalleries;
    protected $lstItemsPerPage;
    protected $txtFilter;

    public $lblStatusAlbum;
    public $lstStatusAlbum;
    public $lblPostDate;
    public $calPostDate;
    public $lblPostUpdateDate;
    public $calPostUpdateDate;

    protected $intId;
    protected $objAlbum;
    protected $strDateTimeFormat = 'd.m.Y H:i';
    protected $strTemplate = 'GalleryList.tpl.php';

    public function __construct($objParentObject, $strControlId = null)
    {
        try {
            parent::__construct($objParentObject, $strControlId);
        } catch (\QCubed\Exception\Caller $objExc) {
            $objExc->IncrementOffset();
            throw $objExc;
        }

        $this->intId = Application::instance()->context()->queryStringItem('id');
        if (strlen($this->intId)) {
            $this->objAlbum = Albums::load($this->intId);
        } else {
            // does nothing
        }

        $this->createTable();
        $this->createInputs();
        $this->createButtons();
        $this->createModals();
        $this->createToastr();
    }

    protected function createTable()
    {
        $this->dtgGalleries = new Q\Plugin\VauuTable($this);
        $this->dtgGalleries->CssClass = "table vauu-table table-hover table-responsive";

        $col = $this->dtgGalleries->createNodeColumn(t('Title'), QQN::ListOfGalleries()->Title);
        $col->CellStyler->Width = '40%';

        $col = $this->dtgGalleries->createNodeColumn(t('Status'), QQN::ListOfGalleries()->StatusObject);
        $col->HtmlEntities = false;
        $col->CellStyler->Width = '20%';

        $col = $this->dtgGalleries->createNodeColumn(t('Created'), QQN::ListOfGalleries()->PostDate);
        $col->Format = 'DD.MM.YYYY hhhh:mm:ss';
        $col->CellStyler->Width = '20%';

        $col = $this->dtgGalleries->createNodeColumn(t('Modified'), QQN::ListOfGalleries()->PostUpdateDate);
        $col->Format = 'DD.MM.YYYY hhhh:mm:ss';
        $col->CellStyler->Width = '20%';

        $this->dtgGalleries->Paginator = new Bs\Paginator($this);
        $this->dtgGalleries->Paginator->LabelForPrevious = t('Previous');
        $this->dtgGalleries->Paginator->LabelForNext = t('Next');
        $this->dtgGalleries->ItemsPerPage = 10;

        $this->dtgGalleries->UseAjax = true;
        $this->dtgGalleries->SortColumnIndex = 2;
        $this->dtgGalleries->SortDirection = -1;
        $this->dtgGalleries->setDataBinder('dtgGalleries_Bind', $this);
        $this->dtgGalleries->RowParamsCallback = [$this, 'dtgGalleries_GetRowParams'];
        $this->dtgGalleries->addAction(new Q\Event\CellClick(0, null, Q\Event\CellClick::rowDataValue('value')),
            new Q\Action\AjaxControl($this,'dtgGalleriesRow_Click'));

        ////////////////////////////

        $this->lstItemsPerPage = new Q\Plugin\Select2($this);
        $this->lstItemsPerPage->addCssFile(QCUBED_FILEUPLOAD_ASSETS_URL . '/css/select2-web-vauu.css');
        $this->lstItemsPerPage->MinimumResultsForSearch = -1;
        $this->lstItemsPerPage->Theme = 'web-vauu';
        $this->lstItemsPerPage->Width = '100%';
        $this->lstItemsPerPage->SelectionMode = Q\Control\ListBoxBase::SELECTION_MODE_SINGLE;
        $this->lstItemsPerPage->SelectedValue = $this->dtgGalleries->ItemsPerPage;
        $this->lstItemsPerPage->addItems(array(10, 25, 50, 100));
        $this->lstItemsPerPage->AddAction(new Change(), new Q\Action\AjaxControl($this,'lstItemsPerPage_Change'));

        $this->txtFilter = new Bs\TextBox($this);
        $this->txtFilter->Placeholder = t('Search...');
        $this->txtFilter->TextMode = Q\Control\TextBoxBase::SEARCH;
        $this->txtFilter->setHtmlAttribute('autocomplete', 'off');
        $this->txtFilter->addCssClass('search-box');
        $this->addFilterActions();
    }

    protected function createInputs()
    {
        $this->txtTitle = new Bs\TextBox($this);
        $this->txtTitle->Placeholder = t('The title of the new album');
        $this->txtTitle->MaxLength = ListOfGalleries::TitleMaxLength;
        $this->txtTitle->setHtmlAttribute('autocomplete', 'off');
        $this->txtTitle->setCssStyle('float', 'left');
        $this->txtTitle->setCssStyle('margin-right', '10px');
        $this->txtTitle->Width = '70%';
        $this->txtTitle->Display = false;
        $this->txtTitle->AddAction(new Q\Event\EnterKey(), new Q\Action\AjaxControl($this,'btnSave_Click'));
        $this->txtTitle->addAction(new Q\Event\EnterKey(), new Q\Action\Terminate());
        $this->txtTitle->AddAction(new Q\Event\EscapeKey(), new Q\Action\AjaxControl($this,'btnCancel_Click'));
        $this->txtTitle->addAction(new Q\Event\EscapeKey(), new Q\Action\Terminate());

        $this->lblStatusAlbum = new Q\Plugin\Label($this);
        $this->lblStatusAlbum->Text = t('Is activated');
        $this->lblStatusAlbum->addCssClass('col-md-3');
        $this->lblStatusAlbum->setCssStyle('font-weight', 400);

        $this->lstStatusAlbum = new Q\Plugin\RadioList($this);
        $this->lstStatusAlbum->addItems([1 => t('Active'), 2 => t('Inactive')]);
        $this->lstStatusAlbum->ButtonGroupClass = 'radio radio-orange radio-inline';

        $this->lblPostDate = new Q\Plugin\Label($this);
        $this->lblPostDate->Text = t('Created');
        $this->lblPostDate->addCssClass('col-md-3');
        $this->lblPostDate->setCssStyle('font-weight', 400);

        $this->calPostDate = new Bs\Label($this);
        $this->calPostDate->setCssStyle('font-weight', 400);
        $this->calPostDate->setCssStyle('font-size', '13px');
        $this->calPostDate->setCssStyle('line-height', 2.5);

        $this->lblPostUpdateDate = new Q\Plugin\Label($this);
        $this->lblPostUpdateDate->Text = t('Updated');
        $this->lblPostUpdateDate->addCssClass('col-md-3');
        $this->lblPostUpdateDate->setCssStyle('font-weight', 400);
        $this->lblPostUpdateDate->Display = false;

        $this->calPostUpdateDate = new Bs\Label($this);
        $this->calPostUpdateDate->setCssStyle('font-weight', 400);
        $this->calPostUpdateDate->setCssStyle('font-size', '13px');
        $this->calPostUpdateDate->setCssStyle('line-height', 2.5);
        $this->calPostUpdateDate->Display = false;
    }

    public function createButtons()
    {
        $this->btnAddAlbum = new Q\Plugin\Button($this);
        $this->btnAddAlbum->Text = t(' Add album');
        $this->btnAddAlbum->Glyph = 'fa fa-plus';
        $this->btnAddAlbum->CssClass = 'btn btn-orange';
        $this->btnAddAlbum->addWrapperCssClass('center-button');
        $this->btnAddAlbum->setCssStyle('float', 'left');
        $this->btnAddAlbum->setCssStyle('margin-right', '10px');
        $this->btnAddAlbum->CausesValidation = false;
        $this->btnAddAlbum->addAction(new Q\Event\Click(), new Q\Action\AjaxControl($this, 'btnAddAlbum_Click'));

        $this->btnSave = new Q\Plugin\Button($this);
        $this->btnSave->Text = t('Save');
        $this->btnSave->CssClass = 'btn btn-orange';
        $this->btnSave->setCssStyle('float', 'left');
        $this->btnSave->setCssStyle('margin-right', '10px');
        $this->btnSave->addWrapperCssClass('center-button');
        $this->btnSave->Display = false;
        $this->btnSave->PrimaryButton = true;
        $this->btnSave->CausesValidation = true;
        $this->btnSave->addAction(new Q\Event\Click(), new Q\Action\AjaxControl($this,'btnSave_Click'));

        $this->btnCancel = new Q\Plugin\Button($this);
        $this->btnCancel->Text = t('Cancel');
        $this->btnCancel->CssClass = 'btn btn-default';
        $this->btnCancel->setCssStyle('float', 'left');
        $this->btnCancel->addWrapperCssClass('center-button');
        $this->btnCancel->Display = false;
        $this->btnCancel->CausesValidation = false;
        $this->btnCancel->addAction(new Q\Event\Click(), new Q\Action\AjaxControl($this, 'btnCancel_Click'));
    }

    public function createModals()
    {
        $this->dlgModal1 = new Bs\Modal($this);
        $this->dlgModal1->Title = t('Tip');
        $this->dlgModal1->Text = t('<p style="margin-top: 15px;">Album cannot be created without name!</p>');
        $this->dlgModal1->HeaderClasses = 'btn-darkblue';
        $this->dlgModal1->addCloseButton(t("I close the window"));

        $this->dlgModal2 = new Bs\Modal($this);
        $this->dlgModal2->Text = t('<p style="line-height: 25px; margin-bottom: 2px;">Cannot create a album with the same name!</p>');
        $this->dlgModal2->Title = t("Warning");
        $this->dlgModal2->HeaderClasses = 'btn-danger';
        $this->dlgModal2->addCloseButton(t("I understand"));
    }

    public function createToastr()
    {
        $this->dlgToastr1 = new Q\Plugin\Toastr($this);
        $this->dlgToastr1->AlertType = Q\Plugin\Toastr::TYPE_SUCCESS;
        $this->dlgToastr1->PositionClass = Q\Plugin\Toastr::POSITION_TOP_CENTER;
        $this->dlgToastr1->Message = t('<strong>Well done!</strong> The album has been created and saved.');
        $this->dlgToastr1->ProgressBar = true;
    }

    protected function btnAddAlbum_Click(ActionParams $params)
    {
        $this->txtTitle->Display = true;
        $this->btnSave->Display = true;
        $this->btnCancel->Display = true;
        $this->txtTitle->Text = null;
        $this->txtTitle->focus();
        $this->btnAddAlbum->Enabled = false;
    }

    protected function btnSave_Click(ActionParams $params)
    {
        $parentFolder = $this->objAlbum->getPath();
        $path = $this->strRootPath . $parentFolder;
        $scanned_directory = array_diff(scandir($path), array('..', '.'));

        if (in_array(QString::sanitizeForUrl(trim($this->txtTitle->Text)), $scanned_directory)) {
            $this->dlgModal2->showDialogBox();
            $this->txtTitle->Text = null;
        } else if (!$this->txtTitle->Text) {
            $this->dlgModal1->showDialogBox();
        } else {
            $this->makeAlbums($this->txtTitle->Text);

            $this->txtTitle->Display = false;
            $this->btnSave->Display = false;
            $this->btnCancel->Display = false;
            $this->btnAddAlbum->Enabled = true;
            $this->txtTitle->Text = null;
        }
    }

    protected function makeAlbums($text)
    {
        $fullPath = $this->strRootPath . $this->objAlbum->getPath() . "/" . QString::sanitizeForUrl(trim($text));
        $relativePath = $this->getRelativePath($fullPath);

        if ($this->strRootPath . $this->objAlbum->getPath()) {
            Folder::makeDirectory($fullPath, 0777);
        }

        $parentId = $this->objAlbum->getFolderId();
        $parentPath = $this->strRootPath . $this->objAlbum->getPath();

        if ($parentId) {
            $objFolder = Folders::loadById($parentId);
            if ($objFolder->getLockedFile() !== 1) {
                $objFolder->setMtime(filemtime($parentPath ));
                $objFolder->setLockedFile(1);
                $objFolder->save();
            }
        }

        $objAddFolder = new Folders();
        $objAddFolder->setParentId($this->objAlbum->getFolderId());
        $objAddFolder->setPath($relativePath);
        $objAddFolder->setName(trim($text));
        $objAddFolder->setType('dir');
        $objAddFolder->setMtime(filemtime($fullPath));
        $objAddFolder->setLockedFile(0);
        $objAddFolder->setActivitiesLocked(1);
        $objAddFolder->save();

        $objAddGallery = new ListOfGalleries();
        $objAddGallery->setAlbumId($this->objAlbum->getId());
        $objAddGallery->setFolderId($objAddFolder->getId());
        $objAddGallery->setTitle(trim($text));
        $objAddGallery->setPath($relativePath);
        $objAddGallery->setTitleSlug(basename(trim($fullPath)));
        $objAddGallery->setPostDate(Q\QDateTime::Now());
        $objAddGallery->setStatus(2);
        $objAddGallery->save();

        foreach ($this->tempFolders as $tempFolder) {
            $tempPath = $this->strTempPath . '/_files/' . $tempFolder . $relativePath;
            Folder::makeDirectory($tempPath, 0777);
        }

        $this->dtgGalleries->refresh();
    }

    protected function btnUpdate_Click(ActionParams $params)
    {
        $scanned_directory = array_diff(scandir($this->strRootPath), array('..', '.'));

        if (!$this->txtTitle->Text) {
            $this->dlgModal1->showDialogBox();
        } else if (!in_array(trim($this->txtTitle->Text), $scanned_directory)) {
            $this->updateAlbums($this->txtTitle->Text, $this->lstStatusAlbum->SelectedValue);
        } else {
            $this->updateAlbums($this->txtTitle->Text, $this->lstStatusAlbum->SelectedValue);
        }
    }

    protected function updateAlbums($text, $status)
    {
        $fullPath = $this->strRootPath . "/" . QString::sanitizeForUrl(trim($text));
        $relativePath = $this->getRelativePath($fullPath);

        $objUpdateAlbum = Albums::loadById($this->objAlbum->getid());
        $oldPath = $objUpdateAlbum->getPath();

        if ($this->strRootPath) {
            $this->rename($this->strRootPath . "/" . $objUpdateAlbum->getPath(), $fullPath);
        }

        $objUpdateFolder = Folders::loadById($this->objAlbum->getFolderId());
        $objUpdateFolder->setName(trim($text));
        $objUpdateFolder->setPath($relativePath);
        $objUpdateFolder->setMtime(filemtime($fullPath));
        $objUpdateFolder->save();

        $objUpdateAlbum->setTitle(trim($text));
        $objUpdateAlbum->setPath($relativePath);
        $objUpdateAlbum->setTitleSlug(QString::sanitizeForUrl(trim($text)));
        $objUpdateAlbum->setPostUpdateDate(Q\QDateTime::Now());
        $objUpdateAlbum->setIsEnabled($status);
        $objUpdateAlbum->save();

        foreach ($this->tempFolders as $tempFolder) {
            $tempPath = $this->strTempPath . '/_files/' . $tempFolder . $oldPath;
            $newPath = $this->strTempPath . '/_files/' . $tempFolder . "/" . QString::sanitizeForUrl(trim($text));
            $this->rename($tempPath, $newPath);
        }

        $this->lblPostUpdateDate->Display = true;
        $this->calPostUpdateDate->Display = true;
        $this->calPostUpdateDate->Text = $objUpdateAlbum->getPostUpdateDate()->qFormat('DD.MM.YYYY hhhh:mm:ss');

        $this->dlgToastr1->notify();
    }

    protected function btnCancel_Click(ActionParams $params)
    {
        $this->txtTitle->Display = false;
        $this->btnSave->Display = false;
        $this->btnCancel->Display = false;
        $this->btnAddAlbum->Enabled = true;
        $this->txtTitle->Text = null;
    }

    public function dtgGalleries_GetRowParams($objRowObject, $intRowIndex)
    {
        $strKey = $objRowObject->primaryKey();
        $params['data-value'] = $strKey;
        return $params;
    }

    protected function dtgGalleriesRow_Click(ActionParams $params)
    {
        $intGalleryId = intval($params->ActionParameter);
        $intGallery = ListOfGalleries::load($intGalleryId);
        $intAlbumId = $intGallery->getAlbumId();
        $intFolderId = $intGallery->getFolderId();
        Application::redirect('gallery.php' . '?id=' . $intGalleryId . '&album=' . $intAlbumId . '&folder=' . $intFolderId );
    }

    protected function lstItemsPerPage_Change(ActionParams $params)
    {
        $this->dtgGalleries->refresh();
    }

    protected function addFilterActions()
    {
        $this->txtFilter->addAction(new Input(300), new Q\Action\AjaxControl($this, 'filterChanged'));
        $this->txtFilter->addActionArray(new EnterKey(),
            [
                new Q\Action\AjaxControl($this, 'FilterChanged'),
                new Terminate()
            ]
        );
    }

    protected function filterChanged()
    {
        $this->dtgGalleries->refresh();
    }

    public function dtgGalleries_Bind()
    {
        $strSearchValue = $this->txtFilter->Text;
        $strSearchValue = trim($strSearchValue);

        if (is_null($strSearchValue) || $strSearchValue === '') {
            $objCondition = QQ::all();
        } else {
            $objCondition = QQ::orCondition(
                QQ::like(QQN::ListOfGalleries()->Title, "%" . $strSearchValue . "%"),
                QQ::like(QQN::ListOfGalleries()->PostDate, "%" . $strSearchValue . "%"),
                QQ::like(QQN::ListOfGalleries()->PostUpdateDate, "%" . $strSearchValue . "%")
            );
        }

        $this->dtgGalleries->TotalItemCount = ListOfGalleries::countAll();

        $objClauses = array();
        if ($objClause = $this->dtgGalleries->OrderByClause)
            $objClauses[] = $objClause;
        if ($objClause = $this->dtgGalleries->LimitClause)
            $objClauses[] = $objClause;

        $this->dtgGalleries->DataSource = ListOfGalleries::queryArray($objCondition, $objClauses);
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

    public function rename($old, $new)
    {
        return (!file_exists($new) && file_exists($old)) ? rename($old, $new) : null;
    }
}