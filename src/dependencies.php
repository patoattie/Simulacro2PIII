<?php

use Slim\App;
use Illuminate\Database\Capsule\Manager as Capsule; //BD

return function (App $app) {
    $container = $app->getContainer();

    // view renderer
    $container['renderer'] = function ($c) {
        $settings = $c->get('settings')['renderer'];
        return new \Slim\Views\PhpRenderer($settings['template_path']);
    };

    // monolog
    $container['logger'] = function ($c) {
        $settings = $c->get('settings')['logger'];
        $logger = new \Monolog\Logger($settings['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
        return $logger;
    };

    //BD
    //$app = new \Slim\App(["settings" => $config]);
    // Service factory for the ORM
    //$container = $app->getContainer();
    $dbSettings = $container->get('settings')['db'];

    $capsule = new Capsule;
    $capsule->addConnection($dbSettings);
    $capsule->bootEloquent();
    $capsule->setAsGlobal();
};
