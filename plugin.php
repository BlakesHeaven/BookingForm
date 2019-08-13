<?php
/**
 *  BookingForm
 *
 *  @package Bludit
 *  @subpackage Plugins
 *  @author novafacile OÜ
 *  @version 1.0.1
 *  @release 2018-11-25
 *  @info plugin based on contact plugin by Frédéric K (http://flatboard.free.fr)
 *
 */
class pluginBookingForm extends Plugin {
  private $eventName = ''; 
  private $senderName = '';
  private $senderEmail = '';
  private $validateEmail = '';
  private $senderPhone = '';
  private $numberOfTickets = 0; 
  private $paymantConfirmed = 'No';
  private $otherPartyNames = '';
  private $pickupPoint = '';  
  private $sittingNear = '';
  private $specialNeeds = '';
  private $success = false;
  private $error = false;
  private $reCaptchaResult = false;

  // install plugin
  public function init() {
    $this->dbFields = array(
      'email' => '',    // <= Your contact email
      'page'  => '',    // <= Slug url of contact page
      'type'  => 'text',  // <= True = HTML or False for text mail format
      'subject' => '', // subject for email (optional)
      'smtphost' => '',
      'smtpport' => '',
      'username' => '',
      'password' => '',
      'google-recaptcha' => '',
      'recaptcha-site-key' => '',
      'recaptcha-secret-key' => '',
      'sendEmailFrom' => 'fromUser',
      'domainAddress' => '',
      'gdpr-checkbox' => '',
      'gdpr-checkbox-text' => ''
    );
  }

