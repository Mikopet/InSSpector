<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));
// Try to load YAML config, in other case Exception
if (file_exists(__DIR__.'/config.yml')) {
    try {
        $app['config'] = Symfony\Component\Yaml\Yaml::parse(file_get_contents(__DIR__.'/config.yml'));
    } catch (Exception $e) {
        $flashes[]=array("danger", "Unable to parse config.yml: ".$e->getMessage());
    }
} else {
    $flashes[]=array("danger", "Unable to parse config.yml: file not found");
}

foreach ($flashes as $flash) {
    $app['session']->getFlashBag()->add('flashMessages', $flash);
}





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
