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
require 'vendor/autoload.php';
require_once 'features/bootstrap/generic/RedefinedMinkContext.php';
/**
 * Features context.
 */
class DefaultFeatureContext extends RedefinedMinkContext {
private $elementArray = array();
private $browser;
private $sauceEnabled;
public $bankHolidays;
private $timeoutDuration;

  /**
   * Initializes context.
   * Every scenario gets it's own context object.
   *
   * @param array $parameters context parameters (set them up through behat.yml)
   */
  public function __construct(array $parameters) {
    $this->default_browser = $parameters['default_browser'];
    if (isset($parameters['drupal_users'])) {
      $this->drupal_users = $parameters['drupal_users'];
    }
    if (isset($parameters['post title'])) {
      $this->postTitle = $parameters['post title'];
    }
    if (isset($parameters['environment'])) {
      $this->environment = $parameters['environment'];
    }
    if (isset($parameters['browser'])) {
      $this->browser = $parameters['browser'];
    }
    if (isset($parameters['bank_holidays'])) {
      $this->bankHolidays = $parameters['bank_holidays'];
    }
    if (isset($parameters['timeout_duration'])) {
      $this->timeoutDuration = $parameters['timeout_duration'];
    }
  }



  /** Check if page has links mentioned in links parameter.
   *      
   * @Given /^I (?:should |)see the following:$/
   */
  public function iShouldSeeTheFollowingLinks(TableNode $table) {$page = $this->getSession()->getPage();
    $table = $table->getHash();
    
    foreach ($table as $key => $value) {
      $link = $table[$key]['links'];
      $result = $page->findLink($link);
      if (empty($result)) {
        throw new Exception("The link '" . $link . "' was not found");
      }
    }
  }

  /** Check if page does not have links mentioned. 
   * @Given /^I should not see the following <links>$/
   */
  public function iShouldNotSeeTheFollowingLinks(TableNode $table) {
    $page = $this->getSession()->getPage();
    $table = $table->getHash();
    foreach ($table as $key => $value) {
      $link = $table[$key]['links'];
      $result = $page->findLink($link);
      if (!empty($result)) {
        throw new Exception("The link '" . $link . "' was found");
      }
    }
  }

  /**
   * Function to check if the field specified is outlined in red or not
   *
   * @Given /^the field "([^"]*)" should be outlined in red$/
   *
   * @param string $field
   *   The form field label to be checked.
   */
  public function theFieldShouldBeOutlinedInRed($field) {
    $page = $this->getSession()->getPage();
    // get the object of the field
    $formField = $page->findField($field);
    if (empty($formField)) {
      throw new Exception('The page does not have the field with label "' . $field . '"');
    }
    // get the 'class' attribute of the field
    $class = $formField->getAttribute("class");
    // we get one or more classes with space separated. Split them using space
    $class = explode(" ", $class);
    // if the field has 'error' class, then the field will be outlined with red
    if (!in_array("error", $class)) {
      throw new Exception('The field "' . $field . '" is not outlined with red');
    }
  }

  /** Enter random text in testfield
   *
   * @Given /^I fill in "([^"]*)" with random text$/
   */
  public function iFillInWithRandomText($label) {
    // A @Tranform would be more elegant.
    $randomString = $this->randomString(10);

    $step = "I fill in \"$label\" with \"$randomString\"";
    return new Then($step);
  }

    /** Check if page contains today's date in specified format
	 *
     * @Then /^I should see todays date in "([^"]*)" format$/
     */
    public function seeTodaysDate($format) {

        $todaysDate = date($format);
        $this->assertSession()->pageTextContains($todaysDate);

    }

  /** Check if page contains specified date in specified format
   *
   * @Then /^I should see todays date in "([^"]*)" format and "([^"]*)"$/
   */
  public function seeTodaysDatePlus($format,$increase) {
    $date = date('Y-m-d'); //get todays date
    $addedDate = date('Y-m-d', strtotime($date . $increase));
    $formattedDate = new DateTime($addedDate);
    $finalDate = $formattedDate->format($format);
    $this->assertSession()->pageTextContains($finalDate);
  }





  /**
   * Helper function to fetch user details stored in behat.local.yml.
   *
   * @param string $type
   *   The user type, e.g. drupal.
   *
   * @param string $name
   *   The username to fetch the password for.
   *
   * @return string
   *   The matching password or FALSE on error.
   */
  public function fetchUserDetails($type, $name) {
    $property_name = $type . '_users';
    try {
      $property = $this->$property_name;
      $details = $property[$name];

      return $details;
    } catch (Exception $e) {
      throw new Exception("Non-existant user/password for $property_name:$name please check behat.local.yml.");
    }
  }

  /**
   * Checks the review process is functioning
   *
   * @Then /^I check the omniture tags:$/
   */
  public function checkOmnitureTags(PyStringNode $markdown) {
    $page = $this->getSession()->getPage();
    $omtags = $page->find('xpath', '//*[contains(text(),"/************* DO NOT ALTER ANYTHING BELOW THIS LINE ! **************/")]');
    $tagText = $omtags->getHtml();
    $lines = $markdown->getLines();
    foreach ($lines as $value) {
      if (strpos($value, "\"/number/\"") == true) {
        $varTag = strstr($value, '"', true); // PHP 5.3
        $varTag = "/" . $varTag . '"([0-9]+)"/i';
        //echo $varTag;
        if (preg_match($varTag, $tagText) == false)
          throw new Exception("ERROR: Dynamic tag failure");
      }
      else {
        if (strpos($tagText, $value) == false) {
          throw new Exception("ERROR: Found incorrect match! The following tag \n " . $value . " \n was not fo  und anywhere on the page!");
        }
      }
    }
  }

  /**
   * Checks the review process is functioning
   *
   * @Then /^I check it is the next working day$/
   */
  public function iCheckItIsTheNextWorkingDay() {
    $date = new DateTime($this->getTheNextWorkingday());
    $dateString = $date->format("D. j M");
    $this->assertSession()->pageTextContains($dateString);
  }

  private function getTheNextWorkingday() {

    $date = date('Y-m-j'); //get todays date
    $bankholidayArray = explode(",", $this->bankHolidays); //get bank holiday as array
    $deliveryDate = date('Y-m-j ', strtotime($date . ' +1 Weekday')); //get next weekday from today
    foreach ($bankholidayArray as &$value) { //for every bank holiday
      if ($deliveryDate === $value) { //if the next day is a bank holiday
        $deliveryDate = date('Y-m-j', strtotime($value . ' +1 Weekday')); //look for next weekday after bank holiday, set it as delivery
      }
    }
   // $deliveryDate="Wed. 3 Jul";
    return $deliveryDate;
  }
   /**
    * function returns working date with a specified no of days from today's date excluding bank holidays.
    **/
  public function getTheWorkingDayFrom($days) {
    $date = date('Y-m-j');
    $bankholidayArray = explode(",", $this->bankHolidays); //get bank holiday as array
    $temp_deliveryDate = date('Y-m-j', strtotime($date . ' +' . $days . ' days'));
    $dayOfWeek = date('l', strtotime($date . ' +' . $days . ' days'));
    if ($dayOfWeek === "Sunday") {
      $deliveryDate = date('Y-m-j', strtotime($temp_deliveryDate . ' +1 day'));
    } else {
      $deliveryDate = $temp_deliveryDate;
    }
    foreach ($bankholidayArray as $value) { //for every bank holiday
      if ($deliveryDate === $value) { //if the next day is a bank holiday
        $deliveryDate = date('Y-m-j', strtotime($value . ' +1 Weekday')); //look for next weekday after bank holiday, set it as delivery
      }
    }
    //$deliveryDate="Wed. 3 Jul";
    return $deliveryDate;
  }

  /**
   * Waits for a set amount of time.
   *
   * @Then /^I wait for (\d+) seconds$/
   */
  public function iWaitFor($seconds) {
    $miliseconds = $seconds * 1000;
    $this->getSession()->wait($miliseconds);
  }

