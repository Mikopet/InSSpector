<?php

require_once __DIR__.'/../vendor/autoload.php'; // loading vendors

// Making te application, and register a few features
$app = new Silex\Application();
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\AssetServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

// Try to load YAML config, in other case catch Exception, and create flash array
if (file_exists(__DIR__.'/config.yml')) {
    try {
        $app['config'] = Symfony\Component\Yaml\Yaml::parse(file_get_contents(__DIR__.'/config.yml'));
    } catch (Exception $e) {
        $flashes[]=array("danger", "Unable to parse config.yml: ".$e->getMessage());
    }
} else {
    $flashes[]=array("danger", "Unable to parse config.yml: file not found");
}

// if we have any messages, we create error/info popups
foreach ($flashes as $flash) {
    $app['session']->getFlashBag()->add('flashMessages', $flash);
}

// few variables for templates
$app['debug'] = true;
$app['current_uri'] = trim($_SERVER['REQUEST_URI'], '/');

////////////////////// ROUTING //////////////////////

/*
 * Index page
 */
$app->get('/', function () use ($app) {
    return $app['twig']->render('index.twig', array());
});

/*
 * Server page
 * also searches the last shoot screen
 *
 * @param string $server
 */
$app->get('/{server}', function ($server) use ($app) {
    $s=$app->escape($server);

    $files = glob($app['config']['servers'][$s]['shots_dir']."/*.jpg");
    $files = array_combine($files, array_map("filemtime", $files));
    arsort($files);

    $latest_file = basename(key($files), ".jpg");

    return $app['twig']->render('server.twig', array(
        'server'        => $app['config']['servers'][$s],
        'currentServer' => $s,
        'lastShot'      => $latest_file
    ));
});

/*
 * ScreenShot Page
 *
 * @param string $server
 * @param string $shot
 */
$app->get('/{server}/{shot}', function ($server, $shot) use ($app) {
    return $app['twig']->render('shot.twig', array(
        'shot' => $app->escape($shot),
        'currentServer' => $app->escape($server)
    ));
})->bind('shots');

/*
 * Image Response
 *
 * @param string $server
 * @param string $shot
 */
$app->get('/img/{server}/{shot}', function ($server, $shot) use ($app) {
    $file = basename(urldecode($app->escape($shot).".jpg"));
    $fileDir = $app['config']['servers'][$app->escape($server)]['shots_dir'];

    $path = $fileDir .'/'. $file;

    if (file_exists($path))
    {
        $contents = file_get_contents($path);
        header('Content-type: image/jpeg');
        return $contents;
    }
    return ($path);
})->bind('images');


// AAAAAAND IT'S RAN
$app->run();
