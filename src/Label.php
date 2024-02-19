<?php

namespace QCubed\Plugin\Control;

use QCubed\Control\BlockControl;

/**
 * Class Label
 *
 * Converts\QCubed\Control\Label to a drawing boot strategy according to the client's desired theme.
 * @package QCubed\Plugin
 */
class Label extends \QCubed\Control\Label
{
    protected $strCssClass = "control-label";
    protected $strTagName = "label";
    protected $blnRequired = false;

    protected function getInnerHtml()
    {
        $strToReturn = BlockControl::getInnerHtml();
        if ($this->blnRequired) {
            $strToReturn = $strToReturn . sprintf('<span class="required" aria-required="true"> * </span>');
        }
        return $strToReturn;
    }
}