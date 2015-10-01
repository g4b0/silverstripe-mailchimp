<?php

/**
 * MailChimpExtension
 *
 * @author Gabriele Brosulo <gabriele.brosulo@zirak.it>
 * @creation-date 22-Apr-2015
 */
class MailChimpExtension extends Extension {
	
	private static $allowed_actions = array(
			'MailChimpSubscribeForm',
	);
	
	/**
	 * Form di subscribe
	 * @return \Form
	 */
	public function MailChimpSubscribeForm() {
		return new MailChimpSubscribeForm($this->owner, 'MailChimpSubscribeForm');
	}
}
