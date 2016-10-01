<?php

use Symfony\Component\HttpFoundation\Response;

require_once __DIR__ . '/../vendor/autoload.php'; // loading vendors

// Making te application, and register a few features
$app = new Silex\Application();
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\AssetServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array('twig.path' => __DIR__ . '/../views'));

// Try to load YAML config, in other case catch Exception, and create flash array
if (file_exists(__DIR__ . '/../config.yml')) {
    try {
        $app['config'] = Symfony\Component\Yaml\Yaml::parse(file_get_contents(__DIR__ . '/../config.yml'));
    } catch (Exception $e) {
        $flashes[] = array("danger", "Unable to parse config.yml: " . $e->getMessage());
    }
} else {
    $flashes[] = array("danger", "Unable to parse config.yml: file not found");
}

// if we have any messages, we create error/info popups
foreach ($flashes as $flash) {
    $app['session']->getFlashBag()->add('flashMessages', $flash);
}

// few variables for templates
$app['debug'] = true;
$app['current_uri'] = trim($_SERVER['REQUEST_URI'], '/');

////////////////////// FUNCTIONS ////////////////////// TODO: refactor to objects
$screenShot = function ($server, $shot) use ($app) {
    $file = basename(htmlspecialchars_decode($shot) . ".jpg");
    $fileDir = $app['config']['servers'][$server]['shots_dir'];
    return $fileDir . '/' . $file;
};
$lastShot = function ($s, $direction = false) use ($app) {
    $files = glob($app['config']['servers'][$s]['shots_dir'] . "/*.jpg");
    $files = array_combine($files, array_map("filemtime", $files));
    if ($direction == 'first') {
        asort($files);
    } else {
        arsort($files);
    }

    if (empty($files)) {
        $app['session']->getFlashBag()->add('flashMessages', array('warning', "No images found"));
    }

    return basename(key($files), ".jpg");
};
$nearShot = function ($server, $shot) use ($app) {
    $files = glob($app['config']['servers'][$server]['shots_dir'] . "/*.jpg");
    $files = array_combine($files, array_map("filemtime", $files));
    arsort($files);
    $names = array_keys($files);

    $match = array_keys(array_filter($names, function ($var) use ($shot) {
        $name = array_pop(explode('/', $var));
        return (htmlspecialchars_decode($shot) == basename($name, ".jpg"));
    }))[0];


    if (isset($match)) {
        if ($match != 0) {
            $next = basename($names[$match - 1], ".jpg");
        }
        if ($match != count($names) - 1) {
            $prev = basename($names[$match + 1], ".jpg");
        }
    }

    return array('prev' => $prev, 'next' => $next);
};
/////////////////////// ROUTING ///////////////////////

/**
 * Index page
 */
$app->get('/', function () use ($app) {
    return $app['twig']->render('index.twig', array());
})->bind('home');

/**
 * Server page
 * also searches the last shoot screen
 *
 * @param string $server
 */
$app->get('/{server}', function ($server) use ($app, $lastShot) {
    $s = $app->escape($server);

    return $app['twig']->render('server.twig', array(
        'server'        => $app['config']['servers'][$s],
        'currentServer' => $s,
        'lastShot'      => $lastShot($s)
    ));
})->bind('server');

/**
 * ScreenShot Page
 *
 * @param string $server
 * @param string $shot
 */
$app->get('/{server}/{shot}', function ($server, $shot) use ($app, $screenShot, $lastShot, $nearShot) {
    $sh = $app->escape($shot);
    $se = $app->escape($server);
    $path = $screenShot($se, $sh);

    if ($sh == "last" || $sh == "first") {
        $sh = $lastShot($se, $sh);
        if ($sh) {
            return $app->redirect(
                $app['url_generator']->generate('shots', array(
                    'server' => $se,
                    'shot'   => htmlspecialchars_decode($sh)
                ))
            );
        } else {
            return $app->redirect($app['url_generator']->generate('home'));
        }
    }

    return $app['twig']->render('shot.twig', array(
        'shot'          => htmlspecialchars_decode($sh),
        'shotTime'      => filemtime($path),
        'shotSize'      => filesize($path),
        'currentServer' => $se,
        'lastShot'      => $lastShot($se),
        'nearShot'      => $nearShot($se, $sh)
    ));
})->bind('shots');

/**
 * Image Response
 *
 * @param string $server
 * @param string $shot
 */
$app->get('/img/{server}/{shot}', function ($server, $shot) use ($app, $screenShot) {
    $path = $screenShot($server, htmlspecialchars_decode($shot));

    $response = new Response();
    $response->setContent(file_exists($path) ? file_get_contents($path) : file_get_contents("assets/img_not_found.jpg"));
    $response->setStatusCode(Response::HTTP_OK);
    $response->headers->set('Content-Type', 'image/jpeg');
    $response->headers->set('Content-Length', filesize($path));

    $response->send();
})->bind('images');

// AAAAAAND IT'S RAN
$app->run();
