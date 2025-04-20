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
        );
    }),
    'ipsBatchCommandExecutor' => DI\factory(function(DI\Container $c) {
        return new Batch\CommandExecutor(
            $c->get('ipsModelSettings'),
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
    'ipsModelCommand' => DI\factory(function(DI\Container $c) {
        return new Model\Command();
    }),
    'ipsModelPoint' => DI\factory(function(DI\Container $c) {
        return new Model\Point();
    }),
    'ipsModelSettings' => DI\factory(function(DI\Container $c) {
        return new Model\Settings();
    }),
    'ipsModelTwitch' => DI\factory(function(DI\Container $c) {
        return new Model\Twitch(
            $c->get('ipsModelAccessToken'),
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
    'www/setting' => DI\factory(function(DI\Container $c) {
        return new Controller\Setting(
            $c->get('ipsModelSettings'),
            $c->get('ipsModelAccessToken'),
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
    // validator
    'validator' => DI\factory(function(DI\Container $c) {
        return new Rakit\Validation\Validator(['lang' => 'ja']);
    }),
];