  /**
   * Switches to Iframe
   *
   * @Then /^I switch to the "([^"]*)" frame$/
   */
  public function SwitchToFrame($frame) {
    $this->getSession()->switchToIFrame($frame);
  }

  /**
   * Switches to Iframe's, used for 3d secure*
   * @Then /^I switch back from the frame$/
   */
  public function switchBackFromFrame() {
    $this->getSession()->switchToIFrame(null);
  }

  /**
   * Switches to window
   *
   * @Then /^I switch to the "([^"]*)" window$/
   */
  public function switchToWindow($window) {
    $this->getSession()->switchToWindow($window);
  }

  /**
   * Switches to window
   *
   * @Then /^I switch back from the window$/
   */
  public function switchBackFromWindow() {
    $this->getSession()->switchToWindow(null);
  }

  /**
   * Accept alert.                                                ]
   *
   * @Then /^I accept alert$/
   */
  public function acceptAlert() {
    $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
  }

  /**
   * Pay with a 3D secure card.                                                ]
   *
   * @Then /^I pay with a non 3D secure card$/
   */
  public function payWithANon3dSecure() {
    $currentdate = date("Y");
    if ($this->browser == "firefox") {
      $this->getSession()->switchToIFrame("datacash-payment-frame");
      $page = $this->getSession()->getpage();
      $page->fillField("dc_card_number", "1000350000000007");
      $page->fillField("dc_capf1", "Test user");
      $page->selectFieldOption("exp_month", "03");
      $page->selectFieldOption("exp_year", $currentdate + 1);
      $page->fillField("dc_cv2_number", "123");
      $page->pressButton("Confirm and pay");
      $this->getSession()->switchToIFrame(null);
      $this->getSession()->wait(10000);
    } else { //Chrome has issues using mink functions in a iframe. Instead inject javascript to change the values if using chrome.
      $this->getSession()->switchToIFrame("datacash-payment-frame");
      $this->getSession()->getDriver()->executeScript('document.getElementById(\'dc_card_number\').value="1000350000000007"');
      $this->getSession()->getDriver()->executeScript('document.getElementById(\'dc_capf1\').value="Test user"');
      $this->getSession()->getDriver()->executeScript('document.getElementsByName("exp_month")[0].value="03"');
      $this->getSession()->getDriver()->executeScript('document.getElementsByName("exp_year")[0].value="2014"');
      $this->getSession()->getDriver()->executeScript('document.getElementById(\'dc_cv2_number\').value="123"');
      $this->getSession()->getDriver()->executeScript('document.getElementsByClassName("nextButton")[0].click();');
      $this->getSession()->switchToIFrame(null);
      $this->getSession()->wait(5000);
    }
  }

  /**
   * Pay with a 3D secure card.
   *
   * @Then /^I scroll to the bottom$/
   */
  public function scrollBottom() {
    $this->getSession()->wait(2000);
    $this->getSession()->getDriver()->executeScript("scroll(0, 50000)");

    $this->getSession()->wait(1000);
  }

  /**
   * Resets the browser, clearing cookies.
   *
   * @Then /^I wait (\d+) miliseconds$/
   */
  public function waitMiliseconds($miliseconds) {
    $this->getSession()->wait($miliseconds);
  }


    /**
   * Pay with a 3D secure card.
   *
   * @Then /^I pay with a non authenticated 3D secure card$/
   */
  public function payWithA3dSecureFail() {

    $currentdate = date("Y");
    $this->getSession()->switchToIFrame("datacash-payment-frame");
    $page = $this->getSession()->getpage();
    $page->fillField("Card number", "1000350000000007");
    $page->fillField("Cardholder name", "Test User");
    $page->selectFieldOption("exp_month", "01");
    $page->selectFieldOption("exp_year", $currentdate + 1);
    $page->fillField("Security code", "123");
    $page->pressButton("Confirm and pay");
    $this->getSession()->wait(7000);
    $page->pressButton("Not Authenticated");
    $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
    $this->getSession()->switchToIFrame(null);
    $this->getSession()->wait(7000);
    echo 'Finished';
  }

  /**
   * Checks the terms and conditions have been accepted
   *
   * @Given /^I have accepted the terms and conditions$/
   */
  public function iAcceptTheTermsAndConditions() {
    if ($this->getSession()->getPage()->find('xpath', '//*[contains(text(),"You have already accepted the terms and conditions")]')) {
      return;
    }
    else if($this->getSession()->getPage()->find('xpath','//*[contains(text(),"You have already accepted the")]')){
      return ;
    }
    else if ($this->getSession()->getPage()->findById("edit-terms-conditions")) {
      $this->getSession()->getPage()->checkField("edit-terms-conditions");
    } else if ($this->getSession()->getPage()->findById("edit-smartstamp-tc")) {
      $this->getSession()->getPage()->checkField("edit-smartstamp-tc");
    } else if ($this->getSession()->getPage()->findById("edit-olp-flows-tc")) {
      $this->getSession()->getPage()->checkField("edit-olp-flows-tc");
    } else if ($this->getSession()->getPage()->findById("edit-prepay-tc")) {
      $this->getSession()->getPage()->checkField("edit-prepay-tc");
    }
    else if($this->getSession()->getPage()->find('xpath','//*[contains(text(),"I have previously read and accepted the")]')){
      return ;
    }
    else if($this->getSession()->getPage()->findById("edit-olp-flows-tc")){
      $this->getSession()->getPage()->checkField("edit-olp-flows-tc");
    }

    else if($this->getSession()->getPage()->findById("edit-prepay-tc")){
      $this->getSession()->getPage()->checkField("edit-prepay-tc");
    }
  }

  /**
   * Pay with a 3D secure card.                                                ]
   *
   * @Then /^I pay with a 3D secure card$/
   */
  public function payWithA3dSecure() {

    $currentdate = date("Y");
    if ($this->browser == "firefox") {
      $this->getSession()->switchToIFrame("datacash-payment-frame");
      $page = $this->getSession()->getpage();
      $page->fillField("Card number", "1000350000000007");
      $page->fillField("dc_capf1", "Test User");
      $page->selectFieldOption("exp_month", "01");
      $page->selectFieldOption("exp_year", $currentdate + 1);
      $page->fillField("Security code", "123");
      $page->pressButton("Confirm and pay");
      $this->getSession()->wait(19000);
      $this->getSession()->switchToIFrame(null);
	  $this->getSession()->switchToIFrame(null);
      $this->getSession()->switchToIFrame("datacash-payment-frame");
      $page->pressButton("Authenticated");
      try {
        $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
      } catch (Exception $e) {
        echo 'Testing on UAT, no alert found';
      }
      $this->getSession()->wait(15000);
      $this->getSession()->switchToIFrame(null);
    } else { // Chrome has issues using mink functions in a iframe. Instead inject javascript to change the values if using chrome.
      $this->getSession()->switchToIFrame("datacash-payment-frame");
      $this->getSession()->getDriver()->executeScript('document.getElementById(\'dc_card_number\').value="1000350000000007"');
      $this->getSession()->getDriver()->executeScript('document.getElementById(\'dc_capf1\').value="Test user"');
      $this->getSession()->getDriver()->executeScript('document.getElementsByName("exp_month")[0].value="01"');
      $this->getSession()->getDriver()->executeScript('document.getElementsByName("exp_year")[0].value="2014"');
      $this->getSession()->getDriver()->executeScript('document.getElementById(\'dc_cv2_number\').value="123"');
      $this->getSession()->getDriver()->executeScript('document.getElementsByClassName("nextButton")[0].click();');
      $this->getSession()->wait(5000);
      $this->getSession()->getDriver()->executeScript('document.getElementsByName("choice")[0].click();');
      $this->getSession()->switchToIFrame(null);
      $this->getSession()->wait(5000);

      try { //On SIT3 a alert is present that causes behat tests to fail, if detected accept it. Otherwise ignore.
        $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
      } catch (Exception $e) {
        echo 'Testing on UAT, no alert found';
      }
    }
  }

