<?php

namespace QCubed\Plugin;

use QCubed\Control\ControlBase;
use QCubed\Exception\Caller;
use QCubed\Exception\InvalidCast;
use QCubed\QString;
use QCubed\Type;
use QCubed\Html;
use QCubed\Project\Application;

/**
*@property string $Template Path to the HTML template (.tpl.php) file (applicable in case a template is being used for Render)
*/

class Tabs extends \QCubed\Project\Control\ControlBase
{
    protected $strSelectedId;
    /** @var string Path to the HTML template (.tpl.php) file (applicable in case a template is being used for Render) */
    protected $strTemplate = null;


    public function validate()
    {
        return true;
    }

    public function parsePostData()
    {
    }

    public function getControlHtml()
    {
        $strHtml = '';
        foreach ($this->objChildControlArray as $objChildControl) {
            $strInnerHtml = Html::renderTag('a',
                [
                    'href' => '#' . $objChildControl->ControlId . '_tab',
                    'aria-controls' => $objChildControl->ControlId . '_tab',
                    'role' => 'tab',
                    'data-toggle' => 'tab'
                ],
                QString::htmlEntities($objChildControl->Name)
            );
            $attributes = ['role' => 'presentation'];
            if ($objChildControl->ControlId == $this->strSelectedId) {
                $attributes['class'] = 'active';
            }

            $strTag = Html::renderTag('li', $attributes, $strInnerHtml);
            $strHtml .= $strTag;
        }
        $strHtml = Html::renderTag('ul', ['class' => 'nav nav-tabs', 'role' => 'tablist'], $strHtml);

        $strInnerHtml = '';
        foreach ($this->objChildControlArray as $objChildControl) {
            $class = 'tab-pane';
            $strItemHtml = null;
            if ($objChildControl->ControlId == $this->strSelectedId) {
                $class .= ' active';
            }
            $strItemHtml = $objChildControl->render(false);

            $strTemplateEvaluated = '';
            if ($this->strTemplate) {
                global $_CONTROL;
                $objCurrentControl = $_CONTROL;
                $_CONTROL = $this;
                $strTemplateEvaluated = $this->evaluateTemplate($this->strTemplate);
                $_CONTROL = $objCurrentControl;
            }

            $strItemHtml .= $strTemplateEvaluated;

            $strInnerHtml .= Html::renderTag('div',
                [
                    'role' => 'tabpanel',
                    'class' => $class,
                    'id' => $objChildControl->ControlId . '_tab'
                ],
                $strItemHtml
            );
        }

        $strTag = Html::renderTag('div', ['class' => 'tab-content'], $strInnerHtml);

        $strHtml .= $strTag;

        $strTag = $this->renderTag('div', null, null, $strHtml);

        return $strTag;
    }

    public function addChildControl(ControlBase $objControl)
    {
        parent::addChildControl($objControl);
        if (count($this->objChildControlArray) == 1) {
            $this->strSelectedId = $objControl->ControlId;    // default to first item added being selected
        }
    }

    public function getEndScript()
    {
        Application::executeJavaScript(sprintf("jQuery(function(){
  // Change tab on load
  var hash = window.location.hash;
  hash && jQuery('ul.nav a[href=\"' + hash + '\"]').tab('show');

  jQuery('.nav-tabs a').click(function (e) {
    jQuery(this).tab('show');
    var scrollmem = jQuery('body').scrollTop();
    window.location.hash = this.hash;
    jQuery('html,body').scrollTop(scrollmem);
  });

  // Change tab on hashchange
  window.addEventListener('hashchange', function() {
    var changedHash = window.location.hash;
    changedHash && jQuery('ul.nav a[href=\"' + changedHash + '\"]').tab('show');
  }, false);})"));
    }

    /**
     * PHP __get magic method implementation
     * @param string $strName Name of the property
     *
     * @return mixed
     * @throws Caller
     */
    public function __get($strName)
    {
        switch ($strName) {
            case "Template": return $this->strTemplate;

            default:
                try {
                    return parent::__get($strName);
                } catch (Caller $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
        }
    }

    /**
     * PHP __set magic method implementation
     * @param string $strName Property Name
     * @param string $mixValue Property Value
     *
     * @throws Caller|InvalidCast
     * @return void
     */
    public function __set($strName, $mixValue)
    {
        switch ($strName) {
            case "Template":
                try {
                    $this->blnModified = true;
                    if ($mixValue) {
                        if (file_exists($strPath = $this->getTemplatePath($mixValue))) {
                            $this->strTemplate = Type::cast($strPath, Type::STRING);
                        } else {
                            throw new Caller('Could not find template file: ' . $mixValue);
                        }
                    } else {
                        $this->strTemplate = null;
                    }
                    break;
                } catch (InvalidCast $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }

            default:
                try {
                    parent::__set($strName, $mixValue);
                } catch (Caller $objExc) {
                    $objExc->incrementOffset();
                    throw $objExc;
                }
                break;
        }
    }
}