Laravel SendGrid Driver
====

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/4232643f-006c-473b-97ff-d0f67fa497ee/big.png)](https://insight.sensiolabs.com/projects/4232643f-006c-473b-97ff-d0f67fa497ee)
[![Build Status](https://scrutinizer-ci.com/g/s-ichikawa/laravel-sendgrid-driver/badges/build.png?b=master)](https://scrutinizer-ci.com/g/s-ichikawa/laravel-sendgrid-driver/build-status/master)

A Mail Driver with support for Sendgrid Web API, using the original Laravel API.
This library extends the original Laravel classes, so it uses exactly the same methods.

To use this package required your [Sendgrid Api Key](https://sendgrid.com/docs/User_Guide/Settings/api_keys.html).
Please make it [Here](https://app.sendgrid.com/settings/api_keys).

#Notification

If your project using guzzlehttp/guzzle 6.2.0 or less, you can use version [1.0.0](https://github.com/s-ichikawa/laravel-sendgrid-driver/tree/1.0.0)
But the old version has [security issues](https://github.com/guzzle/guzzle/releases/tag/6.2.1), 

#Install (Laravel5.1~)

Add the package to your composer.json and run composer update.
```json
"require": {
    "s-ichikawa/laravel-sendgrid-driver": "^1.1"
},
```

or installed with composer
```
$ composer require s-ichikawa/laravel-sendgrid-driver
```

Add the sendgrid service provider in config/app.php:
```php
'providers' => [
    Sichikawa\LaravelSendgridDriver\SendgridTransportServiceProvider::class,
];
```

#Install (Laravel5.0)

Add the package to your composer.json and run composer update.
```json
"require": {
    "s-ichikawa/laravel-sendgrid-driver": "5.0.x-dev"
},
```

or installed with composer
```
$ composer require s-ichikawa/laravel-sendgrid-driver:5.0.x-dev
```

Remove the default service provider and add the sendgrid service provider in config/app.php:
```php
'providers' => [
//  'Illuminate\Mail\MailServiceProvider',

    'Sichikawa\LaravelSendgridDriver\MailServiceProvider',
];
```

# Install (Lumen)

Add the package to your composer.json and run composer update.
```json
"require": {
    "s-ichikawa/laravel-sendgrid-driver": "^1.1"
},
```

or installed with composer
```
$ composer require s-ichikawa/laravel-sendgrid-driver
```

Add the sendgrid service provider in bootstrap/app.php
```php
$app->configure('mail');
$app->configure('services');
$app->register(Sichikawa\LaravelSendgridDriver\MailServiceProvider::class);

unset($app->availableBindings['mailer']);
```

Create mail config files.
config/mail.php
```
<?php
return [
    'driver' => env('MAIL_DRIVER', 'sendgrid'),
];
```

#API v3

##Configure

.env
```
MAIL_DRIVER=sendgrid
SENDGRID_API_KEY='YOUR_SENDGRID_API_KEY'
```

config/services.php (In using lumen, require creating config directory and file.)
```
    'sendgrid' => [
        'api_key' => env('SENDGRID_API_KEY'),
        'version' => 'v3',
    ],
```

##Request Body Parameters

Every request made to /v3/mail/send will require a request body formatted in JSON containing your email’s content and metadata.
Required parameters are set by Laravel's usually mail sending, but you can also use useful features like "categories" and "send_at".

```
\Mail::send('view', $data, function (Message $message) {
    $message
        ->to('foo@example.com', 'foo_name')
        ->from('bar@example.com', 'bar_name')
        ->embedData([
            'categories' => ['user_group1'],
            'send_at' => $send_at->getTimestamp(),
        ], 'sendgrid/x-smtpapi');
});
```

more info
https://sendgrid.com/docs/API_Reference/Web_API_v3/Mail/index.html#-Request-Body-Parameters

#API v2

##Configure

.env
```
MAIL_DRIVER=sendgrid
SENDGRID_API_KEY='YOUR_SENDGRID_API_KEY'
```

config/services.php (In using lumen, require creating config directory and file.)
```
    'sendgrid' => [
        'api_key' => env('SENDGRID_API_KEY')
    ],
```

#Use SMTP API

Sendgrid's [SMTP API](https://sendgrid.com/docs/API_Reference/SMTP_API/index.html) is a very handy feature.

To use this 'sendgrid/x-smtpapi' functionality, use our embedData() function.

##API v2

```
\Mail::send('view', $data, function (Message $message) {
    $message
        ->to('foo@example.com', 'foo_name')
        ->from('bar@example.com', 'bar_name')
        ->embedData([
            'to' => ['user1@example.com', 'user2@example.com'],
            'sub' => [
                '-email-' => ['user1@example.com', 'user2@example.com'],
            ],
            'category' => 'user_group1',
            'unique_args' => [
                'user_id' => 123
            ]
        ], 'sendgrid/x-smtpapi');
});
```

##API v3

```
\Mail::send('view', $data, function (Message $message) {
    $message
        ->to('foo@example.com', 'foo_name')
        ->from('bar@example.com', 'bar_name')
        ->replyTo('foo@bar.com', 'foobar');
        ->embedData([
            'personalizations' => [
                [
                    'to' => [
                        'email' => 'user1@example.com',
                        'name' => 'user1',
                    ],
                    'substitutions' => [
                        '-email-' => 'user1@example.com',
                    ],
                ],
                [
                    'to' => [
                        'email' => 'user2@example.com',
                        'name' => 'user2',
                    ],
                    'substitutions' => [
                        '-email-' => 'user2@example.com',
                    ],
                ],
            ],
            'categories' => ['user_group1'],
            'custom_args' => [
                'user_id' => "123" // Make sure this is a string value
            ]
        ], 'sendgrid/x-smtpapi');
});
```

- custom_args values have to be strings. Sendgrid API gives a non-descriptive error message when you enter non-string values.

## Difference v2 vs v3

Have a look at '[How to migrate](https://sendgrid.com/docs/Classroom/Send/v3_Mail_Send/how_to_migrate_from_v2_to_v3_mail_send.html)' for more information on the difference in parameters.