  /**
   * Opens specified external page.
   *
   * @Given /^(?:|I )am on external page "(?P<page>[^"]+)"$/
   * @When /^(?:|I )go to external page "(?P<page>[^"]+)"$/
   */
  public function visitExternal($page) {
    $this->getSession()->visit($page);
  }

  /**
   * Check for fields in the page.
   *
   * @Then /^I should see the field "([^"]*)"$/
   */
  public function iShouldSeeTheField($field) {
    $field = $this->getSession()->getPage()->findField($field);
    if (empty($field)) {
      throw new Exception("The field '" . $field . "' was not found on the page");
    }
    return;
  }

  /**
   * Fill in field with current day.
   *
   * @Then /^the "([^"]*)" field should contain the current day$/
   */
  public function assertContainsDay($field) {
    $day = date('d');
    $page = $this->getSession()->getpage();
    $el = $page->find('css', '#' . $field . '');
    $selectedValue = $el->getValue();
    if ($selectedValue != $day) {
      throw new exception('Month "' . $day . '" not selected in "' . $field . '" dropdown');
    }
  }

  /**
   * Fill in field with current month.
   *
   * @Then /^the pdf should contain the text "([^"]*)"$/
   */
  public function assertPDFContainsText($text) {
    require_once 'features/bootstrap/generic/pdf2text.php';
    $url = $this->getSession()->getCurrentUrl();
    $path = 'DownloadedBehatPDF.pdf';
    $aContext = array(
        'http' => array(
            'proxy' => '10.23.12.100:8080',
            'request_fulluri' => true,
        ),
    );
    $cxContext = stream_context_create($aContext);

    $sFile = file_get_contents($url, False, $cxContext);
    file_put_contents($path, $sFile);
    $result = pdf2text('sample.pdf');
    if (strpos($result, $text) == false) {
      throw new Exception("The text was not found in the pdf");
    }
  }

  /**
   * Fill in field with current month.
   *
   * @Then /^the "([^"]*)" field should contain the current month$/
   */
  public function assertContainsMonth($field) {
    $month = date('m');
    $page = $this->getSession()->getpage();
    $el = $page->find('css', '#' . $field . '');
    $selectedValue = $el->getValue();
    if ($selectedValue != $month) {
      throw new exception('Month "' . $month . '" not selected in "' . $field . '" dropdown');
    }
  }

  /**
   * Fill in field with current Year.
   *
   * @Then /^the "([^"]*)" field should contain the current year$/
   */
  public function assertContainsYear($field) {
    $year = date('Y');
    $page = $this->getSession()->getpage();
    $el = $page->find('css', '#' . $field . '');
    $selectedValue = $el->getValue();
    if ($selectedValue != $year) {
      throw new exception('Year "' . $year . '" not selected in "' . $field . '" dropdown');
    }
  }

  /**
   * Checks the review process is functioning
   *
   * @Given /^I should see the text:$/
   */
  public function iShouldSeeTheText(PyStringNode $markdown) {
 
    $linesArray = $markdown->getLines();
    foreach ($linesArray as $value) {
      $this->assertSession()->pageTextContains($value);
    }
  }

  /**
   * Check for the page title.
   *
   * @Then /^I should see the title "([^"]*)"$/
   */
  public function iShouldSeeTheTitle($title) {
    $page = $this->getSession()->getpage();
    $element = $page->find('css', 'h1');
    if (strpos($element->getText(), $title) === FALSE) {
      throw new Exception($title . 'tile is not found as expected.');
    }
  }

  /**
   * Check for the page which I am in -since multistep form checking the title.
   *
   * @Then /^I should see the page "([^"]*)"$/
   */
  public function iShouldSeeThePage($title) {
    $page = $this->getSession()->getpage();
    $element = $page->find('css', 'h1');
    if (strpos($element->getText(), $title) === FALSE) {
      throw new Exception($title . 'tile is not found as expected.');
    }
  }

  /**
   * Select a radio button by id.
   *
   * @Given /^I select radio button "([^"]*)"$/
   */
  public function iSelectRadioButton($id) {
    $session = $this->getSession();
    $element = $session->getPage();
    $radiobutton = $element->findById($id);
    if (NULL === $radiobutton) {
      throw new ElementNotFoundException(
              $this->getSession(), 'form field', 'id|name|label|value', null
      );
    }
    //$value = $radiobutton->getAttribute('value');
    //$radiobutton->selectOption($value, True);
    //Commented out to fix issue trigering javascript. Click used instead.
    $radiobutton->click();
  }

  /**
   * Should be able to see the datacash iframe.
   *
   * @Then /^I should see the datacash iframe$/
   */
  public function iShouldSeeTheDatacashIframe() {
    $page = $this->getSession()->getPage();
    $iframe = $page->find('css', 'iframe.datacash-frame');
    if (empty($iframe)) {
      throw new Exception("No iframe found");
    }
  }

  /**
   * Check value of a text field the page.
   *
   * @Then /^I should see the text field "([^"]*)" with value "([^"]*)"$/
   */
  public function iShouldSeeTheTextFieldWithValue($field_name, $value) {
    $page = $this->getSession()->getPage();
    // Get all the text fields of the page
    $textfields = $page->findAll('xpath', '//input[@type="text"]');
    if (empty($textfields)) {
      throw new Exception("The page does not contain any text fields");
    }
    foreach ($textfields as $text_field) {
      if ($text_field->getAttribute('name') == $field_name && $text_field->getAttribute('value') == $value) {
        return;
      }
    }
    throw new Exception("No such text filed with given value found");
  }

  /**
   * Click on element with xpath as parameter
   *
   * @When /^I click on the element with xpath "([^"]*)"$/
   */
  public function iClickOnTheElementWithXpath($xpath) {

    $session = $this->getSession(); // get the mink session

    $element = $session->getPage()->find('xpath', $xpath); // runs the actual query and returns the element
    // errors must not pass silently
    if (null === $element)
      throw new InvalidArgumentException(sprintf('Could not evaluate XPath: "%s"', $xpath));

    // ok, let's click on it
    $element->doubleClick();
  }

  /**
   * add the title tag to the class array "elementArray".
   *
   * @Given /^I note the title$/
   */
  public function noteTitle() {
    $page = $this->getSession()->getPage();
    $element = $page->find('css', 'title');
    $text = $element->getText();
    array_push($this->elementArray, $text);
  }

  /**
   * print out the class array "elementArray".
   *
   * @Given /^I print out the array$/
   */
  public function printArray() {
    print_r($this->elementArray);
  }

  /** 
   * Verify a pdf conatains text specified
   * 
   * @Given /^I verify text "([^"]*)" in downloaded pdf "([^"]*)" at "([^"]*)"$/
   */
  public function iVerifyTextInDownloadedPdfAt($textToVerify, $imagePath1, $imagePath2) {

    require_once 'features/bootstrap/libarys/pdf2text.php';

    //below code will check check if an image/pdf file exists in a directory, and if so,
    //return a filename which doesnt exists e.g. if you try 'flower.jpg' and it exists,
    //then it tries 'flower[1].jpg' and if that one exists it tries 'flower[2].jpg' and so on.
    //It works fine at my place. Ofcourse you can use it also for other filetypes than images.

    $path = $imagePath2;

    $latest_ctime = 0;
    $latest_filename = '';

    $d = dir($imagePath2);
    while (false !== ($entry = $d->read())) {
      $filepath = "{$imagePath2}/{$entry}";
      // could do also other checks than just checking whether the entry is a file
      if (is_file($filepath) && filectime($filepath) > $latest_ctime) {
        $latest_ctime = filectime($filepath);
        $latest_filename = $entry;
      }
    }

    //Below code gets text from pdf file stored at path $directory.$latest_filename
    $result = pdf2text($imagePath2 . $latest_filename);


    if (stristr($result, $textToVerify) === false) {
      throw new Exception("The text was not found in the pdf");
    }
  }

  /** 
   * Uncheck checkboxes in web page
   *
   * @Then /^I uncheck checkboxes "([^"]*)"$/
   */
  public function iUncheckCheckboxes($unchecks) {

    $checkboxes = explode(',', $unchecks);
    $page = $this->getSession()->getPage();
    foreach ($checkboxes as $check) {
      if ($check == true) {
        $page->findById($check)->uncheck();
      } else {
        throw new PendingException();
      }
    }
  }

