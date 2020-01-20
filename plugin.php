<?php
/**
 *  BookingForm
 *
 *  @package Bludit
 *  @subpackage Plugins
 *  @author David Blake
 *  @version 1.0.1
 *  @release 2020-01-20
 *  @info plugin based on Contact3 plugin by novafacile OÜ
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
	private $loadOnController = array('BookingForm');


  public function init() {
    $this->dbFields = array(
		'email' 	=> '',
		'page'  	=> '',
		'type'  	=> 'text',
		'subject'	=> '',
		'smtphost'	=> '',
		'smtpport'	=> '',
		'username'	=> '',
		'password'	=> '',
		'google-recaptcha'		=> '',
		'recaptcha-site-key'	=> '',
		'recaptcha-secret-key	'=> '',
		'sendEmailFrom'			=> 'fromUser',
		'domainAddress'			=> '',
		'gdpr-checkbox'			=> '',
		'gdpr-checkbox-text'	=> '',
		'numberOfBookingLogsToDisplay' => 6
    );
  }

  // config form
  public function form() {

    global $site, $L, $staticPages;

	$fileUrl = $site->url();
	$numberOfBookingLogsToDisplay = $this->getValue('numberOfBookingLogsToDisplay');
	$count = 1;

    $html  = '';
	$html .= '<div class="alert alert-primary" role="alert">';
	$html .= $this->description();
	$html .= '</div>';

    // create pageOptions;
    $pageOptions = array();

    // get all content as page
    foreach ($staticPages as $page) {
      $pageOptions[$page->key()] = $page->title();
    }
    // sort by name
    ksort($pageOptions);

    // TO: email address
	$directory = $this->workspace();
	
	array_multisort(array_map('filemtime', (
		$csvFilesArray = glob($directory . "*.csv")
			)), SORT_DESC, $csvFilesArray);

    $html .= '<h4>'.$L->get('display-booking-logs-title').'</h4>';
    $html .= $L->get('display-booking-logs-desc');

	$html .= '<div>';
	$html .= '<label>'.$L->get('number-of-booking-logs-to-display-label').'</label>';
	$html .= '<input id="jsnumberOfBookingLogsToDisplay" name="numberOfBookingLogsToDisplay" type="number" value="'.$this->getValue('numberOfBookingLogsToDisplay').'">';
	$html .= '<span class="tip">'.$L->get('number-of-booking-logs-to-display-tip').'</span>';
	$html .= '</div>';

    $html .= '<div>';
    $html .= '<label>'.$L->get('results-file-label').'</label>';
	$html .= '<span class="tip">'.$L->get('results-file-tip').'</span>';

	// Display booking log entries in Plugin Settings page
	foreach($csvFilesArray as $csvFile) {

		if ($count++ > $numberOfBookingLogsToDisplay ) break;
		clearstatcache(true);		
		$html .= "<hr><label><b>".basename($csvFile)." | Last changed: ".date("d F Y H:i", filemtime("$csvFile"))."</b></label>";
		
		$row = 1;
		if (($handle = fopen("$csvFile", "r")) !== FALSE) {		

			$html .= '<table border="1"  width="100%">';
		   
			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				$num = count($data);
				if ($row == 1) {
					$html .= '<thead><tr>';
				}else{
					$html .= '<tr>';
				}
			   
				for ($column=1; $column < $num; $column++) { // $column=0 would be the first column, it is skipped here.

					if(empty($data[$column])) {
					   $value = "&nbsp;";
					}else{
					   $value = $data[$column];
					}
					if ($row == 1) {
						$html .= '<th>'.$value.'</th>';
					}else{
						$html .= '<td>'.$value.'</td>';
					}
				}
			   
				if ($row == 1) {
					$html .= '</tr></thead><tbody>';
				}else{
					$html .= '</tr>';
				}
				$row++;
			}
		   
			$html .= '</tbody></table>';
			fclose($handle);
		}
	}

    $html .= '</div>'.PHP_EOL;

    $html .= '<hr><h4>'.$L->get('booking-form-config-title').'</h4>';
    $html .= $L->get('booking-form-config-desc');
	
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
    $html .= '<label>'.$L->get('select-content-label').'</label>';
    $html .= '<select name="page">'.PHP_EOL;
    $html .= '<option value="">- '.$L->get('static-pages').' -</option>'.PHP_EOL;
    foreach ($pageOptions as $key => $value) {
      $html .= '<option value="'.$key.'" '.($this->getValue('page')==$key?'selected':'').'>'.$value.'</option>'.PHP_EOL;
    }
    $html .= '</select>';
    $html .= '<span class="tip">'.$L->get('select-content-tip').'</span>';
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
    $html .= '<label>'.$L->get('email-subject').'</label>';
    $html .= '<input name="subject" type="text" class="form-control" value="'.$this->getValue('subject').'">';
    $html .= '</div>'.PHP_EOL;

    $html .= '<br>';

    /**
     * SMTP Settings
     * Contribution by Dominik Sust
     * Git: https://github.com/HarleyDavidson86/bludit-plugins/commit/eb395c73ea4800a00f4ec5e9c9baabc5b9db19e8 
    **/
    $html .= '<hr><h4>SMTP</h4>';
    $html .= $L->get('smtp-options');

    // SMTP Host
    $html .= '<div>';
    $html .= '<label>'.$L->get('smtp-host').'</label>';
    $html .= '<input name="smtphost" type="text" class="form-control" value="'.$this->getValue('smtphost').'">';
    $html .= '</div>'.PHP_EOL;

    // SMTP Port
    $html .= '<div>';
    $html .= '<label>'.$L->get('smtp-port').'</label>';
    $html .= '<input name="smtpport" type="text" class="form-control" value="'.$this->getValue('smtpport').'">';
    $html .= '</div>'.PHP_EOL;

    // SMTP Username
    $html .= '<div>';
    $html .= '<label>'.$L->get('smtp-username').'</label>';
    $html .= '<input name="username" type="text" class="form-control" value="'.$this->getValue('username').'">';
    $html .= '</div>'.PHP_EOL;

    // SMTP Password
    $html .= '<div>';
    $html .= '<label>'.$L->get('smtp-password').'</label>';
    $html .= '<input name="password" type="password" class="form-control" value="'.$this->getValue('password').'">';
    $html .= '</div>'.PHP_EOL;
    
    $html .= '<br>';

    // GDPR
    $html .= '<hr><h4>'.$L->get('gdpr-header').'</h4>';
    $html .= $L->get('gdpr-header-tip');

    // Activate GDPR Checkbox
    $html .= '<div>';
    $html .= '<label>'.$L->get('gdpr-checkbox-label').'</label>';
    $html .= '<select name="gdpr-checkbox">'.PHP_EOL;
    $html .= '<option value="false" '.($this->getValue('gdpr-checkbox')==false?'selected':'').'>'.$L->get('deactivate').'</option>'.PHP_EOL;
    $html .= '<option value="true" '.($this->getValue('gdpr-checkbox')==true?'selected':'').'>'.$L->get('activate').'</option>'.PHP_EOL;
    $html .= '</select>';
    $html .= '</div>'.PHP_EOL;

    // GDPR Chechbox Text
    $html .= '<div>';
    $html .= '<label>'.$L->get('gdpr-checkbox-legal-text').'</label>';
    $html .= '<input name="gdpr-checkbox-text" type="text" class="form-control" value="'.$this->getValue('gdpr-checkbox-text').'">';
    $html .= '<span class="tip">'.$L->get('gdpr-checkbox-text-tip').'</span>';
    $html .= '</div>'.PHP_EOL;

    $html .= '<br>';

    // Google reCaptcha v2
    $html .= '<hr><h4>Spam Protection</h4>';
    $html .= $L->get('anti-spam-info');

    // activate reCaptcha
    $html .= '<div>';
    $html .= '<label>'.$L->get('activate-google-recaptcha-v2').'</label>';
    $html .= '<select name="google-recaptcha">'.PHP_EOL;
    $html .= '<option value="false" '.($this->getValue('google-recaptcha')==false?'selected':'').'>'.$L->get('deactivate').'</option>'.PHP_EOL;
    $html .= '<option value="true" '.($this->getValue('google-recaptcha')==true?'selected':'').'>'.$L->get('activate').'</option>'.PHP_EOL;
    $html .= '</select>';
    $html .= '</div>'.PHP_EOL;

    // website key
    $html .= '<div>';
    $html .= '<label>'.$L->get('recaptcha-website-key').'</label>';
    $html .= '<input name="recaptcha-site-key" type="text" class="form-control" value="'.$this->getValue('recaptcha-site-key').'" autocomplete="off">';
    $html .= '</div>'.PHP_EOL;

    // secret key
    $html .= '<div>';
    $html .= '<label>'.$L->get('recaptcha-secret-key').'</label>';
    $html .= '<input name="recaptcha-secret-key" type="text" class="form-control" value="'.$this->getValue('recaptcha-secret-key').'" autocomplete="off">';
    $html .= '</div>'.PHP_EOL;

    $html .= '<br>';

    // output
    $html .= '<br><br>';
    return $html;

  }

  // Load CSS for ADMIN form
	public function adminHead()
	{
		IF (!in_array($GLOBALS['ADMIN_CONTROLLER'], $this->loadOnController) ) {
			return false;
		}

		// Include plugin's CSS files
		$html = $this->includeCSS('style.css');

		return $html;
	}

  // Load CSS for contact form
  public function siteHead() {
    $webhook = $this->getValue('page');
    IF($this->webhook($webhook)) {
      $html = '';
      $css = THEME_DIR_CSS . 'contact3.css';
      IF(file_exists($css)) {
        $html .= Theme::css('css' . DS . 'contact3.css');
      } else {
        $html .= '<link rel="stylesheet" href="' .$this->htmlPath(). 'layout' . DS . 'contact3.css">' .PHP_EOL;
      }

      IF($this->getValue('google-recaptcha')){
        $html .= '<script src="https://www.google.com/recaptcha/api.js"></script>';
      }

      return $html;
    }
  } 


  // Load contact form and send email
  public function pageEnd(){
    $webhook = $this->getValue('page');
    IF($this->webhook($webhook)) {
      
      // send email IF submit 
      IF(isset($_POST['submit'])) {

        $this->reCaptchaResult = $this->googleRecaptchaValidation();

        // get post paramaters
        $this->readPost();
        $this->error = $this->validatePost();

        // check IF it's a bot
        IF($this->isBot()) {
          $this->error = true;
          // fake success for bot
          $this->success = true;
        }

        // IF no error until now, then create and send email
        IF(!$this->error){
          IF(empty($this->getValue('smtphost'))) {
			$this->success = $this->useSendmail(true);	// $SentReceipt = True, ie to sender			  
            $this->success = $this->useSendmail(false);	// $SentReceipt = false, ie to club
          } else{
            $this->success = $this->useSmtp();
          }

          IF($this->success){
			$this->addBooking();
            $this->clearForm();
          }
        }
      }

      $this->includeContactForm();
    }
  }

  public function googleRecaptchaForm(){
    IF($this->getValue('google-recaptcha')){
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
    IF(isset($_POST['interested'])) {
      $bot = true;
    }
    // return bot status
    return $bot;
  }

  private function isHtml(){
    IF($this->getValue('type') === 'html') {
      return true;
    } else {
      return false;
    }
  }

  private function readPost(){
    // removes bad content - just a little protection - could be better
    IF(isset($_POST['eventName'])) { 
      $this->eventName =  trim(strip_tags($_POST['eventName']));
    }
    IF(isset($_POST['senderName'])) { 
      $this->senderName =  trim(strip_tags($_POST['senderName']));
    }
    IF(isset($_POST['senderEmail'])) {
      $this->senderEmail =  trim(strip_tags(preg_replace("/[^0-9a-zA-ZäöüÄÖÜÈèÉéÂâáÁàÀíÍìÌâÂ@ \-\+\_\.]/", " ", $_POST['senderEmail'])));
    }
    IF(isset($_POST['validateEmail'])){
      $this->validateEmail = trim(strip_tags($_POST['validateEmail']));
    }
    IF(isset($_POST['senderPhone'])){
      $this->senderPhone = trim(strip_tags($_POST['senderPhone']));
	}
    IF(isset($_POST['numberOfTickets'])){
      $this->numberOfTickets = trim(strip_tags($_POST['numberOfTickets']));
    }
    IF(isset($_POST['paymantConfirmed'])){
      $this->paymantConfirmed = trim(strip_tags($_POST['paymantConfirmed']));
    }
    IF(isset($_POST['otherPartyNames'])){
      $this->otherPartyNames = trim(strip_tags($_POST['otherPartyNames']));
    }
    IF(isset($_POST['pickupPoint'])){
      $this->pickupPoint = trim(strip_tags($_POST['pickupPoint']));
	} 
    IF(isset($_POST['sittingNear'])){
      $this->sittingNear = trim(strip_tags($_POST['sittingNear']));
    }
    IF(isset($_POST['specialNeeds'])){
      $this->specialNeeds = trim(strip_tags($_POST['specialNeeds']));
    }
  }

	private function validatePost(){
		global $L;
		IF(trim($this->eventName)==='')
			$error = $L->get('select-the-event').'<br>'; 
		ELSEIF (trim($this->senderName)==='')
			$error = $L->get('enter-your-name').'<br>';                            
		ELSEIF (trim($this->senderEmail)==='')
			$error = $L->get('enter-email-address').'<br>';
		ELSEIF (trim($this->validateEmail)<>trim($this->senderEmail) )
			$error = $L->get('email-entered-not-same').'<br>';
		ELSEIF (trim($this->senderPhone)==='')
			$error = $L->get('enter-phone-number').'<br>';
		ELSEIF (trim($this->numberOfTickets) < 1)
			$error = $L->get('ticket-count-zero-warning').'<br>';
		ELSEIF (trim($this->paymantConfirmed) ==='No')
			$error = $L->get('confirm-payment').'<br>';
		ELSEIF (trim($this->otherPartyNames) ==='' and trim($this->numberOfTickets) > 1)
			$error = $L->get('other-party-names-needed').'<br>';
		ELSEIF ($this->getValue('gdpr-checkbox') && !$_POST['gdpr-checkbox']) {
			$error = $L->get('accept-privacy-policy').'<br>';
		}
		ELSEIF(!$this->reCaptchaResult){
		  $error = $L->get('robot-check-warning');
		}
		else
		  $error = false;
		return $error;
	}

	private function getSubject(){
		global $site, $L;
		$subject = $this->getValue('subject');
		IF(empty($subject)){
		  $subject = $L->get('New contact from'). ' - ' .$site->title();
		}
		return $subject;
	}

	private function getEmailText(){
		global $L;

		IF($this->isHtml()) {
			$emailText  = '<b>'.$L->get('booking-event')	.': </b>'.$this->eventName			.'<br>';
			$emailText .= '<b>'.$L->get('booking-name')		.': </b>'.$this->senderName			.'<br>';
			$emailText .= '<b>'.$L->get('booking-email')	.': </b>'.$this->senderEmail		.'<br>';
		//	$emailText .= '<b>'.$L->get('booking-chk-email').': </b>'.$this->validateEmail		.'<br>';// Not needed unless you want it in the emails		
			$emailText .= '<b>'.$L->get('booking-phone')	.': </b>'.$this->senderPhone		.'<br>';
			$emailText .= '<b>'.$L->get('booking-tickets')	.': </b>'.$this->numberOfTickets	.'<br>';
			$emailText .= '<b>'.$L->get('booking-pickup')	.': </b>'.$this->pickupPoint    	.'<br>';
			$emailText .= '<b>'.$L->get('booking-payment')	.': </b>'.$this->paymantConfirmed	.'<br>';
			$emailText .= '<b>'.$L->get('booking-others')	.': </b>'.$this->otherPartyNames	.'<br>';
			$emailText .= '<b>'.$L->get('booking-sit-near')	.': </b>'.$this->sittingNear		.'<br>';
			$emailText .= '<b>'.$L->get('booking-needs')	.': </b>'.nl2br($this->specialNeeds).'<br>';
	  
			IF($this->getValue('gdpr-checkbox')){
				$emailText .= sanitize::htmlDecode($this->getValue('gdpr-checkbox-text')).'<br>';
			}
		} 
		else {
			$emailText  = $L->get('booking-event')			.': '.$this->eventName			."\r\n\r";
			$emailText .= $L->get('booking-name')			.': '.$this->senderName			."\r\n\r";
			$emailText .= $L->get('booking-email')			.': '.$this->senderEmail		."\r\n\r";
		//	$emailText .= $L->get('booking-chk-email')		.': '.$this->validateEmail		."\r\n\r";	// Not needed unless you want it in the emails
			$emailText .= $L->get('booking-phone')			.': '.$this->senderPhone		."\r\n\r";
			$emailText .= $L->get('booking-tickets')		.': '.$this->numberOfTickets	."\r\n\r";
			$emailText .= $L->get('booking-pickup')			.': '.$this->pickupPoint		."\r\n\r";
			$emailText .= $L->get('booking-payment')		.': '.$this->paymantConfirmed	."\r\n\r";
			$emailText .= $L->get('booking-others')			.': '.$this->otherPartyNames	."\r\n\r";
			$emailText .= $L->get('booking-sit-near')		.': '.$this->sittingNear		."\r\n\r";
			$emailText .= $L->get('booking-needs')			.': '.$this->specialNeeds		."\r\n\r";

			IF($this->getValue('gdpr-checkbox')){
			$emailText .= strip_tags(sanitize::htmlDecode($this->getValue('gdpr-checkbox-text')))."\r\n\r";
			}
		}
		return $emailText;
	}

	private function frontendMessage(){
		global $L;
		IF($this->success) {
			$html = '<div class="alert alert-success">' .$L->get('booking-confirmation'). '</div>' ."\r\n";
		} ELSEIF(!is_bool($this->error)) {
			$html = '<div class="alert alert-danger">'. $this->error. '</div>' ."\r\n";
		} ELSEIF($this->error) {
			$html = '<div class="alert alert-danger">' .$L->get('sending-email-error').'</div>' ."\r\n";
		} else {
			$html = '';
		}
		return $html;
	}

	private function useSendmail($SentReceipt = false){
		global $L;
		$success		= false;
		$sendFrom		= $this->getValue('sendEmailFrom');
		$adminEmail		= $this->getValue('email');
		$senderName		= $this->senderName;
		$senderEmail	= $this->senderEmail;
		$domainEmail	= $this->getValue('domainAddress');
		$subject		= $this->getSubject();

		// email headers

				IF ($SentReceipt) {
					switch ($sendFrom)
					{
						case "fromTo":
							$email_headers	= "From: $subject <"		. $adminEmail	.">\r\n";
							$email_headers .= "Reply-To: $subject <"	. $adminEmail	.">\r\n";
							break;
						case "fromDomain":
							$email_headers	= "From: $subject <"		. $domainEmail	.">\r\n";
							$email_headers .= "Reply-To: $subject <"	. $adminEmail	.">\r\n";
							break;
						default: // fromUser
							$email_headers	= "From: $subject <"		. $adminEmail	.">\r\n";
							$email_headers .= "Reply-To: $subject <"	. $adminEmail	.">\r\n";
					}
				}
				ELSE {
					switch ($sendFrom)
					{
						case "fromTo":
							$email_headers	= "From: $senderName <"		. $adminEmail	.">\r\n";
							$email_headers .= "Reply-To: $senderName <"	. $senderEmail	.">\r\n";
							break;
						case "fromDomain":
							$email_headers	= "From: $senderName <"		. $domainEmail	.">\r\n";
							$email_headers .= "Reply-To: $senderName <"	. $senderEmail	.">\r\n";
							break;
						default: // fromUser
							$email_headers	= "From: $senderName <"		. $senderEmail	.">\r\n";
							$email_headers .= "Reply-To: $subject <"	. $senderEmail	.">\r\n";
					}
				}

		$email_headers .= 'MIME-Version: 1.0' ."\r\n";

		IF($this->isHtml()){
		  $email_headers .= 'Content-type: text/html; charset="' .CHARSET. '"'	."\r\n";
		} else {
		  $email_headers .= 'Content-type: text/plain; charset="' .CHARSET. '"'	."\r\n";
		}

		$email_headers .= 'Content-transfer-encoding: 8bit' ."\r\n";
		$email_headers .= 'Date: ' .date("D, j M Y G:i:s O")."\r\n"; // eg Sat, 7 Jun 2019 12:35:58 -0700

		// send email via sendmail => mail(to,subject,message,headers,parameters);
		IF ($SentReceipt) {
			$success = mail($senderName	."<". $senderEmail.">", 								// To:		Sent receipt back to user
							$subject	.' ('.$this->eventName.')', 							// Subject:	Same Subject
							$L->get('booking-confirmation')."\r\n\r\n".$this->getEmailText(),	// Same message with prefix, eg "thank you for booking"
							$email_headers														// Same From: ReplyTo
							);
		}
		else {
			$success = mail($adminEmail, 
							$subject.' ('.$this->eventName.')', 
							$this->getEmailText(), 
							$email_headers
							);
		}

		IF(!$success){

			$errorMessage = error_get_last()['message'];

			IF (isset($errorMessage)) {
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
		global $L;
		$success		= false;
		$sendFrom		= $this->getValue('sendEmailFrom');
		$adminEmail		= $this->getValue('email');
		$senderName		= $this->senderName;
		$senderEmail	= $this->senderEmail;
		$domainEmail	= $this->getValue('domainAddress');
		$subject		= $this->getSubject();

		// load PHPMailer
		require __DIR__ . DS . 'phpmailer' . DS . 'PHPMailerAutoload.php';

		try {
			$mail = new PHPMailer;

			$mail->isSMTP();
			$mail->Host = $this->getValue('smtphost');
			$mail->Port = $this->getValue('smtpport');
			$mail->SMTPAuth = true;
			$mail->Username = $this->getValue('username');
			#Function is needed IF Password contains special characters like &
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

			IF($mail->send()) {
				$success = true;
			} 
			else {
				$errorMessage = error_get_last()['message'];

				IF (isset($errorMessage)) {
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
		IF(file_exists($template)) {
		  include($template);
		} else {
		  include(__DIR__ . DS . 'layout' . DS . 'contact3.php');
		}   
	}

	private function googleRecaptchaValidation(){
		IF($this->getValue('google-recaptcha')){
		  $secretKey = $this->getValue('recaptcha-secret-key');
		  $json = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secretKey.'&response='.$_POST['g-recaptcha-response']);
		  $data = json_decode($json);
		  return $data->success;
		} else {
		  return true;
		}
	}

	// Keep only number of logs defined in numberOfDaysToKeep, numberOfWeeksToKeep & numberOfMonthsToKeep.
	public function deleteOldLogs( $periodType, $numberToKeep )
	{
		$logs = Filesystem::listFiles($this->workspace(), '*-'.$periodType, 'log', true);
		$remove = array_slice($logs, $numberToKeep);

		foreach ($remove as $log) {
			Filesystem::rmfile($log);
		}
	}

	// Add a line to the Event Booking csv file
	public function addBooking()
	{

		$currentTime = Date::current('Y-m-d H:i:s');
		$logEventFile = $this->workspace().$this->eventName.'.csv';		
		IF ( !file_exists($logEventFile) ) {
			$fileHeader = array('EventName','BookerName','BookerEmail','BookerPhone','Tickets','PickupPoint',
								'Paid','OtherNames','SitNear','SpecialNeeds','TimeBooked');
			$handle = fopen($logEventFile, "a");
			fputcsv($handle, $fileHeader);
			fclose($handle);		
		}

		$booking = array($this->eventName,$this->senderName,$this->senderEmail,$this->senderPhone,$this->numberOfTickets,$this->pickupPoint
						,$this->paymantConfirmed,$this->otherPartyNames,$this->sittingNear,$this->specialNeeds,$currentTime );

		$handle = fopen($logEventFile, "a");
		fputcsv($handle, $booking );
		fclose($handle);

	}
}
