<?php
/**
 *   (C) Copyright 1997-2013 hSenid International (pvt) Limited.
 *   All Rights Reserved.
 *
 *   These materials are unpublished, proprietary, confidential source code of
 *   hSenid International (pvt) Limited and constitute a TRADE SECRET of hSenid
 *   International (pvt) Limited.
 *
 *   hSenid International (pvt) Limited retains all title to and intellectual
 *   property rights in these materials.
 */

include_once 'MoUssdReceiver.php';
include_once 'MtUssdSender.php';
include_once 'log.php';
ini_set('error_log', 'ussd-app-error.txt');

@ob_start();

$receiver = new MoUssdReceiver(); // Create the Receiver object
$receiverSessionId = $receiver->getSessionId();
session_id($receiverSessionId); //Use received session id to create a unique session
session_start();

$content = $receiver->getMessage(); // get the message content
$address = $receiver->getAddress(); // get the sender's address
$requestId = $receiver->getRequestID(); // get the request ID
$applicationId = $receiver->getApplicationId(); // get application ID
$encoding = $receiver->getEncoding(); // get the encoding value
$version = $receiver->getVersion(); // get the version
$sessionId = $receiver->getSessionId(); // get the session ID;
$ussdOperation = $receiver->getUssdOperation(); // get the ussd operation

logFile("[ content=$content, address=$address, requestId=$requestId, applicationId=$applicationId, encoding=$encoding, version=$version, sessionId=$sessionId, ussdOperation=$ussdOperation ]");

//your logic goes here......
$responseMsg = array(
    "main" => "SMARTCTG
			This Month Revenue Shared Date Is 12/11/2016
	000.Exit",
    "solution" => "Solutions
	1.Hosting
	2.SMS Ads
	3.Voice Ads
	4.VAS
	999.Back",
    "marketing" => "Marketing
	1.Web Marketing
	2.Field Marketing
	999.Back",
    "support" => "SmartCTG Support
	1.Marketing Support
	2.Technical Support
	999.Back",
    "hosting" => "WebHosting
	Web: www.smartctg.com
	Tel: 01820-029522
	Mail: admin@smartctg.com
	Since: 2015 
	999.Back",
    "sms-ads" => "SMS Ads
	Web: www.smartctg.com
	Tel: 01820-029522
	Mail: admin@smartctg.com
	999.Back",
    "voice-ads" => "Voice Ads
	Web: www.smartctg.com
	Tel: 01820-029522
	Mail: admin@smartctg.com
	999.Back",
    "vas" => "Value Added Service
	Web: www.smartctg.com
	Tel: 01820-029522
	Mail: admin@smartctg.com
	999.Back",
    "web" => "Web marketing correctly unavailable.  
	999.Back",
    "field" => "Field Marketing
	Contact SmartCTG Fielders
	Call: 01819-655484
	999.Back",
    "m-sp" => "Marketing
	Md.Arifur Islam
	Phone:01819-655484
	Email:
	999.Back",
    "technical-support" => "Technical Support
	Md Abu Bokor Siddick
	Phone:01820-29522
	Email: support@smartctg.com
	999.Back");

logFile("Previous Menu is := " . $_SESSION['menu-Opt']); //Get previous menu number
if (($receiver->getUssdOperation()) == "mo-init") { //Send the main menu
    loadUssdSender($sessionId, $responseMsg["main"],$address);
    if (!(isset($_SESSION['menu-Opt']))) {
        $_SESSION['menu-Opt'] = "main"; //Initialize main menu
    }

}
if (($receiver->getUssdOperation()) == "mo-cont") {
    $menuName = null;

    switch ($_SESSION['menu-Opt']) {
        case "main":
            switch ($receiver->getMessage()) {
                case "1":
                    $menuName = "solution";
                    break;
                case "2":
                    $menuName = "marketing";
                    break;
                case "3":
                    $menuName = "support";
                    break;
                default:
                    $menuName = "main";
                    break;
            }
            $_SESSION['menu-Opt'] = $menuName; //Assign session menu name
            break;
        case "ngo":
            $_SESSION['menu-Opt'] = "ngo-list"; //Set to ngo menu back
            switch ($receiver->getMessage()) {
                case "1":
                    $menuName = "hosting";
                    break;
                case "2":
                    $menuName = "sms-ads";
                    break;
                case "3":
                    $menuName = "voice-ads";
                    break;
                case "4":
                    $menuName = "vas";
                    break;
                case "999":
                    $menuName = "main";
                    $_SESSION['menu-Opt'] = "main";
                    break;
                default:
                    $menuName = "main";
                    break;
            }
            break;
        case "NgoMettingSchedule":
            $_SESSION['menu-Opt'] = "NgoMettingSchedule-list"; //Set to product menu back
            switch ($receiver->getMessage()) {
                case "1":
                    $menuName = "web";
                    break;
                case "2":
                    $menuName = "field";
                    break;
                case "999":
                    $menuName = "main";
                    $_SESSION['menu-Opt'] = "main";
                    break;
                default:
                    $menuName = "main";
                    break;
            }
            break;
        case "support":
            $_SESSION['menu-Opt'] = "support-list"; //Set to career menu back
            switch ($receiver->getMessage()) {
                case "1":
                    $menuName = "m-sp";
                    break;
                case "2":
                    $menuName = "technical-support";
                    break;
                case "999":
                    $menuName = "main";
                    $_SESSION['menu-Opt'] = "main";
                    break;
                default:
                    $menuName = "main";
                    break;
            }
            break;
        case "ngo-list" || "NgoMettingSchedule-list" || "support-list":
            switch ($_SESSION['menu-Opt']) { //Execute menu back sessions
                case "ngo-list":
                    $menuName = "ngo";
                    break;
                case "NgoMettingSchedule-list":
                    $menuName = "NgoMettingSchedule";
                    break;
                case "support-list":
                    $menuName = "support";
                    break;
            }
            $_SESSION['menu-Opt'] = $menuName; //Assign previous session menu name
            break;
    }

    if ($receiver->getMessage() == "000") {
        $responseExitMsg = "Exit Program!";
        $response = loadUssdSender($sessionId, $responseExitMsg,$address);
        session_destroy();
    } else {
        logFile("Selected response message := " . $responseMsg[$menuName]);
        $response = loadUssdSender($sessionId, $responseMsg[$menuName], $address);
    }

}
/*
    Get the session id and Response message as parameter
    Create sender object and send ussd with appropriate parameters
**/

function loadUssdSender($sessionId, $responseMessage,$address)
{
    $password = "bdd5a8b2d0496eba1dd4402607c7674e";
    $destinationAddress = $address;
    if ($responseMessage == "000") {
        $ussdOperation = "mt-fin";
    } else {
        $ussdOperation = "mt-cont";
    }
    $chargingAmount = "1.00";
    $applicationId = "APP_002720";
    $encoding = "440";
    $version = "1.0";

    try {
        // Create the sender object server url
        $sender = new MtUssdSender("https://developer.bdapps.com/ussd/send"); // Application ussd-mt sending https url

        $response = $sender->ussd($applicationId, $password, $version, $responseMessage,
            $sessionId, $ussdOperation, $destinationAddress, $encoding, $chargingAmount);
        return $response;
    } catch (UssdException $ex) {
        //throws when failed sending or receiving the ussd
        error_log("USSD ERROR: {$ex->getStatusCode()} | {$ex->getStatusMessage()}");
        return null;
    }
}

?>
