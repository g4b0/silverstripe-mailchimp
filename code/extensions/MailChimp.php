<?php

class MailChimp extends DataExtension
{
    
    /**
     * Recupera il Form dal controller
     * @return Form
     */
    public function McSubscribeForm()
    {
        $controller = new MailChimpController();
        return $controller->McSubscribeForm();
    }
}
