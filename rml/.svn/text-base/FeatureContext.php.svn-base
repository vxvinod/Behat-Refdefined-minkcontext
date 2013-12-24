<?php

//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';
//

use Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Drupal\DrupalExtension\Context\DrupalContext;
use Symfony\Component\Process\Process;
use Behat\Behat\Context\Step\Given;
use Behat\Behat\Context\Step\When;
use Behat\Behat\Context\Step\Then;
use UserTable\UserTable;
use Behat\Behat\Event\ScenarioEvent;
use Behat\Behat\Event\StepEvent;
use Behat\Behat\Event\SuiteEvent;

use Behat\Mink\Exception\ElementNotFoundException;
use Guzzle\Http\Client;
require 'vendor/autoload.php';
require 'features/bootstrap/generic/DefaultFeatureContext.php';
/**
 * Features context.
 */

class FeatureContext extends DefaultFeatureContext {
private $users;
private $generateTable;
private $userTable;
private $uid;
private $guid;
public $olpamount="22.60 GBP";


  /**
   * Initializes context.
   * Every scenario gets it's own context object.
   *
   * @param array $parameters context parameters (set them up through behat.yml)
   */
  public function __construct(array $parameters) {
    $this->userTable = new UserTable();
    $this->generateTable = false;
    parent::__construct($parameters);
    if (isset($parameters['generate_user_table'])) {
      $this->generateTable = $parameters['generate_user_table'];
    }
   }

    /**
     * Function searches for article at top of list on press releases page, and searches the title to check the search function is working correctly.
     * This is an RMG specific function and should be in the correct featurecontext file.
     * @Given /^I search the most recent article$/
     */
    public function searchArticle() {

        $expression =  "//div[contains(concat(' ',normalize-space(@class),' '),' view-news-listing ')]//div[contains(concat(' ',normalize-space(@class),' '),' attachment-before ')]//li [contains(concat(' ',normalize-space(@class),' '),' views-row-1 ')]//h2[contains(concat(' ',normalize-space(@class),' '),' news-title')]/a";
        $searchText = $this->getSession()->getPage()->find('xpath',$expression);
        $search = $searchText->getText();

        return array(
        new When ("I fill in \"edit-keys\" with \"$search\""),
        new When ("I press \"edit-submit-news-listing\"")
        );
    }


    /**
     * Below function should be in RMG specific featurecontext file.
     * Below function gets the date from the article at top of the list and checks that it is less than a month old.
     * @Given /^I see the newest article is less than a month old$/
     */
    public function lessThanMonth() {

       $expression = "//div[contains(concat(' ',normalize-space(@class),' '),' view-news-listing ')]//div[contains(concat(' ',normalize-space(@class),' '),' attachment-before ')]//li [contains(concat(' ',normalize-space(@class),' '),' views-row-1 ')]//div[contains(concat(' ',normalize-space(@class),' '),' padding_19_0_7_0 ')]/span";

        $mydate = $this->getSession()->getPage()->find('xpath',$expression);
        #The $mydate object can be null and will cause an exception if no articles are found

        if(empty($mydate)){
            throw new Exception ("No articles have come up on search");
        }

        $myStringDate = $mydate->getText();

        $mytime = strtotime($myStringDate);
        $one_month_ago = strtotime('now -1 month');


     if($mytime>$one_month_ago){

     }
     else {
         throw new Exception ("Latest press release is older than 1 month" . $myStringDate);

     }

    }

  /**
   * Below function gets the order number from the page and save it in to OrderNumbers.txt file under root directory
   * @Given /^I save the order number$/
   */
  public function iSaveTheOrderNumber() {
    $order = $this->getSession()->getPage()->find('xpath', '//*[contains(text(),"number")]');

	//echo "First 160 chars: ".substr($this->getSession()->getPage()->getText() , 0, 160) . "\n";

    if (empty($order)) {
      throw new Exception("No order num found");
    }
    else {
		  //some application's workflows end on page which displays text as Your order number is 8888 with amount 8.88
		  //below xpath works for retrieving order number from such line
		  $order = $this->getSession()->getPage()->find('xpath', '//*[contains(text(),"number")]//child::*[1]');

		  //some application's workflows end on page which displays text as Your order number is: 8888
		  //below xpath works for retrieving order number from such line
		  if (empty($order)) {
			$order = $this->getSession()->getPage()->find('xpath', '//*[contains(text(),"number")]//following-sibling::*');
		  }

		  $orderNumber =  trim($order->getText());
		  echo "Order num:".$orderNumber;
		  preg_match("([0-9]+)",$orderNumber,$matches);

		  print ' Order number is: '. $matches[0];
		  $filename = 'OrderNumbers.txt';
		  $fp = fopen($filename, "a+");
		  fwrite($fp,'Order Number :'.$matches[0]."\n");
		  fclose($fp);
    }
  }
  /*After each scenario this will get executed
  /** @AfterScenario */
  public function after($event) {

     if($this->generateTable) {
     if(!$this->users == null) {
      $date = date('Y-m-d H:i:s');
      $test = $event->getScenario()->getTitle();
      $last = end($this->users);
      print("called the addrow event");
      $this->userTable->addRow($last,$test,$date);
  }
  }
    //Clear the users out, make every Scenario independent
    $this->users = array();


  }


  /**
   * Fills in form field with specified id|name|label|value with email generated on date.
   *
   * @Then /^I fill in "(?P<field>(?:[^"]|\\")*)" with a random email address$/
   */
  public function fillFieldGenerated($field) {

    $randomnum = rand(1, 10000);
    $seconds = date('His');
    $total = $randomnum * $seconds / 10;
    $user = round($total).'@rmgsmoketest.com';
    $this->users[] = $user;
    $this->getSession()->getPage()->fillField($field, $user);
  }

