<?php

namespace IPS\Batch;

use IPS\Model as Model;
use IPS\Model\Config as Config;
use IPS\Model\Log as Log;

class ChatMonitor
{
    const IDOL_TIMEOUT = 50;
    
    private $socket;
    private $user;
    private $accessToken;
    private $setting;
    private $channel;
    
    public function __construct(Model\User $user, Model\AccessToken $accessToken, Model\Setting $setting)
    {
        $this->user = $user;
        $this->accessToken = $accessToken;
        $this->setting = $setting;
    }

    public function __destruct()
    {
        if($this->socket) {
            fclose($this->socket);
        }
    }

    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    public function execute()
    {
        $token = Model\Oauth\Factory::create();
        $refresh = Config::get('refresh_token');
        $setting = $this->setting->get($this->channel);
        $refresh = $setting['refresh_token'];
        $token->setRefresh($refresh);
        $this->connectTwitch($token);

        $lastPingTimestamp = 0;
        
        while(!feof($this->socket)) {
            // サーバーからのデータを受信
            $data = fgets($this->socket);
            Log::debug(var_export($data, true));
            
            $meta = stream_get_meta_data($this->socket);
            if($meta['timed_out']) {
                Log::debug('---> PING');
                fwrite($this->socket, "PING :tmi.twitch.tv\r\n");
                $lastPingTimestamp = time();
                continue;
            }

            // case no data or failure
            if(!$data) {
                $this->connectTwitch($token);
                continue;
            }

            // case PING
            if (strpos($data, 'PING') !== false) {
                $pong = substr($data, 5);
                fwrite($this->socket, "PONG :{$pong}\r\n");
                continue;
            }

            // case receive chat message
            if (strpos($data, 'PRIVMSG') !== false) {
                // チャットメッセージの内容を抽出
                preg_match('/^:([^!]+)!.+PRIVMSG #([^ ]+) :(.+)/', $data, $matches);
                if(!isset($matches[3])) {
                    continue;
                }

                $login = $matches[1];
                $channel = $matches[2];
                $message = $matches[3];

                $this->user->updateTimestamp($login, $channel);

                // チャットメッセージの先頭が ! の場合、当該チャンネルのコマンドを実行
                if(substr($message, 0, 1) === '!') {
                    Log::debug("Exec command >>> {$message}");
                    $argument = escapeshellarg(trim($message));
                    $script = __DIR__ . '/../script/command.php';
                    $command = "php {$script} {$login} {$channel} {$argument}";
                    exec("{$command} &");
                }
            }

            // 一定時間経過ごとにPINGを発報する
            if(time() - $lastPingTimestamp > static::IDOL_TIMEOUT) {
                Log::debug('---> PING');
                fwrite($this->socket, "PING :tmi.twitch.tv\r\n");
                $lastPingTimestamp = time();
            }
        }

        fclose($this->socket);
    }

    /**
     * TwitchのチャットにIRC経由で接続する
     *
     * Access Tokenの有効期限が切れている場合、
     * 再取得の上で既存接続を破棄して再接続を試みる
     */
    private function connectTwitch(Model\Oauth\Token $token)
    {
        if($token->isExpired()) {
            $this->accessToken->refresh($token);
        }
        $oauthToken = $token->getAccess();
        Log::debug("Access Token: {$oauthToken}");

        // Twitch IRCサーバー情報
        $server = Config::get('twitch', 'irc', 'host');
        $port = Config::get('twitch', 'irc', 'port');
        $nickname = Config::get('twitch', 'irc', 'nick');
        $timeout = Config::get('twitch', 'irc', 'timeout');
        $channel = "#{$this->channel}";

        // IRCサーバーに接続
        if($this->socket) {
            fclose($this->socket);
        }
        $this->socket = pfsockopen($server, $port, $errno, $errstr, $timeout);
        if (!$this->socket) {
            Log::error("IRC connect error: {$errstr} ({$errno})");
            die("接続エラー: $errstr ($errno)\n");
        }
        stream_set_timeout($this->socket, static::IDOL_TIMEOUT);

        // IRCサーバーにログイン
        fwrite($this->socket, "PASS oauth:{$oauthToken}\r\n");
        fwrite($this->socket, "NICK {$nickname}\r\n");
        fwrite($this->socket, "JOIN {$channel}\r\n");

        Log::info("IRC connect successed. start monitoring chat... >>> {$channel}");
    }

}
