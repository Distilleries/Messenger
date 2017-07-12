[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Distilleries/Messenger/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Distilleries/Messenger/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Distilleries/Messenger/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Distilleries/Messenger/?branch=master)
[![Build Status](https://travis-ci.org/Distilleries/Messenger.svg?branch=master)](https://travis-ci.org/Distilleries/Messenger)
[![Total Downloads](https://poser.pugx.org/distilleries/messenger/downloads)](https://packagist.org/packages/distilleries/messenger)
[![Latest Stable Version](https://poser.pugx.org/distilleries/messenger/version)](https://packagist.org/packages/distilleries/messenger)
[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat)](LICENSE) 

# Facebook Messenger 

This repo contain some tools to work with facebook messenger bot and  laravel/lumen. This repo work on beta of facebook messenger. Some big change are coming for sure but I guess that can help you.


## Table of contents
1. [Installation](#installation)
1. [Implement contract](#implement-contract)
1. [Change service provider ](#change-service-provider)
1. [Example of MessengerContract implementation](#example-of-messengercontract-implementation)
1. [Create application](#create-application)
1. [Host your application](#host-your-application)
1. [Facade](#facade)
    1. [sendTextMessage](#sendtextmessage)
    1. [sendImageMessage](#sendimagemessage)
    1. [getCurrentUserProfile](#getcurrentuserprofile)
    1. [sendCard](#sendcard)
    1. [persistMenu](#persistmenu)
1. [Example](#example)




##Installation for Laravel

`composer required distilleries/messenger`



Add Service provider to `bootstrap/app.php`:

``` php
   
   $app->register(Distilleries\Messenger\MessengerLumenServiceProvider::class);
   
```


##Installation for Lumen

`composer required distilleries/messenger`




Add Service provider to `config/app.php`:

``` php
    'providers' => [
          \Distilleries\Messenger\MessengerServiceProvider::class,
    ]
```

And Facade (also in `config/app.php`) replace the laravel facade `Mail`
   

``` php
    'aliases' => [
       'Messenger'           => 'Distilleries\Messenger\Facades\Messenger'
    ]
```


## Implement contract
To easily implement the fonctionality for your application I created a `Distilleries\Messenger\Contracts\MessengerReceiverContract`.


| Event | Method | Description | 
| ----- | ------ | ------------|
| messaging_optins | receivedAuthentication | Subscribes to Authentication Callback via the Send-to-Messenger Plugin |
| message | receivedMessage | Subscribes to Message Received Callback |
| message_deliveries | receivedDeliveryConfirmation | Subscribes to Message Delivered Callback |
| messaging_postbacks | receivedPostback | Subscribes to Postback Received Callback |
| all other | defaultHookUndefinedAction | Call when the other methods was no called |



## Change service provider 


To change the class use go to `app/Providers/MessengerServiceProvider.php` and change the class inside the share function.

```php
    
    $this->app->singleton('Distilleries\Messenger\Contracts\MessengerReceiverContract', function ($app) {
                return new MyMessengerClass();
    });
      
```

### Example of MessengerContract implementation

```php

class MyMessengerClass implements MessengerContract
{

  
    public function receivedAuthentication($event)
    {
        $senderID    = $event->sender->id;
        Messenger::sendTextMessage($senderID, "Authentication successful");
    }


    public function receivedMessage($event)
    {
        $senderID    = $event->sender->id;
        Messenger::sendTextMessage($senderID, 'Test');
        Messenger::sendImageMessage($senderID, env('APP_URL') . '/assets/images/logo.png');,
        Messenger::sendCard($senderID, [
            'template_type' => 'generic',
            'elements'      => [
                [
                    "title"     => "Messenger Boilerplate",
                    "image_url" => env('APP_URL') . '/assets/images/logo.png',
                    "subtitle"  => "example subtitle",
                    'buttons'   => [
                        [
                            'type'  => "web_url",
                            'url'   => "https://github.com/Distilleries/lumen-messenger-boilerplate",
                            'title' => "Come download it!"
                        ]
                    ]
                ]

            ]
        ]);
  
    }

    public function receivedDeliveryConfirmation($event)
    {
        $senderID    = $event->sender->id;
        Messenger::sendTextMessage($senderID, 'Test');
    }


    public function receivedPostback($event)
    {
       $senderID       = $event->sender->id;
       Messenger::sendTextMessage($senderID, 'Test');
    }

}
```

## Create application
Follow the messenger documentation to create the app [https://developers.facebook.com/docs/messenger-platform/quickstart](https://developers.facebook.com/docs/messenger-platform/quickstart).

* For the webhook uri use `/webhook`
* For the `VALIDATION_TOKEN`, generate a random key


After the application created and the page created and associated copy the `.env.example` to `.env`

```
    VALIDATION_TOKEN=
    PAGE_ACCESS_TOKEN=
```


## Host your application
You have to host your application to become use it. Facebook can't send you a web hook in local. So make sure you have an hosting ready before start you development.

>Your bot is in sandobox by default. Only the people with the permission in your application can talk with it.


## Facade

### sendTextMessage

[Officiale documention](https://developers.facebook.com/docs/messenger-platform/send-api-reference/text-message)

 ```php 
    Messenger::sendTextMessage($senderID, "Authentication successful");
 ```
 
### sendImageMessage


[Officiale documention](https://developers.facebook.com/docs/messenger-platform/send-api-reference/image-attachment)

  ```php 
    Messenger::sendImageMessage($senderID, env('APP_URL') . '/assets/images/logo.png');
    
  ``` 
### getCurrentUserProfile

[Officiale documention](https://developers.facebook.com/docs/messenger-platform/user-profile)


  ```php 
    Messenger::getCurrentUserProfile($senderID);
    
  ```
  
### sendCard

[Officiale documention](https://developers.facebook.com/docs/messenger-platform/send-api-reference/file-attachment)

  
 ```php
         Messenger::sendCard($senderID, [
              'template_type' => 'generic',
              'elements'      => [
                  [
                      "title"     => "Messenger Boilerplate",
                      "image_url" => env('APP_URL') . '/assets/images/logo.png',
                      "subtitle"  => "example subtitle",
                      'buttons'   => [
                          [
                              'type'  => "web_url",
                              'url'   => "https://github.com/Distilleries/lumen-messenger-boilerplate",
                              'title' => "Come download it!"
                          ]
                      ]
                  ]
  
              ]
          ]);
 ```
 

## Example
On this messenger class you can say `hi` and the bot give you an answer like this :

Hi First name Last name

Send a picture with a picto on the bottom right`

I customize your profile picture. Do you like it?



## JSON Structure

```
  "config": {
    "start_btn": true,
    "home_text": false
  },
```

- `start_btn` Whether or not the "Start" button is displayed at the first discussion.
- `home_text` You can put here the greeting text that will be displayed. (https://developers.facebook.com/docs/messenger-platform/messenger-profile/greeting-text)

```
  "start": {
  }
```

- `start` is the very first workflow that will be triggered at the first discussion.

Workflow :

```
"text"
```
When using "text" a simple text is sent to the conversation. It can be an @array, in this case several textes will be send one after the other.


```
  "attachment": {
    "type": "template",
    "payload": {
      "template_type": "button",
      "text": "Here are the link you are looking for:",
      "buttons": [
        {
          "type": "web_url",
          "url": "https://github.com/Distilleries/Messenger",
          "title": "Awesome link",
          "webview_height_ratio": "full"
        },
       {
         "type":"postback",
         "title":"Start Chatting",
         "payload":"USER_DEFINED_PAYLOAD",
         "postback" : {
                // ANOTHER WORKFLOW
         }
       }
      ]
    }
  }
```

Instead of a "text", you can also send a default facebook `attachment` (https://developers.facebook.com/docs/messenger-platform/send-api-reference/button-template).
`buttons` can trigger another `workflow` when the type is set to `postback`.


```
"text"
"input": {
      "name": "link",
      "regexpr": "^([a-z0-9_\\.-]+)@([\\da-z\\.-]+)\\.([a-z\\.]{2,6})$",
      "postback_failed": {
        "text": "Invalid address can you retry please?"
        // YOU CAN USE AN ATTACHMENT INSTEAD OF TEXT HERE
      },
      "postback_success": {
        // ANOTHER WORKFLOW
      }
}
```
This workflow allows the user to input some variable.
The variable will be stored in the database once validated by the regexpr (not required).
You're backend can also implement `Distilleries\Messenger\Contracts\MessengerProxyContract` to be able to add several checks on the input.
If the input is valid against the regexpr, it will be passed to `MessengerProxyContract@receivedInput`. This method should return `true` to validate the input.

The input name `"link"` is a reserved key word. It will be used to bound the MessengerUser with your own BackendUser using the user input.
In this example, the bot asks for the user's `email`. Because we used the reserved keyword `link` for the name, once the input has been validated (through the regexpr AND eventually your own backoffice logic), then the MessengerUser will be linked to your backend user object using this value.
Your backend user object must have been set in the `messenger.php` config file.



```
"text"
"quick_replies": [{
            "content_type": "text",
            "title": "Yes I do",
            "payload": "YES_RECEIVE",
            "postback": {
                // ANOTHER WORKFLOW
            }
        },
          {
            "content_type": "text",
            "title": "No I don't",
            "postback": {
                // ANOTHER WORKFLOW
            }
          }]
```

This will trigger a quick reply prompt to the user (https://developers.facebook.com/docs/messenger-platform/send-api-reference/quick-replies).
`content-type`, `title` are default facebook parameters, they are directly transmitted to the fb endpoint.
`payload` is optional (a random key is generated if not set)
