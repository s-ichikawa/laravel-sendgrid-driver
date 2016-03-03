Laravel SendGrid Driver
====

[![Build Status](https://scrutinizer-ci.com/g/s-ichikawa/laravel-sendgrid-driver/badges/build.png?b=master)](https://scrutinizer-ci.com/g/s-ichikawa/laravel-sendgrid-driver/build-status/master)

A Mail Driver with support for Sendgrid Web API, using the original Laravel API.
This library extends the original Laravel classes, so it uses exactly the same methods.

To use this package required your [Sendgrid Api Key](https://sendgrid.com/docs/User_Guide/Settings/api_keys.html).
Please make it [Here](https://app.sendgrid.com/settings/api_keys).

#Install (Laravel5.1~)

Add the package to your composer.json and run composer update.
```json
"require": {
    "s-ichikawa/laravel-sendgrid-driver": "dev-master"
},
```

or installed with composer
```
$ composer require s-ichikawa/laravel-sendgrid-driver:dev-master
```

Remove the default service provider and add the sendgrid service provider in app/config/app.php:
```php
'providers' => [
//  Illuminate\Mail\MailServiceProvider::class,

    Sichikawa\LaravelSendgridDriver\MailServiceProvider::class,
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

Remove the default service provider and add the sendgrid service provider in app/config/app.php:
```php
'providers' => [
//  'Illuminate\Mail\MailServiceProvider',

    'Sichikawa\LaravelSendgridDriver\MailServiceProvider',
];
```

#Configure

.env
```
MAIL_DRIVER=sendgrid
SENDGRID_API_KEY='YOUR_SENDGRID_API_KEY'
```

config/service.php
```
    'sendgrid' => [
        'api_key' => env('SENDGRID_API_KEY')
    ]
```

#Use SMTP API

Sendgrid's [SMTP API](https://sendgrid.com/docs/API_Reference/SMTP_API/index.html) is so cool feature.
This function can use by setting embed data to message.
and, set 'sendgrid/x-smtpapi' to data name or content-type.

```
\Mail::send('view', $data, function (Message $message) {
    $message
        ->to('foo@example.com', 'foo_name')
        ->from('bar@example.com', 'bar_name')
        ->embedData([
            'category' => 'user_group1',
            'unique_args' => [
                'user_id' => 123
            ]
        ], 'sendgrid/x-smtpapi');
});
```