  /**
   * Fills in form field with specified the last generated email address.
   *
   * @Then /^I fill in "(?P<field>(?:[^"]|\\")*)" with the last random email address$/
   */
  public function fillFieldGeneratedLast($field) {
  $last = end($this->users);
  $this->getSession()->getPage()->fillField($field, $last);
  }

  /** Checks history table
   *
   * @Then /^I should see "([^"]*)" in history$/
   */
  public function iShouldSeeInHistory($amounttoverify) {

	$session=$this->getSession();
	$page=$session->getPage();
	$amount =$page->find('xpath','//table[contains(@class,"views-table")]//tr[1]/td[4]');   //checks history table's first reocrd in credit column
	if ($amount=== Null ){
		throw new ElementNotFoundException($this->getSession(), 'table cell', 'id|name|label|value',null  );
	}

	$actualamount = $amount->getText();

	//veryfying payment method is Credit Card
	$paymentmethod =$page->find('xpath','//table[contains(@class,"views-table")]//tr[1]/td[3]');   //checks history table's first reocrd in credit column
	if ($paymentmethod=== Null ){
		throw new ElementNotFoundException($this->getSession(), 'table cell', 'id|name|label|value',null  );
	}
	$method= trim($paymentmethod->getText());
	if($method!="Credit Card")
			throw new Exception("Payment method Credit Card not found");


	preg_match("/([0-9\.,-]+)/", $actualamount, $match);

	$actualamount = $match[0];

	if($amounttoverify != $actualamount)
		Throw new Exception( "No such amount found.");

  }

  /**
   * Private function for the whoami step.
   */
  private function whoami() {

    //********* CODE DOES NOT WORK FOR CONFIRMING LOG IN *******************
    $this->getSession()->visit($this->locatePath('/user/edit'));
    $this->getSession()->wait(2000);
    $element = $this->getSession()->getPage();
    try {
    $this->assertSession()->pageTextNotContains("Access denied. You must login to view this page.");
    }
    catch(Exception $e) {
     return FALSE;
  }
    // Go to the user profile page and extract the email address from the form field.
    if ($find = $element->findField('mail')) {
      $email_address = $find->getValue();
      if ($email_address) {
        return $email_address;
      }
    }
    return FALSE;
  }

  /**
   * Authenticates a user.
   *
   * @Given /^I am logged in as "([^"]*)" with the password "([^"]*)"$/
   */
  public function iAmLoggedInAsWithThePassword($email_address, $passwd) {
     $user = $this->whoami();
    $cookies = $this->getSession()->getDriver()->getWebDriverSession()->getAllCookies();
    foreach ($cookies as $cookie) {
      if(preg_match("/RSESS.*/",$cookie['name'])) {
        echo "\033[34;1m - Debug Message - \033[34m User already logged in - logging out.. \033[0m \n";
        $this->getSession()->visit($this->locatePath('/logout'));
      }
    }

    $element = $this->getSession()->getPage();
    if (empty($element)) {
      throw new Exception('Page not found');
    }

    // Go to the user login page.
    $this->getSession()->visit($this->locatePath('/user/login'));

    // If I see this, I'm not logged in at all so log the user in.
    $element->fillField('name', $email_address);
    echo "\033[34;1m - Debug Message - \033[34m Currently filling in name with the email address ".$email_address."\033[0m \n";
    $element->fillField('pass', $passwd);
    echo "\033[34;1m - Debug Message - \033[34m Currently filling in pass with the password ".$passwd."\033[0m \n";
    echo "\033[34;1m - Debug Message - \033[34m Getting Current URL = ".$this->getSession()->getCurrentUrl()."\033[0m \n";

    $submit = $element->findButton('Login');
    if (empty($submit)) {
      throw new Exception('No submit button at ' . $this->getSession()->getCurrentUrl());
    }
    $name = $element->findById('edit-name');
    $pass =  $element->findById('edit-pass');
    // Log in.
        echo "\033[34;1m - Debug Message - \033[34m The text in the username field is  = ".$name->getValue()."\033[0m \n";
        echo "\033[34;1m - Debug Message - \033[34m The text in the password field is  = ".$pass->getValue()."\033[0m \n";


    $submit->click();
    $cookies = $this->getSession()->getDriver()->getWebDriverSession()->getAllCookies();
    foreach ($cookies as $cookie) {
      if(preg_match("/RSESS.*/",$cookie['name'])) {
        return;
      }
    }
    throw new Exception("Could not log user in!");
  }

  /**
   * Authenticates a user with password from configuration.                                                ]
   *
   * @Given /^I am logged in as "([^"]*)"$/
   */
  public function iAmLoggedInAs($email_address) {
    $details = $this->fetchUserDetails('drupal', $email_address);
    $email_address = $details['email'];
    $password = $details['password'];
    $this->iAmLoggedInAsWithThePassword($email_address, $password);
  }

 /**
   * Authenticates with user and password from pre-set user.
   *
   * @Given /^I am logged in as the "([^"]*)" user$/
   */
  public function iAmLoggedInAsTheUser($usertype) {
    $details = $this->fetchUserDetails('drupal',$usertype.' user');
    $email_address = $details['email'];
    $password = $details['password'];
    if($usertype == "pfw"){
      $this->pfwIAmLoggedInAsWithThePassword($email_address, $password);
    }
    else{
      $this->iAmLoggedInAsWithThePassword($email_address, $password);
    }
  }

  /**
   * Check for the tabs in the page.
   *
   * @Then /^I should see the following <tabs>$/
   */
  public function iShouldSeeTheFollowingTabs() {
    $tab_links_det = $this->getSession()->getPage()->findById("edit-my-ab-button");
    $tab_links = $tab_links_det->getAttribute('value');
    if (!$tab_links) {
       throw new Exception('No tabs specified');
    }
    return $tab_links;
  }