  // config form
  public function form() {
    global $L, $staticPages;

    // create pageOptions;
    $pageOptions = array();

    // get all content as page
    foreach ($staticPages as $page) {
      $pageOptions[$page->key()] = $page->title();
    }
    // sort by name
    ksort($pageOptions);

    $html = '';

    // TO: email address
    $html .= '<div>';
    $html .= '<label>'.$L->get('Your Email').'</label>';
    $html .= '<input id="jsemail" name="email" type="text" class="form-control" value="'.$this->getValue('email').'">';
    $html .= '<span class="tip">'.$L->get('your-email-tip').'</span>';
    $html .= '</div>'.PHP_EOL;

    // Send from which address
    $html .= '<div>';
    $html .= '<label>'.$L->get('send-from-which-address').'</label>';
    $html .= '<select name="sendEmailFrom">'.PHP_EOL;
    $html .= '<option value="fromUser" '	.($this->getValue('sendEmailFrom')==='fromUser'?'selected':'').'>'	.$L->get('send-from-user')	.'</option>'.PHP_EOL;
    $html .= '<option value="fromTo" '		.($this->getValue('sendEmailFrom')==='fromTo'?'selected':'').'>'	.$L->get('send-from-to')	.'</option>'.PHP_EOL;
    $html .= '<option value="fromDomain" '	.($this->getValue('sendEmailFrom')==='fromDomain'?'selected':'').'>'.$L->get('send-from-domain').'</option>'.PHP_EOL;
    $html .= '</select>';
    $html .= '<span class="tip">'.$L->get('send-from-which-address-tip').'</span>';
    $html .= '</div>'.PHP_EOL;

      // FROM domain email address
    $html .= '<div>';
    $html .= '<label>'.$L->get('Domain Email').'</label>';
    $html .= '<input id="jsdomainFromAddress" name="domainAddress" type="text" class="form-control" value="'	.$this->getValue('domainAddress').'">';
    $html .= '<span class="tip">'.$L->get('domain-email-tip').'</span>';
    $html .= '</div>'.PHP_EOL;

    // select static page
    $html .= '<div>';
    $html .= '<label>'.$L->get('Select a content').'</label>';
    $html .= '<select name="page">'.PHP_EOL;
    $html .= '<option value="">- '.$L->get('static-pages').' -</option>'.PHP_EOL;
    foreach ($pageOptions as $key => $value) {
      $html .= '<option value="'.$key.'" '.($this->getValue('page')==$key?'selected':'').'>'.$value.'</option>'.PHP_EOL;
    }
    $html .= '</select>';
    $html .= '<span class="tip">'.$L->get('the-list-is-based-only-on-published-content').'</span>';
    $html .= '</div>'.PHP_EOL;

    // select email type
    $html .= '<div>';
    $html .= '<label>'.$L->get('Content Type').'</label>';
    $html .= '<select name="type">'.PHP_EOL;
    $html .= '<option value="text" '.($this->getValue('type')=='text'?'selected':'').'>'.$L->get('text').'</option>'.PHP_EOL;
    $html .= '<option value="html" '.($this->getValue('type')=='html'?'selected':'').'>'.$L->get('html').'</option>'.PHP_EOL;
    $html .= '</select>';
    $html .= '</div>'.PHP_EOL;

    // email Subject
    $html .= '<div>';
    $html .= '<label>'.$L->get('Email Subject').'</label>';
    $html .= '<input name="subject" type="text" class="form-control" value="'.$this->getValue('subject').'">';
    $html .= '</div>'.PHP_EOL;

    $html .= '<br><br>';

    /**
     * SMTP Settings
     * Contribution by Dominik Sust
     * Git: https://github.com/HarleyDavidson86/bludit-plugins/commit/eb395c73ea4800a00f4ec5e9c9baabc5b9db19e8 
    **/
    $html .= '<h4>SMTP</h4>';
    $html .= $L->get('smtp-options');

    // SMTP Host
    $html .= '<div>';
    $html .= '<label>'.$L->get('SMTP Host').'</label>';
    $html .= '<input name="smtphost" type="text" class="form-control" value="'.$this->getValue('smtphost').'">';
    $html .= '</div>'.PHP_EOL;

    // SMTP Port
    $html .= '<div>';
    $html .= '<label>'.$L->get('SMTP Port').'</label>';
    $html .= '<input name="smtpport" type="text" class="form-control" value="'.$this->getValue('smtpport').'">';
    $html .= '</div>'.PHP_EOL;

    // SMTP Username
    $html .= '<div>';
    $html .= '<label>'.$L->get('SMTP Username').'</label>';
    $html .= '<input name="username" type="text" class="form-control" value="'.$this->getValue('username').'">';
    $html .= '</div>'.PHP_EOL;

    // SMTP Password
    $html .= '<div>';
    $html .= '<label>'.$L->get('SMTP Password').'</label>';
    $html .= '<input name="password" type="password" class="form-control" value="'.$this->getValue('password').'">';
    $html .= '</div>'.PHP_EOL;
    
    $html .= '<br><br>';

    // GDPR
    $html .= '<h4>'.$L->get('GDPR').'</h4>';
    $html .= $L->get('gdpr-tip');

    // Activate GDPR Checkbox
    $html .= '<div>';
    $html .= '<label>'.$L->get('GDPR Checkbox').'</label>';
    $html .= '<select name="gdpr-checkbox">'.PHP_EOL;
    $html .= '<option value="false" '.($this->getValue('gdpr-checkbox')==false?'selected':'').'>'.$L->get('deactivate').'</option>'.PHP_EOL;
    $html .= '<option value="true" '.($this->getValue('gdpr-checkbox')==true?'selected':'').'>'.$L->get('activate').'</option>'.PHP_EOL;
    $html .= '</select>';
    $html .= '</div>'.PHP_EOL;

    // GDPR Chechbox Text
    $html .= '<div>';
    $html .= '<label>'.$L->get('GDPR Checkbox Legal Text').'</label>';
    $html .= '<input name="gdpr-checkbox-text" type="text" class="form-control" value="'.$this->getValue('gdpr-checkbox-text').'">';
    $html .= '<span class="tip">'.$L->get('gdpr-checkbox-text-tip').'</span>';
    $html .= '</div>'.PHP_EOL;

    $html .= '<br><br>';

    // Google reCaptcha v2
    $html .= '<h4>Spam Protection</h4>';
    $html .= $L->get('anti-spam-info');

    // activate reCaptcha
    $html .= '<div>';
    $html .= '<label>'.$L->get('Google reCAPTCHA v2').'</label>';
    $html .= '<select name="google-recaptcha">'.PHP_EOL;
    $html .= '<option value="false" '.($this->getValue('google-recaptcha')==false?'selected':'').'>'.$L->get('deactivate').'</option>'.PHP_EOL;
    $html .= '<option value="true" '.($this->getValue('google-recaptcha')==true?'selected':'').'>'.$L->get('activate').'</option>'.PHP_EOL;
    $html .= '</select>';
    $html .= '</div>'.PHP_EOL;

    // website key
    $html .= '<div>';
    $html .= '<label>'.$L->get('reCaptcha Website Key').'</label>';
    $html .= '<input name="recaptcha-site-key" type="text" class="form-control" value="'.$this->getValue('recaptcha-site-key').'" autocomplete="off">';
    $html .= '</div>'.PHP_EOL;

    // secret key
    $html .= '<div>';
    $html .= '<label>'.$L->get('reCaptcha Secret Key').'</label>';
    $html .= '<input name="recaptcha-secret-key" type="text" class="form-control" value="'.$this->getValue('recaptcha-secret-key').'" autocomplete="off">';
    $html .= '</div>'.PHP_EOL;

    $html .= '<br><br>';

    // output
    $html .= '<br><br>';
    return $html;

  }

