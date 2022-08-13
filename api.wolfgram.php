<?php

/**
 * Tiny Telegram bot API implementation for standalone projects
 */
class UbillingTelegram {

    /**
     * Contains current instance bot token
     *
     * @var string
     */
    protected $botToken = '';

    /**
     * Contains base Telegram API URL 
     */
    protected $apiUrl = 'https://api.telegram.org/bot';

    /**
     * Maximum message length
     */
    const MESSAGE_LIMIT = 4095;

    /**
     * Creates new Telegram object instance
     * 
     * @param string $token
     */
    public function __construct($token = '') {
        if (!empty($token)) {
            $this->botToken = $token;
        }
    }

    /**
     * Sets current instance auth token
     * 
     * @param string $token
     * 
     * @return void
     */
    public function setToken($token) {
        $this->botToken = $token;
    }

    /**
     * Preprocess keyboard for sending with directPushMessage
     * 
     * @param array $buttonsArray
     * @param bool $inline
     * @param bool $resize
     * @param bool  $oneTime
     * 
     * @return array
     */
    public function makeKeyboard($buttonsArray, $inline = false, $resize = true, $oneTime = false) {
        $result = array();
        if (!empty($buttonsArray)) {
            if (!$inline) {
                $result['type'] = 'keyboard';

                $keyboardMarkup = array(
                    'keyboard' => $buttonsArray,
                    'resize_keyboard' => $resize,
                    'one_time_keyboard' => $oneTime
                );

                $result['markup'] = $keyboardMarkup;
            }

            if ($inline) {
                $result['type'] = 'inline';
                $keyboardMarkup = $buttonsArray;

                $result['markup'] = $keyboardMarkup;
            }
        }
        return($result);
    }

    /**
     * Split message into chunks of safe size
     * 
     * @param string $message
     * 
     * @return array
     */
    protected function splitMessage($message) {
        $result = preg_split('~~u', $message, -1, PREG_SPLIT_NO_EMPTY);
        $chunks = array_chunk($result, self::MESSAGE_LIMIT);
        foreach ($chunks as $i => $chunk) {
            $chunks[$i] = join('', (array) $chunk);
        }
        $result = $chunks;

        return ($result);
    }

    /**
     * Sends message to some chat id using Telegram API
     * 
     * @param int $chatid remote chatId
     * @param string $message text message to send
     * @param array $keyboard keyboard encoded with makeKeyboard method
     * @param bool $nosplit dont automatically split message into 4096 slices
     * @param int $replyToMsgId optional message ID which is reply for
     * 
     * @return string/bool
     */
    public function directPushMessage($chatid, $message, $keyboard = array(), $noSplit = false, $replyToMsgId = '') {
        $result = '';
        if ($noSplit) {
            $result = $this->apiSendMessage($chatid, $message, $keyboard, $replyToMsgId);
        } else {
            $messageSize = mb_strlen($message, 'UTF-8');
            if ($messageSize > self::MESSAGE_LIMIT) {
                $messageSplit = $this->splitMessage($message);
                if (!empty($messageSplit)) {
                    foreach ($messageSplit as $io => $eachMessagePart) {
                        $result = $this->apiSendMessage($chatid, $eachMessagePart, $keyboard, $replyToMsgId);
                    }
                }
            } else {
                $result = $this->apiSendMessage($chatid, $message, $keyboard, $replyToMsgId);
            }
        }
        return($result);
    }