  /**
   * Pay with a 3D secure card.                                                ]
   *
   * @Then /^I have saved redirections$/
   */
  public function iHaveSavedRedirections() {
    $this->getSession()->visit($this->locatePath('/personal/receiving-mail/redirection'));
    $page =$this->getSession()->getPage();
    $savedRedirections = $page->find('css', 'div.saved-redirection');
    if (empty($savedRedirections)) {
      throw new Exception("This user has no saved Redirections");
    }
  }

  /**
   * Check the basket page has selected items or empty.
   *
   * @Then /^I should see my basket if it is not empty$/
   */
  public function iShouldSeeMyBasketIfItIsNotEmpty() {
    $page = $this->getSession()->getpage();
    $table =$page->find('css', "table > tbody > tr > td.Remove > a");
    if (empty($table)) {
      throw new Exception("Table empty");
    }
  }

  /**
   * Fill in field with a date 5 days from today
   * @Then /^the "([^"]*)" field should contain the earliest possible redirection date from today$/
   */
  public function assertRedirectionStart ($field) {
    $future_day = substr($this->getTheWorkingDayFrom(4),8,9);
    $page = $this->getSession()->getpage();
    $el = $page->find('css', '#'.$field.'');
    $selectedValue = $el->getValue();
    if ($selectedValue != $future_day) {
        throw new exception('Value not in selected dropdown');
    }
  }

  /**
   * Fill field with an illegal (<5 days) date
   * @Then /^I select a date from "([^"]*)" that is too close to todays date$/
   */
  public function illegalDateTooSoon($field){
    $future_timestamp =  mktime(0, 0, 0, date("m")  , date("d")+3, date("Y")); //returns 4 days (inclusive) from now in timestamp format
    $futureTimestampArray =(getdate($future_timestamp)); //returns an array of date/time info from that day
    $future_day = (String)($futureTimestampArray['mday']); //isolates the day of the month value and converts it to a string
    $page = $this->getSession()->getpage();
    $el = $page->find('css', '#'.$field.'');
    $el->selectOption($future_day);
  }

  /**
   * Fill field with an illegal (<5 days) date
   * @Then /^I select a date from "([^"]*)" that is too far from todays date$/
   */
  public function illegalDate($field){
    $future_timestamp =  mktime(0, 0, 0, date("m")  , date("d")+90, date("Y")); //returns 90 days (exclusive) from now in timestamp format
    $futureTimestampArray =(getdate($future_timestamp)); //returns an array of date/time info from that day
    $future_day = (String)($futureTimestampArray['mday']); //isolates the day of the month value and converts it to a string
    $page = $this->getSession()->getpage();
    $el = $page->find('css', '#'.$field.'');
    $el->selectOption($future_day);
  }

   /**
    *	Gets new working date if current date is bank holiday or sunday
    */
   private function getNextWorkingDate($currentdate){

	$newdate = date('Y-m-d',strtotime($currentdate)); //get todays date
    $bankholidayArray = explode(",", $this->bankHolidays); //get bank holiday as array

    foreach ($bankholidayArray as &$value) { //for every bank holiday
      if ($newdate === $value) { //if the next day is a bank holiday
        $newdate = date('Y-m-d', strtotime($value .  ' +1 Weekday')); //look for next weekday after bank holiday, set it as delivery
      }
    }
    return $newdate;
   }

  /**
   * This function selects today's date on card from the "date on card" drop-down for the new eRedelivery journey
   *
   * @Given /^I select today's date on card for Redelivery from "([^"]*)"$/
   */
 public function iSelectTodayDateOnCardForRedeliveryNewFrom($id) {
    
	// Converts date in format like Fri 3 May 2013.
    $currentdate = date("D j M Y");

	// Get element on page
	$session = $this->getSession();
    $element = $session->getPage();
    $dateoncard = $element->findById($id);

	// Verify if that element present on page
	if (NULL === $dateoncard) {
      throw new ElementNotFoundException($this->getSession(), 'date on card', 'id|name|label|value', null);
      }

    // Get today's day and populate it into the variable called $day
	$day = date('N',strtotime($currentdate));
	
	// If the current day is a Sunday, add one day to make it a Monday
    if ($day ==7 ){
	$currentdate = date('Y-m-d', strtotime($day . ' +1 day'))	;
	}
	
	// Select date form drop down.
	$dateoncard->selectOption($currentdate);

  }

   /**
   * This function selects earliest available date on card for Redelivery New Journey
   *
   * @And /^I select earliest valid date on card for Redelivery from "([^"]*)"$/
   */
  public function iSelectEarliestValidDateOnCardRedeliveryNewFrom($id) {
    
	// Converts date in format like Fri 3 May 2013.
    $currentdate = date("D j M Y");

    // Gets today minus 16 calendar day in format like Fri 03 May 2013.
    $earliestdate = date("D j M Y", strtotime($currentdate. "- 16 days"));

	// Get element on page
	$session = $this->getSession();
    $element = $session->getPage();
    $dateoncard = $element->findById($id);

	// Verify if that element present on page
	if (NULL === $dateoncard) {
      throw new ElementNotFoundException($this->getSession(), 'date on card', 'id|name|label|value', null);
      }
	  
	// Get today's day and populate it into the variable called $day
    $day = date('N',strtotime($currentdate));
	
	// If today's day is Sunday, add one day and set the variable $currentdate to Monday
    if ($day ==7 ){
	$currentdate = date('Y-m-d', strtotime($day . ' +1 day'))	;
	}
   
	// Selects date form drop down.
	$dateoncard->selectOption($earliestdate);

  }

