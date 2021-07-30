<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\MpesaTransaction;
use Illuminate\Support\Facades\Log;
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
        $timestamp = $lipa_time;
        $lipa_na_mpesa_password = base64_encode($BusinessShortCode . $passkey . $timestamp);
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
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $this->generateAccessToken()));
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
    /**
     * Business To Customer (B2C)
     * */
    public function b2cRequest()
    {
        $ch = curl_init('https://sandbox.safaricom.co.ke/mpesa/b2c/v1/paymentrequest');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $this->generateAccessToken()));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
            "InitiatorName" => "Derrick mbugua",
            "SecurityCredential" => "jmQgwp14uBOT1O2iaqnlKcr4KQj6duEmSMSt5wPzuWnYbmV1whQXuXN4+977bNaaVlZN8lQQnQ85xmPshml4NVfe6XIPzx/H8SH3kUMPSK8tFIwDA5x9RiCOqkKBGoOm8D5CKnZp67nFqGcXspk8VkxJHVjPg4sXvXynIUCG6aqjEF78ZoZVZAedpIr7eXCgd24RsOx/5nEQJ8zYZqkcssXk1U6ZoxU7vrQm8XGWhYAWMzhtlwPVdi29SAkE/vzubjlisEQJPNgRVqMlbYSViGB8WRHJTah+BF7rtoSIB/7+/s//jX99EJKqyVO3F/AVYlGvZxDqvJ3BAUEbCJuw8Q==",
            "CommandID" => "SalaryPayment",
            "Amount" => 1,
            "PartyA" => 600980,
            "PartyB" => 254715153806,
            "Remarks" => "Test remarks",
            "QueueTimeOutURL" => "https://mydomain.com/b2c/queue",
            "ResultURL" => "https://mydomain.com/b2c/result",
            "Occassion" => "Good job",
        )));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response     = curl_exec($ch);
        curl_close($ch);
        echo $response;
    }

    /**
     * Generate access token
     * */

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

    /**
     * Generate access token
     * */

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

    /**
     *  M-pesa Validation Method
     * Safaricom will only call your validation if you have requested by writing an official letter to them
     */
    public function mpesaValidation(Request $request)
    {
        Log::info("Validation endpoint hit");
        Log::info($request->all());
    }
    /**
     * M-pesa Transaction confirmation method, we save the transaction in our databases
     */
    public function mpesaConfirmation(Request $request)
    {
        Log::info("Confirmation endpoint hit");
        Log::info($request->all());
    }

    /**
     * M-pesa Register Validation and Confirmation method
     */
    public function mpesaRegisterUrls()
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://sandbox.safaricom.co.ke/mpesa/c2b/v1/registerurl');
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization: Bearer ' . $this->generateAccessToken()));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(array(
            'ShortCode' => "600983",
            'ResponseType' => 'Completed',
            'ConfirmationURL' => "https://186addcf06ed.ngrok.io/api/v1/confirmation",
            'ValidationURL' => "https://186addcf06ed.ngrok.io/api/v1/validation"
        )));
        $curl_response = curl_exec($curl);
        return $curl_response;
    }

    /**
     * M-pesa Register URLS
     */
    public function registerUrls()
    {
        $ch = curl_init('https://sandbox.safaricom.co.ke/mpesa/c2b/v1/registerurl');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization: Bearer ' . $this->generateAccessToken()));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
            "ShortCode" => "600983",
            "ResponseType" => "Completed",
            "ConfirmationURL" => "https://186addcf06ed.ngrok.io/api/v1/confirmation",
            "ValidationURL" => "https://186addcf06ed.ngrok.io/api/v1/validation",
        )));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response     = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}
