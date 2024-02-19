<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Plugin;

use QCubed\Bootstrap as Bs;
use QCubed\Control\ControlBase;
use QCubed\Control\FormBase;
use QCubed\Control\Panel;
use QCubed\Exception\Caller;
use QCubed\Html;
use QCubed\Js;
use QCubed\Project\Application;
use QCubed\Type;

/**
 * Class Alert
 *
 * Implements the Bootstrap "Alert" functionality. This can be a static block of text, or can alternately have a close
 * button that automatically hides the alert.
 *
 * Per Bootstraps documentation, you MUST specify an alert type class. Do this by using AddCssClass, or the CssClass
 * Attribute with a plus in front of the class. For example:
 * 	$objAlert->CssClass = '+' . Bootstrap::AlertSuccess; // It does not work at all. Use only the AddCssClass.
 *
 * Use Display or Visible to show or hide the alert as needed. Or, set the
 * Dismissable attribute.
 *
 * Since its a \QCubed\Control\Panel, you can put text, template or child controls in it.
 *
 * By default, alerts will fade on close. Remove the fade class if you want to turn this off.
 *
 * Call Close() to close the alert manually.
 *
 * @property boolean $Dismissable .......
 * @property boolean $FullEffect .......
 * @property boolean $HalfEffect .......
 *
 */

class Alert extends Panel {
	protected $strCssClass = 'alert fade in';

	protected $blnDismissable = false;

	protected $blnFullEffect = false;
	protected $blnHalfEffect = false;

    /**
     * Alert constructor.
     * @param ControlBase|FormBase $objParent
     * @param null $strControlId
     */
	public function __construct ($objParent, $strControlId = null) {
		parent::__construct ($objParent, $strControlId);

		$this->setHtmlAttribute("role", "alert");
		Bs\Bootstrap::loadJS($this);
	}

    /**
     * Returns the inner html of the tag.
     *
     * @return string
     */
	protected function getInnerHtml() {
		$strText = parent::getInnerHtml();

		if ($this->blnDismissable) {
			$strText = Html::renderTag('button',
				['type'=>'button',
				'class'=>'close',
				'data-dismiss'=>'alert',
				'aria-label'=>"Close",
				],
					_nl() . _indent('<span aria-hidden="true">&times;</span>', 1) . _nl() , false, true) . _nl()
			. $strText;
		}
		return $strText;
	}

    /**
     * Attach the javascript to the control
     */
	protected function makeJqWidget() {
	    parent::makeJqWidget();
		if ($this->blnDismissable) {
			Application::executeControlCommand($this->ControlId, 'on', 'closed.bs.alert',
				new Js\Closure("qcubed.recordControlModification ('{$this->ControlId}', '_Display', false)"), Application::PRIORITY_HIGH);
		}

		if ($this->blnFullEffect) {
			Application::executeJavaScript(sprintf('window.setTimeout(function() {$j("#%s").fadeIn(1000);}, 100); window.setTimeout(function() {$j("#%s").fadeOut(1000);}, 5000)',
				$this->ControlId, $this->ControlId));
		}

		if ($this->blnHalfEffect) {
			Application::executeJavaScript(sprintf('window.setTimeout(function() {$j("#%s").fadeIn(1000);}, 100)',
				$this->ControlId));
		}
	}

	/**
	 * Closes the alert using the Bootstrap javascript mechanism to close it. Removes the alert from the DOM.
	 * Bootstrap has no mechanism for showing it again, so you will need
	 * to redraw the control to show it.
	 */
	public function close() {
		$this->blnDisplay = false;
		Application::executeControlCommand($this->ControlId, 'alert', 'close');
	}

    /**
     * @param string $strName
     * @return mixed
     * @throws Caller
     */
	public function __get($strName) {
		switch ($strName) {
			case "Dismissable":
			case "HasCloseButton": // QCubed synonym
				return $this->blnDismissable;

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
     * @param string $strName
     * @param string $mixValue
     * @throws Caller
     * @return void
     */
	public function __set($strName, $mixValue) {
		switch ($strName) {
			case 'Dismissable':
			case "HasCloseButton": // QCubed synonym
				$blnDismissable = Type::cast($mixValue, Type::BOOLEAN);
				if ($blnDismissable != $this->blnDismissable) {
					$this->blnDismissable = $blnDismissable;
					$this->blnModified = true;
					if ($blnDismissable) {
						$this->addCssClass(Bs\Bootstrap::ALERT_DISMISSABLE);
						Bs\Bootstrap::loadJS($this);
					} else {
						$this->removeCssClass(Bs\Bootstrap::ALERT_DISMISSABLE);
					}
				}
				break;

			case '_Display':	// Private attribute to record the visible state of the alert
				$this->blnDisplay = $mixValue;
				break;
			case "FullEffect":
				$this->blnFullEffect = Type::cast($mixValue, Type::BOOLEAN);
				break;
			case "HalfEffect":
				$this->blnHalfEffect = Type::cast($mixValue, Type::BOOLEAN);
				break;


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