   /**
   * This function selects invalid date on card, in order to get the non-dismissable overlay message on the new eRedelivery journey
   *
   * @Given /^I select invalid date on card for Redelivery from "([^"]*)"$/
   */
  public function iSelectInvalidDateOnCardForRedeliveryNewFrom($id) {
  
    // Converts date in format like Fri 3 May 2013.
    $currentdate = date("D j M Y");

    // Gets today minus 17 calendar days (including today) in format like Fri 03 May 2013.
    $earliestdate = date("D j M Y", strtotime($currentdate. "- 16 days"));

	// Gets element on page
    $session = $this->getSession();
    $element = $session->getPage();
    $dateoncard = $element->findById($id);

	// Verify if that element present on page
	if (NULL === $dateoncard) {
      throw new ElementNotFoundException($this->getSession(), 'date on card', 'id|name|label|value', null);
      }
	 // Check if earliest date is Sunday
    $day = date('D',strtotime($earliestdate));

	// If earliest date is Sunday, add one extra day
	if ($day =='Sun' ){
	$earliestdate = date('D j M Y', strtotime($earliestdate . ' +1 day'))	;
	}

    // Select earliest date form drop down.
	$dateoncard->selectOption($earliestdate);

  }

  /**
   * This function selects earliest available redelivery date for the new Redelivery Journey
   *
   * @Given /^I select earliest available redelivery date for Redelivery from "([^"]*)"$/
   */
  public function iSelectEarliestAvailableDateForRedeliveryNewFrom($id) {


	// Sets eredelivery_date variable to null
	$eredelivery_date = null;

	// Get's today's date in format like Fri 3 May 2013
	$today = date ("D j M Y");

	// Gets today's date plus two days
	$suggested_redelivery_date = (strtotime($today. ' + 2 days'));
	
	// Get today's day and populate it into a variable called $day
    $day = date('N',strtotime($currentdate));

	// If today's day is a Sunday, add three extra days for Redelivery
	if ($day ==7 ){
	$suggested_redelivery_date = date('Y-m-d', strtotime($day . ' +3 day'));
	}

	// If $suggested_redelivery_date is a Sunday, add an extra day
	if (date('D', $suggested_redelivery_date) == "Sun") {

		$eredelivery_date = (date('D j M Y', $suggested_redelivery_date). ' + 1 days');

	}

	else {

		$eredelivery_date = (date('D j M Y', $suggested_redelivery_date));

	}

	// Get element on page
	$session = $this->getSession();
    $element = $session->getPage();
    $dateoncard = $element->findById($id);

	// Verify if that element present on page
	if (NULL === $dateoncard) {
      throw new ElementNotFoundException($this->getSession(), 'date on card', 'id|name|label|value', null);
      }

	 // Selects date form drop down.
	 $dateoncard->selectOption($eredelivery_date);

  }

  /**
   * Check withdrawal balance on page Withdraw funds | Prepay account
   *
   * @Then /^I should see withdrawal balance as "([^"]*)"$/
   */
  public function iShouldSeeWithdrawalBalanceAs($amountoverify) {
    // throw new PendingException();
    $session=$this->getSession();
	$page=$session->getPage();
    $amount =$page->find('xpath','//table[contains(@class,"sticky-enabled sticky-table caption-less")]//tr[1]/td[2]');   //checks history table's first reocrd in credit column
		if ($amount=== Null ){
		throw new ElementNotFoundException($this->getSession(), 'table cell', 'id|name|label|value',null  );
	}
	$withdraamount = str_replace('£', '',$amount->getText());
	//echo $withdraamount;
	//echo $amountoverify;
	if (floatval($withdraamount) < floatval($amountoverify))
				throw new Exception("withdraw funds are less than last top up");

  }

  /**
   * to verify text results after searching Track N Trace number. 
   * @Then /^I verify "([^"]*)"$/
   */
  public function iVerify($verificationmessage) {
    $session=$this->getSession();
	$page=$session->getPage();
    $message =$page->find('xpath','//*[contains(@id,"tnt-results")]');   //checks track and trace's result
		if ($message === Null ){
		throw new ElementNotFoundException($this->getSession(), 'track and trace results', 'id|name|label|value',null  );
	}
	$deliverymessage = trim($message->getText());

	$lines= preg_split("/[\.]+/",$verificationmessage,null,PREG_SPLIT_NO_EMPTY);

		foreach ($lines as $eachline){
			//echo trim($eachline)."\n";
			if (  strstr($deliverymessage,$eachline,false)!=FALSE) {
				print ("match found: ".$eachline."\n") ;
			}else {
				throw new Exception ("message not found is: ".$eachline."\n");
			}
		}
  }

  /**
   * Check that Signature Time has a valid date in delivered image
   *
   * @Then /^I should see Signature Time$/
   */
  public function iShouldSeeSignatureTime() {

		$session=$this->getSession();
		$page=$session->getPage();

		$timestamp =$page->find('xpath','//span[contains(@class,"right")]');   //checks signature time stamp in right most corner
			if ($timestamp === Null ){
				throw new ElementNotFoundException($this->getSession(), 'signature time stamp', 'id|name|label|value',null  );
		}
		$signaturetime = trim($timestamp->getText());               //check timestamp is displayed
		$time = preg_split("/[' ']+/",$signaturetime,null,PREG_SPLIT_NO_EMPTY);

		list($yy,$mm,$dd)=explode("/",$time[3]);					//check date validity
		if (is_numeric($yy) && is_numeric($mm) && is_numeric($dd))
		{
			if ( checkdate($mm,$dd,$yy))
				echo "\n displayed signature time has a valid date format ";
			else
				throw new Exception ("displayed signature time has no valid date format");
		}
	}

