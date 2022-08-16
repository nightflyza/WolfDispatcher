# WolfDispatcher
WolfDispatcher is an incredibly simple, minimalistic and fast layer of abstraction over the Telegram API that will allow you to develop your interactive Telegram bots with any functionality at an incredible speed, without thinking about the low-level things they will do. Also, maximum attention was paid to the standardization of data formats with which you can work in the process of implementing your bot and the ease of deploying it to production. With the help of WolfDispatcher, you can implement your bot functionality as a portable on-premise solution based on the YALF framework or  also, you can use just two tiny libraries to left alone with your great code and awesome ideas.

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

# Few live bots using WolfDispatcher

  * [@DeTryvogaBot - aerial alerts in Ukraine notifications](https://t.me/DeTryvogaBot)
  * [@dushavolkabot - memes generation and few specific services](https://t.me/dushavolkabot) 
  * [@TlenifyBot - makes all pictures sad and depressing](https://t.me/TlenifyBot)
