<?php

class MailChimpLandingPage extends Page {
	//public static $allowed_children = array();
	private static $can_create = false;
}

class MailChimpLandingPage_Controller extends Page_Controller {
	
	private $errorCode;
	private $errorMessage;
	
	public function init() {
		parent::init();
			
		$this->errorCode = Session::get('MAILCHIMP_ERRCODE');
		$this->errorMessage = Session::get('MAILCHIMP_ERRMSG');
		
		Session::clear('MAILCHIMP_ERRCODE');
		Session::clear('MAILCHIMP_ERRMSG');
	}
	
	public function ErrorCode() {
		return $this->errorCode;
	}
	
	public function ErrorMsg() {
		return $this->errorMessage;
	}
}

