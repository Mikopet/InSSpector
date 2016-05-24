<?php

$app['debug'] = true;
$app['current_uri'] = trim($_SERVER['REQUEST_URI'], '/');

$app['servers'] = array (
    'sd' => array(
        'name'      => "Search & Destroy",
        'shots_dir' => "/home/mikopet/ss",
        'saved_dir' => "/home/mikopet/ss-saved"
    ),
    'tdm' => array(
        'name'      => "Team Deathmatch",
        'shots_dir' => "/home/mikopet/ss",
        'saved_dir' => "/home/mikopet/ss-saved"
    )
);