  /**
   * execute following steps once "View signature" button is clicked in track an trace.
   *
   * @Given /^I check "([^"]*)" and date$/
   */
  public function iCheckAndDate($notes) {
   if($notes === "View Signature Button displayed" ){
		return array(
		 new When("I press \"View signature\""),
		 new Then("I should see \"Signature of delivery for your item\""),
		 new Then("I should see Signature Time")
		);
	}
  }

 /**
   * @Given /^I enter start time$/
   */
  public function iEnterStartTime() {
    $currentmonth = date("M", strtotime(date("Y-M-d H:i", time()) .'+90 minutes'));
	$currentday = date("d", strtotime(date("Y-M-d H:i", time()) .'+90 minutes'));
	$currentyear = date("Y", strtotime(date("Y-M-d H:i", time()) .'+90 minutes'));

    $hour = date("H", strtotime(date("Y-M-d H:i", time()) .'+90 minutes'));	//time in future atleast 30 mins


	return array(							//returning array of steps which will select time atleast 30 minustes in future.
		 new When("I select \"".$currentday."\" from \"start_date_fields[day]\""),
		 new When("I select \"".$currentmonth."\" from \"start_date_fields[month]\""),
		 new When("I select \"".$currentyear."\" from \"start_date_fields[year]\""),
		 new When("I select \"".$hour."\" from \"start_time_hours\""),
		 new When("I select \"00\" from \"start_time_minutes\"")
		);
  }

  /**
   * Below function is top up prepay account if account has less balance
   * @Then /^I topup the prepay account by "([^"]*)"$/
   */
  public function iTopupThePrepayAccountBy($topup) {


    if($this->getSession()->getPage()->findById("edit-amount"))
    {
      return array(
      new When("I select \"".$topup."\" from \"edit-amount\""),
      new When("I have accepted the terms and conditions"),
      new When("I press \"Continue\""),
      new When("I wait for 3 seconds"),
      new When("I pay with a non 3D secure card"),
      new When("I wait for 5 seconds"),
      new When("I should see \"Top-up successful\""),
      new When("I press \"Continue\""),
       new When("I wait for 3 seconds"),
      new When("I press \"Continue\"")
      );
    }
    else {
      return true;
      }


  }

  
  /**
   * Below function is to check image ,but another function to check image by css can also be used
   * @Given /^I check the image$/
   */
  public function iCheckTheImage() {
    $session=$this->getSession();
     $xp=$session->getSelectorsHandler()->xpathLiteral("bing-maps-directions-1111");
    echo $xp;
    if ($session->getSelectorsHandler()->xpathLiteral("bing-maps-directions") && $session->getDriver()->getTagName("//img")=="img")
      return;


  }


  /**
   * comparing whether appropriate results is displayed when user searched post code in branchfinder flow
   * @Given /^I compare postcode "([^"]*)"$/
   */
  public function iComparePostcode($pcode) {
    $page=$this->getSession()->getPage();
   //taking first two characters to match with the result displayed
    $pincode=substr($pcode,0,2);
    #$postcode_result=array();
    //get rowcount with xpath of table
    $i=1;
    while($page->find('xpath',"//table[@class='ftn_result caption-less']/tbody/tr[".$i."]/td[2]")<>Null){
                $cell=$page->find('xpath',"//table[@class='ftn_result caption-less']/tbody/tr[".$i."]/td[2]");
                //trim post code here
                $code=trim($cell->getText());

                //explode with spaces and get excat post code
                $postcode=explode(" ",$code);
                $cnt=count($postcode);
               # echo $postcode;

                $postoffice_code=substr($postcode[$cnt-2],0,2);
               # echo $postoffice_code;
                //take 2/4 chars form start of post code retrieved in earlier step
               # echo $pincode;
                $i=$i+1;

                if(strcmp($postoffice_code,$pincode)==0)
                {
                  continue;
                }
                else{
                  throw new Exception("Postcode displayed is not valid one");
                }

                #echo $dist_value;
                #print_r($distance);

    }
  
  }

 
  /**
   * to check whether the distance displayed in Ascending order in Branch finder flow.
   * @Given /^I compare the distance in the table$/
   */
  public function iCompareTheDistanceInTheTable() {

    $page=$this->getSession()->getPage();
    $distance=array();
    $i=1;
    while($page->find('xpath',"//tbody/tr[".$i."]/td/span[@class='distance']")<>Null){
                $xpath=$page->find('xpath',"//tbody/tr[".$i."]/td/span[@class='distance']");
                $dist_text=$xpath->getText();
                $dist_value=(float) substr($dist_text,0,3);
                array_push($distance,$dist_value);
                #echo $dist_value;
                #print_r($distance);
                $i=$i+1;
    }
    //print_r($distance);
    $array_cnt=count($distance);
    echo $array_cnt;
    for($j=0;$j<$array_cnt-1;$j++)
    {
      $k=$j+1;
      echo $distance[$j];
      if($distance[$j] > $distance[$k])
      {
        throw new Exception("Table is not listed properly");
      }
      else
      {
        continue;
      }

    }
 }

  /**
   * Below function is used to get the id which is present the URL
   * @Given /^I get UID from profile page$/
   */
	public function iGetUidFromProfilePage() {

		$currentUrl = $this->getSession()->getCurrentUrl();
		$arr = explode("/", $currentUrl);
		if (! empty ($arr))
			 $this->uid = $arr[count($arr)-2];
		else
			throw new Exception("url is not correct.");
  }

  /**
   * Below function is used to fill in the UID field woth UID fetched from the URL
   * @Given /^I fill in UID$/
   */
  public function iFillInUid() {

		return array(
			new When("I fill in \"Enter the User ID (uid) of the user you want to lookup\" with \"".$this->uid."\"")
			);
  }

