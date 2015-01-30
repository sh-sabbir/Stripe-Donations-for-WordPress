<div id="hg-stripe-donate-form-<?php echo $button_id ?>">
	<p>
		<button id="hg-stripe-donate-button-<?php echo $button_id ?>"><?php echo $button_label; ?></button>
		<input type="hidden" id="hg-stripe-donate-amount-<?php echo $button_id ?>" value="<?php echo $prefilled_amount / 100; ?>">
	</p>
</div>