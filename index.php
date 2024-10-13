<?php

use App\Middlewares\ContentTypeMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Handlers\Strategies\RequestResponseArgs;

require __DIR__ . '/../vendor/autoload.php';


// Database connection
class DB
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        $this->pdo = new PDO('sqlite:chat.db');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->createTables();
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new DB();
        }
        return self::$instance;
    }

    private function createTables()
    {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS groups (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL
        )");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            group_id INTEGER,
            user_id TEXT NOT NULL,
            content TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (group_id) REFERENCES groups(id)
        )");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS group_members (
            group_id INTEGER,
            user_id TEXT NOT NULL,
            FOREIGN KEY (group_id) REFERENCES groups(id),
            PRIMARY KEY (group_id, user_id)
        )");
    }

    public function query($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}

// Create Slim app
$app = AppFactory::create();
$app->getRouteCollector()->setDefaultInvocationStrategy(new RequestResponseArgs());

// Middleware to parse JSON body
$app->addBodyParsingMiddleware();
$app->add(new ContentTypeMiddleware($app));

// Routes
$app->post('/groups', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    $name = $data['name'] ?? '';

    if (empty($name)) {
        $response->getBody()->write(json_encode(['error' => 'Group name is required']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $db = DB::getInstance();
    $db->query("INSERT INTO groups (name) VALUES (?)", [$name]);
    $groupId = $db->query("SELECT last_insert_rowid() as id")->fetch(PDO::FETCH_ASSOC)['id'];

    $response->getBody()->write(json_encode(['id' => $groupId, 'name' => $name]));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/groups', function (Request $request, Response $response) {
    $db = DB::getInstance();
    $groups = $db->query("SELECT * FROM groups")->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($groups));
    // return $response->withHeader('Content-Type', 'application/json');
    return $response;
});

$app->post('/groups/{group_id}/join', function (Request $request, Response $response, $group_id) {
    $data = $request->getParsedBody();
    $userId = $data['user_id'] ?? '';

    if (empty($userId)) {
        $response->getBody()->write(json_encode(['error' => 'User ID is required']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $db = DB::getInstance();
    $db->query("INSERT OR IGNORE INTO group_members (group_id, user_id) VALUES (?, ?)", [$group_id, $userId]);

    $response->getBody()->write(json_encode(['success' => true]));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->post('/groups/{group_id}/messages', function (Request $request, Response $response, $group_id) {
    $data = $request->getParsedBody();
    $userId = $data['user_id'] ?? '';
    $content = $data['content'] ?? '';

    if (empty($userId) || empty($content)) {
        $response->getBody()->write(json_encode(['error' => 'User ID and content are required']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    $db = DB::getInstance();
    $db->query("INSERT INTO messages (group_id, user_id, content) VALUES (?, ?, ?)", [$group_id, $userId, $content]);
    $messageId = $db->query("SELECT last_insert_rowid() as id")->fetch(PDO::FETCH_ASSOC)['id'];

    $response->getBody()->write(json_encode(['id' => $messageId, 'group_id' => $group_id, 'user_id' => $userId, 'content' => $content]));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->get('/groups/{group_id}/messages', function (Request $request, Response $response, $group_id) {
    $since = $request->getQueryParams()['since'] ?? null;

    $db = DB::getInstance();
    $sql = "SELECT * FROM messages WHERE group_id = ?";
    $params = [$group_id];

    if ($since) {
        $sql .= " AND created_at > ?";
        $params[] = $since;
    }

    $sql .= " ORDER BY created_at ASC";
    $messages = $db->query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($messages));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
