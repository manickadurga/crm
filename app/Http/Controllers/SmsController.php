<?php

namespace App\Http\Controllers;

use App\Models\Customers;
use Illuminate\Http\Request;
use Twilio\Rest\Client;



class SmsController extends Controller
{
    public function sendSmsToCustomer($customerId){
        $customer=Customers::find($customerId);
        if(!$customer){
             return response()->json(['error'=>'Customer not found'],404);
        }
        $sid=env('TWILIO_SID');
        $token=env('TWILIO_AUTH_TOKEN');
        $twilionumber=env('TWILIO_PHONE_NUMBER');
        $client=new Client($sid,$token);
        try{
            $message=$client->messages->create(
                $customer->primary_phone,
                [
                    'from'=>$twilionumber,
                    'body'=>'Hi,' . $customer->name . 'This is Sample Message!'
                ]
                );
                return response()->json(['success'=>'Message Sent Successfully!']);
        }
        catch(\Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }
}
