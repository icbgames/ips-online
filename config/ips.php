<?php

/**
 *
 */
return [
    'twitch' => [
        'api' => [
            'oauth2token' => 'https://id.twitch.tv/oauth2/token',
            'users' => 'https://api.twitch.tv/helix/users',
            'subscriptions' => 'https://api.twitch.tv/helix/subscriptions',
        ],
        'irc' => [
            'host' => 'irc.chat.twitch.tv',
            'port' => 6667,
            'nick' => 'icb_games',
            'timeout' => 60,
        ],
    ],
    'ips' => [
        'url' => 'https://ips-online.link/',
    ],
];
