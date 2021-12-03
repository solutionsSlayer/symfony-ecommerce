<?php

namespace App\Classe;

use MailchimpTransactional;

class Mail
{
    public $mailChimp;

    public function __construct()
    {
        $this->mailChimp = new MailchimpTransactional\ApiClient();
    }

    public function pingMailchimp()
    {
        $this->mailChimp->setApiKey($_SERVER['MAILCHIMP_API_KEY']);
        return $this->mailChimp->users->ping();
    }
}