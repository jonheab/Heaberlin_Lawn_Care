<?php
// require ReCaptcha class
require('recaptcha-master/src/autoload.php');

// configure
$from = 'Heaberlin Lawn Care <servicerequest@heaberlinlawncare.com>';
$sendTo = 'Request Account <jrheab@gmail.com>';
$subject = 'Service Request';
$fields = array('name' => 'Firstname', 'surname' => 'Lastname', 'phone' => 'Phone', 'email' => 'Email', 'address' => 'Address', 'city' => 'City', 'state' => 'State', 'zip' => 'Zipcode', 'springpack' => 'Spring Package', 'yearpack' => '1-Year Package', 'sprinkler' => 'Sprinkler Turn On'); // array variable name => Text to appear in the email
$okMessage = 'Service request successfully submitted. You should recive a conformation email shortly. Thank you!';
$errorMessage = 'There was an error while submitting your request. Please try again or reattempt the ReCapcha.';
$recaptchaSecret = '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe';
// let's do the sending

try
{
    if (!empty($_POST)) {

        // validate the ReCaptcha, if something is wrong, we throw an Exception, 
        // i.e. code stops executing and goes to catch() block
        
        if (!isset($_POST['g-recaptcha-response'])) {
            throw new \Exception('ReCaptcha is not set.');
        }

        // do not forget to enter your secret key in the config above 
        // from https://www.google.com/recaptcha/admin
        
        $recaptcha = new \ReCaptcha\ReCaptcha($recaptchaSecret, new \ReCaptcha\RequestMethod\CurlPost());
        
        // we validate the ReCaptcha field together with the user's IP address
        
        $response = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);


        if (!$response->isSuccess()) {
            throw new \Exception('ReCaptcha was not validated.');
        }
        
        // everything went well, we can compose the message, as usually
        
        $emailText = "Service Request\n---------------------------\n";

        foreach ($_POST as $key => $value) {

            if (isset($fields[$key])) {
                $emailText .= "$fields[$key]: $value\n";
            }
        }
        

        $headers = array('Content-Type: text/plain; charset="UTF-8";',
            'From: ' . $from,
            'Reply-To: ' . $from,
            'Return-Path: ' . $from,
        );

        mail($sendTo, $subject, $emailText, implode("\n", $headers));
        $msg = $_POST['name'] .  ",\n\nThis email is confirming your request for spring services. \n\nThank you,\n\nChris Heaberlin\nOwner\nHeaberlin Lawn Care, LLC \n\n\n\nIf you have any questions please call 970-988-8023 or email chris@heaberlinlawncare.com.\n\nThis is an automated email. Do not reply.";
        
        mail( $_POST['email'], $subject, $msg, 'From: ' . $from );

        $responseArray = array('type' => 'success', 'message' => $okMessage);
    }
}
catch (\Exception $e)
{
    $responseArray = array('type' => 'danger', 'message' => $errorMessage);
}

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $encoded = json_encode($responseArray);

    header('Content-Type: application/json');

    echo $encoded;
}
else {
    echo $responseArray['message'];
}
