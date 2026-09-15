# WolfDispatcher

WolfDispatcher is an incredibly simple, minimalistic and fast layer of abstraction over the Telegram API that will allow you to develop your interactive Telegram bots with any functionality at an incredible speed, without thinking about the low-level things they will do. Also, maximum attention was paid to the standardization of data formats with which you can work in the process of implementing your bot and the ease of deploying it to production. With the help of WolfDispatcher, you can implement your bot functionality as a portable on-premise solution based on the YALF framework or also, you can use just two tiny libraries to left alone with your great code and awesome ideas.

# Requirements

* PHP 5.3.28+ (PHP 7.4 and 8.5 is compatible too)
* Extensions: `curl`, `json`, `mbstring`
* Public **HTTPS** URL pointing to your bot listener script (Telegram webhooks require SSL)

# Installation

## Via Composer (recommended)

```bash
composer require wolfdispatcher/wolfdispatcher
```

```php
<?php

require __DIR__ . '/vendor/autoload.php';
```

## Manual include

Copy `api.wolfgram.php` and `api.wolfdispatcher.php` into your project (or use this repository as-is):

```php
<?php

require_once('api.wolfgram.php');
require_once('api.wolfdispatcher.php');
```

On YALF / Ubilling the library is already available out of the box - you usually do not need install anything there.

# Storage: `exports/`

WolfDispatcher writes webhook autosetup PID files and optional debug logs into `exports/` relative to the **process working directory** (typically the directory of your bot entry script / project root).

* On **YALF / Ubilling** this directory already exists and is writable by design.
* In a **standalone or Composer** project create it once before the first run:

```bash
mkdir -p exports
chmod 775 exports
```

Add `exports/` to your project `.gitignore` so PID files and logs are not committed. Without a writable `exports/` directory, `hookAutosetup()` / debug logging may fail when writing files.

If you prefer to register the webhook yourself, call `installWebHook('https://your.host/bot.php')` once (or set it via BotFather / Telegram API) and you can skip `hookAutosetup()` on every request.

# Your first bot

Point Telegram’s webhook at this script over HTTPS, then:

```php
<?php

require __DIR__ . '/vendor/autoload.php';

class OurBot extends WolfDispatcher {
    protected function actionHello() {
        $this->reply('Hello!');
    }
}

$commands = array(
    'hi' => 'actionHello'
);

$bot = new OurBot('YOUR_BOT_TOKEN');
$bot->setActions($commands);
$bot->hookAutosetup();
$bot->listen();
```

Replace `YOUR_BOT_TOKEN` with the token from [@BotFather](https://t.me/BotFather). Send `hi` to the bot - it should reply `Hello!`.

# Please check out some usage guidelines

  * [Full WolfDispatcher class documentation](http://wiki.ubilling.net.ua/doku.php?id=wolfdispatcher) in Ukrainian
  * [YALF Framework includes WolfDispatcher](http://yalf.nightfly.biz/)
  * Packagist: [wolfdispatcher/wolfdispatcher](https://packagist.org/packages/wolfdispatcher/wolfdispatcher)

# Just working bot demo

[instafiltersdemo.webm](https://user-images.githubusercontent.com/1496954/184504316-59350e09-b1df-4699-b7b6-6e0d370794d8.webm)

# Few live bots using WolfDispatcher

  * [@DeTryvogaBot - aerial alerts in Ukraine notifications](https://t.me/DeTryvogaBot)
  * [@dushavolkabot - memes generation and few specific services](https://t.me/dushavolkabot)
  * [@TlenifyBot - makes all pictures sad and depressing](https://t.me/TlenifyBot)
