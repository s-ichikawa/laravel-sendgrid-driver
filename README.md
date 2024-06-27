Laravel SendGrid Driver
====

[![SymfonyInsight](https://insight.symfony.com/projects/8955bc55-16f6-4ac9-8203-1cdce3d209a8/mini.svg)](https://insight.symfony.com/projects/8955bc55-16f6-4ac9-8203-1cdce3d209a8)
[![Build Status](https://scrutinizer-ci.com/g/s-ichikawa/laravel-sendgrid-driver/badges/build.png?b=master)](https://scrutinizer-ci.com/g/s-ichikawa/laravel-sendgrid-driver/build-status/master)

A Mail Driver with support for Sendgrid Web API, using the original Laravel API.
This library extends the original Laravel classes, so it uses exactly the same methods.

To use this package required your [Sendgrid Api Key](https://sendgrid.com/docs/User_Guide/Settings/api_keys.html).
Please make it [Here](https://app.sendgrid.com/settings/api_keys).


### Compatibility

| Laravel   | laravel-sendgrid-driver |
|-----------| ---- |
| 9, 10, 11 | ^4.0 |
| 7, 8      | ^3.0 |
| 5, 6      | ^2.0 |

# Install (for [Laravel](https://laravel.com/))

Add the package to your composer.json and run composer update.
```json
"require": {
    "s-ichikawa/laravel-sendgrid-driver": "^4.0"
},
```

or installed with composer
```
$ composer require s-ichikawa/laravel-sendgrid-driver
```

# Install (for [Lumen](https://lumen.laravel.com/))

Add the package to your composer.json and run composer update.
```json
"require": {
    "s-ichikawa/laravel-sendgrid-driver": "^4.0"
},
```

or installed with composer
```bash
$ composer require "s-ichikawa/laravel-sendgrid-driver"
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
```php
<?php
return [
    'driver' => env('MAIL_DRIVER', 'sendgrid'),
];
```

## Configure

.env
```
MAIL_DRIVER=sendgrid
SENDGRID_API_KEY='YOUR_SENDGRID_API_KEY'
# Optional: for 7+ laravel projects
MAIL_MAILER=sendgrid 
```

config/services.php (In using lumen, require creating config directory and file.)
```php
    'sendgrid' => [
        'api_key' => env('SENDGRID_API_KEY'),
    ],
```

config/mail.php
```php
    'mailers' => [
        'sendgrid' => [
            'transport' => 'sendgrid',
        ],
    ],
```

### endpoint config
If you need to set custom endpoint, you can set any endpoint by using `endpoint` key.
For example, calls to SendGrid API through a proxy, call endpoint for confirming a request.
```php
    'sendgrid' => [
        'api_key' => env('SENDGRID_API_KEY'),
        'endpoint' => 'https://custom.example.com/send',
    ],
```

## How to use

Every request made to /v3/mail/send will require a request body formatted in JSON containing your email’s content and metadata.
Required parameters are set by Laravel's usually mail sending, but you can also use useful features like "categories" and "send_at".

more info
https://www.twilio.com/docs/sendgrid/api-reference/mail-send/mail-send

Laravel 10, 11:
```php
<?
use Sichikawa\LaravelSendgridDriver\SendGrid;

class SendGridSample extends Mailable
{
    use SendGrid;
    
    public function envelope(): Envelope
    {
        $this->sendgrid([
                'personalizations' => [
                    [
                        'to' => [
                            ['email' => 'to1@gmail.com', 'name' => 'to1'],
                            ['email' => 'to2@gmail.com', 'name' => 'to2'],
                        ],
                        'cc' => [
                            ['email' => 'cc1@gmail.com', 'name' => 'cc1'],
                            ['email' => 'cc2@gmail.com', 'name' => 'cc2'],
                        ],
                        'bcc' => [
                            ['email' => 'bcc1@gmail.com', 'name' => 'bcc1'],
                            ['email' => 'bcc2@gmail.com', 'name' => 'bcc2'],
                        ],
                    ],
                ],
                'categories' => ['user_group1'],
            ]);
        return new Envelope(
            from:    'from@example.com',
            replyTo: 'reply@example.com',
            subject: 'example',
        );
    }
}
```

Laravel 9:
```php
<?
use Sichikawa\LaravelSendgridDriver\SendGrid;

class SendGridSample extends Mailable
{
    use SendGrid;
    
    public function build():
    {
        return $this
            ->view('template name')
            ->subject('subject')
            ->from('from@example.com')
            ->to(['to@example.com'])
            ->sendgrid([
                'personalizations' => [
                    [
                        'to' => [
                            ['email' => 'to1@gmail.com', 'name' => 'to1'],
                            ['email' => 'to2@gmail.com', 'name' => 'to2'],
                        ],
                        'cc' => [
                            ['email' => 'cc1@gmail.com', 'name' => 'cc1'],
                            ['email' => 'cc2@gmail.com', 'name' => 'cc2'],
                        ],
                        'bcc' => [
                            ['email' => 'bcc1@gmail.com', 'name' => 'bcc1'],
                            ['email' => 'bcc2@gmail.com', 'name' => 'bcc2'],
                        ],
                    ],
                ],
                'categories' => ['user_group1'],
            ]);
    }
}
```
## Using Template Id

Illuminate\Mailer has generally required a view file.
But in case of using template id, set an empty array at view function.

Laravel 10, 11:
```php
<?
    public function envelope(): Envelope
    {
        $this->sendgrid([
            'personalizations' => [
                [
                    'dynamic_template_data' => [
                        'title' => 'Subject',
                        'name'  => 's-ichikawa',
                    ],
                ],
            ],
            'template_id' => config('services.sendgrid.templates.dynamic_template_id'),
        ]);
        return new Envelope(
            from:    'from@example.com',
            replyTo: 'reply@example.com',
            subject: 'example',
        );
    }
```

Laravel 9:
```php
<?
    public function build():
    {
        return $this
            ->view('template name')
            ->subject('subject')
            ->from('from@example.com')
            ->to(['to@example.com'])
            ->sendgrid([
                'personalizations' => [
                    [
                        'dynamic_template_data' => [
                            'title' => 'Subject',
                            'name'  => 's-ichikawa',
                        ],
                    ],
                ],
                'template_id' => config('services.sendgrid.templates.dynamic_template_id'),
            ]);
    }
```
