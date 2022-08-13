# WolfDispatcher
WolfDispatcher - tiny Telegram bot framework


# Quick start

```
<?php

require_once('api.wolfgram.php');
require_once('api.wolfdispatcher.php');

```

# Your first bot

```
    class OurBot extends WolfDispatcher {
        protected function actionHello() {
           $this->reply('Hello!');
        }
    }

    $commands=array(
       'hi'=>'actionHello'
    );

    $bot = new OurBot('YOUR_BOT_TOKEN');
    $bot->setActions($commands);
    $bot->hookAutosetup();
    $bot->listen();

```


# Please check out some usage guidelines

  * [Full WolfDispatcher class documentation](http://wiki.ubilling.net.ua/doku.php?id=wolfdispatcher) in Ukrainian
  * [YALF Framework includes WolfDispatcher](http://yalf.nightfly.biz/)


# Just working bot demo

[instafiltersdemo.webm](https://user-images.githubusercontent.com/1496954/184504316-59350e09-b1df-4699-b7b6-6e0d370794d8.webm)
