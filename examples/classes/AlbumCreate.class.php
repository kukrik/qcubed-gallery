<?php

use QCubed as Q;
use QCubed\Bootstrap as Bs;
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

class AlbumCreate extends Q\Control\Panel
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

    public $lblTitle;
    public $txtTitle;
    public $lblStatusAlbum;
    public $lstStatusAlbum;
    public $lblPostDate;
    public $calPostDate;

    public $btnAddAlbum;
    public $btnSave;
    public $btnCancel;

    protected $intId;
    protected $objAlbum;

    protected $strTemplate = 'AlbumCreate.tpl.php';

    public function __construct($objParentObject, $strControlId = null)
    {
        try {
            parent::__construct($objParentObject, $strControlId);
        } catch (\QCubed\Exception\Caller $objExc) {
            $objExc->IncrementOffset();
            throw $objExc;
        }

        $this->createInputs();
        $this->createButtons();
        $this->createModals();
    }

    protected function createInputs()
    {
        $this->lblTitle = new Q\Plugin\Label($this);
        $this->lblTitle->Text = t('Title');
        $this->lblTitle->addCssClass('col-md-3');
        $this->lblTitle->setCssStyle('font-weight', 400);
        $this->lblTitle->Required = true;

        $this->txtTitle = new Bs\TextBox($this);
        $this->txtTitle->Placeholder = t('The title of the new album');
        $this->txtTitle->MaxLength = Albums::TitleMaxLength;
        $this->txtTitle->setHtmlAttribute('autocomplete', 'off');
        $this->txtTitle->setHtmlAttribute('required', 'required');
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
        $this->lstStatusAlbum->SelectedValue = 2;
        $this->lstStatusAlbum->ButtonGroupClass = 'radio radio-orange radio-inline';

        $this->lblPostDate = new Q\Plugin\Label($this);
        $this->lblPostDate->Text = t('Created');
        $this->lblPostDate->addCssClass('col-md-3');
        $this->lblPostDate->setCssStyle('font-weight', 400);
        $this->lblPostDate->Display = false;

        $this->calPostDate = new Bs\Label($this);
        $this->calPostDate->setCssStyle('font-weight', 400);
        $this->calPostDate->setCssStyle('font-size', '13px');
        $this->calPostDate->setCssStyle('line-height', 2.5);
        $this->calPostDate->Display = false;
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
        $this->btnSave->addWrapperCssClass('center-button');
        $this->btnSave->setCssStyle('float', 'left');
        $this->btnSave->setCssStyle('margin-right', '10px');
        $this->btnSave->PrimaryButton = true;
        $this->btnSave->CausesValidation = true;
        $this->btnSave->addAction(new Q\Event\Click(), new Q\Action\AjaxControl($this, 'btnSave_Click'));

        $this->btnCancel = new Q\Plugin\Button($this);
        $this->btnCancel->Text = t('Cancel');
        $this->btnCancel->CssClass = 'btn btn-default';
        $this->btnCancel->setCssStyle('float', 'left');
        $this->btnCancel->addWrapperCssClass('center-button');
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
        $this->dlgModal1->addAction(new \QCubed\Event\DialogButton(), new \QCubed\Action\AjaxControl($this, 'restoreTitle_Click'));
        $this->dlgModal1->addAction(new Bs\Event\ModalHidden(), new \QCubed\Action\AjaxControl($this, 'restoreTitle_Click'));

        $this->dlgModal2 = new Bs\Modal($this);
        $this->dlgModal2->Text = t('<p style="line-height: 25px; margin-bottom: 2px;">Cannot create a folder with the same name!</p>');
        $this->dlgModal2->Title = t("Warning");
        $this->dlgModal2->HeaderClasses = 'btn-danger';
        $this->dlgModal2->addCloseButton(t("I understand"));
    }

    protected function btnSave_Click(ActionParams $params)
    {
        $path = $this->strRootPath;
        $scanned_directory = array_diff(scandir($path), array('..', '.'));

        if (in_array(trim($this->txtTitle->Text), $scanned_directory)) {
            $this->dlgModal2->showDialogBox();
            $this->txtTitle->focus();
        } else if (!$this->txtTitle->Text) {
            $this->dlgModal1->showDialogBox();
        } else {
            $this->makeAlbums($this->txtTitle->Text, $this->lstStatusAlbum->SelectedValue);
        }
    }

    protected function btnCancel_Click(ActionParams $params)
    {
        $this->txtTitle->Text = "";
        Application::redirect("gallerymanager.php", false);
    }

    protected function makeAlbums($text, $status)
    {
        $fullPath = $this->strRootPath . "/" . QString::sanitizeForUrl(trim($text));
        $relativePath = $this->getRelativePath($fullPath);

        if ($this->strRootPath) {
            Folder::makeDirectory($fullPath, 0777);
        }

        $objAddFolder = new Folders();
        $objAddFolder->setParentId(1);
        $objAddFolder->setPath($relativePath);
        $objAddFolder->setName(trim($text));
        $objAddFolder->setType('dir');
        $objAddFolder->setMtime(filemtime($fullPath));
        $objAddFolder->setLockedFile(0);
        $objAddFolder->setActivitiesLocked(1);
        $objAddFolder->save();

        $objAddAlbum = new Albums();
        $objAddAlbum->setFolderId($objAddFolder->getId());
        $objAddAlbum->setTitle(trim($text));
        $objAddAlbum->setPath($relativePath);
        $objAddAlbum->setTitleSlug(QString::sanitizeForUrl(trim($text)));
        $objAddAlbum->setPostDate(Q\QDateTime::Now());
        $objAddAlbum->setIsEnabled($status);
        $objAddAlbum->save();

        foreach ($this->tempFolders as $tempFolder) {
            $tempPath = $this->strTempPath . '/_files/' . $tempFolder . $relativePath;
            Folder::makeDirectory($tempPath, 0777);
        }

        $this->lblPostDate->Display = true;
        $this->calPostDate->Display = true;
        $this->calPostDate->Text = $objAddAlbum->getPostDate()->qFormat('DD.MM.YYYY hhhh:mm:ss');

        Application::redirect("gallery_list.php");
    }


    public function restoreTitle_Click(ActionParams $params)
    {
        $this->txtTitle->Text = $this->objAlbum ? $this->objAlbum->Title : null;
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