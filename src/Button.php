<?php
/**
 *
 *Part of the plugin of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Plugin;

use QCubed\Bootstrap as Bs;
use QCubed\Control\BlockControl;
use QCubed\Project\Application;
use QCubed\Exception\InvalidCast;
use QCubed\Js;
use QCubed\Type;

/**
 * Class Button
 *
 * Bootstrap styled buttons
 * FontAwesome styled icons
 *
 * Here has been implemented Bootstrap tooltip function. Where appropriate, you can activate Tooltip as follows:
 * $objButton->Tip = true;
 * $objButton->ToolTip = t('Text');
 *
 * @property string $Glyph .......
 * @property boolean $Tip .......
 *
 * @package QCubed\Plugin
 */

class Button extends Bs\Button
{
    protected $strGlyph;
    protected $blnTip = false;

    protected function makeJqWidget()
    {
        if ($this->blnTip) {
            $this->setDataAttribute('toggle', 'tooltip');
            Application::executeControlCommand($this->ControlId, "bootstrapTooltip", Application::PRIORITY_HIGH);
        }
    }

    public function __set($strName, $mixValue)
    {
        switch ($strName) {
            case "Glyph":
                $this->strGlyph = Type::cast($mixValue, Type::STRING);
                break;
            case "Tip":
                $this->blnTip = Type::cast($mixValue, Type::BOOLEAN);
                break;

            default:
                try {
                    parent::__set($strName, $mixValue);
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
                break;
        }
    }

    protected function getInnerHtml()
    {
        $strToReturn = BlockControl::getInnerHtml();
        if ($this->strGlyph) {
            $strToReturn = sprintf('<i class="%s" aria-hidden="true"></i>', $this->strGlyph) . $strToReturn;
        }
        return $strToReturn;
    }

}