  // Load CSS for contact form
  public function siteHead() {
    $webhook = $this->getValue('page');
    if($this->webhook($webhook)) {
      $html = '';
      $css = THEME_DIR_CSS . 'contact3.css';
      if(file_exists($css)) {
        $html .= Theme::css('css' . DS . 'contact3.css');
      } else {
        $html .= '<link rel="stylesheet" href="' .$this->htmlPath(). 'layout' . DS . 'contact3.css">' .PHP_EOL;
      }

      if($this->getValue('google-recaptcha')){
        $html .= '<script src="https://www.google.com/recaptcha/api.js"></script>';
      }

      return $html;
    }
  } 


  // Load contact form and send email
  public function pageEnd(){
    $webhook = $this->getValue('page');
    if($this->webhook($webhook)) {
      
      // send email if submit 
      if(isset($_POST['submit'])) {

        $this->reCaptchaResult = $this->googleRecaptchaValidation();

        // get post paramaters
        $this->readPost();
        $this->error = $this->validatePost();

        // check if it's a bot
        if($this->isBot()) {
          $this->error = true;
          // fake success for bot
          $this->success = true;
        }

        // if no error until now, then create and send email
        if(!$this->error){
          if(empty($this->getValue('smtphost'))) {
            $this->success = $this->useSendmail();		// $SentReceipt = false, ie to club
			$this->success = $this->useSendmail(true);	// $SentReceipt = True, ie to sender
          } else{
            $this->success = $this->useSmtp();
          }

          if($this->success){
            $this->clearForm();
          }
        }
        // show frontend message
        //echo $this->frontendMessage(); 
      }

      // include contact form
      $this->includeContactForm();
    }
  }

  public function googleRecaptchaForm(){
    if($this->getValue('google-recaptcha')){
      return $html = '<div class="g-recaptcha" data-sitekey="'.$this->getValue('recaptcha-site-key').'"></div>';
    } else {
      return;
    }
  }

/****
 * private functions
 *****/

  private function isBot(){
    $bot = false;
    
    // check interested checkbox (simple honey pot)
    if(isset($_POST['interested'])) {
      $bot = true;
    }
    // return bot status
    return $bot;
  }

  private function isHtml(){
    if($this->getValue('type') === 'html') {
      return true;
    } else {
      return false;
    }
  }

