<?php

//
// Require 3rd-party libraries here:
//
   require_once 'PHPUnit/Autoload.php';
   require_once 'PHPUnit/Framework/Assert/Functions.php';
//

use Behat\Behat\Exception\PendingException,
    Behat\Gherkin\Node\TableNode;
use Drupal\DrupalExtension\Context\DrupalContext;
use Symfony\Component\Process\Process;
use Behat\Behat\Context\Step\Given;
use Behat\Behat\Context\Step\When;
use Behat\Behat\Context\Step\Then;
use Behat\Behat\Event\ScenarioEvent;
use Behat\Behat\Event\StepEvent;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Mink\Exception\ResponseTextException;
use Behat\Mink\Exception\ExpectationException;
use Behat\MinkExtension\Context;
use Behat\Behat\Context\BehatContext;

require_once 'features/bootstrap/generic/DefaultFeatureContext.php';
require 'vendor/autoload.php';

/**
 * RedefinedMinkContext can be used to re-define all currrent MinkContext definition.
 * Inherited from features/bootstrap/generic/DefaultFeatureContext so that each definition in *
 * DefaultFeatureContext can be also redefined with find function.
 * can be used as base class for all other FeatureContext classes in rml,pol and etc.
 *
 * Below is sample hierarchy for new class
 *	RedefinedMinkContext -> DefaultFeatureContext
 *  FeatureContext   ->  RedefinedMinkContext
 *
 */
class RedefinedMinkContext extends DrupalContext {

	/**
	* Initializes context.
	* Every scenario gets it's own context object.
	*
	* @param array $parameters context parameters (set them up through behat.yml)
	*/
	public function __construct() {

	}
	/**
	*	spin function to wait for element 
	*
	*/
	public function spin($lambda, $retries,$sleep)  {
		do {
			
			$result = $lambda($this->getSession());
					
		} while (!$result && --$retries && sleep($sleep) !== false);
			
			return $result;
	}
	/**
	*	overridden find function to wait for element for atleast 20 sec.
	*	returns the element itself once it is visible.
	*/
	public function find($type, $locator, $retries = 20, $sleep = 1) {

		return $this->spin(function($session) use ($type,$locator) {
		
		$page = $session->getPage();
		if ($el = $page->find($type, $locator)) {
				if ($el->isVisible()) {
					return $el;
				}
			}
			return null;
		}, $retries, $sleep);

	}
	/**
	* pressButton overridden from MinkContext.
	* like this every step definition from MinkContext can be re-written 
	*
	*/
	public function pressButton($button)
	{
		$button = $this->fixStepArgument($button);
		echo "press";
		$elebutton =$this->find( 'named', array(
			'button', $this->getSession()->getSelectorsHandler()->xpathLiteral($button)
		));
		
		if ( null === $elebutton) {
			throw new ElementNotFoundException($this->getSession(), 'button', 'id|name|title|alt|value', $button);
		}	
		$elebutton->press();
		
	}
	
	/**
     * Clicks link overridden from mink context.
     * 
     * 
     */
    public function clickLink($link)
    {
		echo "click";
        $link = $this->fixStepArgument($link);
		$el_link=$this->find('named',array(
				'link_or_button',$this->getSession()->getSelectorsHandler()->xpathLiteral($link))
				);
		if(!($el_link)){
		throw new ElementNotFoundException($this->getSession(), 'link', 'id|name|title|alt|value', $link);
		}	
		
        $el_link->click();
    }
	
	/**
     * Fills in form field overriding mink context.
     *
     */
    public function fillField($field, $value)
    {
		echo "fill field";
		
        //$field = $this->fixStepArgument($field);
        //$value = $this->fixStepArgument($value);
		$el_f = $this->find('named',array(
				'field',$this->getSession()->getSelectorsHandler()->xpathLiteral($field))
				);

		if($el_f === null ){
		throw new ElementNotFoundException($this->getSession(), 'field', 'id|name|title|alt|value', $field);
		}
		else
		{

			echo $el_f->getText().PHP_EOL;
		}

		$el_f->setValue($value);
		
	}
	
	/**
     * Fills in form fields with provided table overriding mink context.
     */
    public function fillFields(TableNode $fields)
    {
        foreach ($fields->getRowsHash() as $field => $value) {
           $el_field=$this->find('named',array(
				'field',$this->getSession()->getSelectorsHandler()->xpathLiteral($field))
				);
		if(!($el_field)){
		throw new ElementNotFoundException($this->getSession(), 'field', 'id|name|title|alt|value', $field);
			}
		$el_field->setValue($value);
        }
    }
	
	
	  /**
     * Selects option in select field with specified id|name|label|value overrriding mink context.
     * 
     */
    public function selectOption($option, $select)
    {
		echo $option;
		echo $select;
        $select = $this->fixStepArgument($select);
        $option = $this->fixStepArgument($option);
		$field=$this->find('named',array(
				'field',$this->getSession()->getSelectorsHandler()->xpathLiteral($select))
				);
		if(!($field)){
		throw new ElementNotFoundException($this->getSession(), 'select option', 'id|name|title|alt|value', $field);
			}
       $field->selectOption($option,false);
    }
	
	
	 /**
     * Selects additional option in select field with specified id|name|label|value.
     *
     */
    public function additionallySelectOption($option, $select)
    {
        $select = $this->fixStepArgument($select);
        $option = $this->fixStepArgument($option);
		$el_field=$this->find('named',array(
				'field',$this->getSession()->getSelectorsHandler()->xpathLiteral($select))
				);
		if(!($el_field)){
		throw new ElementNotFoundException($this->getSession(), 'select option', 'id|name|title|alt|value', $select);
			}
        $el_field->selectFieldOption($option, true);
    }
	
	 /**
     * Checks checkbox overriding mink context.
     */
    public function checkOption($option)
    {
        $option = $this->fixStepArgument($option);
		$el_option=$this->find('named',array(
				'field',$this->getSession()->getSelectorsHandler()->xpathLiteral($option))
				);
		if(!($el_option)){
		throw new ElementNotFoundException($this->getSession(), 'select option', 'id|name|title|alt|value', $option);
			}
        $el_option->check();
    }
	
	/**
    * Unchecks checkbox overriding mink context
   	*
    */ 
    public function uncheckOption($option)
    {
        $option = $this->fixStepArgument($option);
		$el_option=$this->find('named',array(
				'field',$this->getSession()->getSelectorsHandler()->xpathLiteral($option))
				);
		if(!($el_option)){
		throw new ElementNotFoundException($this->getSession(), 'select option', 'id|name|title|alt|value', $option);
			}
        $el_option->uncheck();
    }
	
	 /**
     * Attaches file to field overriding mink context
     */
    public function attachFileToField($field, $path)
    {
        $field = $this->fixStepArgument($field);

        if ($this->getMinkParameter('files_path')) {
            $fullPath = rtrim(realpath($this->getMinkParameter('files_path')), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$path;
            if (is_file($fullPath)) {
                $path = $fullPath;
            }
        }
		$el_field=$this->find('named',array(
				'field',$this->getSession()->getSelectorsHandler()->xpathLiteral($field))
				);
		if(!($el_field)){
		throw new ElementNotFoundException($this->getSession(), 'field', 'id|name|title|alt|value', $field);
			}
	
        $el_field->attachFile($path);
    }
	
	
	
	
	
	 /**
     * Returns fixed step argument (with \\" replaced back to ").
     *
     * @param string $argument
     *
     * @return string
     */
    protected function fixStepArgument($argument)
    {
        return str_replace('\\"', '"', $argument);
    }
	
	
	
	


	
	

}
