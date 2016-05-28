<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
// Try to load YAML config, in other case Exception
$app['config']=array('servers'=>array('example'=>array('name'=>"Example Server")));
if (file_exists(__DIR__.'/config.yml')) {
    try {
        $app['config'] = Symfony\Component\Yaml\Yaml::parse(file_get_contents(__DIR__.'/config.yml'));
    } catch (Exception $e) {
        $app['flash']['errors'] = "Unable to parse config.yml: ".$e->getMessage();
    }
} else {
    $app['flash']['warnings'] = "Unable to parse config.yml: file not found";
}

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));



$app['debug'] = true;
$app['current_uri'] = trim($_SERVER['REQUEST_URI'], '/');

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.twig', array(
        'name' => "asd",
    ));
});

$app->get('/{server}', function ($server) use ($app) {
    $s=$app->escape($server);
    return $app['twig']->render('server.twig', array(
        'server' => $app['config']['servers'][$s],
    ));
});

$app->run();