  /**
   * Below function gets GUID for newly created user from CSM
   * @Given /^I get GUID from "([^"]*)"$/
   */
  public function iGetGuidFrom($edit) {
		$editElement = $this->getSession()->getPage()->find('named', array('field', "\"{$edit}\""));
		$this->guid= $editElement->getAttribute("value");

		if (empty($this->guid))
			throw new Exception ("guid is not populated.");
  }

 

  /**
   * Below function is used get the olp order number from olp page
   *
   * @Given /^I get the olp order amount$/
   */
  public function iGetTheOlpOrderAmount() {

	    $amount= $this->getSession()->getPage()->find('xpath',"//div[@class='Total']")->getText();
		echo $amount;

		$this->olpamount=substr($amount,14)." GBP";
		echo $this->olpamount;


  }

  /**
  *
   *  @Given /^I should see user with the last random email address in salesforce table with class "([^"]*)"$/
   */
  public function iShouldSeeUserWithTheLastRandomEmailAddressInSalesforceTableWithClass($class_name) {

		 //$last = end($this->users);
		 $last = "21769308@rmgsmoketest.com";
		 $row = 2;

		 $table_xpath="//table[@class='".$class_name."']//tr[".$row."]//td[2]";
		 $page=$this->getSession()->getPage();
		 $table_val=$page->find('xpath',$table_xpath);

		if($table_val ===Null){
		   throw new Exception("table not found");
		 }
		else {
				while (true){
					try{
						$table_xpath="//table[@class='".$class_name."']//tr[".$row."]//td[2]";

						$table_val = $page->find('xpath',$table_xpath);
						$table_text = $table_val->getText();
						echo $table_text .PHP_EOL;
						if($table_text == $last)
						{
							$table_xpath="//table[@class='".$class_name."']//tr[".$row."]//th//a";
							$email = $page->find('xpath',$table_xpath);
							$email->doubleClick();
							break;

						}
						$row = $row+1;
					}
					catch (Exception $e){
						throw new Exception("no record found or table rows are not completely dissplayed");
					}
		        }
        }
	}

   /**
   * Check that the second quick link is displaying the correct value for the new eRedelivery and Fee to Pay journeys
   *
   * @Given /^I check the second quick link$/
   */
  public function iCheckYesterdayLink () {

	// Gets the current day in numeric representation of the day of the week (i.e. 0 for Sunday through to 6 for Saturday)
	$currentday = date("w");
	$secondLink_text = "";

	// This switch case switches the second quick link text as per the Fee to Pay HLD

	switch ($secondLink_text) {

		case 0:
			$secondLink_text = "Friday";

		case 1:
			$secondLink_text = "Saturday";

		case 2:
			$secondLink_text = "Yesterday";

		case 3:
			$secondLink_text = "Yesterday";

		case 4:
			$secondLink_text = "Yesterday";

		case 5:
			$secondLink_text = "Yesterday";

		case 6:
			$secondLink_text = "Yesterday";

	}

	// Gets the second link element
	$secondLink = $this->getSession()->getPage()->find('xpath', '//li[contains(@class,"1 last active")]//a[1]');

	// Verify if that element present on page
	if (NULL === $secondLink) {
      throw new ElementNotFoundException($this->getSession(), 'Quick link', 'id|name|label|value', null);
	  }

	// If the first link text does not match the expected value, this throws an exception
	if ($secondLink->getText() != $secondLink_text) {

	$real_value = $secondLink->getText();
    print_r($real_value);

	throw new Exception ("The second quick link text is $real_value");

  }

 }

   /**
   * Check that the first quick link is displaying the correct value for the new eRedelivery and Fee to Pay journeys
   *
   * @Given /^I check the first quick link$/
   */
  public function iCheckTodayLink () {

	// Gets the current day in full format (e.g. Monday)
	$currentday = date("l");
	if ($currentday == "Sunday") {

	$firstLink_text = "Yesterday";

	}

	else {

	$firstLink_text = "Today";

	}

	$firstLink = $this->getSession()->getPage()->find('xpath', '//li[contains(@class,"0 first active")]//a[1]');

	// Verify if that element present on page
	if (NULL === $firstLink) {
      throw new ElementNotFoundException($this->getSession(), 'Quick link', 'id|name|label|value', null);
	  }

	if ($firstLink->getText() != $firstLink_text) {

	$real_value = $firstLink->getText();
    print_r($real_value);

	throw new Exception ("The first quick link text is $real_value");

  }


 }

   /**
   * Checks value when selecting the first quick link for new Fee to Pay and eRedelivery journeys is correct
   *
   * @Given /^I check the first quick link date value in "([^"]*)"$/
   */
   public function iCheckFirstQuickLinkDateValue ($id) {

	// Gets today's date in the format Tue 27 Aug 2013
	$currentdate = date("D j M Y");

	// Gets today's day
	$currentday = date('w');

	// Sets the expected date variable
	$expected_date = "";

	// Sets the selected date variable
	$selected_date = "";

	// Gets the value of the first quick link
	$firstLink = $this->getSession()->getPage()->find('xpath', '//li[contains(@class,"0 first active")]//a[1]');

	// Verify if that element is present on the page
	if (NULL === $firstLink) {
		throw new ElementNotFoundException($this->getSession(), 'Quick link', 'id|name|label|value', null);
		}

	// This switch case calculates the date that the first quick link should have selected
	switch ($expected_date) {

		case 0:
			$expected_date = date('D j M Y', strtotime($currentdate . ' -1 day'));

		case 1:
			$expected_date = date('D j M Y', strtotime($currentdate));

		case 2:
			$expected_date = date('D j M Y', strtotime($currentdate));

		case 3:
			$expected_date = date('D j M Y', strtotime($currentdate));

		case 4:
			$expected_date = date('D j M Y', strtotime($currentdate));

		case 5:
			$expected_date = date('D j M Y', strtotime($currentdate));

		case 6:
			$expected_date = date('D j M Y', strtotime($currentdate));

	}

	// Get element on page
    $session = $this->getSession();
    $element = $session->getPage();
    $dateoncard = $element->findById($id);

	// Verify if that element present on page
	if (NULL === $dateoncard) {
      throw new ElementNotFoundException($this->getSession(), 'date on card', 'id|name|label|value', null);
      }


	// Selects first available date (option two on the drop down list)
	$optionElement = $this->getSession()->getPage()->find('xpath', '//select[@id="' . $id . '"]/option[2]');
	$selected_date = (string)$optionElement->getText();

	// Throws exception if the values do not match
	if ($selected_date != $expected_date) {

	$real_value = $selected_date;


	throw new Exception ("The selected date from the first quick link is $real_value");

	}

   }

