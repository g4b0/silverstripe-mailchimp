<?php

if (class_exists('Widget')) {
	
	class MailChimpSubscribe extends Widget {

		static $title = "MailChimp Subscribe";
		static $cmsTitle = "MailChimp Subscribe";
		static $description = "Form per l'iscrizione a MailChimp";
		static $db = array(
				"Label" => "Varchar(64)",
		);

		public function getCMSFields() {
			$fields = parent::getCMSFields();
			$fields->push(new TextField("Label", _t("title", "Titolo"), self::$title));

			return $fields;

		}

		public function requireDefaultRecords() {
			parent::requireDefaultRecords();

			if (Config::inst()->get('MailChimpController', 'redirect')) {
				if (!SiteTree::get_by_link(Config::inst()->get('MailChimpController', 'redirect_ok'))) {
					$regOk = new MailChimpLandingPage();
					$regOk->Title = _t('MailChimp.SUBSCIPTION_OK	', 'MailChimp Subscription OK');
					$regOk->Content = _t('MailChimp.SUBSCIPTION_OK_CONTENT', '<p>Thanks for subscribing</p>');
					$regOk->URLSegment = Config::inst()->get('MailChimpController', 'redirect_ok');
					$regOk->Sort = 9998;
					$regOk->ShowInMenus = FALSE;
					$regOk->ShowInSearch = FALSE;
					$regOk->write();
					$regOk->publish('Stage', 'Live');
					$regOk->flushCache();
					DB::alteration_message('MailChimp Subsciption OK created', 'created');
				}

				if (!SiteTree::get_by_link(Config::inst()->get('MailChimpController', 'redirect_ko'))) {
					$regOk = new MailChimpLandingPage();
					$regOk->Title = _t('MailChimp.SUBSCIPTION_KO	', 'MailChimp Subscription KO');
					$regOk->Content = _t('MailChimp.SUBSCIPTION_KO_CONTENT', '<p>Something goes wrong subscribing</p>');
					$regOk->URLSegment = Config::inst()->get('MailChimpController', 'redirect_ko');
					$regOk->Sort = 9999;
					$regOk->ShowInMenus = FALSE;
					$regOk->ShowInSearch = FALSE;
					$regOk->write();
					$regOk->publish('Stage', 'Live');
					$regOk->flushCache();
					DB::alteration_message('MailChimp Subsciption KO created', 'created');
				}
			}

		}

		public function McSubscribeForm() {
			return new MailChimpSubscribeForm(Controller::curr(), 'MailChimpSubscribeForm');
		}

	}
	
}