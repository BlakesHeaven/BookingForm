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
			<?php echo
				 '<option value=""	 				'.($this->eventName ===''?'selected':'').					'>Please select from list								</option>'.PHP_EOL
				.'<option value="Penzance Pirates"	'.($this->eventName ==='Penzance Pirates'?'selected':'').	'>Pirates of Penzance ~ 28th Sep 2019 ~ £22pp			</option>'.PHP_EOL
				.'<option value="Ripon Festival" 	'.($this->eventName ==='Ripon Festival'?'selected':'').		'>Ripon Festival ~ 6th Oct 2019 ~ £36pp					</option>'.PHP_EOL
				.'<option value="Jane Eyre" 		'.($this->eventName ==='Jane Eyre'?'selected':'').			'>Jane Eyre ~ 25th Oct 2019 ~ £24pp						</option>'.PHP_EOL
				.'<option value="Harrogate Symphony"'.($this->eventName ==='Harrogate Symphony'?'selected':'').	'>Harrogate Symphony Orchestra ~ 16th Nov 2019 ~ £23pp	</option>'.PHP_EOL
				.'<option value="Absurd Person" 	'.($this->eventName ==='Absurd Person'?'selected':'').		'>Absurd Person Singular ~ 9th Dec 2019 ~ £17pp			</option>'.PHP_EOL
				.'<option value="Cinderella" 		'.($this->eventName ==='Cinderella'?'selected':'').			'>Cinderella ~ Date: 17th Dec 2019 ~ £56pp				</option>'.PHP_EOL;
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