    /**
    *Below function is to check whether the field is highlighted when error message of that field is clicked
   * @Given /^I check the cursor in the "([^"]*)" field$/
   */
  public function iCheckTheCursorInTheField($arg1) {


	 if($this->getSession()->getDriver()->evaluateScript("if (document.activeElement.id == \"".$arg1."\") {return true;} else {return false;}"))
	 {
		echo "i focussed";
	}
		else {
		throw new Exception("Field is not focussed");
	}


  }

	 /**
   *Below function is to dismiss the alert while changing password
   * @Given /^I handle the alert for changing password$/
   */
  public function iHandleTheAlertForChangingPassword() {
    try {

	$this->getSession()->getDriver()->getWebDriverSession()->dismiss_alert();
	}
	catch (Exception $e) {
        echo 'No alert found';

  }
}

 
   /**
   * Checks value when selecting the second quick link for new Fee to Pay and eRedelivery journeys is correct
   *
   * @Given /^I check the second quick link date value in "([^"]*)"$/
   */
  public function iCheckSecondQuickLinkDateValue ($id) {

	// Gets today's date in the format Tue 27 Aug 2013
	$currentdate = date("D j M Y");

	// Gets today's day
	$currentday = date('w');

	// Sets the expected date variable
	$expected_date = "";

	// Sets the selected date variable
	$selected_date = "";

	// Gets the value of the second quick link
	$secondLink = $this->getSession()->getPage()->find('xpath', '//li[contains(@class,"1 last active")]//a[1]');

	// Verify if that element is present on the page
	if (NULL === $secondLink) {
		throw new ElementNotFoundException($this->getSession(), 'Quick link', 'id|name|label|value', null);
		}

	// This switch case calculates the date that the second quick link should have selected
	switch ($expected_date) {

		case 0:
			$expected_date = date('D j M Y', strtotime($currentdate . ' -2 days'));

		case 1:
			$expected_date = date('D j M Y', strtotime($currentdate . ' -1 day'));

		case 2:
			$expected_date = date('D j M Y', strtotime($currentdate));

		case 3:
			$expected_date = date('D j M Y', strtotime($currentdate));

		case 4:
			$expected_date = date('D j M Y', strtotime($currentdate));

		case 5:
			$expected_date = date('D j M Y', strtotime($currentdate));

		case 6:
			$expected_date = date('D j M Y', strtotime($currentdate));

	}

	// Get element on page
    $session = $this->getSession();
    $element = $session->getPage();
    $dateoncard = $element->findById($id);

	// Verify if that element present on page
	if (NULL === $dateoncard) {
      throw new ElementNotFoundException($this->getSession(), 'date on card', 'id|name|label|value', null);
      }


	// Selects second available date (option three on the drop down list)
	$optionElement = $this->getSession()->getPage()->find('xpath', '//select[@id="' . $id . '"]/option[3]');
	$selected_date = (string)$optionElement->getText();

	// Throws exception if the values do not match
	if ($selected_date != $expected_date) {

	$real_value = $selected_date;


	throw new Exception ("The selected date from the first quick link is $real_value");

	}

   }

   /**
   * This function selects earliest available date on card for Surcharge for Fee to Pay New journey
   *
   * @And /^I select earliest available date on card for surcharge for Fee to Pay from "([^"]*)"$/
   */
  public function iSelectEarliestDateOnCardForSurchargeFeeToPayNewFrom($id) {
    // Converts date in format like Fri 3 May 2013.
    $currentdate = date("D j M Y");

    // Gets today minus 16 calendar day in format like Fri 03 May 2013 (this includes today).
    $earliestdate = date("D j M Y", strtotime($currentdate. "- 15 days"));

	// Get element on page
	$session = $this->getSession();
    $element = $session->getPage();
    $dateoncard = $element->findById($id);

	// Verify if that element present on page
	if (NULL === $dateoncard) {
      throw new ElementNotFoundException($this->getSession(), 'date on card', 'id|name|label|value', null);
      }
	 // Check if current day is Sunday
    $day = date('N',strtotime($currentdate)); //get todays date
    if ($day ==7 ){
	$currentdate = date('Y-m-d', strtotime($day . ' -1 day'))	;
	}
	 // Get next working date if current date is Bank holiday or Sunday
     $newdate = $this->getNextWorkingDate($currentdate);

     $currentdate = date("D j M Y",strtotime($newdate));
	 // Selects date form drop down.
	 $dateoncard->selectOption($earliestdate);

  }

