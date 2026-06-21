<?php

namespace App\Support;

class BankManager
{
    // =========================================
    // LOAD BANKS
    // =========================================
    public function loadBanks(
        string $country
    ): array {

        $message = null;
        $data    =  [];
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/bank?country=$country",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer {$_ENV['PAYSTACK_SECRET_KEY']}",
                "Cache-Control: no-cache",
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        if ($err) {
            $message = 'API or server error occurred';
        }
        else{
            $message = 'Banks fetched successfully';
            $result  = json_decode($response, true);
            $data    = $result['data'];
        }

        return [ 'message' => $message, 'data' => $data ];
    }
}
