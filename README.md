# WolfDispatcher
WolfDispatcher - tiny Telegram bot framework


# Usage example

```
    <?php
     
    require_once('api.wolfgram.php');
    require_once('api.wolfdispatcher.php');
     
    class OurBot extends WolfDispatcher {
     
    }
     
     
    $bot = new OurBot('YOUR_BOT_TOKEN');
    $bot->hookAutosetup(true);
    $bot->listen();

```

## Please check out some usage guidelines

  * [Full Documentation](http://wiki.ubilling.net.ua/doku.php?id=wolfdispatcher)
