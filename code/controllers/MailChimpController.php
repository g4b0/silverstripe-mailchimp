<?php

class MailChimpController extends Controller
{

    private static $allowed_actions = array('McSubscribeForm');
    
    /*
     * Mailchimp list ID
     */
    private static $listid = '';
    
    /*
     * Redirect after registration
     */
    private static $redirect = true;
    
    /*
     * URL to redirect after a succesful registration
     */
    private static $redirect_ok = 'reg-ok';
    
    /*
     * URL to redirect after a failing registration
     */
    private static $redirect_ko = 'reg-ko';
    
    /*
     * Display country dropdown list
     */
    private static $country = false;
    
    /*
     * Display topics checkbox fields
     */
    private static $topics = false;
    
    /*
     * Array of topics
     */
    private static $topics_arr = array();
    
    /*
     * 
     */
    private static $otherTopic = false;
    
    /**
     * Create the subscription form
     * @return \Form
     */
    public function McSubscribeForm()
    {
        $fieldsArr = array();

        $email = new EmailField('Email', 'Email');
        $email->setValue('Your e-mail');
        array_push($fieldsArr, $email);

        if (Config::inst()->get('MailChimpController', 'country')) {
            $country = new CountryDropdownField('Country', 'Country');
            array_push($fieldsArr, $country);
        }

        if (Config::inst()->get('MailChimpController', 'topics')) {
            $topicsArr = Config::inst()->get('MailChimpController', 'topicsArr');
            $topics = new CheckboxSetField(
                            $name = "Topics", $title = "I am interested in the following topics", $topicsArr, $value = null
            );
            array_push($fieldsArr, $topics);
        }

        if (Config::inst()->get('MailChimpController', 'otherTopic')) {
            $otherTopic = new TextField('Other', '');
            array_push($fieldsArr, $otherTopic);
        }

        $fields = new FieldList($fieldsArr);
        $actions = new FieldList(
                        new FormAction('McDoSubscribeForm', 'Send Now')
        );
        $required = new RequiredFields(
                        array('Email')
        );

        $form = new Form($this, 'McSubscribeForm', $fields, $actions, $required);

        return $form;
    }

    /**
     * Process the form
     * 
     * @param type $data
     * @param type $form
     * @return type
     */
    public function McDoSubscribeForm($data, Form $form)
    {
        $country = Zend_Locale::getTranslation($data['Country'], "country", 'en_US');
        
        $topicsArr = Config::inst()->get('MailChimpController', 'topicsArr');
        $interest = array();
        $interestTxt = '';
        $i=0;
        foreach ($data['Topics'] as $id) {
            array_push($interest, $topicsArr[$id]);
            $interestTxt .= $topicsArr[$id];
            if (++$i < count($data['Topics'])) {
                $interestTxt .= ',';
            }
        }
        
        $email = $data['Email'];
        $merge_vars = array(
                'FNAME' => '',
                'LNAME' => '',
                'OTHERINT' => $data['Other'],
                'COUNTRY' => $country,
                'GROUPINGS' => array(
                        array('name' => 'Areas of Interest', 'groups' => $interestTxt),
                )
        );
        
        $regOk = self::McSubscribe($email, $merge_vars);
        if ($regOk) {

            // Pulisco la sessione 
            Session::clear('MAILCHIMP_ERRCODE');
            Session::clear('MAILCHIMP_ERRMSG');

            if (Config::inst()->get('MailChimpController', 'redirect')) {
                // Redireziono alla pagina di avvenuta registrazione
                return $this->redirect(Config::inst()->get('MailChimpController', 'redirect_ok'));
            } else {
                // Se non è definita una pagina di redirezione, rimando indietro
                return $this->redirectBack();
            }
        } else {
            // Pagina di errore
            return $this->redirect(Config::inst()->get('MailChimpController', 'redirect_ko'));
        }
    }

    /**
     * Store the user in MailChimp
     * 
     * @param String $email
     * @return Boolean
     */
    public static function McSubscribe($email, $merge_vars = array())
    {
        require_once MAILCHIMP . '/lib/MCAPI.class.php';
        $api = new MCAPI(Config::inst()->get('MailChimpController', 'apikey'));
        $retVal = $api->listSubscribe(Config::inst()->get('MailChimpController', 'listid'), $email, $merge_vars);

        // Pulisco la sessione 
        Session::clear('MAILCHIMP_ERRCODE');
        Session::clear('MAILCHIMP_ERRMSG');

        // Gestione errori
        if ($api->errorCode) {
            // Errori in sessione
            Session::set('MAILCHIMP_ERRCODE', $api->errorCode);
            Session::set('MAILCHIMP_ERRMSG', $api->errorMessage);
            trigger_error("Error subscribing: email [$email] code [$api->errorCode] msg[$api->errorMessage]", E_USER_WARNING);
        }

        return $retVal;
    }
}
