<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'xero' => [
        'client_id'     => env('XERO_CLIENT_ID'),
        'client_secret' => env('XERO_CLIENT_SECRET'),
        'redirect'      => env('XERO_REDIRECT_URI'),
        'scopes'        => [
                'offline_access',
                'openid',
                'profile',
                'email',
                'accounting.contacts.read',
                'accounting.contacts',
                'accounting.transactions.read',
                'accounting.transactions',
                'accounting.reports.read',
                'accounting.settings',
                'accounting.settings.read',
                'accounting.journals.read',
        ],
    ],
    
    'cognito' => [
        'host'          => env('COGNITO_HOST'), // https://<pool>.auth.ap-southeast-2.amazoncognito.com
        'client_id'     => env('COGNITO_CLIENT_ID'),
        'client_secret' => env('COGNITO_CLIENT_SECRET'),
        'redirect'      => env('COGNITO_CALLBACK_URL'),
        'scope'         => explode(',', env('COGNITO_LOGIN_SCOPE', 'openid,email,profile')),
        'logout_uri'    => env('COGNITO_SIGN_OUT_URL'),
        'jwks_url'      => env('COGNITO_JWKS_URL'),
    ],

    'stripe' => [
        'key'    => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'textract' => [
        'key' => env('AWS_TEXTRACT_ACCESS_KEY_ID'),
        'secret' => env('AWS_TEXTRACT_SECRET_ACCESS_KEY'),
        'region' => env('AWS_REGION', 'us-east-1'),
    ],

];
