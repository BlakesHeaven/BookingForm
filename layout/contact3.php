<?php 
/**
 *  Booking layout
 *
 *  @package Bludit
 *  @subpackage Booking Form
 *	@author David Blake
 *  @info: Duplicate this layout in your themes/YOUR_THEME/php/ 
 *	for a custom template.
 */	
	global $L;
	global $url;
	global $site;
	global $pages;
	global $page;
	global $content;
 	$formatter = new NumberFormatter(@$locale,  NumberFormatter::CURRENCY);		// Presets the formatting of the currency to the globally configured location.
 
	$currentDateTime		= strtotime("now");									// The DateTime now, stored as a number for comparisons later.
	$numberOfEventOptions	= 0;
	$eventNameOptions		= '';
	$numberOfEventDisplay	= 0;
	$eventNameDisplay		= '';
	$bankDetailsText		= $L->get('bank-detail-pre-text').$this->getValue('bankDetails');
	$treasurerAddressText	= $L->get('treasurer-address-pre-text').$this->getValue('treasurerAddress');

			
 // Build Bookable Events List
    $pageNumber = 1;															// Page number of the paginator, the first page is 1
    $numberOfItems = -1;														// The value -1 tell to Bludit to returns all the pages on the system
    //$onlyPublished = true;														// Only get the pages with the satus published
    $items = $pages->getList($pageNumber, $numberOfItems, 	
							$published=true, $static=false, $sticky=false, 	
							$draft=false, $scheduled=false);					// Get the list of event pages

	$eventList = array();

	foreach ($items as $key) {
	  
		$event = buildPage($key);												// buildPage function returns a Page-Object
		$openDateTime	= strtotime($event->custom('OpenDate') );				// Is when the event becomes bookable. Stored as a number for comparisons
		$closeDateTime	= strtotime($event->custom('CloseDate'). ' + 1 days');	// Is when the event is no longer bookable. Stored as a number for comparisons
		$eventCost		= $event->custom('Cost');								// How much does the event cost. Expected to be stored without currency symbol.

		IF (empty($eventCost)) {
			$eventCost = '';
		}
		ELSE {
			$eventCost = ' ~ '.$formatter->formatCurrency($eventCost, "GBP");	// Adds the chosen currency symbol 
		}

		IF( !(empty($event->custom('CloseDate')) ) AND ($closeDateTime >= $currentDateTime) AND ($event->isChild()) ) {

			$eventTitle = $event->title();

			$openDate	= IntlDateFormatter::formatObject(
								IntlCalendar::fromDateTime($event->custom('OpenDate'))
							,	"dd/MM/yyyy"
							,	@$locale );

			$lastDayDate	= IntlDateFormatter::formatObject(
								IntlCalendar::fromDateTime($event->custom('CloseDate'))
							,	"dd/MM/yyyy"
							,	@$locale );

			$eventDate	= IntlDateFormatter::formatObject(
								IntlCalendar::fromDateTime($event->custom('EventDate'))
							,	"dd/MM/yyyy"
							,	@$locale );

			// Build the array of events where closing date is in the future
			$eventList[] = array(	'eventTitle'	=>$eventTitle, 
									'EventDate'		=>$eventDate, 
									'EventCost'		=>$eventCost, 										
									'bookingOpen'	=>$openDate, 
									'lastDayDate'	=>$lastDayDate, 
									'openDateTime'	=>$openDateTime, 
									'closeDateTime'	=>$closeDateTime  
								);	

		}
    }

	// Sort the $eventList array ascending by openDateTime. Tip: swap return item 1 & 2 around to sort Decending.
	usort($eventList, function ($item1, $item2) {return $item1['openDateTime'] <=> $item2['openDateTime'];});

	foreach ($eventList as $Event) {

		$strongDisplayStart = '';	$strongDisplayEnd = '';	// Will be used to make a bookable Event bold.

		// Define the current bookable events for dropdown.
		IF ($currentDateTime > $Event[openDateTime] and $currentDateTime < $Event[closeDateTime]) {
			$eventNameOptions .= '<option value="'.$Event[eventTitle].'"'.($this->eventName === $Event[eventTitle] ?'selected':''). '>'.$Event[eventTitle].' ~ Date: '.$Event[EventDate]. $Event[EventCost].'pp</option>'.PHP_EOL;
			$numberOfEventOptions++;
			$strongDisplayStart = '<strong>';
			$strongDisplayEnd = '</strong>';
		}

		// Define the Events list for display.
		$eventNameDisplay .= '<li>'. $strongDisplayStart . $Event[eventTitle] . 
								' ~ Bookable: '.$Event[bookingOpen] . ' to ' . $Event[lastDayDate] . $strongDisplayEnd . '</li>';
		$numberOfEventDisplay++;
	}
 
	IF ($numberOfEventDisplay == 0) {
			$eventNameDisplay = '<li>'.$L->get('no-events-to-display').'</li>'.PHP_EOL; 
		}
	ELSE {
		$eventNameDisplay = //'Out of the '. $numberOfEventDisplay . ' forthcoming events, ' . $numberOfEventOptions . ' is bookable.'.
							'<ul>' . $eventNameDisplay .'</ul>';
	}
  

	IF ($numberOfEventOptions > 1) {
		// Add the "Please select from list" option if more than one event is available.		
		$eventNameOptions = '<option value="" '.($this->eventName ===''?'selected':'').'>Please select from list</option>'.$eventNameOptions.PHP_EOL;	
	}
	ELSEIF ($numberOfEventOptions == 0) {
		// Add a non-option stating that no events are available.
		$eventNameOptions = '<option value="" '.($this->eventName ===''?'selected':'').'>No events are available for booking. Please refer to event send-out dates.</option>'.PHP_EOL;
	}
	ELSE {
		// Nothing to be done - if there is only one event available, just show it.
	}
 ?>
