# WolfDispatcher
WolfDispatcher - tiny Telegram bot framework


# Quick start

```
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
    $bot->hookAutosetup(true);
    $bot->listen();

```


## Please check out some usage guidelines

  * [Full class documentation](http://wiki.ubilling.net.ua/doku.php?id=wolfdispatcher)

## Just bot demo

[instafiltersdemo.webm](https://user-images.githubusercontent.com/1496954/184504316-59350e09-b1df-4699-b7b6-6e0d370794d8.webm)