   /**
   * This function selects earliest available date on card for customs from Fee to Pay New journey
   *
   * @And /^I select earliest available date on card for customs for Fee to Pay from "([^"]*)"$/
   */
  public function iSelectEarliestDateOnCardForCustomsFeeToPayNewFrom($id) {
    // Converts date in format like Fri 3 May 2013.
    $currentdate = date("D j M Y");

    // Gets today minus 16 calendar day in format like Fri 03 May 2013 (this includes today).
    $earliestdate = date("D j M Y", strtotime($currentdate. "- 18 days"));

	// Get element on page
	$session = $this->getSession();
    $element = $session->getPage();
    $dateoncard = $element->findById($id);

	// Verify if that element present on page
	if (NULL === $dateoncard) {
      throw new ElementNotFoundException($this->getSession(), 'date on card', 'id|name|label|value', null);
      }
	 // Check if current day is Sunday
    $day = date('N',strtotime($currentdate)); //get todays date
    if ($day ==7 ){
	$currentdate = date('Y-m-d', strtotime($day . ' -1 day'))	;
	}
	 // Get next working date if current date is Bank holiday or Sunday
     $newdate = $this->getNextWorkingDate($currentdate);

     $currentdate = date("D j M Y",strtotime($newdate));
	 // Selects date form drop down.
	 $dateoncard->selectOption($earliestdate);

  }
   
   /**
   * This function Selects invalid date on card for customs from Fee to Pay New journey to make the non-dismissable overlay appear
   *
   * @And /^I select invalid date on card for customs for Fee to Pay from "([^"]*)"$/
   */
  public function iSelectInvalidDateForCustomsFeeToPayNewFrom($id) {
    // Converts date in format like Fri 3 May 2013.
    $currentdate = date("D j M Y");

    // Gets today minus 19 calendar day in format like Fri 03 May 2013 (this includes today).
    $invalid_date = date("D j M Y", strtotime($currentdate. "- 19 days"));

	// Get element on page
	$session = $this->getSession();
    $element = $session->getPage();
    $dateoncard = $element->findById($id);

	// Verify if that element present on page
	if (NULL === $dateoncard) {
      throw new ElementNotFoundException($this->getSession(), 'date on card', 'id|name|label|value', null);
      }
	 // Check if current day is Sunday
    $day = date('N',strtotime($currentdate)); //get todays date
    if ($day ==7 ){
	$currentdate = date('Y-m-d', strtotime($day . ' -1 day'))	;
	}
	 // Get next working date if current date is Bank holiday or Sunday
     $newdate = $this->getNextWorkingDate($currentdate);

     $currentdate = date("D j M Y",strtotime($newdate));
	 // Selects date form drop down.
	 $dateoncard->selectOption($invalid_date);

  }
  
    /**
   * Selects invalid date on card for surcharge from Fee to Pay New journey
   *
   * @And /^I select invalid date on card for surcharge for Fee to Pay from "([^"]*)"$/
   */
  public function iSelectInvalidDateForSurchargeFeeToPayNewFrom($id) {
    // Converts date in format like Fri 3 May 2013.
    $currentdate = date("D j M Y");

    // Gets today minus 16 calendar day in format like Fri 03 May 2013 (this includes today).
    $invalid_date = date("D j M Y", strtotime($currentdate. "- 16 days"));

	// Get element on page
	$session = $this->getSession();
    $element = $session->getPage();
    $dateoncard = $element->findById($id);

	// Verify if that element present on page
	if (NULL === $dateoncard) {
      throw new ElementNotFoundException($this->getSession(), 'date on card', 'id|name|label|value', null);
      }
	 // Check if current day is Sunday
    $day = date('N',strtotime($currentdate)); //get todays date
    if ($day ==7 ){
	$currentdate = date('Y-m-d', strtotime($day . ' -1 day'))	;
	}
	 // Get next working date if current date is Bank holiday or Sunday
     $newdate = $this->getNextWorkingDate($currentdate);

     $currentdate = date("D j M Y",strtotime($newdate));
	 // Selects date form drop down.
	 $dateoncard->selectOption($invalid_date);

  }
  
   
  /**
    *Below function is to select the option in the table with class name
   * @Given /^I select radiobutton with option "([^"]*)" in table with class name "([^"]*)"$/
   */
  public function iSelectRadiobuttonWithOptionInTableWithClassName($option, $classname) {
   
    //Setting i equal to the current row in user table
    $i=1; 

    //Enter the table, check whether the email address field is present
    while($this->getSession()->getPage()->find('xpath',"//tbody//tr[".$i."]")<>Null)
    {
      
      $flag=0;
      //Get option address in current row
      $name=$this->getSession()->getPage()->find('xpath',"//tbody//tr[".$i."]/td[2]")->getText();
      

      //Check if the option in current row in table
      if(strcmp($name,$option)==0)
      {
		
	  
              //Check to see if primary user image is present against the required email
               if( $this->getSession()->getPage()->find('xpath',"//tbody//tr[".$i."]/td[1]//input")<>Null)
                { 
				$element=$this->getSession()->getPage()->find('xpath',"//tbody//tr[".$i."]/td[1]//input");
				$element->doubleClick();
				echo "selected";
				$flag=1;
                  break;
               }
               
        }
        //Move onto next row
        $i=$i+1;

       }
        //No option has been found (Should never get called!...)
        if($flag==0)
        {
          throw new Exception("primary user is not there");

        }

  }

  /**
   * @Then /^I change the token value to "([^"]*)"$/
   */
  public function iChangeTheTokenValueTo($arg1) {
    $page = $this->getSession()->getPage();
    $source_button =  $page->find('xpath', '//*[@id="cke_36_label"]');
    $source_button->Click();
    $textarea = $page->find('xpath','//*[@id="cke_contents_edit-body"]/textarea');
    $textarea->setValue("<p>[" . $arg1 .  "]</p>");
  }

  /**
   * @Given /^I uncheck Automatic alias checkbox$/
   */
  public function iUncheckAutomaticAliasCheckbox() {
    $label = $this->getSession()->getPage()->find('xpath','//*[@id="edit-pathauto-perform-alias-wrapper"]/label');
    $label->Click();
  }

}