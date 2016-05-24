<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

include "config.php";

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.twig', array(
        'name' => "asd",
    ));
});
/*
$app->get('/{server}', function ($server) use ($app) {
    $s=$app->escape($server);
    return 'Hello '.$app['servers'][$s][name];
});
*/
$app->run();