 /**  
  * Verify a table with css class in webpage has a column name 
  *  
  *  @Then /^I verify the table with class "([^"]*)" has column name "([^"]*)"$/
  */

 public function iVerifyTheTableWithClassHasColumnName($class_name,$col_header) {

     $i=1;
   do
   {
     $column_xpath="//table[@class='".$class_name."']//tr//th[".$i."]";
    // $column_xpath="{$xpath}{$str}";
     $page=$this->getSession()->getPage();
     $column_val=$page->find('xpath',$column_xpath);
     if($column_val==Null)
     {        throw new Exception("column header not found");
       break;

     }
     else{

       $column_text=$column_val->getText();
       if($column_text==$col_header)
       {
       //  echo $column_text;
       //echo $col_header;
         return true;
         break;
       }


       $i++;
     }
   }while($column_val<>Null);
 }

  /** 
   * Verify that a table with css class has text(value ) in a cell mentioned with row, column
   *
   * @Then /^I verify the table with class "([^"]*)" has "([^"]*)" in row "([^"]*)" and column "([^"]*)"$/
   */
 public function iVerifyTheTableWithClassHasInRowAndColumn($class_name, $text, $row, $column) {
   $table_xpath="//table[@class='".$class_name."']//tr[".$row."]//td[".$column."]";
    // $table_xpath="{$xpath}{$str}";
     //echo $table_xpath;
     $page=$this->getSession()->getPage();
     $table_val=$page->find('xpath',$table_xpath);

     if($table_val ===Null)
     {
       throw new Exception("Element not found");

     }
     else{

         $table_text=$table_val->getText();
         if($table_text==$text)
         {
           return true;
         }
         else
         {
		 echo $table_val->getText()."hi";
         throw new Exception("Text not found");
         }

       }
 }

 /** 
  * Verify table with css class has n number of rows
  * 
  * @Then /^I verify the table with class "([^"]*)" has "([^"]*)" no of rows$/
  */
 public function iVerifyTheTableWithClassHasNoOfRows2($class_name, $row) {
   $page=$this->getSession()->getPage();
   $j=0;
     for($i=1;$i<=$row;$i++)
     {

       $table_xpath="//table[@class='".$class_name."']//tr[".$i."]";

       //echo $table_xpath;
       $table_val=$page->find('xpath',$table_xpath);
       if($table_val ===Null)
       {
         //echo "table has only".$j."rows";
         throw new Exception("Number of Rows is not correct");
         break;

       }
       else{
         return true;
         }


       $j=$i;
     }
 }

  /**
   * This step definition will verify a particular option is selected
   *
   * @Given /^I verify option "([^"]*)" in list with name "([^"]*)" should be selected$/
   */
  public function iVerifyOptionInListWithNameShouldBeSelected($optionValue, $select) {

	    $selectElement = $this->getSession()->getPage()->find('named', array('select', "\"{$select}\""));
	    $optionElement = $selectElement->find('named', array('option', "\"{$optionValue}\""));

		//it should have the attribute selected and it should be set to selected
	    if ($optionElement->getAttribute("selected") == true)
			return true;
		else
			{

				throw new Exception("No such Option is selected");
			}
	}


  /**
   * Switches to the last opened window.
   *
   * @Given /^I switch to the last opened window$/
   */
  public function iSwitchToLast() {
    $window_array = $this->getWindowNames();
    $this->getSession()->switchToWindow(end($window_array));
  }

  /**
   * Return the names of all open windows
   *
   * @return array    array of all open windows
   */
  public function getWindowNames()
  {
    return $this->getSession()->getDriver()->getWebDriverSession()->window_handles();
  }

  /**
   * Return the name of the currently active window
   *
   * @return string    the name of the current window
   */
  public function getWindowName()
  {
    return $this->getSession()->getDriver()->getWebDriverSession()->window_handle();
  }


  /**
   *  Upload a file to input=file field
   *
   * @Given /^I upload the file at "([^"]*)" to "([^"]*)"$/
   */
  public function iUploadTheFileAt($filepath,$field) {
    $this->getSession()->getDriver()->getWebDriverSession()->element('name', $field)->value(array('value' => str_split($filepath)));
  }



  /**
   * Resets the browser, clearing cookies.
   *
   * @Given /^I perform the tealium loop (\d+) times with a wait of (\d+) miliseconds/
   */
  public function tealiumLoop($loop,$miliseconds) {
    for ($i = 1; $i <= $loop; $i++) {
      $sessionbehat = $this->getSession();
      echo "\033[34m Start \033[0m \n";
      $sessionbehat->visit($this->locatePath('/'));
      $sessionbehat->wait(3000,"document.readyState == 'complete'");
      echo "\033[32m locate path is ".$this->locatePath('/track-trace')." \033[0m \n";
      $sessionbehat->wait($miliseconds);
      echo "\033[34m Finished waiting ".$miliseconds." miliseconds\033[0m \n";
      $sessionbehat->visit($this->locatePath('/track-trace'));
      $sessionbehat->wait(3000,"document.readyState == 'complete'");
      echo "\033[34m Navigated to Track and trace \033[0m \n";
      $sessionbehat->visit($this->locatePath('/'));
      $sessionbehat->wait(3000,"document.readyState == 'complete'");
      echo "\033[34m Navigated to home page \033[0m \n";
      $sessionbehat->wait($miliseconds);
      echo "\033[34m Finished waiting ".$miliseconds." miliseconds\033[0m \n";
      $sessionbehat->reset();
      echo "\033[34m Restarting the browser\033[0m \n";
      echo "\033[34m Loop performed  ".$i." times \033[0m \n";
      echo "\n";

    }
  }



  /**
   * Resets the browser, clearing cookies.
   *
   * @Given /^I perform the tealium loop (\d+) times with a wait of (\d+) milliseconds and (\d+) milliseconds/
   */
  public function tealiumLoop2($loop,$miliseconds,$miliseconds2) {
    for ($i = 1; $i <= $loop; $i++) {
      $sessionbehat = $this->getSession();
      echo "\033[32m Starting \033[0m \n";
      $sessionbehat->executeScript("document.location.href = 'http://wwwuat.royalmail.com'");
      echo "\033[34m Started to load home page \033[0m \n";
      $sessionbehat->wait($miliseconds);
      echo "\033[34m Finished waiting ".$miliseconds." miliseconds\033[0m \n";
      echo "\033[34m Loading track and trace \033[0m \n";
      $sessionbehat->visit($this->locatePath('/track-trace'));
      $sessionbehat->wait(3000,"document.readyState == 'complete'");
      echo "\033[34m Finished loading track and trace \033[0m \n";
      echo "\033[34m Started to load home page \033[0m \n";
      $sessionbehat->visit($this->locatePath('/'));
      $sessionbehat->wait(3000,"document.readyState == 'complete'");
      echo "\033[34m Finished loading home page \033[0m \n";
      $sessionbehat->wait($miliseconds2);
      echo "\033[34m Finished waiting ".$miliseconds2." miliseconds\033[0m \n";
      $sessionbehat->reset();
      echo "\033[34m Resetting cache \033[0m \n";
      echo "\033[34m Loop performed \033[32m ".$i." \033[34m times \033[0m  \n";
      echo "\n";

    }
  }

   /**
	* Resets the browser, clearing cookies.
	*
	* @Given /^I perform the tealium loop (\d+) times and wait for the page to load with a additional wait of (\d+) milliseconds$/
	*/
  public function tealiumLoop3($loop,$miliseconds) {
    for ($i = 1; $i <= $loop; $i++) {
      $sessionbehat = $this->getSession();
      echo "\033[32m Starting \033[0m \n";
      $sessionbehat->visit($this->locatePath('/specialist-services/publishing/uk-delivery/publishing-mail'));
      $sessionbehat->wait(3000,"document.readyState == 'complete'");
      echo "\033[34m Loaded home page \033[0m \n";
      $sessionbehat->wait($miliseconds);
      echo "\033[34m Finished waiting ".$miliseconds." miliseconds\033[0m \n";
      $sessionbehat->reset();
      echo "\033[34m Resetting cache \033[0m \n";
      echo "\033[34m Loop performed \033[32m ".$i." \033[34m times \033[0m  \n";
      echo "\n";

    }
  }



