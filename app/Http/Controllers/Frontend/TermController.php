<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\FrontendController;

class TermController extends FrontendController
{
    public function __construct()
    {
        parent::__construct();
        $this->data['site_title'] = 'Şartlar ve Koşullar';
    }

    public function __invoke()
    {
        return view('frontend.terms', $this->data);
    }
}
