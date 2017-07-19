[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Distilleries/Messenger/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Distilleries/Messenger/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Distilleries/Messenger/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Distilleries/Messenger/?branch=master)
[![Build Status](https://travis-ci.org/Distilleries/Messenger.svg?branch=master)](https://travis-ci.org/Distilleries/Messenger)
[![Total Downloads](https://poser.pugx.org/distilleries/messenger/downloads)](https://packagist.org/packages/distilleries/messenger)
[![Latest Stable Version](https://poser.pugx.org/distilleries/messenger/version)](https://packagist.org/packages/distilleries/messenger)
[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat)](LICENSE) 

# Facebook Messenger 

This repo contain some tools to work with facebook messenger bot and  laravel/lumen. This repo work on beta of facebook messenger. Some big change are coming for sure but I guess that can help you.


## Table of contents
1. [Installation For Lumen](#installation-for-lumen)
1. [Installation For Laravel](#installation-for-laravel)
1. [Create application](#create-application)
1. [Configure your json file](#configure-your-json-file)
1. [Host your application](#host-your-application)
1. [User Link](#user-link)
1. [JSON Structure](#json-structure)
    1. [Configuration](#configuration)
    1. [Start](#start)
    1. [Free input](#free-input)
    1. [Default answers](#default-answers)
    1. [Scheduled Tasks](#scheduled-tasks)
    1. [Conditions](#conditions)
    1. [Basic Workflow](#basic-workflow)
1. [Proxy](#proxy)
    1. [Implement contract](#implement-contract)
    1. [Change service provider ](#change-service-provider)




## Installation for Lumen

`composer required distilleries/messenger`



Add Service provider to `bootstrap/app.php`:

``` php

   $app->register(Distilleries\Messenger\MessengerLumenServiceProvider::class);

```


In your `Console/Kernel`, configure the scheduler:

```
$schedule->command('messenger:cron')->hourly();
```

## Installation for Laravel

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

In your `Console/Kernel`, configure the scheduler:

```
$schedule->command('messenger:cron')->hourly();
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


## Configure your json file

Create your `messenger.json`  in `storage/json/`, and configure it using the syntax explained below.
Once ready, execute

`php artisan messenger:json`

to load the configuration file in the database.


## Host your application
You have to host your application to become use it. Facebook can't send you a web hook in local. So make sure you have an hosting ready before start you development.

>Your bot is in sandobox by default. Only the people with the permission in your application can talk with it.


## User Link
You can easily link your backend user model with MessengerUser, and thus having access to all the stored input asked by the bot to the user.

In the `messenger.php` config file, do not forget to setup these values :

```
'user_link_class'   => 'App\Models\MyModel',
'user_link_field'   => 'field'
```

And in your `messenger.json` file, add an input with the name `link` to your workflow (mostlikely at the starting workflow).

Once this setup is done, the user (validated) input will be stored as a link between your backend user `user_link_class` and `MessengerUser` using the field `user_link_field`.

### example


> _messenger.php_
```
'user_link_class'   => 'App\Models\User',
'user_link_field'   => 'email'
```
> _messenger.json_
```
{
  "config": {
    "start_btn": true,
    "home_text": false
  },
  "start": {
    "text": "Can you please provide your email address, so I can check if I kwow you?",
    "input": {
      "name": "link",
      "regexpr": "^([a-z0-9_\\.-]+)@([\\da-z\\.-]+)\\.([a-z\\.]{2,6})$",
      "postback_failed": {
        "text": "This email seems not valid to me... Can you retype ?"
      },
      "postback_success": {
        "text": "I've found you in our database, cheers."
      }
    }
  },
```
The bot configured above will ask for the user's email. If this email match the regexpr, it will be saved in the database and the `MessengerUser` will be linked to the `App\Models\User` using the `App\Models\User@email` attribtue.


## JSON Structure

### Configuration
```
  "config": {
    "start_btn": true,
    "home_text": false
  },
```

- `start_btn` Whether or not the "Start" button is displayed at the first discussion.
- `home_text` You can put here the greeting text that will be displayed. (https://developers.facebook.com/docs/messenger-platform/messenger-profile/greeting-text)

### Start

`start` is the very first workflow that will be triggered at the first discussion.

```
  "start": {
  }
```

### Free input

`free` is an array of keywords that you're bot would react to.

```
  "free": [
    {
      "keywords": ["how","walk","get there"],
      "text": "It's only 5 minutes walking from the tube station."
      // YOU CAN PUT HERE AN ATTACHMENT/QUICK REPLIES/SIMPLE REPLIES INSTEAD OF THE TEXT
    },
    {
      "keywords": ["when","time"],
      "variable": "WHEN_ASKED",
      "text": "Well, it's happenning tomorrow of course!"
      // YOU CAN PUT HERE AN ATTACHMENT/QUICK REPLIES/SIMPLE REPLIES INSTEAD OF THE TEXT
    }
  ]
```

### Default answers

`default` is an array of default workflows the bot should react by default (i.e. the input is not recognized as `free` input, or the user is not within any other workflows).

This field is an array because it accepts multiple entries with conditions, like so:
```
  "default": [
    {
      "conditions": {
        "date_field": {
          "field": "trip_date",
          "type": "after"
        }
      },
      "text": "Sorry I don't understand what you mean. I hope you've enjoyed your trip!"
      // YOU CAN PUT HERE AN ATTACHMENT/QUICK REPLIES/SIMPLE REPLIES INSTEAD OF THE TEXT
    },
    {
      "conditions": {
        "date_field": {
          "field": "trip_date",
          "type": "before"
        }
      },
      "text": "Sorry I don't understand what you mean. You're approching from your awesome trip! Ask me if you have any questions."
      // YOU CAN PUT HERE AN ATTACHMENT/QUICK REPLIES/SIMPLE REPLIES INSTEAD OF THE TEXT
    }
  ]
```

### Scheduled tasks

You can schedule messages to be send later.
There is two different ways:

1. Using `date_time`:
You can send a message at the exact specified time. It will be parsed using Carbon's constructor
`new Carbon($date_time);`
```
  "cron": [
    {
      "conditions": {
        "date_time": "2017-07-12 16:55:00"
      },
      "text": "Hello, it is 16:55 today."
      // YOU CAN PUT HERE AN ATTACHMENT/QUICK REPLIES/SIMPLE REPLIES INSTEAD OF THE TEXT
    }
  ]
```

2. Using `date_time`:
You can send a message using a datetime stored in your main user model. `field` is the attribute concerned in your backend user model. It will be parsed using DateTime's modify method.
`$date_now->modify($modifier)`
```
  "cron": [
    {
      "conditions": {
        "date_field": {
          "field": "inserted_at",
          "modifier": "+1 days"
        }
      },
      "text": "Hello, I'm informing you that in one day, good things will happen"
      // YOU CAN PUT HERE AN ATTACHMENT/QUICK REPLIES/SIMPLE REPLIES INSTEAD OF THE TEXT
    }
  ]
```

In the example above, the message is sent 1 day after the `inserted_at` date of the backend user.


### Conditions

Scheduled tasks always comes with some dates conditions. But these conditions can also be applied to any kind of workflow.

* `Date field`
Use a date field located in the table of the backend user linked.
The condition is checked if the current date is after (or before) the related field of the user.
@type: "before"/"after" wheter the date must be in the future (after) or in the past (before)
@modifier: (optional) can modify the date field using DateTime’s modify method
```
      "conditions": {
        "date_field": {
          "field": "reservation_date",
          "type": "before"
        },
      "text": "Your reservation date is in the future!"
      },
```

* `User Progress`
The condition is checked if the user has registered the exact variables during other conversations with this bot.
```
      "conditions": {
        "user_progress": ['YES_I_WANT']
      },
      "text": "Well, I can see you've previously said YES to me!"
```

* `User Variables`
A list of conditions related to the backend user model.
@field: The related field in the linked model
@operator: `=`, `!=`, `>=`, `<=`, `>`, `<`
@value: the value
```
      "conditions": {
        "user_variable": [
            {
            "field" : "type",
            "operator": "=",
            "value" :"vip"
            }
        ]
      },
      "text": "Welcome to our special VIP program, enjoy!"
```

### Basic workflow

* __Simple text__
When using "text" a simple text is sent to the conversation. It can be an @array, in this case several textes will be send one after the other.

```
"text": "Hello my dear!"
```

* __Attachment / FB Templates__
Instead of a "text", you can also send a default facebook `attachment` (https://developers.facebook.com/docs/messenger-platform/send-api-reference/button-template).
`buttons` can trigger another `workflow` when the type is set to `postback`.

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


* __Inputs__
This workflow allows the user to input some variable.
The variable will be stored in the database once validated by the regexpr (not required).
You're backend can also implement `Distilleries\Messenger\Contracts\MessengerProxyContract` to be able to add several checks on the input.
If the input is valid against the regexpr, it will be passed to `MessengerProxyContract@receivedInput`. This method should return `true` to validate the input.
The input name `"link"` is a reserved key word. It will be used to bound the MessengerUser with your own BackendUser using the user input.
`postback_exists`: (optional - "link" only) if set, this workflow will be triggered if no backend user has been found using this key.
`postback_unique`: (optional) if set, this workflow will be triggered if another messenger user has registered this input.
`postback_failed_proxy`: (option) if set, this workflow will be triggered if the proxy returns false.

```
"text": "Can you please provide your email address, so I can check if I kwow you?",
"input": {
      "name": "link",
      "regexpr": "^([a-z0-9_\\.-]+)@([\\da-z\\.-]+)\\.([a-z\\.]{2,6})$",
      "postback_failed": {
        "text": "Invalid address can you retry please?"
        // YOU CAN USE AN ATTACHMENT INSTEAD OF TEXT HERE
      },
      "postback_exists": {
        "text": "I don't know this email address, are you sure I know you?"
        // YOU CAN USE AN ATTACHMENT INSTEAD OF TEXT HERE
      },
      "postback_unique": {
        "text": "You have already been registered with another facebook account"
        // YOU CAN USE AN ATTACHMENT INSTEAD OF TEXT HERE
      },
      "postback_success": {
        // ANOTHER WORKFLOW
      }
}
```

> In this example, the bot asks for the user's `email`. Because we used the reserved keyword `link` for the name, once the input has been validated (through the regexpr AND eventually your own backoffice logic), then the MessengerUser will be linked to your backend user object using this value.
Your backend user object must have been set in the `messenger.php` config file.


* __Quick replies__
This will trigger a quick reply prompt to the user (https://developers.facebook.com/docs/messenger-platform/send-api-reference/quick-replies).
`content-type`, `title` are default facebook parameters, they are directly transmitted to the fb endpoint.
`payload` is optional (a random key is generated if not set)

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

* __Free replies__
This will allow the user to reply any kind of text and would recognize keywords to trigger another workflow.
```
    {
      "text": "What is your favorite fruit ?",
      "replies": [
        {
          "keywords": ["apple", "orange", "watermelon"],
          "text": "These are the best for summer time!"
        },
        {
          "keywords": ["banana", "mango", "passion", "litchi"],
          "text": "Well... Obviously you've got a exotic taste"
        }
      ]
    }
```

* __Store progress variables__
At any workflow, you can use the `variable` option. It will store this variable in the database once the discussion has reach that level in the worflow.
```
    {
      "text": "What is your favorite fruit ?",
      "replies": [
        {
          "keywords": ["apple", "orange", "watermelon"],
          "text": "These are the best for summer time!",
          "variable": "ROUND_SHAPED_FRUITS"
        },
        {
          "keywords": ["banana", "mango", "passion", "litchi"],
          "text": "Well... Obviously you've got a exotic taste",
          "variable": "EXOTIC_FRUITS"
        }
      ]
    }
```

## Proxy

A proxy contract can be overriden to implements your own logic and to intercept events.


### Implement contract
To easily implement this fonctionality for your application I created a `Distilleries\Messenger\Contracts\MessengerProxyContract`.


| Method | Description | Return |
| ------ | ------------| ------------|
| receivedInput | Callback when an input is received. | `true` if the input is valid, `false` if not (default `true`)|
| userHasBeenLinked | Callback when the user backend has been linked to the messenger user | - |
| getPlaceholdersArray | List of placeholders and their related values | An array of key/value `@key` is the placeholder string `@value` is a function($messengerUser, $backendUser) that returns the value|
| variableCreated | Callback when a variable is saved | - |



### Change service provider


To change the class use go to `app/Providers/AppServiceProvider.php` and change the class inside the share function.

```php

$this->app->singleton('Distilleries\Messenger\Contracts\MessengerProxyContract', function ($app) {
    return new MyMessengerProxy();
});

```

