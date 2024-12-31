<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class sendWelcomeEmailController extends Controller
{
    public function sendWelcomeEmail($data) { 
        Mail::to($data['email'])->send(new WelcomeMail($data)); }
}
