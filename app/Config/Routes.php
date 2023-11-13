<?php

use CodeIgniter\Router\RouteCollection;

/**
 * --------------------------------------------------------------------
 * Router Setup
 * Đoạn này sẽ phủ định phần config trong file Routing.php
 * --------------------------------------------------------------------
 */
// $routes->setDefaultNamespace('App\Controllers');
// $routes->setDefaultController('Home');
// $routes->setDefaultMethod('index');
// $routes->setTranslateURIDashes(false);
// $routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
$routes->setAutoRoute(true);

/**
 * @var RouteCollection $routes
 */
// $routes->get('/', 'Home::index');
// include file kiểu này để mỗi lần update code thì check git nó tiện
require_once __DIR__ . '/MyRoutes.php';
