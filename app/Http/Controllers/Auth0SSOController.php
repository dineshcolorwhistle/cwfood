<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class Auth0SSOController extends Controller
{
    public static function createtoken()
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://'.env("AUTH0_DOMAIN").'/oauth/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{"client_id":"'.env("AUTH0_CLIENT_ID").'","client_secret":"'.env("AUTH0_CLIENT_SECRET").'","audience":"'.env("AUTH0_AUDIENCE").'","grant_type":"client_credentials"}',
            CURLOPT_HTTPHEADER => array(
            'Content-Type:application/json'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $data = json_decode($response);
        return $data->access_token;
    }

    public static function getUserByID($data)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://'.env("AUTH0_DOMAIN").'/api/v2/users/'.$data->UserID,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization:Bearer '.Auth0SSOController::createtoken()
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        echo $response;
    }

    public static function getAllAuth0Users()
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://'.env("AUTH0_DOMAIN").'/api/v2/users',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization:Bearer '.Auth0SSOController::createtoken()
            ),
        ));
        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        $result['response'] = json_decode($response, true);;
        $result['httpcode'] = $httpcode;
        return $result;
    }


    public static function createUsers($data)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://'.env("AUTH0_DOMAIN").'/api/v2/users',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
            "email": "'.$data["email"].'",
            "user_metadata": {},
            "blocked": false,
            "name": "'.$data["name"].'",
            "email_verified": true,
            "connection": "email",  
            "verify_email": true
        }',
        CURLOPT_HTTPHEADER => array(
            'Authorization:Bearer '.Auth0SSOController::createtoken(),
            'Content-Type:application/json'
        ),
        ));
        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        $result['response'] = json_decode($response, true);;
        $result['httpcode'] = $httpcode;
        return $result;
    }

    public static function deleteUsers($id)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://'.env("AUTH0_DOMAIN").'/api/v2/users/'.$id,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'DELETE',
        CURLOPT_HTTPHEADER => array(
            'Authorization:Bearer '.Auth0SSOController::createtoken(),
            'Content-Type:application/json'
        ),
        ));
        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err = curl_error($curl); 
        curl_close($curl);      
        if ($err) {
            return false;
        } else {
            return true;
        }
    }

    public static function create_passwordchange_token($id)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
          CURLOPT_URL => "https://".env('AUTH0_DOMAIN')."/api/v2/tickets/password-change",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS =>'{
            "result_url": "'.env('APP_URL').'/callback",
            "user_id": "'.$id.'",
            "ttl_sec": 0,
            "mark_email_as_verified": false,
            "includeEmailInRedirect": false
            }',
          CURLOPT_HTTPHEADER => [
            'Authorization:Bearer '.Auth0SSOController::createtoken(),
            "content-type:application/json"
          ],
        ]);
        $response = curl_exec($curl);
        $ticket = json_decode($response, true);
        curl_close($curl);
        $err = curl_error($curl);       
        if ($err) {
            return false;
        } else {
            return $ticket['ticket'];
        }
    }

    
}