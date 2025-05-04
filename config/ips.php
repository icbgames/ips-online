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
            'eventsubs' => 'https://api.twitch.tv/helix/eventsub/subscriptions',
        ],
        'irc' => [
            'host' => 'irc.chat.twitch.tv',
            'port' => 6667,
            'nick' => 'icb_games',
            'timeout' => 60,
        ],
        'oauth' => [
            'url' => 'https://id.twitch.tv/oauth2/authorize?client_id=htukebwn7imooessy0j06vhjqyfc2v&redirect_uri=https://ips-online.link/&response_type=code&scope=channel:read:subscriptions%20chat:read%20chat:edit%20channel:read:redemptions&state=ips-online',
        ],
    ],
    'ips' => [
        'url' => 'https://ips-online.link/',
        'channels' => [
            'kirukiru_21',
            'mira_kiryu',
            'mayuko7s',
        ],
        // 0 -> ERROR
        // 1 -> ERROR, WARN
        // 2 -> ERROR, WARN, INFO
        // 3 -> ERROR, WARN, INFO, DEBUG
        'log' => [
            'level' => 3,
        ],
    ],
];