  /**
   * price-finder loop
   *
   * @Given /^I perform the tealium pricefinder loop (\d+) times$/
   */
  public function tealiumprciefinderLoop($loop) {
    for ($i = 1; $i <= $loop; $i++) {
      $sessionbehat = $this->getSession();
      echo "\033[32m Starting \033[0m \n";
      $sessionbehat->visit($this->locatePath('/price-finder'));
      $sessionbehat->wait(3000,"document.readyState == 'complete'");
      echo "\033[34m Loaded price finder page \033[0m \n";
      $sessionbehat->wait(500);
      echo "\033[34m Finished waiting 500 miliseconds\033[0m \n";
      $radiobutton = $sessionbehat->getPage()->findById("service_UK");
      $radiobutton->click();
      $sessionbehat->wait(2500);
      echo "\033[34m Finished selecting UK radio button\033[0m \n";
      $nextbutton = $sessionbehat->getPage()->findById("btn_next");
      $nextbutton->click();
      $sessionbehat->wait(1500);
      echo "\033[34m Finished pressing next\033[0m \n";
      $sessionbehat->getPage()->fillField("txt_weight","100");
      echo "\033[34m Finished filling in weight with 100\033[0m \n";
      $nextbutton2 = $sessionbehat->getPage()->findById("btn_change_price");
      $nextbutton2->click();
      $sessionbehat->wait(1500);
      echo "\033[34m Finished pressing next\033[0m \n";
      $sessionbehat->wait(1500);
      $nextbutton3 = $sessionbehat->getPage()->findById("btn_find_price");
      $nextbutton3->click();
      echo "\033[34m Finished pressing next\033[0m \n";
      echo "\033[34m waiting for ajax to finish\033[0m \n";
      $sessionbehat->wait(3000);
      $sessionbehat->reset();
      echo "\033[34m Resetting cache \033[0m \n";
      echo "\033[34m Loop performed \033[32m ".$i." \033[34m times \033[0m  \n";
      echo "\n";

    }
  }
  
  public function getOrderIDFromUrl() {
    $currentPage = $this->getSession()->getCurrentUrl();
    $parts = explode("/",$currentPage);
    // Remove last part i.e checkout|basket|complete
    array_pop($parts);
    // return order ID
    return array_pop($parts);
  }
  
  
  /** 
   * Verify a list has all items appearing inside in it.
   *
   * @Given /^I verify the values in the dropdownlist in id "([^"]*)"$/
   */
  public function iVerifyTheValuesInTheDropdownlistInId($id, PyStringNode $string) {
  
                $drop_xpath="//*[@id='".$id."']";
                $element=$this->getSession()->getPage()->find('xpath',$drop_xpath);
                $drop_items=$string->getLines();
                foreach ($drop_items as $value) {
                                $item= $element->find('named',array('option',"\"{$value}\""));
                                if($item==null)
                                throw new Exception ($value ." Element not found in the drop downlist box");
                                else
                                continue;
                }
                echo "sompleted";
  }

    
  /**
   * verify radio button with id is checked.
   *
   * @Given /^I verify radiobutton with id "([^"]*)" is checked$/
   */

  public function iVerifyRadiobuttonWithIdIsChecked($id) {
  
                $radio_xpath="//*[@id='".$id."']";
                $element=$this->getSession()->getPage()->find('xpath',$radio_xpath);
                
                if($element->getAttribute("checked")==true)
                                return true;
                else
                throw new Exception($name ."Element not found"); 
                
                
                
                
    
                
  }

  /**
   * Checks that the specified table contains the specified number of rows in its body
   *
   * @Then /^I should see "(\d+)" rows in the "(\d+)(?:st|nd|rd|th)" "([^"]*)" table$/
   */

    public function iShouldSeeRowsInTheNthTable($nth, $index, $table)
    {
        $tables = $this->getSession()->getPage()->findAll('css', $table);
        if (!isset($tables[$index - 1])) {
            throw new \Exception(sprintf('The %d table "%s" was not found in the page', $index, $table));
        }
        $rows = $tables[$index - 1]->findAll('css', 'tbody tr');
    
        if($nth == count($rows)) {
        return true; }
        else {
          throw new Exception ("wrong number of rows");
        }
        
    }

  /**
   * Checks order/prepay-top up with specified response and refernce amount
   *
   * e.g. Given I check the datacash with "DECLINED" Response for "OLP" Reference with amount "([^"]*)"$/
   *
   * @Given /^I check the datacash with "([^"]*)" response for order type "([^"]*)" with amount "([^"]*)"$/
   */
  public function iCheckTheDatacashWithResponseForReferenceWithAmountforrefund($resp, $order_type, $amount) {
  $i=2;
  //system time
  $sysdat=new DateTime();
  $sysdat->format("j M Y H:i:s");
  //check if $Datacashdate is present or not , check for null
  try{
    $Datacashdate=$this->getSession()->getPage()->find('xpath',"//table[@id='list']//tr[2]//td[3]")->getText();
  }
  catch(Exception $e){
    throw new ElementNotFoundException($this->getSession(), 'table cell', 'id|name|label|value',null  );
  }
  $Ddatetime = new DateTime($Datacashdate);
  $Ddatetime->format("j M Y H:i:s");

  //loop till order time is less than current time.
  while($Ddatetime<$sysdat){

    $Datacashresp=$this->getSession()->getPage()->find('xpath',"//table[@id='list']//tr[".$i."]//td[7]")->getText();
      //verify if response matches.
      if($Datacashresp==$resp){

          $DCreference=$this->getSession()->getPage()->find('xpath',"//table[@id='list']//tr[".$i."]//td[2]")->getText();
          if (preg_match("/".$order_type."/",$DCreference)==1){

            $Damount=$this->getSession()->getPage()->find('xpath',"//table[@id='list']//tr[".$i."]//td[4]")->getText();
            preg_match("/([0-9\.,-]+)/", $Damount, $match);
            $Damount = $match[0];
              //verify if amount matches.
               if($Damount == $amount)  {

                break;
              }
          }
      }

    $i=$i+1;

    if($this->getSession()->getPage()->find('xpath',"//table[@id='list']//tr[".$i."]//td[3]") <> null){
      $Ddatetime=new DateTime($this->getSession()->getPage()->find('xpath',"//table[@id='list']//tr[".$i."]//td[3]")->getText());
    }
    else{
      throw new Exception($this->getSession(), 'table cell', "Table not found",null  );
    }
  }

  }

  /**
   *  checks order/prepay-top up with specified response and refernce amount
   *
   * e.g. Given I check the datacash with "DECLINED" Response for "OLP" Reference with amount "([^"]*)"$/
   *
   * @Given /^I check the datacash with "([^"]*)" Response for "([^"]*)" Reference with amount "([^"]*)"$/
   */
  public function iCheckTheDatacashWithResponseForReferenceWithAmount($resp, $ref, $amount) {
	$i=2;
	//system time
	$sysdat=new DateTime();
	$sysdat->format("j M Y H:i:s");
	//check if $Datacashdate is present or not , check for null
	try{
		$Datacashdate=$this->getSession()->getPage()->find('xpath',"//table[@id='list']//tr[2]//td[3]")->getText();
	}
	catch(Exception $e){
		throw new ElementNotFoundException($this->getSession(), 'table cell', 'id|name|label|value',null  );
	}
	$Ddatetime = new DateTime($Datacashdate);
	$Ddatetime->format("j M Y H:i:s");

	//loop till order time is less than current time.
	while($Ddatetime<$sysdat){

		$Datacashresp=$this->getSession()->getPage()->find('xpath',"//table[@id='list']//tr[".$i."]//td[7]")->getText();
			//verify if response matches.
			if($Datacashresp==$resp){

					$DCreference=$this->getSession()->getPage()->find('xpath',"//table[@id='list']//tr[".$i."]//td[1]")->getText();
					if (preg_match("/".$ref."/",$DCreference)==1){

						$Damount=$this->getSession()->getPage()->find('xpath',"//table[@id='list']//tr[".$i."]//td[4]")->getText();
						preg_match("/([0-9\.,-]+)/", $Damount, $match);
						$Damount = $match[0];
							//verify if amount matches.
							 if($Damount == $amount)  {

								break;
							}
					}
			}

		$i=$i+1;

		if($this->getSession()->getPage()->find('xpath',"//table[@id='list']//tr[".$i."]//td[3]") <> null){
			$Ddatetime=new DateTime($this->getSession()->getPage()->find('xpath',"//table[@id='list']//tr[".$i."]//td[3]")->getText());
		}
		else{
			throw new Exception($this->getSession(), 'table cell', "Table not found",null  );
		}
	}

  }

