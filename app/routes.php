<?php

declare(strict_types=1);

/** @var \App\Core\Router $router */

$router->get('/', 'StoreController@home');
$router->get('/catalog', 'StoreController@catalog');
$router->get('/api/catalog', 'StoreController@catalog');
$router->get('/product/{slug}', 'StoreController@product');
$router->get('/about', 'StoreController@about');
$router->get('/faq', 'StoreController@faq');
$router->get('/contact', 'StoreController@contact');
$router->post('/contact', 'StoreController@contactSubmit', ['csrf']);
$router->get('/private-label', 'StoreController@privateLabel');
$router->post('/private-label', 'StoreController@privateLabelSubmit', ['csrf']);

$router->get('/quote', 'QuoteController@show');
$router->post('/quote', 'QuoteController@submit', ['csrf']);

$router->get('/login', 'AuthController@loginForm', ['guest']);
$router->post('/login', 'AuthController@login', ['guest', 'csrf']);
$router->post('/logout', 'AuthController@logout', ['csrf']);
$router->get('/apply', 'AuthController@applyForm');
$router->post('/apply', 'AuthController@applySubmit', ['csrf']);
$router->get('/dashboard', 'AuthController@dashboard', ['auth']);

$router->get('/admin/login', 'AuthController@adminLoginForm', ['guest']);
$router->post('/admin/login', 'AuthController@login', ['guest', 'csrf']);

$admin = ['auth', 'role:admin'];
$post = ['auth', 'role:admin', 'csrf'];

$router->get('/admin', 'Admin\\DashboardController@index', $admin);

$router->get('/admin/products', 'Admin\\ProductController@index', $admin);
$router->get('/admin/products/create', 'Admin\\ProductController@create', $admin);
$router->post('/admin/products', 'Admin\\ProductController@store', $post);
$router->get('/admin/products/{id}/edit', 'Admin\\ProductController@edit', $admin);
$router->post('/admin/products/{id}', 'Admin\\ProductController@update', $post);
$router->post('/admin/products/{id}/delete', 'Admin\\ProductController@destroy', $post);

$router->get('/admin/categories', 'Admin\\CategoryController@index', $admin);
$router->post('/admin/categories', 'Admin\\CategoryController@store', $post);
$router->post('/admin/categories/{id}', 'Admin\\CategoryController@update', $post);

$router->get('/admin/applications', 'Admin\\ApplicationController@index', $admin);
$router->get('/admin/applications/{id}', 'Admin\\ApplicationController@show', $admin);
$router->post('/admin/applications/{id}/approve', 'Admin\\ApplicationController@approve', $post);
$router->post('/admin/applications/{id}/reject', 'Admin\\ApplicationController@reject', $post);

$router->get('/admin/dealers', 'Admin\\DealerController@index', $admin);
$router->get('/admin/dealers/{id}', 'Admin\\DealerController@show', $admin);
$router->post('/admin/dealers/{id}/toggle', 'Admin\\DealerController@toggle', $post);

$router->get('/admin/quotes', 'Admin\\QuoteController@index', $admin);
$router->get('/admin/quotes/{id}', 'Admin\\QuoteController@show', $admin);
$router->post('/admin/quotes/{id}/status', 'Admin\\QuoteController@status', $post);
$router->post('/admin/quotes/{id}/convert', 'Admin\\QuoteController@convert', $post);

$router->get('/admin/orders', 'Admin\\OrderController@index', $admin);
$router->get('/admin/orders/{id}', 'Admin\\OrderController@show', $admin);
$router->post('/admin/orders/{id}/status', 'Admin\\OrderController@status', $post);

$router->get('/admin/private-label', 'Admin\\PrivateLabelController@index', $admin);
$router->get('/admin/private-label/{id}', 'Admin\\PrivateLabelController@show', $admin);
$router->post('/admin/private-label/{id}/status', 'Admin\\PrivateLabelController@status', $post);

$router->get('/admin/messages', 'Admin\\MessageController@index', $admin);
$router->get('/admin/messages/{id}', 'Admin\\MessageController@show', $admin);

$router->get('/admin/settings', 'Admin\\SettingsController@index', $admin);
$router->post('/admin/settings', 'Admin\\SettingsController@save', $post);
