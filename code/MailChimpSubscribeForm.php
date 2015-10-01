<?php

/**
 * MailChimpSubscribeForm
 *
 * @author Gabriele Brosulo <gabriele.brosulo@zirak.it>
 * @creation-date 22-Apr-2015
 */
class MailChimpSubscribeForm extends Form {

	private $mc;
	private $mcGroups;
	
	public function __construct(Controller $controller, $name) {

		// Recupero le info sul grouping da MailChimp
		try {
			$this->mc = new Mailchimp(Config::inst()->get('MailChimpController', 'apikey'));
		} catch (Mailchimp_Error $e) {
			echo $e->getMessage();
			exit;
		}

		// Workaround per problema SSL
		curl_setopt($this->mc->ch, CURLOPT_SSL_VERIFYPEER, false);
		
		try {
			$this->mcGroups = $this->mc->lists->interestGroupings(Config::inst()->get('MailChimpController', 'listid'));
		} catch (Mailchimp_Error $e) {
			// No groups
      $this->mcGroups = array();
		}
		
		// Creo il Form
		$fieldsArr = array();

		/*
		 * EMAIL
		 */
		$email = new EmailField('Email', 'Email');
		array_push($fieldsArr, $email);

		/*
		 * TOPICS
		 */
		if (Config::inst()->get('MailChimpController', 'topics')) {

			// Merge delle informazioni di MailChimp con quelle della conf
			$topicsArr = Config::inst()->get('MailChimpController', 'topicsArr');
			foreach ($this->mcGroups as $grouping) {
				// Se esiste nella config devo presentarlo all'utente
				if (array_key_exists($grouping['name'], $topicsArr)) {
					$groups = array();
					foreach ($grouping['groups'] as $group) {
						if (in_array($group['name'], $topicsArr[$grouping['name']])) {
							$groups[$group['id']] = $group['name'];
						}
					}
					$topics = new CheckboxSetField(
									'Topic-' . $grouping['id'], $grouping['name'], $groups, NULL
					);
					array_push($fieldsArr, $topics);
				}
			}
		}

		$fields = new FieldList($fieldsArr);
		$actions = new FieldList(
						new FormAction('doSubscribe', 'Send Now')
		);
		
		$required = new RequiredFields(
						array('Email')
		);

		parent::__construct($controller, $name, $fields, $actions, $required);
	}
	
	/**
	 * Gestione dell'iscrizione
	 * 
	 * @param type $data
	 * @param type $form
	 */
	public function doSubscribe($data, $form) {
		
		// Email
		$email = $data['Email'];
		
		// Preparo l'array dei gruppi selezionati
		$groups = array();
		foreach ($data as $key => $val) {
			if (strstr($key, 'Topic') !== FALSE) {
				foreach ($val as $id) {
					$groups[] = array('id' => $id);
				}
			}
		}
		
		// Merge Vars
		$merge_vars = array(
				'groupings' => $groups
		);

		$regOk = $this->McSubscribe($email, $merge_vars);
		if ($regOk) {

			// Pulisco la sessione 
			Session::clear('MAILCHIMP_ERRCODE');
			Session::clear('MAILCHIMP_ERRMSG');

			if (Config::inst()->get('MailChimpController', 'redirect')) {
				// Redireziono alla pagina di avvenuta registrazione
				return Controller::curr()->redirect(Config::inst()->get('MailChimpController', 'redirect_ok'));
			} else {
				// Se non è definita una pagina di redirezione, rimando indietro
				return Controller::curr()->redirectBack();
			}
		} else {
			// Pagina di errore
			return Controller::curr()->redirect(Config::inst()->get('MailChimpController', 'redirect_ko'));
		}
		
	}
	
	/**
	 * Chiamata all'API di subscribe
	 * @return type
	 */
	private function McSubscribe($email, $merge_vars) {
		
//		var_dump($email);
//		var_dump($merge_vars); exit;

    // Pulisco la sessione
		Session::clear('MAILCHIMP_ERRCODE');
		Session::clear('MAILCHIMP_ERRMSG');

    $email_arr = ['email' => $email];
    try {
      $retVal = $this->mc->lists->subscribe(Config::inst()->get('MailChimpController', 'listid'), $email_arr, $merge_vars);
    } catch (Exception $e) {
      // Errori in sessione
      //var_dump($e); exit;
			Session::set('MAILCHIMP_ERRCODE', $e->getCode());
			Session::set('MAILCHIMP_ERRMSG', $e->getMessage());
			trigger_error("Error subscribing: email [$email] code [{$e->getCode()}] msg[{$e->getMessage()}]", E_USER_WARNING);
      return false;
    }

		return $retVal;
	}

	/**
	 * Overrido la forTemplate per usare i miei files
	 * @return type
	 */
	public function forTemplate() {
		
		$retVal = $this->renderWith(array($this->class, 'Form'));
		// Now that we're rendered, clear message
		$this->clearMessage();
		
		return $retVal;
	}
	
}
