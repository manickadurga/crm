<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send all email
    | messages unless another mailer is explicitly specified when sending
    | the message. All additional mailers can be configured within the
    | "mailers" array. Examples of each type of mailer are provided.
    |
    */

    'default' => env('MAIL_MAILER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the mailers used by your application plus
    | their respective settings. Several examples have been configured for
    | you and you are free to add your own as your application requires.
    |
    | Laravel supports a variety of mail "transport" drivers that can be used
    | when delivering an email. You may specify which one you're using for
    | your mailers below. You may also add additional mailers if needed.
    |
    | Supported: "smtp", "sendmail", "mailgun", "ses", "ses-v2",
    |            "postmark", "log", "array", "failover", "roundrobin"
    |
    */
    
       'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
<<<<<<< HEAD
            'host' => env('MAIL_HOST', 'smtp.gmail.com'), // Use Outlook SMTP server
            'port' => env('MAIL_PORT', 587), // Outlook SMTP port
            'encryption' => env('MAIL_ENCRYPTION', 'tls'), // Use TLS encryption
            'username' => env('MAIL_USERNAME', 'sukasini2001@gmail.com'), // Your Outlook email address
            'password' => env('MAIL_PASSWORD', 'yydltuacnazwlzyz'), // Your Outlook email password
=======
            'host' => env('MAIL_HOST', 'smtp.gmail.com'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME','mukilaraj1007@gmail.com'),
            'password' => env('MAIL_PASSWORD','qrwtlvidioubfqbc'),
>>>>>>> 68e4740 (Issue -#35)
            'timeout' => null,
            'local_domain'=>env('MAIL_EHLO_DOMAIN'),
            
        ],
    ],


    

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
            // 'message_stream_id' => env('POSTMARK_MESSAGE_STREAM_ID'),
            // 'client' => [
            //     'timeout' => 5,
            // ],
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
        ],

        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers' => [
                'ses',
                'postmark',
            ],
        ],

    

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | You may wish for all emails sent by your application to be sent from
    | the same address. Here you may specify a name and address that is
    | used globally for all emails that are sent by your application.
    |
    */
    'from' => [
<<<<<<< HEAD
        'address' => env('MAIL_FROM_ADDRESS', 'sukasini2001@gmail.com'), // Your Outlook email address
        'name' => env('MAIL_FROM_NAME', 'SUKASINI'), // Your Name or Your App Name
    ],
];
=======
        'address' => env('MAIL_FROM_ADDRESS', 'mukilaraj1007@gmail.com'),
        'name' => env('MAIL_FROM_NAME', 'mukila'),
    ],

];
>>>>>>> 68e4740 (Issue -#35)
