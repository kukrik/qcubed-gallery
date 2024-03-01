<?php
require_once('qcubed.inc.php');

error_reporting(E_ALL); // Error engine - always ON!
ini_set('display_errors', TRUE); // Error display - OFF in production env or real server
ini_set('log_errors', TRUE); // Error logging

use QCubed\Project\Control\ControlBase;
use QCubed\Project\Control\FormBase as Form;
use QCubed\Control\DataRepeater;

class ExamplesForm extends Form
{
    protected $dtrGalleryList;

    protected function formCreate()
    {
        $this->dtrGalleryList = new DataRepeater($this);
        $this->dtrGalleryList->Template = 'dtr_GalleryList.tpl.php';
        $this->dtrGalleryList->setDataBinder('dtrGalleryList_Bind');
        $this->dtrGalleryList->UseWrapper = false;
    }

    public function dtrGalleryList_Bind()
    {
        $this->dtrGalleryList->DataSource = ListOfGalleries::loadAll();
    }
}
ExamplesForm::Run('ExamplesForm');