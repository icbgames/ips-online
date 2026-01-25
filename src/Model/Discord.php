<?php

namespace IPS\Model;

/**
 * Discordへのpostを行うクラス
 */
class Discord
{
    /**
     * Constructor
     *
     */
    public function __construct()
    {
    }

    /**
     * Discordにメッセージを送信
     *
     * @param string $message
     */
    public function post($message)
    {
        $token = Config::get('discord', 'token');
        $channel = Config::get('discord', 'channel');

        $url = "https://discord.com/api/v10/channels/{$channel}/messages";
        $data = ['content' => $message];
        $headers = [
            "Authorization: Bot {$token}",
            'Content-Type: application/json',
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        if(curl_errno($ch)) {
            Log::debug("Discord API Error: " . curl_error($ch));
        }
    }
}
