<?
declare(strict_types=1);
php\nuse IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Http\Exceptions\HttpException;
use IslamWiki\Http\Controllers\PageController;
use IslamWiki\Http\Controllers\UserController;
use IslamWiki\Http\Controllers\ApiController;

return function (\IslamWiki\Core\Application $app) {
    $container = $app->getContainer();
    $db = $container->get(Connection::class);
    
    // Create controller instances
    $pageController = new PageController($db);
    $userController = new UserController($db);
    $apiController = new ApiController($db);
    
    // Homepage
    $app->get('/', function (Request $request) use ($pageController) {
        return $pageController->show($request, 'Main_Page');
    });
    
    // Page routes
    $app->get('/wiki', [$pageController, 'index']);
    $app->get('/wiki/{slug:.+}', [$pageController, 'show']);
    $app->get('/wiki/{slug:.+}/history', [$pageController, 'history']);
    $app->get('/wiki/{slug:.+}/history/{revisionId:\d+}', [$pageController, 'showRevision']);
    $app->get('/wiki/{slug:.+}/revert/{revisionId:\d+}', [$pageController, 'revert']);
    $app->get('/wiki/{slug:.+}/edit', [$pageController, 'edit']);
    $app->post('/wiki/{slug:.+}', [$pageController, 'update']);
    $app->get('/create', [$pageController, 'create']);
    $app->post('/create', [$pageController, 'store']);
    $app->post('/wiki/{slug:.+}/lock', [$pageController, 'lock']);
    $app->post('/wiki/{slug:.+}/unlock', [$pageController, 'unlock']);
    
    // User authentication routes
    $app->get('/login', [$userController, 'showLoginForm']);
    $app->post('/login', [$userController, 'login']);
    $app->get('/logout', [$userController, 'logout']);
    $app->get('/register', [$userController, 'showRegistrationForm']);
    $app->post('/register', [$userController, 'register']);
    $app->get('/profile', [$userController, 'showProfile']);
    $app->post('/profile', [$userController, 'updateProfile']);
    
    // API routes
    $app->group('/api', function () use ($apiController) {
        // Pages
        $this->get('/pages', [$apiController, 'listPages']);
        $this->post('/pages', [$apiController, 'createPage']);
        $this->get('/pages/{slug:.+}', [$apiController, 'getPage']);
        $this->put('/pages/{slug:.+}', [$apiController, 'updatePage']);
        $this->delete('/pages/{slug:.+}', [$apiController, 'deletePage']);
        $this->get('/pages/{slug:.+}/history', [$apiController, 'getPageHistory']);
        
        // Search
        $this->get('/search', [$apiController, 'search']);
        
        // Users
        $this->get('/users/current', [$apiController, 'getCurrentUser']);
    });
    
    // Error handling
    $app->addErrorMiddleware(true, true, true);
    
    // 404 Not Found handler
    $app->setErrorHandler(function (Request $request, Throwable $exception) use ($app) {
        $code = $exception->getCode();
        $message = $exception->getMessage();
        
        if ($exception instanceof HttpException) {
            $code = $exception->getStatusCode();
        } elseif ($exception instanceof PDOException) {
            $code = 500;
            $message = 'Database error';
        } else {
            $code = $code >= 400 && $code < 600 ? $code : 500;
        }
        
        $accept = $request->getHeaderLine('Accept');
        $wantsJson = strpos($accept, 'application/json') !== false;
        
        if ($wantsJson) {
            $response = new Response();
            $response->getBody()->write(json_encode([
                'error' => [
                    'code' => $code,
                    'message' => $message,
                ]
            ]));
            
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus($code);
        }
        
        // Render error page
        return $app->getContainer()->get('view')->render(
            new Response($code),
            'error.twig',
            [
                'code' => $code,
                'message' => $message,
                'exception' => $exception,
            ]
        )->withStatus($code);
    });
};
