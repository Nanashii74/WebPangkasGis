<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('kelurahan/geojson', 'Kelurahan::geojson');
$routes->post('route/directions', 'Directions::route');
