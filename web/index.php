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

////////////////////// FUNCTIONS //////////////////////
$screenShot = function($server, $shot) use ($app) {
    $file = basename(urldecode($shot.".jpg"));
    $fileDir = $app['config']['servers'][$server]['shots_dir'];
    return $fileDir .'/'. $file;
};
$lastShot = function($s) use ($app) {
    $files = glob($app['config']['servers'][$s]['shots_dir']."/*.jpg");
    $files = array_combine($files, array_map("filemtime", $files));
    arsort($files);

    if (empty($files)) {
        $app['session']->getFlashBag()->add('flashMessages', array('warning',"no images found"));
    }

    return basename(key($files), ".jpg");
};
/////////////////////// ROUTING ///////////////////////

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
$app->get('/{server}', function ($server) use ($app, $lastShot) {
    $s=$app->escape($server);

    return $app['twig']->render('server.twig', array(
        'server'        => $app['config']['servers'][$s],
        'currentServer' => $s,
        'lastShot'      => $lastShot($s)
    ));
});

/*
 * ScreenShot Page
 *
 * @param string $server
 * @param string $shot
 */
$app->get('/{server}/{shot}', function ($server, $shot) use ($app, $screenShot, $lastShot) {
    $sh = $app->escape($shot);
    $se = $app->escape($server);
    $path = $screenShot($se, $sh);

    return $app['twig']->render('shot.twig', array(
        'shot'          => $sh,
        'shotTime'     => filemtime($path),
        'shotSize'     => filesize($path),
        'currentServer' => $se,
        'lastShot'      => $lastShot($se)
    ));
})->bind('shots');

/*
 * Image Response
 *
 * @param string $server
 * @param string $shot
 */
$app->get('/img/{server}/{shot}', function ($server, $shot) use ($app, $screenShot) {
    $sh = $app->escape($shot);
    $se = $app->escape($server);
    $path = $screenShot($se, $sh);

    header('Content-Type: image/jpeg');
    return file_exists($path)?file_get_contents($path):file_get_contents("assets/img_not_found.jpg");
})->bind('images');

// AAAAAAND IT'S RAN
$app->run();
