<?php

use Slim\Factory\AppFactory;
use App\Controllers\GroupController;
use App\Controllers\MembersController;
use App\Controllers\MessageController;
use App\Middlewares\ContentTypeMiddleware;
use App\Controllers\MockController;
use App\Models\MockModel;

require_once '../config.php';
require  '../vendor/autoload.php';

$app = AppFactory::create();

$app->addBodyParsingMiddleware();

/*
IMPORTANT!!!!
YOU WROTE IT!!! USE IT!
*/
// Add custom middleware beneath ErrorMiddleware
$app->add(new ContentTypeMiddleware());

// Handle 404 how?
$app->addErrorMiddleware(true, true, true);


$app->map(['POST', 'GET'], '/groups', new GroupController());
$app->map(['POST', 'GET'], '/groups/{group_id}', new GroupController());
$app->map(['POST', 'GET'], '/groups/{group_id}/members', new MembersController());
$app->map(['POST', 'GET'], '/groups/{group_id}/messages', new MessageController());
// $app->post('/groups', [GroupController::class, 'createGroup']);
// $app->get('/groups/{id}', [GroupController::class, 'listGroups']);
// $app->post('/groups/{group_id}/join', [GroupController::class, 'joinGroup']);

// $app->map(['POST', 'GET'], '/try', new MockController(new MockModel()));

// $app->post('/groups/{group_id}/messages', [MessageController::class, 'sendMessage']);
// $app->get('/groups/{group_id}/messages', [MessageController::class, 'listMessages']);

$app->run();