  /**
   * checks olp order/prepay-top up with specified response and refernce amount from orders.txt file
   *
   * e.g. Given I check the datacash with "OK, Not sent" Response for order Reference with amount "5.65"
   *
   * @Given /^I check the datacash with "([^"]*)" response for order reference with amount "([^"]*)"$/
   */
  public function iCheckTheDatacashWithResponseForOrderReferenceWithAmount($resp, $amount) {
	$i=2;
	$sysdat=new DateTime();
	$sysdat->format("j M Y H:i:s");

	 $filename = 'OrderNumbers.txt';
			 require_once 'features/bootstrap/libarys/File.php';
			 //recover last order from OrderNumbers.txt
			 $lastline =File::readLastLines($filename,1);
			 $ordernumber = explode(":",$lastline[0]);
			 
			 if (is_numeric($ordernumber[1]))
				throw new Exception("order no is not available in file");
			 $ref=$ordernumber[1];
			 $ref=trim($ref,"\t\n\r\0\x0B");

	try{
		$Datacashdate=$this->getSession()->getPage()->find('xpath',"//table[@id='list']//tr[2]//td[3]")->getText();
	}
	catch(Exception $e){
		throw new ElementNotFoundException($this->getSession(), 'table cell', 'id|name|label|value',null  );
	}
	$Ddatetime = new DateTime($Datacashdate);
	$Ddatetime->format("j M Y H:i:s");

	while($Ddatetime < $sysdat) {
					
					$DCreference=$this->getSession()->getPage()->find('xpath',"//table[@id='list']//tr[".$i."]//td[1]")->getText();
					
					//because if position of $ref is 0. The statement (0 != false) evaluates to false.
					echo strpos($DCreference,$ref);
					if (strpos($DCreference,$ref)!== false){
						
						$Damount=$this->getSession()->getPage()->find('xpath',"//table[@id='list']//tr[".$i."]//td[4]")->getText();
						preg_match("/([0-9\.,-]+)/", $Damount, $match);
						$Damount = $match[0];

							 if($Damount === $amount)  {
									$Datacashresp=$this->getSession()->getPage()->find('xpath',"//table[@id='list']//tr[".$i."]//td[7]")->getText();

									if($Datacashresp==$resp){
										echo $DCreference." with status ".$Datacashresp." with amount ".$Damount;
										break;
									}
									else{
										throw new Exception("Status is not correct for order ".$ref );
									}
							}
				}

		$i=$i+1;
		
		if($this->getSession()->getPage()->find('xpath',"//table[@id='list']//tr[".$i."]//td[3]") <> null){

			$Ddatetime=new DateTime($this->getSession()->getPage()->find('xpath',"//table[@id='list']//tr[".$i."]//td[3]")->getText());
		//echo $Ddatetime->format("j M Y H:i:s");
		}
		else{
			throw new Exception($this->getSession(), 'table cell', "Table not found",null  );
		}
	}
  }
 
  /**
   * Pay with a 3D secure card.                                                ]
   *
   * @Then /^I pay with$/
   */
  public function payWith($card,$card_no,$exp_mnth,$cvv) {

    $currentdate = date("Y");
      $this->getSession()->switchToIFrame("datacash-payment-frame");
      $page = $this->getSession()->getpage();
	  $page->selectFieldOption("dc_cardtype",$card);
      $page->fillField("Card number", $card_no);
      $page->fillField("Name on card", "Test user");
      $page->selectFieldOption("exp_month", $exp_mnth);
      $page->selectFieldOption("exp_year", $currentdate + 1);
      $page->fillField("Security code", $cvv);
      $page->pressButton("Confirm and pay");
      $this->getSession()->wait(15000);
      if($exp_mnth == '01') {
      $this->getSession()->switchToIFrame("datacash-payment-frame");
      
      $page->pressButton("Authenticated");
    

      try {
        $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
      } catch (Exception $e) {
        echo 'Testing on UAT, no alert found';
      }
      $this->getSession()->wait(15000);
      $this->getSession()->switchToIFrame(null);
    } 
      try { //On SIT3 a alert is present that causes behat tests to fail, if detected accept it. Otherwise ignore.
        $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
      } catch (Exception $e) {
        echo 'Testing on UAT, no alert found';
      }
    }
  
  /**
   * payment with card no with specified exp date.
   *
   * @Then /^I pay via "([^"]*)" card number "([^"]*)" with exp date "([^"]*)"$/
   */
  public function iPayViaWithExpDateWithCvvNumber($card,$card_no, $exp_mth) {
    
	switch($card) {
	case "Visa":
		$this->payWith('Visa',$card_no,$exp_mth,'012');
		break;
		
	case "MasterCard":
		$this->payWith('MasterCard',$card_no,$exp_mth,'012');
		break;
		
	case "Maestro":
		$this->payWith('Maestro',$card_no,$exp_mth,'444');
		break;
		
	case "Visa Electron":
		$this->payWith('Visa Electron',$card_no,$exp_mth,'444');
		break;

    case "Mastercardnon3d":
    $this->payWith('MasterCard',$card_no,$exp_mth,'012');
    break;

    case "default":
    $this->payWith('Visa',$card_no,$exp_mth,'012');
    break;
		
	}
  }

  /**
   * This checks to see if a button with specified text as value does not exists
   *
   * @Then /^I should not see the button "([^"]*)"$/
   */
    public function iShouldNotSeeTheButton ($button_name) {

    $session = $this->getSession();
    $element = $session->getPage();
    $button = $element->findButton($button_name);

    // Verify that the element does not exist on the page
	if (!NULL === $button) {
        throw new Exception("The button '" . $button . "' is present on this page");
      }

  }
  
  /**
   * Checks to see if a field with a specified text as value is not present
   *
   * @Given /^I should not see the field "([^"]*)"$/
   */
    public function iShouldNotSeeTheField($id) {
    
	$session = $this->getSession();
	$element = $session->getPage();
	$field = $element->findField($id);
	
      if(!NULL === $field) {
        throw new Exception("The field '" . $field . "' is present on this page");
      }
    }
	
  /**
   * Checks to see if a radio button with a specified text as value is not present
   *
   * @Given /^I should not see the radio button "([^"]*)"$/
   */
  public function iShouldNotSeeTheRadioButton($id) {
    $session = $this->getSession();
    $element = $session->getPage();
    $radiobutton = $element->findById($id);
    if (!NULL === $radiobutton) {
	    throw new Exception("The radio button '" . $radiobutton . "' is present on this page");

    }

  }
  
 /**
  * Checks to see if a checkbox with a specified text as value is not present
  *
  *@Given /^I should not see the checkbox "([^"]*)"$/
  */ 
  public function iShouldNotSeeTheCheckbox($id) {
  
  $session = $this->getSession();
  $element = $session->getPage();
  $checkbox = $element->findByID($id);
  
  if (!NULL === $checkbox) {
		throw new Exception("The checkbox '" . $checkbox . "' is present on this page");
		
	}
	
  }
 
