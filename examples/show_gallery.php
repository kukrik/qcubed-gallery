<?php
require_once('qcubed.inc.php');


error_reporting(E_ALL); // Error engine - always ON!
ini_set('display_errors', TRUE); // Error display - OFF in production env or real server
ini_set('log_errors', TRUE); // Error logging

use QCubed as Q;
use QCubed\Project\Control\ControlBase;
use QCubed\Project\Control\FormBase as Form;
use QCubed\Query\QQ;


class ExamplesForm extends Form
{
    protected $objGallery;

    protected function formCreate()
    {
        $this->objGallery = new Q\Plugin\NanoGallery($this);
        $this->objGallery->createNodeParams([$this, 'Gallery_Draw']);
        $this->objGallery->setDataBinder('Gallery_Bind');

        $this->objGallery->ItemsBaseURL = $this->objGallery->TempUrl . '/_files';
        $this->objGallery->ThumbnailWidth = 150;
        $this->objGallery->ThumbnailHeight = 150;
    }


    protected function Gallery_Bind()
    {
        $this->objGallery->DataSource = Galleries::QueryArray(
            QQ::Equal(QQN::Galleries()->ListId, 11));
    }

    public function Gallery_Draw(Galleries $objGallery)
    {
        $a['path'] = $objGallery->Path;
        $a['description'] = $objGallery->Description;
        $a['author'] = $objGallery->Author;
        return $a;
    }
}
ExamplesForm::Run('ExamplesForm');