  private function readPost(){
    // removes bad content - just a little protection - could be better
    if(isset($_POST['eventName'])) { 
      $this->eventName =  trim(strip_tags($_POST['eventName']));
    }
    if(isset($_POST['senderName'])) { 
      $this->senderName =  trim(strip_tags($_POST['senderName']));
    }
    if(isset($_POST['senderEmail'])) {
      $this->senderEmail =  trim(preg_replace("/[^0-9a-zA-ZäöüÄÖÜÈèÉéÂâáÁàÀíÍìÌâÂ@ \-\+\_\.]/", " ", $_POST['senderEmail']));
    }
    if(isset($_POST['validateEmail'])){
      $this->validateEmail = trim(strip_tags($_POST['validateEmail']));
    }
    if(isset($_POST['senderPhone'])){
      $this->senderPhone = trim(strip_tags($_POST['senderPhone']));
	}
    if(isset($_POST['numberOfTickets'])){
      $this->numberOfTickets = trim(strip_tags($_POST['numberOfTickets']));
    }
    if(isset($_POST['paymantConfirmed'])){
      $this->paymantConfirmed = $_POST['paymantConfirmed'];
    }
    if(isset($_POST['otherPartyNames'])){
      $this->otherPartyNames = trim(strip_tags($_POST['otherPartyNames']));
    }
    if(isset($_POST['pickupPoint'])){
      $this->pickupPoint = trim(strip_tags($_POST['pickupPoint']));
	} 
    if(isset($_POST['sittingNear'])){
      $this->sittingNear = trim(strip_tags($_POST['sittingNear']));
    }
    if(isset($_POST['specialNeeds'])){
      $this->specialNeeds = trim(strip_tags($_POST['specialNeeds']));
    }
  }

  private function validatePost(){
    global $L;
    if(trim($this->eventName)==='')
		$error = $L->get('please-select-the-event').'<br>'; 
    elseif(trim($this->senderName)==='')
		$error = $L->get('please-enter-your-name').'<br>';                            
    elseif(trim($this->senderEmail)==='')
		$error = $L->get('please-enter-a-valid-email-address').'<br>';
    elseif(trim($this->validateEmail)<>trim($this->senderEmail) )
      $error = $L->get('email-entered-not-same');
    elseif(trim($this->senderPhone)==='')
		$error = $L->get('please-enter-phone-number').'<br>';
    elseif(trim($this->numberOfTickets) < 1)
		$error = $L->get('tickets-greater-than-zero').'<br>';
    elseif(trim($this->paymantConfirmed) ==='No')
		$error = $L->get('confirm-payment').'<br>';
    elseif(trim($this->otherPartyNames) ==='' and trim($this->numberOfTickets) > 1)
		$error = $L->get('other-party-names-needed').'<br>';
    elseif ($this->getValue('gdpr-checkbox') && !$_POST['gdpr-checkbox']) {
		$error = $L->get('Please accept the privacy policy').'<br>';
    }
    elseif(!$this->reCaptchaResult){
      $error = $L->get('please-check-that-you-are-not-a-robot');
    }
    else
      $error = false;
    return $error;
  }

  private function getSubject(){
    global $site, $L;
    $subject = $this->getValue('subject');
    if(empty($subject)){
      $subject = $L->get('New contact from'). ' - ' .$site->title();
    }
    return $subject;
  }

