Laravel SendGrid Driver
====

このパッケージはLaravelにSendGrid WebAPIでメールを送信するためのAPIドライバーを追加できます。
インストールと設定を行うだけですぐに使いはじめる事ができます。


※このパッケージを使用するためには[SendGridのAPI KEY](https://sendgrid.com/docs/User_Guide/Settings/api_keys.html)が必要となります。
下記URLよりご自身の発行をしてください。
https://app.sendgrid.com/settings/api_keys

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