  /** 
   * Check value inside a element with xpath selector 
   * @Given /^I check the value "([^"]*)" in xpath "([^"]*)"$/
   */
  public function iCheckTheValueInXpath($arg1, $arg2) {

	$test=$this->getSession()->getDriver()->getValue($arg2);
	echo  strlen($test);
	echo  $test;

  }

 /**
   * Select a number from number a drop-down
   *
   * THIS METHOD SEEMS POINTLESS AS THE SELECT METHOD WOULD BE BETTER.
   * THIS SHOULD BE DEPRECATED ONCE INSTANCES WITHIN FEATURES ARE REMOVED.
   *
   * @Then /^I select number "([^"]*)" from "([^"]*)"$/
   */
  public function iSelectNumber ($number, $id) {

	$session = $this->getSession();
	$element = $session->getPage();
	$item_drop_down = $element->findById($id);

	//Verify if that element present on page
	if (NULL === $item_drop_down) {
      throw new ElementNotFoundException($this->getSession(), 'item number drop down not found', 'id|name|label|value', null);
      }

	$item_drop_down->selectOption($number);

	$dateoncard = $element->findById($id);

  }

 /**
   * This checks to see if a button with specified text as value exists
   *
   * @Then /^I should see the button "([^"]*)"$/
   */
  public function iShouldSeeTheButton ($button_name) {

    $session = $this->getSession();
    $element = $session->getPage();
    $button = $element->findButton($button_name);

    //Verify if that element present on page
	if (NULL === $button) {
      throw new ElementNotFoundException($this->getSession(), 'button', 'id|name|label|value', null);
      }

  }

  /**
   * This functions replaces text in argument with either GUID or with latest order number
   * so that CSM user can go to that page
   *
   * @Given /^I modify URL "([^"]*)" and go to it$/
   */
  public function iModifyUrlAndGoToIt($url) {
		if (empty($url))
			throw new Exception("URL cannot be empty");

		if ( stristr($url,"<guid>")){

				if (empty($this->guid))
					throw new Exception("guid is not available");
			$newurl = str_replace("<guid>",$this->guid,$url);
		}
		if ( stristr($url,"<olporder>")){
			 $filename = 'OrderNumbers.txt';
			 require_once 'features/bootstrap/libarys/File.php';
			 //recover last order from OrderNumbers.txt
			 $lastline =File::readLastLines($filename,1);
			 $ordernumber = explode(":",$lastline[0]);
			 if (is_numeric($ordernumber[1]))
				throw new Exception("order no is not available in file");
			 $newurl = str_replace("<olporder>",$ordernumber[1],$url);;
        }

		return array(
			new When("I go to \"".$newurl."\"")
		);
	}
	 
  /**
   * Connects user to jump server 10.74.242.10 for execution of sql or other set of commands
   * need to develop more functions like this.function is using libssh2
   * 
   *@Given /^I connect to database$/
   */
  public function iConnectToDatabase() {

		$connection =ssh2_connect("10.74.242.10",22);

		if(!$connection)
		{
		die ("connection failed");
		}

		echo "connection established";

		if(!ssh2_auth_password($connection, "phuch", "Pass123")) {
        die ("fail: unable to authenticate\n");
    }

	$dbq= "d sqlq 'select * from users \G;'";
	echo "loggined in";

	$shell=ssh2_shell($connection, 'xterm');
	fwrite( $shell, 'ssh rmg15web92stg'.PHP_EOL);
	fwrite( $shell, 'Pass123'.PHP_EOL);
	sleep(1);

	fwrite( $shell, 'sudo su buildbot'.PHP_EOL);
	fwrite( $shell, 'Pass123'.PHP_EOL);
	sleep(1);
	fwrite( $shell, 'cdrml'.PHP_EOL);
	sleep(1);

	fwrite( $shell, $dbq.PHP_EOL);
	sleep(10);

        // Then u can fetch the stream to see what happens on stdio
        while($line = fgets($shell)) {
                flush();
                echo $line."<br />";
        }
  }
 
  /**
   * check datacash application for a response with specified reference. 
   * and alternate set of methods can be developed where no UI of datacash is required.
   * @Given /^I check the datacash with "([^"]*)" Response for "([^"]*)" Reference$/
   */
	//before this step get the olp amount
  public function iCheckTheDatacashWithResponse($resp,$ref) {

	$i=2;
	$sysdat=new DateTime();
	$sysdat->format("j M Y H:i:s");
	//amount to be verified
	$olpamount=$this->olpamount;
	//Check if $Datacashdate is present or not , check for null
	try{
		$Datacashdate=$this->getSession()->getPage()->find('xpath',"//table[@id='list']//tr[2]//td[3]")->getText();
		}
	catch(Exception $e){
		throw new ElementNotFoundException($this->getSession(), 'table cell', 'id|name|label|value',null  );
	}
	$Ddatetime = new DateTime($Datacashdate);
	$Ddatetime->format("j M Y H:i:s");

	while($Ddatetime<$sysdat){

		$Datacashresp=$this->getSession()->getPage()->find('xpath',"//table[@id='list']//tr[".$i."]//td[7]")->getText();

			if($Datacashresp==$resp){

					$DCreference=$this->getSession()->getPage()->find('xpath',"//table[@id='list']//tr[".$i."]//td[1]")->getText();
					if (preg_match("/".$ref."/",$DCreference)==1){

						$Damount=$this->getSession()->getPage()->find('xpath',"//table[@id='list']//tr[".$i."]//td[4]")->getText();

						if (strcmp($Damount,$olpamount)==0){

							//echo "Transaction :".$resp."";
							break;
						}
					}
			}

		$i=$i+1;
		if($this->getSession()->getPage()->find('xpath',"//table[@id='list']//tr[".$i."]//td[3]") <> null){
			$Ddatetime=new DateTime($this->getSession()->getPage()->find('xpath',"//table[@id='list']//tr[".$i."]//td[3]")->getText());
			$Ddatetime->format("j M Y H:i:s");
		}
		else{
			throw new Exception($this->getSession(), 'table cell', 'Table not found',null  );
		}
	}

   }

  /**
   * This function helps to download a csv file which has exported contacts from address book
   *
   * @Given /^I download csv file$/
   */
  public function iDownloadCsvFile() {
   
     ///get url to be downloaded.
	$link = $this->getSession()->getPage()->find('css','fieldset a');
	if($link == null ) 
					throw new Exception ("Link element not found ");
	$fileUrl= $link->getAttribute('href');       
   
	$sessionCookie="";
	
	//iterate thriugh each cookie to create name1=value1; name2=value2 format. 
	$cookies = $this->getSession()->getDriver()->getWebDriverSession()->getAllCookies();
	foreach( $cookies as $cookie){
		foreach( $cookie as $key=>$value ){
		if (strpos($key,"name")=== 0 ){
			$sessionCookie .= $cookie[$key]."=".$cookie["value"]."; ";
		}
	}
	}
	//remove last ; and space from cookie string.	
	$sessionCookie = trim(trim($sessionCookie),";");
	$cookieString = "";
	$cookieString = "Cookie: ".$sessionCookie;
		
	//using wget utility for getting resource csv file on server.
	$command = "wget --cookies=on --no-check-certificate --header \"".$cookieString."\" ".$fileUrl;
	exec($command);


  }
   
	/**
     * Checks that the data of the specified row of table matches the given schema
     *
     * @Then /^the data in the "(?P<nth>\d+)(?:st|nd|rd|th)" row of first "\d+" values of the "(?P<table>[^"]*)" table should match:$/
     */
    public function theDataOfTheRowShouldMatch($nth,$value, $table, TableNode $text)
    {
        // creating css selector string
        $rowsSelector = sprintf('%s tbody tr', $table);
        //fetch the rows from the table
        $rows = $this->getSession()->getPage()->findAll('css', $rowsSelector);
             if (!isset($rows[$nth - 1])) {
            throw new \Exception(sprintf('The row %d was not found in the "%s" table', $nth, $table));
        }
        //fetch the data from the row
        $cells = (array)$rows[$nth - 1]->findAll('css', 'td');
        //fetch the data from the table node from the feature
        $hash = current($text->getHash());
        //fetch the keys of hash from table node (column name)
        $keys = array_keys($hash);
          //compare the table node value and actual table available in the web page 
        for ($i = 0; $i < $value; $i++) {
           strcmp($hash[$keys[$i]], $cells[$i]->getText()==0);
        }
    }

