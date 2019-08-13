<?php 
/**
 *  Contact layout
 *
 *  @package Bludit
 *  @subpackage Booking Form
 *	@author David Blake
 *  @info: Duplicate this layout in your themes/YOUR_THEME/php/ 
 *	for a custom template.
 */	
 
	$currentDate	= strtotime("now");
	$event1OpenDate	= strtotime("2019-08-07");	$event1CloseDate	= strtotime("2019-08-22");
	$event2OpenDate	= strtotime("2019-08-14");	$event2CloseDate	= strtotime("2019-08-29");
	$event3OpenDate	= strtotime("2019-09-04");	$event3CloseDate	= strtotime("2019-09-17");
	$event4OpenDate	= strtotime("2019-09-11");	$event4CloseDate	= strtotime("2019-09-26");
	$event5OpenDate	= strtotime("2019-10-23");	$event5CloseDate	= strtotime("2019-11-07");
	$event6Opendate	= strtotime("2019-09-18");	$event6Closedate	= strtotime("2019-10-03");

	$numberOfEvents = 0;
	$eventNameOptions = '';

	IF ($currentDate > $event1OpenDate and $currentDate < $event1CloseDate) {
		$eventNameOptions .= '<option value="Penzance Pirates"	'.($this->eventName ==='Penzance Pirates'?'selected':'').	'>Pirates of Penzance ~ 28th Sep 2019 ~ £22pp			</option>'.PHP_EOL;
		$numberOfEvents++; // add 1 to number of events
	}
	IF ($currentDate > $event2OpenDate and $currentDate < $event2CloseDate) {
		$eventNameOptions .= '<option value="Ripon Festival" 	'.($this->eventName ==='Ripon Festival'?'selected':'').		'>Ripon Festival ~ 6th Oct 2019 ~ £36pp					</option>'.PHP_EOL;
		$numberOfEvents++;	
	}
	IF ($currentDate > $event3OpenDate and $currentDate < $event3CloseDate) {
		$eventNameOptions .= '<option value="Jane Eyre" 		'.($this->eventName ==='Jane Eyre'?'selected':'').			'>Jane Eyre ~ 25th Oct 2019 ~ £24pp						</option>'.PHP_EOL;
		$numberOfEvents++;
	}
	IF ($currentDate > $event4OpenDate and $currentDate < $event4CloseDate) {
		$eventNameOptions .= '<option value="Harrogate Symphony"'.($this->eventName ==='Harrogate Symphony'?'selected':'').	'>Harrogate Symphony Orchestra ~ 16th Nov 2019 ~ £23pp	</option>'.PHP_EOL;
		$numberOfEvents++;
	}
	IF ($currentDate > $event5OpenDate and $currentDate < $event4CloseDate) {
		$eventNameOptions .= '<option value="Absurd Person" 	'.($this->eventName ==='Absurd Person'?'selected':'').		'>Absurd Person Singular ~ 9th Dec 2019 ~ £17pp			</option>'.PHP_EOL;
		$numberOfEvents++;
	}
	IF ($currentDate > $event6Opendate and $currentDate < $event6CloseDate) {
		$eventNameOptions .= '<option value="Cinderella" 		'.($this->eventName ==='Cinderella'?'selected':'').			'>Cinderella ~ Date: 17th Dec 2019 ~ £56pp				</option>'.PHP_EOL;
		$numberOfEvents++;
	}
	// Add the "Please select from list" option if more than one event available
	IF ($numberOfEvents > 1) {
		$eventNameOptions = '<option value="" '.($this->eventName ===''?'selected':'').'>Please select from list</option>'.$eventNameOptions.PHP_EOL;	
	}
	ELSEIF ($numberOfEvents == 0) {
		$eventNameOptions = '<option value="" '.($this->eventName ===''?'selected':'').'>No events are available for booking. Please refer to event send-out dates.</option>'.PHP_EOL;
	}
	// If there is only one event available, just show that.
 
?>
<form method="post" action="<?php echo '.' . DS . $page->slug(); ?>" class="contact3">
	<?php echo $this->frontendMessage(); ?>
	<input type="hidden" name="tokenCSRF" value="<?php echo $security->getTokenCSRF(); ?>">
	<!-- eventName -->
    <div class="form-group" >
		<label><?php echo $L->get('event-name-label');?></label>
		<select name="eventName" class="form-control" required >
			<?php
				echo $eventNameOptions;
			?>
		</select>
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
		<input id="validateEmail" type="email" name="validateEmail" value="<?php echo sanitize::email($this->validateEmail); ?>" placeholder="<?php echo $L->get('validate-email-placeholder'); ?>" class="form-control" required >
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
	<!-- paymantConfirmed -->
    <div class="form-group">
		<label><?php echo $L->get('paymant-confirmed-label'); ?></label>
		<select name="paymantConfirmed" class="form-control" required>
			<?php echo
				 '<option value="No"	'.($this->paymantConfirmed ==='No'?'selected':'')	.'>No	</option>'
				.'<option value="Yes"	'.($this->paymantConfirmed ==='Yes'?'selected':'')	.'>Yes	</option>'
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
		<textarea id="specialNeeds" rows="3" name="specialNeeds" placeholder="<?php echo $L->get('special-needs-placeholder'); ?>" class="form-control" ><?php echo sanitize::html($this->specialNeeds); ?></textarea>
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

	<button id="submit" name="submit" type="submit" class="btn btn-primary"><?php echo $L->get('Send'); ?></button>
</form>