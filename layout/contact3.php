<?php 
/**
 *  Contact layout
 *
 *  @package Bludit
 *  @subpackage Contact
 *  @author Frédéric K
 *	@author novafacile OÜ.
 *  @info: Duplicate this layout in your themes/YOUR_THEME/php/ 
 *	for a custom template.
 */	
?>
<form method="post" action="<?php echo '.' . DS . $page->slug(); ?>" class="contact3">
	<?php echo $this->frontendMessage(); ?>
	<input type="hidden" name="tokenCSRF" value="<?php echo $security->getTokenCSRF(); ?>">
	<!-- eventName -->
    <div class="form-group" >
		<label><?php echo $L->get('event-name-label');?></label>
		<select name="eventName" class="form-control" required >
			<option value=""	 				>Please select from list								</option>
			<option value="Penzance Pirates"	>Pirates of Penzance~Date ~ 28th Sep 2019 ~ £22pp		</option>
			<option value="Ripon Festival" 		>Ripon Festival ~ 6th Oct 2019 ~ £36pp					</option>
			<option value="Jane Eyre" 			>Jane Eyre ~ 25th Oct 2019 ~ £24pp						</option>
			<option value="Harrogate Symphony" 	>Harrogate Symphony Orchestra ~ 16th Nov 2019 ~ £23pp	</option>
			<option value="Absurd Person" 		>Absurd Person Singular ~ 9th Dec 2019 ~ £17pp			</option>
			<option value="Cinderella" 			>Cinderella ~ Date: 17th Dec 2019 ~ £56pp				</option>
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
	<!-- senderPhone -->
	<div class="form-group">
		<label><?php echo $L->get('your-phone-label'); ?></label>
		<input id="senderPhone" type="tel" name="senderPhone" value="<?php echo sanitize::email($this->senderPhone); ?>" placeholder="<?php echo $L->get('your-phone-placeholder'); ?>" class="form-control" required >
	</div>
	<!-- numberOfTickets -->
	<div class="form-group">
		<label><?php echo $L->get('number-of-tickets-label'); ?></label>
		<input id="numberOfTickets" type="number" name="numberOfTickets" value="0" class="form-control" required >
	</div>	
	<!-- paymantConfirmed -->
    <div class="form-group">
		<label><?php echo $L->get('paymant-confirmed-label'); ?></label>
		<select name="paymantConfirmed" class="form-control" required>
			<option value="No"	>No		</option>
			<option value="Yes" >Yes	</option>
		</select>
    </div>
	<!-- otherPartyNames -->
	<div class="form-group">
		<label><?php echo $L->get('other-party-names-label'); ?></label>	
		<textarea id="otherPartyNames" rows="3" name="otherPartyNames" placeholder="<?php echo $L->get('other-party-names-placeholder'); ?>" class="form-control" ><?php echo sanitize::html($this->otherPartyNames); ?></textarea>
	</div>
	<!-- pickupPoint -->
    <div class="form-group">
		<label><?php echo $L->get('pickup-point-label'); ?></label>
		<select name="pickupPoint" class="form-control" required >
			<option value=""	 	>Please select from list		</option>
			<option value="Hebden" 	>Hebden							</option>
			<option value="Colvend" >Colvend Carpark, Grassington	</option>
			<option value="Hedgerow">Hedgerow, Threshfield			</option>
			<option value="OldHall" >Old Hall Inn, Threshfield		</option>
			<option value="Cracoe" 	>Cracoe							</option>
		</select>
    </div>
	<!-- sittingNear -->
	<div class="form-group">
		<label><?php echo $L->get('sit-near-label'); ?></label>	
		<textarea id="sittingNear" rows="3" name="sittingNear" placeholder="<?php echo $L->get('sitting-near-placeholder'); ?>" class="form-control" ><?php echo sanitize::html($this->sittingNear); ?></textarea>
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