  /**
   * Checks if image is present in webpage.
   *
   * @Given /^I check the image with css "([^"]*)"$/
   */
  public function iCheckTheImageWithCss($imgcss) {
    

    $img=$this->getSession()->getPage()->find('css',$imgcss);
    $result=($img<>null) ? true:false;
    echo $result;
    return $result;

  }
  
  
// comparing whether appropriate results is displayed when user searched post code in branchfinder flow
  /**
   * @Given /^I compare postcode "([^"]*)" in table "([^"]*)" in column "([^"]*)"$/
   */
  public function iComparePostcodeInTableInColumn($pcode,$classname,$row) {
    $page=$this->getSession()->getPage();
   //taking first two characters to match with the result displayed
    $pincode=substr($pcode,0,2);
    #$postcode_result=array();
    //get rowcount with xpath of table
    $i=1;
    while($page->find('xpath',"//table[@class=".$classname."]/tbody/tr[".$i."]/td[".$row."]")<>Null){
                $cell=$page->find('xpath',"//table[@class='ftn_result caption-less']/tbody/tr[".$i."]/td[2]");
                //trim post code here
                $code=trim($cell->getText());

                //explode with spaces and get excat post code
                $postcode=explode(" ",$code);
                $cnt=count($postcode);
               # echo $postcode;

                $postoffice_code=substr($postcode[$cnt-2],0,2);
                echo $postoffice_code;
                //take 2/4 chars form start of post code retrieved in earlier step
                echo $pincode;
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

    #$postcode_result=substr($postcode[0],-7,2);
    #echo $postcode[0];
   # echo $postcode_result;
    #print_r($postcode);


  }
  
	/**
	*Below function is used to select the value using the xpath
	* @Given /^I select "([^"]*)" by xpath "([^"]*)"$/
	*/
  public function iSelectByXpath($value, $xpath) {

    $field1 = $this->getSession()->getPage()->find('xpath',$xpath);
    if(empty($field1) ){
    throw new ElementNotFoundException($this->getSession(), ' select field ', 'id|name|label|value',null);
    }
  $field->selectOption($value);
  }
  
   /**
   * Below function is for payment using particular card number
   * @Then /^I pay with a credit card with card number "([^"]*)"$/
   */
  public function iPayWithACreditCardWithCardNumber($cardno) {
    $currentdate = date("Y");
    $this->getSession()->switchToIFrame("datacash-payment-frame");
    $page = $this->getSession()->getpage();
    $page->fillField("Card number", $cardno);
    $page->fillField("Cardholder name", "Test user");
    $page->selectFieldOption("exp_month", "01");
    $page->selectFieldOption("exp_year", $currentdate + 1);
    $page->fillField("Security code", "012");
    $page->pressButton("Confirm and pay");
    $this->getSession()->wait(10000);
	$this->getSession()->switchToIFrame("datacash-payment-frame");
    $page->pressButton("Authenticated");
	 try {
        $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
      } catch (Exception $e) {
        echo 'Testing on UAT, no alert found';
      }
    $this->getSession()->switchToIFrame(null);
    $this->getSession()->wait(7000);
    echo 'Finished';
  }
  
   /**
   * click on link which is inside a table cell.
   *
   * @Given /^I click on the value "([^"]*)" in the table has class name "([^"]*)" in the column "([^"]*)"$/
   */
  public function iClickOnTheValueInTheTableHasClassNameInTheColumn($value, $classname, $colnum) {

 $i=1;
   do
   {
     $column_xpath="//table[@class='".$classname."']//tr[".$i."]//td[".$colnum."]//a";

     $page=$this->getSession()->getPage();
     $column_val=$page->find('xpath',$column_xpath);

	 //check if link is present in table cell
     if($column_val == Null) {
       throw new Exception("link text not found");
       break;
     }
     else{  //click link once link text is matched

       $column_text=$column_val->getText();

       if(preg_match("/".$value."/",$column_text) == 1 )
       {
		   echo $column_text;
		   $column_val=$page->find('xpath',"//table[@class='".$classname."']//tr[".$i."]//td[".$colnum."]//a");
		   $column_val->doubleClick();

         return true;
         break;
       }
	   if($i==20) //move to next page in case link is not found
	   {
		$i=0;
		$j="//*/a[text()='2']";
		return array(
			new When("I click on the element with xpath \"".$j."\""),
			new When("I click on the value \"".$value."\" in the table has class name \"".$classname."\" in the column \"".$colnum."\"")
		);

		}
		echo $i;

       $i++;
     }
   }while($column_val<>Null);
  }
  
	/**
	* Below function is to double click on the element identified using CSS
	* @Given /^I click on the element with css "([^"]*)"$/
	*/
  public function iDoubleclickOnTheElementWithCss($arg1) {

    $this->getSession()->getPage()->find('css',$arg1)->click();
  }

	/**
	* Wait for a element till timeout completes
	*
	*  @Then /^(?:|I )wait for "(?P<element>[^"]*)" element$/
	*/
  public function iWaitForSecondsForFieldToBeVisible($seconds,$element) {
    
	  $this->iWaitSecondsForElement( $this->timeoutDuration, $element);
	}
	
	 /**
     * Wait for a element
     *
     * @Then /^(?:|I )wait "(?P<seconds>\d+)" seconds? for "(?P<element>[^"]*)" element$/
     */
    public function iWaitSecondsForElement($seconds, $element)
    {
	 
     $startTime = time();
	 //check if xpath is passed in argument or only name,value,id is passed. another set of attributes can be added.
	 if ( strpos($element,"//") === 0 ){
	 	 $element = $element;
	 }
	 else{
		$element = $element ." | //descendant-or-self::*[not(@type = 'hidden')]/descendant::*[@value = '".$element."'] | 			descendant-or-self::*[@class = '".$element."'] | descendant-or-self::*[@name = '".$element."'] | descendant-or-self::*[@id = '".$element."'] | descendant-or-self::*[@text = '".$element."'] | descendant-or-self::a[text()='".$element."']";
	}
	
	//loop to check element's existance.
    do {
            $now = time();
            $e = null;

            try {
                $node = $this->getSession()->getPage()->findAll('xpath', $element);
                $this->assertCount(1, $node);
            }
            catch (ExpectationException $e) {
                if ($now - $startTime >= $seconds) {
                    $message = sprintf('The element "%s" was not found after a %s seconds timeout', $element, $seconds);
                    throw new ResponseTextException($message, $this->getSession(), $e);
                }
            }
            if ($e == null) {
                break;
            }
        } while ($now - $startTime < $seconds);
    }

	/**
	*	Checks expected no of elements in an array.
	*/
	 protected function assertCount($expected, array $elements, $message = null)
    {
        if (intval($expected) !== count($elements)) {
            if (is_null($message)) {
                $message = sprintf(
                    '%d elements found, but should be %d.',
                    count($elements),
                    $expected
                );
            }
              throw new ExpectationException($message, $this->getSession());
        }
    }


  /**
   * @Given /^I save the order id from url$/
   */
  public function iSaveTheOrderIdFromUrl() {
    $url=$this->getSession()->getDriver()->getCurrentUrl();
    $url_file='./orderid.txt';
    $today = date("Y-m-d_H.i.s");
    $flag=0;
    try {
    $url_arr=explode('/',$url);
      foreach($url_arr as &$value){
        if(preg_match('/^\d+/',$value)){
          $fp=fopen($url_file,"a+");
          fwrite($fp,$today."--orderid--".$value.PHP_EOL);
          fclose($fp);
          $flag=1;
          }
      }
      if($flag!=1){
        throw new Exception("Order id not found in the URL");
        }   
    }
    catch(Exception $e){
      throw new Exception("Exception in getting order id from url".$e);
    }
  }
	
}
