<?php
require_once('qcubed.inc.php');


error_reporting(E_ALL); // Error engine - always ON!
ini_set('display_errors', TRUE); // Error display - OFF in production env or real server
ini_set('log_errors', TRUE); // Error logging

use QCubed as Q;
use QCubed\Action\ActionParams;
use QCubed\Project\Application;
use QCubed\Bootstrap as Bs;
use QCubed\Project\Control\ControlBase;
use QCubed\Project\Control\FormBase as Form;
use QCubed\Query\QQ;


class ExamplesForm extends Form
{
    protected $objGallery;
    protected $lblTitle;
    protected $intGalleriesList;
    protected $objGalleriesList;
    protected $btnBack;

    protected function formCreate()
    {
        $this->intGalleriesList = Application::instance()->context()->queryStringItem('id');
        if (strlen($this->intGalleriesList)) {
            $this->objGalleriesList = ListOfGalleries::load($this->intGalleriesList);
        }

        $this->objGallery = new Q\Plugin\NanoGallery($this);
        $this->objGallery->createNodeParams([$this, 'GalleriesList_Draw']);
        $this->objGallery->setDataBinder('GalleriesList_Bind');

        $this->objGallery->ItemsBaseURL = $this->objGallery->TempUrl . '/_files';
        $this->objGallery->ThumbnailWidth = 200;
        $this->objGallery->ThumbnailHeight = 150;
        $this->objGallery->ThumbnailBorderVertical = 0;
        $this->objGallery->ThumbnailBorderHorizontal = 0;
        $this->objGallery->ThumbnailGutterWidth = 15;
        $this->objGallery->ThumbnailGutterHeight = 15;
        $this->objGallery->ThumbnailAlignment = 'center';
        $this->objGallery->ImageTransition = 'swipe';
        $this->objGallery->GalleryDisplayMode = 'rows';
        $this->objGallery->GalleryMaxRows = 1;
        $this->objGallery->GalleryMaxItems = null;
        $this->objGallery->ThumbnailLabel = [
            "position" => "onBottom","display" => false
        ];
        $this->objGallery->ViewerToolbar = [
            "display" => true, "standard"=> "label", "fullWidth" => true, "minimized" =>  "minimizeButton, label, fullscreenButton, downloadButton, infoButton"
        ];
        $this->objGallery->ViewerTools = [
            "topLeft" => "pageCounter",
            "topRight" => "playPauseButton, zoomButton, rotateLeftButton, rotateRightButton, fullscreenButton, shareButton, downloadButton, closeButton"
        ];

        $this->objGallery->LocationHash = false;

        $this->lblTitle = new Q\Plugin\Label($this);
        $this->lblTitle->Text = $this->objGalleriesList->Title;
        $this->lblTitle->setCssStyle('font-weight', 400);
        $this->lblTitle->UseWrapper = false;

        $this->btnBack = new Bs\Button($this);
        $this->btnBack->Text = t('Back');
        $this->btnBack->CssClass = 'btn btn-default';
        $this->btnBack->UseWrapper = false;
        $this->btnBack->addAction(new Q\Event\Click(), new Q\Action\Ajax( 'btnBack_Click'));
    }

    protected function GalleriesList_Bind()
    {
        $this->objGallery->DataSource = ListOfGalleries::queryArray(
            QQ::Equal(QQN::ListOfGalleries()->Id, $this->intGalleriesList),
            QQ::clause(QQ::expand(QQN::ListOfGalleries()->GalleriesAsList))
        );
    }

    public function GalleriesList_Draw(ListOfGalleries $objList)
    {
        $a['list_description'] = $objList->ListDescription;
        $a['list_author'] = $objList->ListAuthor;
        $a['path'] = $objList->_GalleriesAsList->Path;
        $a['description'] = $objList->_GalleriesAsList->Description;
        $a['author'] = $objList->_GalleriesAsList->Author;
        $a['status'] = $objList->_GalleriesAsList->Status;
        return $a;
    }

    protected function btnBack_Click(ActionParams $params)
    {
        Application::redirect('list.php');
    }
}
ExamplesForm::Run('ExamplesForm');