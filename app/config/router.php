<?php

$router = $di->getRouter();

// Define your routes here

$router->handle($_SERVER['REQUEST_URI']);



$router->add(
    '/logout',
    [
        'controller' => 'auth',
        'action' => 'logout',
    ]
);


$router->add(
    '/dashboard',
    [
        'controller' => 'dashboard',
        'action'     => 'index',
    ]
);


$router->add(
    '/userlist',
    [
        'controller' => 'dashboard',
        'action'     => 'userlist',
    ]
);

$router->add(
    '/myprofile',
    [
        'controller' => 'dashboard',
        'action'     => 'myprofile',
    ]
);

$router->add(
    '/change_pass',
    [
        'controller' => 'dashboard',
        'action'     => 'changepass',
    ]
);

$router->add(
    '/company',
    [
        'controller' => 'dashboard',
        'action'     => 'companydetails',
    ]
);

$router->add(
    '/homepage',
    [
        'controller' => 'dashboard',
        'action'     => 'homepage',
    ]
);

$router->add(
    '/profile',
    [
        'controller' => 'index',
        'action'     => 'myprofile',
    ]
);

$router->add(
    '/updateprofile',
    [
        'controller' => 'index',
        'action'     => 'updatemyprofile',
    ]
);

