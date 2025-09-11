<?php

use function DI\factory;
use IPS\Batch as Batch;
use IPS\Model as Model;
use IPS\Controller as Controller;

return [
    'ipsBatchChatMonitor' => DI\factory(function(DI\Container $c) {
        return new Batch\ChatMonitor(
            $c->get('ipsModelUser'),
            $c->get('ipsModelAccessToken'),
            $c->get('ipsModelSettings'),
        );
    }),
    'ipsBatchCommandExecutor' => DI\factory(function(DI\Container $c) {
        return new Batch\CommandExecutor(
            $c->get('ipsModelSettings'),
        );
    }),
    'ipsBatchMonitor' => DI\factory(function(DI\Container $c) {
        return new Batch\Monitor(
            $c->get('ipsModelDiscord'),
        );
    }),
    'ipsBatchPointChecker' => DI\factory(function(DI\Container $c) {
        return new Batch\PointChecker(
            $c->get('ipsModelPoint'),
            $c->get('ipsModelSettings'),
            $c->get('ipsModelUser'),
            $c->get('ipsModelTwitch'),
        );
    }),
    'ipsBatchPointUpdater' => DI\factory(function(DI\Container $c) {
        return new Batch\PointUpdater(
            $c->get('ipsModelSettings'),
            $c->get('ipsModelUser'),
            $c->get('ipsModelPoint'),
        );
    }),
    'ipsBatchSubscriptionUpdater' => DI\factory(function(DI\Container $c) {
        return new Batch\SubscriptionUpdater(
            $c->get('ipsModelUser'),
            $c->get('ipsModelTwitch'),
        );
    }),
    'ipsModelAccessToken' => DI\factory(function(DI\Container $c) {
        return new Model\AccessToken(
            $c->get('ipsModelSettings'),
        );
    }),
    'ipsModelAppAccessToken' => DI\factory(function(DI\Container $c) {
        return new Model\AppAccessToken();
    }),
    'ipsModelCommand' => DI\factory(function(DI\Container $c) {
        return new Model\Command();
    }),
    'ipsModelDiscord' => DI\factory(function(DI\Container $c) {
        return new Model\Discord();
    }),
    'ipsModelPoint' => DI\factory(function(DI\Container $c) {
        return new Model\Point();
    }),
    'ipsModelSettings' => DI\factory(function(DI\Container $c) {
        return new Model\Settings();
    }),
    'ipsModelStream' => DI\factory(function(DI\Container $c) {
        return new Model\Stream();
    }),
    'ipsModelTwitch' => DI\factory(function(DI\Container $c) {
        return new Model\Twitch(
            $c->get('ipsModelAccessToken'),
            $c->get('ipsModelAppAccessToken'),
        );
    }),
    'ipsModelUser' => DI\factory(function(DI\Container $c) {
        return new Model\User(
            $c->get('ipsModelTwitch')
        );
    }),
    // for Web
    'www/top' => DI\factory(function(DI\Container $c) {
        return new Controller\Top(
            $c->get('ipsModelAccessToken'),
            $c->get('ipsModelTwitch')
        );
    }),
    'www/information' => DI\factory(function(DI\Container $c) {
        return new Controller\Information(
        );
    }),
    'www/policy' => DI\factory(function(DI\Container $c) {
        return new Controller\Policy(
        );
    }),
    'www/setting' => DI\factory(function(DI\Container $c) {
        return new Controller\Setting(
            $c->get('ipsModelSettings'),
            $c->get('ipsModelAccessToken'),
        );
    }),
    'www/ignore' => DI\factory(function(DI\Container $c) {
        return new Controller\Ignore(
            $c->get('ipsModelPoint'),
            $c->get('ipsModelAccessToken'),
        );
    }),
    'www/ranking' => DI\factory(function(DI\Container $c) {
        return new Controller\Ranking(
            $c->get('ipsModelPoint'),
        );
    }),
    'www/error' => DI\factory(function(DI\Container $c) {
        return new Controller\Error();
    }),
    // for Rest API
    'api/setting' => DI\factory(function(DI\Container $c) {
        return new Controller\Api\Setting(
            $c->get('validator'),
            $c->get('ipsModelSettings'),
        );
    }),
    'api/point' => DI\factory(function(DI\Container $c) {
        return new Controller\Api\Point(
            $c->get('validator'),
            $c->get('ipsModelPoint'),
        );
    }),
    'api/event' => DI\factory(function(DI\Container $c) {
        return new Controller\Api\Event(
            $c->get('ipsModelSettings'),
            $c->get('ipsModelPoint'),
        );
    }),
    'api/reset' => DI\factory(function(DI\Container $c) {
        return new Controller\Api\Reset(
            $c->get('validator'),
            $c->get('ipsModelPoint')
        );
    }),
    'api/ignore' => DI\factory(function(DI\Container $c) {
        return new Controller\Api\Ignore(
            $c->get('validator'),
            $c->get('ipsModelPoint')
        );
    }),
    // validator
    'validator' => DI\factory(function(DI\Container $c) {
        return new Rakit\Validation\Validator(['lang' => 'ja']);
    }),
];