  private function getEmailText(){
    global $L;
    if($this->isHtml()) {
		$emailText  = '<b>'.$L->get('booking-event')	.': </b>'.$this->eventName			.'<br>';
		$emailText .= '<b>'.$L->get('booking-name')		.': </b>'.$this->senderName			.'<br>';
		$emailText .= '<b>'.$L->get('booking-email')	.': </b>'.$this->senderEmail		.'<br>';
		$emailText .= '<b>'.$L->get('booking-chk-email').': </b>'.$this->validateEmail		.'<br>';			
		$emailText .= '<b>'.$L->get('booking-phone')	.': </b>'.$this->senderPhone		.'<br>';
		$emailText .= '<b>'.$L->get('booking-tickets')	.': </b>'.$this->numberOfTickets	.'<br>';
		$emailText .= '<b>'.$L->get('booking-pickup')	.': </b>'.$this->pickupPoint    	.'<br>';
		$emailText .= '<b>'.$L->get('booking-payment')	.': </b>'.$this->paymantConfirmed	.'<br>';
		$emailText .= '<b>'.$L->get('booking-others')	.': </b>'.$this->otherPartyNames	.'<br>';
		$emailText .= '<b>'.$L->get('booking-sit-near')	.': </b>'.$this->sittingNear		.'<br>';
		$emailText .= '<b>'.$L->get('booking-needs')	.': </b>'.nl2br($this->specialNeeds).'<br>';
  
      if($this->getValue('gdpr-checkbox')){
        $emailText .= sanitize::htmlDecode($this->getValue('gdpr-checkbox-text')).'<br>';
      }
    } 
	else {
		$emailText  = $L->get('booking-event')			.': '.$this->eventName			."\r\n\r";
		$emailText .= $L->get('booking-name')			.': '.$this->senderName			."\r\n\r";
		$emailText .= $L->get('booking-email')			.': '.$this->senderEmail		."\r\n\r";
		$emailText .= $L->get('booking-chk-email')		.': '.$this->validateEmail		."\r\n\r";		
		$emailText .= $L->get('booking-phone')			.': '.$this->senderPhone		."\r\n\r";
		$emailText .= $L->get('booking-tickets')		.': '.$this->numberOfTickets	."\r\n\r";
		$emailText .= $L->get('booking-pickup')			.': '.$this->pickupPoint		."\r\n\r";
		$emailText .= $L->get('booking-payment')		.': '.$this->paymantConfirmed	."\r\n\r";
		$emailText .= $L->get('booking-others')			.': '.$this->otherPartyNames	."\r\n\r";
		$emailText .= $L->get('booking-sit-near')		.': '.$this->sittingNear		."\r\n\r";
		$emailText .= $L->get('booking-needs')			.': '.$this->specialNeeds		."\r\n\r";

		if($this->getValue('gdpr-checkbox')){
		$emailText .= strip_tags(sanitize::htmlDecode($this->getValue('gdpr-checkbox-text')))."\r\n\r";
		}
    }
    return $emailText;
  }


  private function frontendMessage(){
    global $L;
    if($this->success) {
      $html = '<div class="alert alert-success">' .$L->get('booking-confirmation'). '</div>' ."\r\n";
    } elseif(!is_bool($this->error)) {
      $html = '<div class="alert alert-danger">'. $this->error. '</div>' ."\r\n";
    } elseif($this->error) {
      $html = '<div class="alert alert-danger">' .$L->get('an-error-occurred-while-sending').'</div>' ."\r\n";
    } else {
      $html = '';
    }
    return $html;
  }


	private function useSendmail($SentReceipt = false){
		global $L;
		$success = false;
		$sendFrom = $this->getValue('sendEmailFrom');
		$senderName = $this->senderName;
		$subject = $this->getSubject();

		// email headers

				 IF ($SentReceipt) {
					switch ($sendFrom)
						{
						case "fromTo":
							$email_headers	= "From: $subject <"		. $this->getValue('email')			.">".PHP_EOL;
							$email_headers .= "Reply-To: $subject <"	. $this->getValue('email')			.">".PHP_EOL;
							break;
						case "fromDomain":
							$email_headers	= "From: $subject <"		. $this->getValue('domainAddress')	.">".PHP_EOL;
							$email_headers .= "Reply-To: $subject <"	. $this->getValue('email')			.">".PHP_EOL;
							break;		
						default: // fromUser
							$email_headers	= "From: $subject <"		. $this->getValue('email')			.">".PHP_EOL;
						}
				}
				ELSE {
					switch ($sendFrom)
						{
						case "fromTo":
							$email_headers	= "From: $senderName <"		. $this->getValue('email')			.">".PHP_EOL;
							$email_headers .= "Reply-To: $senderName <"	. $this->senderEmail				.">".PHP_EOL;
							break;
						case "fromDomain":
							$email_headers	= "From: $senderName <"		. $this->getValue('domainAddress')	.">".PHP_EOL;
							$email_headers .= "Reply-To: $senderName <"	. $this->senderEmail				.">".PHP_EOL;
							break;		
						default: // fromUser
							$email_headers	= "From: $senderName <"		. $this->senderEmail				.">".PHP_EOL;					
					}
				}

		$email_headers .= 'MIME-Version: 1.0' ."\r\n";

		if($this->isHtml()){
		  $email_headers .= 'Content-type: text/html; charset="' .CHARSET. '"' ."\r\n";
		} else {
		  $email_headers .= 'Content-type: text/plain; charset="' .CHARSET. '"' ."\r\n";
		}

		$email_headers .= 'Content-transfer-encoding: 8bit' ."\r\n";
		$email_headers .= 'Date: ' .date("D, j M Y G:i:s O")."\r\n"; // Sat, 7 Jun 2001 12:35:58 -0700

		// send email via sendmail => mail(to,subject,message,headers,parameters);
		IF ($SentReceipt) {
			$success = mail($senderName."<". $this->senderEmail.">", 								// Sent receipt back to user
							$this->getSubject().' ('.$this->eventName.')', 							// Same Subject
							$L->get('booking-confirmation').PHP_EOL.PHP_EOL.$this->getEmailText(),	// Same message with prefix, thanks you for booking
							$email_headers															// Same From: ReplyTo
							);
		}
		else {
			$success = mail($this->getValue('email'), 
							$this->getSubject().' ('.$this->eventName.')', 
							$this->getEmailText(), 
							$email_headers
							);
		}

		if(!$success){

			$errorMessage = error_get_last()['message'];

			if (isset($errorMessage)) {
				$this->error = $errorMessage;		
			}
			else {
					$this->error = true;
			}		
		}
		return $success;
	}

