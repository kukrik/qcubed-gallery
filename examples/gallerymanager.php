<?php
require_once('qcubed.inc.php');

error_reporting(E_ALL); // Error engine - always ON!
ini_set('display_errors', TRUE); // Error display - OFF in production env or real server
ini_set('log_errors', TRUE); // Error logging

use QCubed as Q;
use QCubed\Bootstrap as Bs;
use QCubed\Plugin\GalleryManager;
use QCubed\Project\Control\ControlBase;
use QCubed\Project\Control\FormBase as Form;
use QCubed\Action\Ajax;
use QCubed\Event\Click;
use QCubed\Action\ActionParams;

class ExamplesForm extends Form
{

    //

    /**
     * @return void
     * @throws Q\Exception\Caller
     */
    protected function formCreate()
    {
        parent::formCreate();

        ////////////////////////////

        //
    }


}
ExamplesForm::Run('ExamplesForm');