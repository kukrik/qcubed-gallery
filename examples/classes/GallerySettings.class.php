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

class GallerySettings extends Q\Control\Panel
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
    public $dlgToastr1;
    public $dlgToastr2;

    public $btnAddAlbum;

    public $lblTitle;
    public $txtTitle;
    public $lblStatusAlbum;
    public $lstStatusAlbum;
    public $lblPostDate;
    public $calPostDate;
    public $lblPostUpdateDate;
    public $calPostUpdateDate;

    public $btnSave;
    public $btnUpdate;
    public $btnCancel;

    protected $intId;
    protected $objAlbum;
    protected $oldTitle;
    protected $strNewPath;

    protected $strTemplate = 'GallerySettings.tpl.php';

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

        $this->createInputs();
        $this->createButtons();
        $this->createModals();
        $this->createToastr();
    }

    protected function createInputs()
    {
        $this->lblTitle = new Q\Plugin\Label($this);
        $this->lblTitle->Text = t('Title');
        $this->lblTitle->addCssClass('col-md-3');
        $this->lblTitle->setCssStyle('font-weight', 400);
        $this->lblTitle->Required = true;

        $this->txtTitle = new Bs\TextBox($this);
        $this->txtTitle->Placeholder = t('The title of the album');
        $this->txtTitle->Text = $this->objAlbum->Title ? $this->objAlbum->Title : null;
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
        $this->lstStatusAlbum->SelectedValue = $this->objAlbum->IsEnabled ? $this->objAlbum->IsEnabled : null;
        $this->lstStatusAlbum->ButtonGroupClass = 'radio radio-orange radio-inline';

        $this->lblPostDate = new Q\Plugin\Label($this);
        $this->lblPostDate->Text = t('Created');
        $this->lblPostDate->addCssClass('col-md-3');
        $this->lblPostDate->setCssStyle('font-weight', 400);

        $this->calPostDate = new Bs\Label($this);
        $this->calPostDate->Text = $this->objAlbum->PostDate ? $this->objAlbum->PostDate->qFormat('DD.MM.YYYY hhhh:mm:ss') : null;
        $this->calPostDate->setCssStyle('font-weight', 400);
        $this->calPostDate->setCssStyle('font-size', '13px');
        $this->calPostDate->setCssStyle('line-height', 2.5);

        $this->lblPostUpdateDate = new Q\Plugin\Label($this);
        $this->lblPostUpdateDate->Text = t('Updated');
        $this->lblPostUpdateDate->addCssClass('col-md-3');
        $this->lblPostUpdateDate->setCssStyle('font-weight', 400);
        $this->lblPostUpdateDate->Display = false;

        $this->calPostUpdateDate = new Bs\Label($this);
        $this->calPostUpdateDate->Text = $this->objAlbum->PostUpdateDate ? $this->objAlbum->PostUpdateDate->qFormat('DD.MM.YYYY hhhh:mm:ss') : null;
        $this->calPostUpdateDate->setCssStyle('font-weight', 400);
        $this->calPostUpdateDate->setCssStyle('font-size', '13px');
        $this->calPostUpdateDate->setCssStyle('line-height', 2.5);
        $this->calPostUpdateDate->Display = false;

        if (!empty($this->objAlbum->getPostUpdateDate())) {
            $this->lblPostUpdateDate->Display = true;
            $this->calPostUpdateDate->Display = true;
        } else {
            $this->lblPostUpdateDate->Display = false;
            $this->calPostUpdateDate->Display = false;
        }
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

        $this->btnUpdate = new Q\Plugin\Button($this);
        $this->btnUpdate->Text = t('Update');
        $this->btnUpdate->CssClass = 'btn btn-orange';
        $this->btnUpdate->setCssStyle('float', 'left');
        $this->btnUpdate->setCssStyle('margin-right', '10px');
        $this->btnUpdate->addWrapperCssClass('center-button');
        $this->btnUpdate->CausesValidation = false;
        $this->btnUpdate->addAction(new Q\Event\Click(), new Q\Action\AjaxControl($this,'btnUpdate_Click'));

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
    }

    public function createToastr()
    {
        $this->dlgToastr1 = new Q\Plugin\Toastr($this);
        $this->dlgToastr1->AlertType = Q\Plugin\Toastr::TYPE_SUCCESS;
        $this->dlgToastr1->PositionClass = Q\Plugin\Toastr::POSITION_TOP_CENTER;
        $this->dlgToastr1->Message = t('<strong>Well done!</strong> The album has been edited or the album status has been changed.');
        $this->dlgToastr1->ProgressBar = true;

        $this->dlgToastr2 = new Q\Plugin\Toastr($this);
        $this->dlgToastr2->AlertType = Q\Plugin\Toastr::TYPE_WARNING;
        $this->dlgToastr2->PositionClass = Q\Plugin\Toastr::POSITION_TOP_CENTER;
        $this->dlgToastr2->Message = t('<strong>Sorry!</strong> Album name change failed!');
        $this->dlgToastr2->ProgressBar = true;
    }

    protected function btnUpdate_Click(ActionParams $params)
    {
        $scanned_directory = array_diff(scandir($this->strRootPath), array('..', '.'));

        if (!$this->txtTitle->Text) {
            $this->dlgModal1->showDialogBox();
        } else if (!in_array(QString::sanitizeForUrl(trim($this->txtTitle->Text)), $scanned_directory)) {
            $this->updateAlbums($this->txtTitle->Text, $this->lstStatusAlbum->SelectedValue);
        } else {
            $this->updateAlbums($this->txtTitle->Text, $this->lstStatusAlbum->SelectedValue);
        }
    }

    protected function btnCancel_Click(ActionParams $params)
    {
        $this->txtTitle->Text = "";
        Application::redirect('gallery_list.php' . '?id=' . $this->objAlbum->Id, false);
    }

    protected function updateAlbums($text, $status)
    {
        $tempFolderItems = [];
        $tempListItems = [];
        $tempFileIds = [];

        $objListOfGalleries = ListOfGalleries::loadAll();
        $objGalleries = Galleries::loadAll();
        $objFolders = Folders::loadAll();
        $objFiles = Files::loadAll();

        $objUpdateAlbum = Albums::loadById($this->objAlbum->getid());
        $oldPath = $objUpdateAlbum->getPath();
        $objUpdateFolder = Folders::loadById($this->objAlbum->getFolderId());

        $fullPath = $this->strRootPath . "/" . QString::sanitizeForUrl(trim($text));
        $relativePath = $this->getRelativePath($fullPath);

        if ($this->strRootPath) {
            $this->rename($this->strRootPath . "/" . $objUpdateAlbum->getPath(), $fullPath);
        }

        $objUpdateAlbum->setTitle(trim($text));
        $objUpdateAlbum->setPath($relativePath);
        $objUpdateAlbum->setTitleSlug(QString::sanitizeForUrl(trim($text)));
        $objUpdateAlbum->setPostUpdateDate(Q\QDateTime::Now());
        $objUpdateAlbum->setIsEnabled($status);
        $objUpdateAlbum->save();

        $objUpdateFolder->setName(trim($text));
        $objUpdateFolder->setPath($relativePath);
        $objUpdateFolder->setMtime(filemtime($fullPath));
        $objUpdateFolder->save();

        foreach ($this->tempFolders as $tempFolder) {
            $tempPath = $this->strTempPath . '/_files/' . $tempFolder . $oldPath;
            $newPath = $this->strTempPath . '/_files/' . $tempFolder . "/" . QString::sanitizeForUrl(trim($text));
            $this->rename($tempPath, $newPath);
        }

        $tempFolderItems = $this->fullScanIds($this->objAlbum->getFolderId());
        $tempListItems = $this->fullScanGalleryIds($this->objAlbum->getId());

        if (count($tempFolderItems)) {
            foreach ($objFolders as $objFolder) {
                foreach ($tempFolderItems as $temp) {
                    if ($temp == $objFolder->getId()) {
                        $newPath = str_replace($oldPath, QString::sanitizeForUrl(trim($this->txtTitle->Text)), $objFolder->getPath());
                        $this->strNewPath = $this->strRootPath . '/' . $newPath;

                        if (is_dir($this->strRootPath . $objFolder->getPath())) {
                            $this->rename($this->strRootPath . $objFolder->getPath(), $this->strNewPath);
                        }

                        foreach ($this->tempFolders as $tempFolder) {
                            if (is_dir($this->strTempPath . '/_files/' . $tempFolder . $objFolder->getPath())) {
                                $this->rename($this->strTempPath . '/_files/' . $tempFolder . $objFolder->getPath(), $this->strTempPath . '/_files/' . $tempFolder . $this->getRelativePath($this->strNewPath));
                            }
                        }

                        $objFolder = Folders::loadById($objFolder->getId());
                        $objFolder->setName(basename($this->strNewPath));
                        $objFolder->setPath($this->getRelativePath($this->strNewPath));
                        $objFolder->setMtime(time());
                        $objFolder->save();
                    }
                }
            }
        }

        if (count($tempListItems)) {
            foreach ($objListOfGalleries as $objGallery) {
                foreach ($tempListItems as $galleryItem) {
                    if ($galleryItem == $objGallery->getId()) {
                        $newPath = str_replace($oldPath, QString::sanitizeForUrl(trim($this->txtTitle->Text)), $objGallery->getPath());
                        $this->strNewPath = $this->strRootPath . '/' . $newPath;

                        $objGallery = ListOfGalleries::loadById($objGallery->getId());
                        $objGallery->setPath($this->getRelativePath($this->strNewPath));
                        $objGallery->setPostUpdateDate(Q\QDateTime::Now());
                        $objGallery->save();
                    }
                }
            }
        }

        if (Galleries::countAll()) {
            foreach ($objGalleries as $objGallery) {
                $tempFileIds[] = $objGallery->getFileId();
                $replacePath = str_replace(basename($oldPath), QString::sanitizeForUrl(trim($this->txtTitle->Text)), $objGallery->getPath());

                $objGallery->setPath($replacePath);
                $objGallery->setPostUpdateDate(Q\QDateTime::Now());
                $objGallery->save();
            }

            foreach ($tempFileIds as $tempFileId) {
                foreach ($objFiles as $objFile) {
                    if ($tempFileId == $objFile->getId()) {
                        $replacePath = str_replace(basename($oldPath), QString::sanitizeForUrl(trim($this->txtTitle->Text)), $objFile->getPath());

                        $objFile->setPath($replacePath);
                        $objFile->setMtime(time());
                        $objFile->save();
                    }
                }
            }
        }

        if (file_exists($this->strRootPath . $objUpdateFolder->getPath())) {
            $this->dlgToastr1->notify();
        } else {
            $this->dlgToastr2->notify();
        }

        $this->lblPostUpdateDate->Display = true;
        $this->calPostUpdateDate->Display = true;
        $this->calPostUpdateDate->Text = $objUpdateAlbum->getPostUpdateDate()->qFormat('DD.MM.YYYY hhhh:mm:ss');
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

    /**
     * Recursively scan for all descendant folder IDs given a parent folder ID.
     *
     * @param int $parentId
     * @return array
     */
    protected function fullScanIds($parentId)
    {
        $descendantIds = [];
        $objFolders = Folders::loadAll();

        foreach ($objFolders as $objFolder) {
            if ($objFolder->ParentId == $parentId) {
                $descendantIds[] = $objFolder->Id;
                array_push($descendantIds, ...$this->fullScanIds($objFolder->Id));
            }
        }

        return $descendantIds;
    }

    /**
     * Recursively scan for all descendant gallery IDs given a parent album ID.
     *
     * @param int $parentId
     * @return array
     */
    protected function fullScanGalleryIds($albumId)
    {
        $descendantIds = [];
        $objListOfGalleries = ListOfGalleries::loadAll();

        foreach ($objListOfGalleries as $objGallery) {
            if ($objGallery->AlbumId == $albumId) {
                $descendantIds[] = $objGallery->Id;
                array_push($descendantIds, ...$this->fullScanGalleryIds($objGallery->Id));
            }
        }

        return $descendantIds;
    }
}