	private function useSmtp()
	{
	$success = false;
	$sendFrom = $this->getValue('sendEmailFrom');

	// load PHPMailer
	require __DIR__ . DS . 'phpmailer' . DS . 'PHPMailerAutoload.php';

	try {
		$mail = new PHPMailer;

		$mail->isSMTP();
		$mail->Host = $this->getValue('smtphost');
		$mail->Port = $this->getValue('smtpport');
		$mail->SMTPAuth = true;
		$mail->Username = $this->getValue('username');
		#Function is needed if Password contains special characters like &
		$mail->Password = html_entity_decode($this->getValue('password'));
		
		$mail->CharSet = CHARSET;
		$mail->isHTML($this->isHTML());

		switch ($sendFrom) // Set email FROM address
			{
				case "fromTo": 
					$mail->setFrom($this->getValue('email'));
					$mail->addReplyTo($this->senderEmail, $this->senderName);
					break;
				case "fromDomain":
					$mail->setFrom($this->getValue('domainAddress'));
					$mail->addReplyTo($this->senderEmail, $this->senderName);
					break;		
				default: // fromUser
					$mail->setFrom($this->senderEmail, $this->senderName); 
			}

		  $mail->addAddress($this->getValue('email'));
		  $mail->Subject = $this->getSubject();
		  $mail->Body = $this->getEmailText();

		if($mail->send()) {
			$success = true;
		} 
		else {
			$errorMessage = error_get_last()['message'];

			if (isset($errorMessage)) {
				$this->error = $errorMessage;		
			}
			else {
					$this->error = true;
			}	
		}
	}
	catch (phpmailerException $e) {
			echo $e->errorMessage();	//Pretty error messages from PHPMailer
	}
		catch (Exception $e) {
			echo $e->getMessage();		//Boring error messages from anything else!
		}
	return $success;
	}


  private function clearForm(){
	$this->eventName		= '';  
    $this->senderName		= '';
    $this->senderEmail		= '';
    $this->validateEmail	= '';
    $this->senderPhone		= '';
	$this->numberOfTickets	= 0;
	$this->pickupPoint		= '';
	$this->paymantConfirmed	= 'No';
	$this->otherPartyNames	= '';
	$this->sittingNear		= '';
	$this->specialNeeds		= '';
  }

  private function includeContactForm(){
    global $page, $security, $L;
    $template = THEME_DIR_PHP . 'contact3.php';
    if(file_exists($template)) {
      include($template);
    } else {
      include(__DIR__ . DS . 'layout' . DS . 'contact3.php');
    }   
  }

  private function googleRecaptchaValidation(){
    if($this->getValue('google-recaptcha')){
      $secretKey = $this->getValue('recaptcha-secret-key');
      $json = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secretKey.'&response='.$_POST['g-recaptcha-response']);
      $data = json_decode($json);
      return $data->success;
    } else {
      return true;
    }
  }


}