    /**
     * Sends message to some chat id via Telegram API
     * 
     * @param int $chatid remote chatId
     * @param string $message text message to send
     * @param array $keyboard keyboard encoded with makeKeyboard method
     * @param int $replyToMsgId optional message ID which is reply for
     * @throws Exception
     * 
     * @return string/bool
     */
    protected function apiSendMessage($chatid, $message, $keyboard = array(), $replyToMsgId = '') {
        $result = '';
        $data['chat_id'] = $chatid;
        $data['text'] = $message;

        //default sending method
        $method = 'sendMessage';

        //setting optional replied message ID for normal messages
        if ($replyToMsgId) {
            $method = 'sendMessage?reply_to_message_id=' . $replyToMsgId;
        }

        //location sending
        if (ispos($message, 'sendLocation:')) {
            $cleanGeo = str_replace('sendLocation:', '', $message);
            $cleanGeo = explode(',', $cleanGeo);
            $geoLat = trim($cleanGeo[0]);
            $geoLon = trim($cleanGeo[1]);
            $locationParams = '?chat_id=' . $chatid . '&latitude=' . $geoLat . '&longitude=' . $geoLon;
            if ($replyToMsgId) {
                $locationParams .= '&reply_to_message_id=' . $replyToMsgId;
            }
            $method = 'sendLocation' . $locationParams;
        }

        //custom markdown
        if (ispos($message, 'parseMode:{')) {
            if (preg_match('!\{(.*?)\}!si', $message, $tmpMode)) {
                $cleanParseMode = $tmpMode[1];
                $parseModeMask = 'parseMode:{' . $cleanParseMode . '}';
                $cleanMessage = str_replace($parseModeMask, '', $message);
                $data['text'] = $cleanMessage;
                $method = 'sendMessage?parse_mode=' . $cleanParseMode;
                if ($replyToMsgId) {
                    $method .= '&reply_to_message_id=' . $replyToMsgId;
                }
            }
        }

        //venue sending
        if (ispos($message, 'sendVenue:')) {
            if (preg_match('!\[(.*?)\]!si', $message, $tmpGeo)) {
                $cleanGeo = $tmpGeo[1];
            }

            if (preg_match('!\((.*?)\)!si', $message, $tmpAddr)) {
                $cleanAddr = $tmpAddr[1];
            }

            if (preg_match('!\{(.*?)\}!si', $message, $tmpTitle)) {
                $cleanTitle = $tmpTitle[1];
            }

            $data['title'] = $cleanTitle;
            $data['address'] = $cleanAddr;


            $cleanGeo = explode(',', $cleanGeo);
            $geoLat = trim($cleanGeo[0]);
            $geoLon = trim($cleanGeo[1]);
            $locationParams = '?chat_id=' . $chatid . '&latitude=' . $geoLat . '&longitude=' . $geoLon;
            if ($replyToMsgId) {
                $locationParams .= '&reply_to_message_id=' . $replyToMsgId;
            }
            $method = 'sendVenue' . $locationParams;
        }

        //photo sending
        if (ispos($message, 'sendPhoto:')) {
            if (preg_match('!\[(.*?)\]!si', $message, $tmpPhoto)) {
                $cleanPhoto = $tmpPhoto[1];
            }

            if (preg_match('!\{(.*?)\}!si', $message, $tmpCaption)) {
                $cleanCaption = $tmpCaption[1];
                $cleanCaption = urlencode($cleanCaption);
            }

            $photoParams = '?chat_id=' . $chatid . '&photo=' . $cleanPhoto;
            if (!empty($cleanCaption)) {
                $photoParams .= '&caption=' . $cleanCaption;
            }

            if ($replyToMsgId) {
                $photoParams .= '&reply_to_message_id=' . $replyToMsgId;
            }
            $method = 'sendPhoto' . $photoParams;
        }

        //sending keyboard
        if (!empty($keyboard)) {
            if (isset($keyboard['type'])) {
                if ($keyboard['type'] == 'keyboard') {
                    $encodedKeyboard = json_encode($keyboard['markup']);
                    $data['reply_markup'] = $encodedKeyboard;
                }

                if ($keyboard['type'] == 'inline') {
                    $encodedKeyboard = json_encode(array('inline_keyboard' => $keyboard['markup']));
                    $data['reply_markup'] = $encodedKeyboard;
                    $data['parse_mode'] = 'HTML';
                }

                $method = 'sendMessage';
            }
        }

        //removing keyboard
        if (ispos($message, 'removeKeyboard:')) {
            $keybRemove = array(
                'remove_keyboard' => true
            );
            $encodedMarkup = json_encode($keybRemove);
            $cleanMessage = str_replace('removeKeyboard:', '', $message);
            if (empty($cleanMessage)) {
                $cleanMessage = __('Keyboard deleted');
            }
            $data['text'] = $cleanMessage;
            $data['reply_markup'] = $encodedMarkup;
        }


        //POST data encoding
        $data_json = json_encode($data);

        if (!empty($this->botToken)) {
            $url = $this->apiUrl . $this->botToken . '/' . $method;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
            $result = curl_exec($ch);
            curl_close($ch);
        } else {
            throw new Exception('EX_TOKEN_EMPTY');
        }
        return($result);
    }

