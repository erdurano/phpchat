<?php

use Slim\Factory\AppFactory;
use App\Controllers\GroupController;
use App\Controllers\MessageController;
use Slim\Middleware\BodyParsingMiddleware;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, true, true);

$app->post('/groups', [GroupController::class, 'createGroup']);
$app->get('/groups', [GroupController::class, 'listGroups']);
$app->post('/groups/{group_id}/join', [GroupController::class, 'joinGroup']);

$app->post('/groups/{group_id}/messages', [MessageController::class, 'sendMessage']);
$app->get('/groups/{group_id}/messages', [MessageController::class, 'listMessages']);

$app->run();
