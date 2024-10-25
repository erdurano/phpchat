<?php

use Slim\Factory\AppFactory;
use App\Controllers\GroupController;
use App\Controllers\MembersController;
use App\Controllers\MessageController;
use App\Middlewares\ContentTypeMiddleware;
use App\Controllers\MockController;
use App\Models\MockModel;

// require_once '../config.php';
require  '../vendor/autoload.php';

$app = AppFactory::create();

$app->addBodyParsingMiddleware();

// Add custom middleware beneath ErrorMiddleware
$app->add(new ContentTypeMiddleware());

$app->addErrorMiddleware(true, true, true);

$app->map(['POST', 'GET'], '/groups', new GroupController());
$app->map(['POST', 'GET'], '/groups/{group_id}/members', new MembersController());
$app->map(['POST', 'GET'], '/groups/{group_id}/messages', new MessageController());

$app->run();