<!-- Begin the HTML and inject the variable values -->
<div>
	<label><?php echo $L->get('list-events-label');?></label>
	<?php echo $eventNameDisplay;?>	
	<hr>	
</div>

<div>

	<!-- This title might be needed if the chosen page Title is not someting like "Booking Form"-->
	<!--label><?php echo $L->get('booking-form-label');?></label-->

	<form id="TopOfForm" method="post" action="<?php echo '.' . DS . $page->slug(); ?>#TopOfForm" class="contact3">
		<?php echo $this->frontendMessage(); ?>
		<input type="hidden" name="tokenCSRF" value="<?php echo $security->getTokenCSRF(); ?>">

		<!-- eventName -->
		<div class="form-group" >
			<label><?php echo $L->get('event-name-label');?></label>
			<select name="eventName" class="form-control" required ><?php echo $eventNameOptions; ?></select>
		</div>

		<!-- senderName -->
		<div class="form-group">
			<label><?php echo $L->get('your-name-label'); ?></label>
			<input id="senderName" type="text" name="senderName" value="<?php echo sanitize::html($this->senderName); ?>" placeholder="<?php echo $L->get('your-name-placeholder'); ?>" class="form-control" required >
		</div>

		<!-- senderEmail -->
		<div class="form-group">
			<label><?php echo $L->get('your-email-label'); ?></label>
			<input id="senderEmail" type="email" name="senderEmail" value="<?php echo sanitize::email($this->senderEmail); ?>" placeholder="<?php echo $L->get('your-email-placeholder'); ?>" class="form-control" required >
		</div>

		<!-- senderEmail -->
		<div class="form-group">
			<label><?php echo $L->get('validate-email-label'); ?></label>
			<input id="validateEmail" type="email" onDrag="return false" onDrop="return false" onPaste="return false" name="validateEmail" value="<?php echo sanitize::email($this->validateEmail); ?>" placeholder="<?php echo $L->get('validate-email-placeholder'); ?>" class="form-control" required >
		</div>
		
		<!-- senderPhone -->
		<div class="form-group">
			<label><?php echo $L->get('your-phone-label'); ?></label>
			<input id="senderPhone" type="tel" name="senderPhone" value="<?php echo sanitize::email($this->senderPhone); ?>" placeholder="<?php echo $L->get('your-phone-placeholder'); ?>" class="form-control" required >
		</div>

		<!-- numberOfTickets -->
		<div class="form-group">
			<label><?php echo $L->get('number-of-tickets-label'); ?></label>
			<?php echo
				'<input id="numberOfTickets" type="number" name="numberOfTickets" value="'.$this->numberOfTickets.'" class="form-control" required >'
			?>
		</div>	

		<!-- paymentOption -->
		<div class="form-group">
			<label><?php echo $L->get('payment-option-label'); ?></label>
			<select name="paymentOption" class="form-control" required>
				<?php echo
					 '<option value=""	 			'.($this->paymentOption ===''?'selected':'')			.'>Please select from list	  </option>'.PHP_EOL
					.'<option value="B2Btransfer"	'.($this->paymentOption ==='B2Btransfer'?'selected':'')	.'>'.$bankDetailsText		.'</option>'.PHP_EOL
					.'<option value="Cheque" 		'.($this->paymentOption ==='Cheque'?'selected':'')		.'>'.$treasurerAddressText	.'</option>'.PHP_EOL
				?>
			</select>
		</div>

		<!-- otherPartyNames -->
		<div class="form-group">
			<label><?php echo $L->get('other-party-names-label'); ?></label>	
			<input id="otherPartyNames" type="text" name="otherPartyNames" value="<?php echo sanitize::html($this->otherPartyNames); ?>" placeholder="<?php echo $L->get('other-party-names-placeholder'); ?>" class="form-control" >
		</div>

		<!-- pickupPoint -->
		<div class="form-group">
			<label><?php echo $L->get('pickup-point-label'); ?></label>
			<select name="pickupPoint" class="form-control" required >
				<?php echo
					 '<option value=""	 		'.($this->pickupPoint ===''?'selected':'').			'>Please select from list		</option>'.PHP_EOL
					.'<option value="Hebden"	'.($this->pickupPoint ==='Hebden'?'selected':'').	'>Hebden						</option>'.PHP_EOL
					.'<option value="Colvend" 	'.($this->pickupPoint ==='Colvend'?'selected':'').	'>Colvend Carpark, Grassington	</option>'.PHP_EOL
					.'<option value="Hedgerow" 	'.($this->pickupPoint ==='Hedgerow'?'selected':'').	'>Hedgerow, Threshfield			</option>'.PHP_EOL
					.'<option value="OldHall" 	'.($this->pickupPoint ==='OldHall'?'selected':'').	'>Old Hall Inn, Threshfield		</option>'.PHP_EOL
					.'<option value="Cracoe" 	'.($this->pickupPoint ==='Cracoe'?'selected':'').	'>Cracoe						</option>'.PHP_EOL;
				?>
			</select>
		</div>

		<!-- sittingNear -->
		<div class="form-group">
			<label><?php echo $L->get('sit-near-label'); ?></label>	
			<input id="sittingNear" type="text" name="sittingNear" value="<?php echo sanitize::html($this->sittingNear); ?>" placeholder="<?php echo $L->get('sitting-near-placeholder'); ?>" class="form-control" >
		</div>

		<!-- specialNeeds -->
		<div class="form-group">
			<label><?php echo $L->get('special-needs-label'); ?></label>	
			<textarea id="specialNeeds" rows="2" name="specialNeeds" placeholder="<?php echo $L->get('special-needs-placeholder'); ?>" class="form-control" ><?php echo sanitize::html($this->specialNeeds); ?></textarea>
		</div>

		<!-- interested (a trap for bots)-->
		<input type="checkbox" name="interested">

		<?php if ($this->getValue('gdpr-checkbox')): ?>
			<div class="form-check">
				<input type="checkbox" name="gdpr-checkbox" id="gdpr-checkbox" class="form-check-input" required>
				<label for="gdpr-checkbox" class="form-check-label"><?php echo sanitize::htmlDecode($this->getValue('gdpr-checkbox-text')); ?></label>
			</div>
		<?php endif; ?> 	

		<?php echo $this->googleRecaptchaForm(); ?>

		<button id="submit" name="submit" type="submit" class="btn btn-primary"><?php echo $L->get('submit-form'); ?></button>
	</form>
</div>