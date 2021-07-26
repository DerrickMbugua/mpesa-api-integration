<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MpesaController extends Controller
{
      /**
     * Lipa na M-PESA password
     * */
    public function lipaNaMpesaPassword()
    {
        $lipa_time = Carbon::rawParse('now')->format('YmdHms');
        $passkey = getenv('MPESA_PASSKEY');
        $BusinessShortCode = 174379;
        $timestamp =$lipa_time;
        $lipa_na_mpesa_password = base64_encode($BusinessShortCode.$passkey.$timestamp);
        return $lipa_na_mpesa_password;
    }
    /**
     * Lipa na M-PESA STK Push method
     * */
    public function stkPush()
    {
        $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer '.$this->generateAccessToken()));
        $curl_post_data = [
            //Fill in the request parameters with valid values
            'BusinessShortCode' => 174379,
            'Password' => $this->lipaNaMpesaPassword(),
            'Timestamp' => Carbon::rawParse('now')->format('YmdHms'),
            'TransactionType' => "CustomerPayBillOnline",
            'Amount' => 1,
            'PartyA' => 254715153806, // replace this with your phone number
            'PartyB' => 174379,
            'PhoneNumber' => 254715153806, // replace this with your phone number
            'CallBackURL' => "https://mydomain.com/path",
            'AccountReference' => "PrimeDEVS",
            'TransactionDesc' => "Payment of X"
        ];
        $data_string = json_encode($curl_post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        $curl_response = curl_exec($curl);
        return $curl_response;
    }

    public function generateAccessToken()
    {
        $consumer_key = getenv('MPESA_CONSUMER_KEY');
        $consumer_secret = getenv('MPESA_CONSUMER_SECRET');
        $credentials = base64_encode($consumer_key . ":" . $consumer_secret);
        $url = "https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Authorization: Basic " . $credentials));
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $curl_response = curl_exec($curl);
        $access_token = json_decode($curl_response);
        return $access_token->access_token;
    }

    public function token()
    {
        $consumer_key = getenv('MPESA_CONSUMER_KEY');
        $consumer_secret = getenv('MPESA_CONSUMER_SECRET');
        $credentials = base64_encode($consumer_key . ":" . $consumer_secret);
        $ch = curl_init('https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Basic " . $credentials));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

}