    /**
     * Sets HTTPS web hook URL for some bot
     * 
     * @param string $webHookUrl HTTPS url to send updates to. Use an empty string to remove webhook integration
     * @param int $maxConnections Maximum allowed number of simultaneous HTTPS connections to the webhook for update delivery, 1-100. Defaults to 40.
     * 
     * @return string
     */
    public function setWebHook($webHookUrl, $maxConnections = 40) {
        $result = '';
        if (!empty($this->botToken)) {
            $data = array();
            if (!empty($webHookUrl)) {
                $method = 'setWebhook';
                if (ispos($webHookUrl, 'https://')) {
                    $data['url'] = $webHookUrl;
                    $data['max_connections'] = $maxConnections;
                } else {
                    throw new Exception('EX_NOT_SSL_URL');
                }
            } else {
                $method = 'deleteWebhook';
            }

            $url = $this->apiUrl . $this->botToken . '/' . $method;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_POST, 1);
            if (!empty($data)) {
                $data_json = json_encode($data);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
            }

            $result = curl_exec($ch);
            curl_close($ch);
        } else {
            throw new Exception('EX_TOKEN_EMPTY');
        }
        return($result);
    }

    /**
     * Returns bot web hook info
     * 
     * @return string
     */
    public function getWebHookInfo() {
        $result = '';
        if (!empty($this->botToken)) {
            $method = 'getWebhookInfo';
            $url = $this->apiUrl . $this->botToken . '/' . $method;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            $result = curl_exec($ch);
            curl_close($ch);
        }

        return($result);
    }

    /**
     * Returns chat data array by its chatId
     * 
     * @param int chatId
     * 
     * @return array
     */
    public function getChatInfo($chatId) {
        $result = array();
        if (!empty($this->botToken) AND ( !empty($chatId))) {
            $method = 'getChat';
            $url = $this->apiUrl . $this->botToken . '/' . $method . '?chat_id=' . $chatId;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            $result = curl_exec($ch);
            curl_close($ch);

            if (!empty($result)) {
                $result = json_decode($result, true);
            }
        }

        return($result);
    }

    /**
     * Returns file path by its file ID
     * 
     * @param string $fileId
     * 
     * @return string
     */
    public function getFilePath($fileId) {
        $result = '';
        if (!empty($this->botToken)) {
            $method = 'getFile';
            $url = $this->apiUrl . $this->botToken . '/' . $method . '?file_id=' . $fileId;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            $result = curl_exec($ch);
            curl_close($ch);

            if (!empty($result)) {
                $result = json_decode($result, true);
                if (@$result['ok']) {
                    //we got it!
                    $result = $result['result']['file_path'];
                } else {
                    //something went wrong
                    $result = '';
                }
            }
        }
        return($result);
    }

    /**
     * Returns some file content
     * 
     * @param string $filePath
     * 
     * @return mixed
     */
    public function downloadFile($filePath) {
        $result = '';
        if (!empty($this->botToken)) {
            $cleanApiUrl = str_replace('bot', '', $this->apiUrl);
            $url = $cleanApiUrl . 'file/bot' . $this->botToken . '/' . $filePath;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            $result = curl_exec($ch);
            curl_close($ch);
        }
        return($result);
    }

    /**
     * Returns preprocessed message in standard, fixed fields format
     * 
     * @param array $messageData
     * @param bool $isChannel
     * 
     * @return array
     */
    protected function preprocessMessageData($messageData, $isChannel = false) {
        $result = array();
        $result['message_id'] = $messageData['message_id'];

        if (!$isChannel) {
            //normal messages/groups
            $result['from']['id'] = $messageData['from']['id'];
            $result['from']['first_name'] = $messageData['from']['first_name'];
            @$result['from']['username'] = $messageData['from']['username'];
            @$result['from']['language_code'] = $messageData['from']['language_code'];
        } else {
            //channel posts
            $result['from']['id'] = $messageData['sender_chat']['id'];
            $result['from']['first_name'] = $messageData['sender_chat']['title'];
            @$result['from']['username'] = $messageData['sender_chat']['username'];
            @$result['from']['language_code'] = '';
        }
        $result['chat']['id'] = $messageData['chat']['id'];
        $result['date'] = $messageData['date'];
        $result['chat']['type'] = $messageData['chat']['type'];
        @$result['text'] = $messageData['text'];
        @$result['photo'] = $messageData['photo'];
        @$result['document'] = $messageData['document'];
        //photos and documents have only caption
        if (!empty($result['photo']) OR ! empty($result['document'])) {
            @$result['text'] = $messageData['caption'];
        }
        @$result['voice'] = $messageData['voice'];
        @$result['audio'] = $messageData['audio'];
        @$result['video_note'] = $messageData['video_note'];
        @$result['location'] = $messageData['location'];
        @$result['sticker'] = $messageData['sticker'];
        @$result['new_chat_member'] = $messageData['new_chat_member'];
        @$result['left_chat_member'] = $messageData['left_chat_member'];
        @$result['reply_to_message'] = $messageData['reply_to_message'];
        //decode replied message too if received
        if ($result['reply_to_message']) {
            $result['reply_to_message'] = $this->preprocessMessageData($result['reply_to_message']);
        }


        return($result);
    }

    /**
     * Returns webhook data
     * 
     * @param bool $rawData receive raw reply or preprocess to something more simple.
     * 
     * @return array
     */
    public function getHookData($rawData = false) {
        $result = array();
        $postRaw = file_get_contents('php://input');
        if (!empty($postRaw)) {
            $postRaw = json_decode($postRaw, true);

            if (!$rawData) {
                if (isset($postRaw['message'])) {
                    if (isset($postRaw['message']['from'])) {
                        $result = $this->preprocessMessageData($postRaw['message']);
                    }
                } else {
                    if (isset($postRaw['channel_post'])) {
                        $result = $this->preprocessMessageData($postRaw['channel_post'], true);
                    }
                }
            } else {
                $result = $postRaw;
            }
        }

        return($result);
    }

}

/**
 * Some legacy workaround here
 */
if (!function_exists('ispos')) {

    /**
     * Checks for substring in string
     * 
     * @param string $string
     * @param string $search
     * @return bool
     */
    function ispos($string, $search) {
        if (strpos($string, $search) === false) {
            return(false);
        } else {
            return(true);
        }
    }

}

if (!function_exists('curdatetime')) {

    /**
     * Returns current date and time in mysql DATETIME view
     * 
     * @return string
     */
    function curdatetime() {
        $currenttime = date("Y-m-d H:i:s");
        return($currenttime);
    }

}

if (!function_exists('__')) {

    /**
     * Dummy i18n function
     * 
     * @param string $str
     * @return string
     */
    function __($str) {
        global $lang;
        if (isset($lang['def'][$str])) {
            if (!empty($lang['def'][$str])) {
                $str = $lang['def'][$str];
            }
        }
        return($str);
    }

}

$starttime = explode(' ', microtime());
$starttime = $starttime[1] + $starttime[0];
$query_counter = 0;
