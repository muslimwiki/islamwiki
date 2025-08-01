<?php
declare(strict_types=1);

/**
 * Skin Middleware
 * 
 * Updates skin data dynamically for each request based on the current user.
 * 
 * @package IslamWiki\Http\Middleware
 * @version 0.0.29
 * @license AGPL-3.0-only
 */

namespace IslamWiki\Http\Middleware;

use IslamWiki\Core\Application;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Skins\SkinManager;

class SkinMiddleware
{
    /**
     * @var Application The application instance
     */
    private Application $app;
    
    /**
     * Constructor
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }
    
    /**
     * Handle the request and update skin data
     */
    public function handle(Request $request, callable $next): Response
    {
        error_log("SkinMiddleware::handle - Starting skin middleware execution");
        
        // Update skin data for the current user
        $this->updateSkinDataForCurrentUser();
        
        error_log("SkinMiddleware::handle - Skin middleware execution completed");
        
        // Continue with the request
        return $next($request);
    }
    
    /**
     * Update skin data for the current user
     */
    private function updateSkinDataForCurrentUser(): void
    {
        error_log("SkinMiddleware::updateSkinDataForCurrentUser - Starting");
        
        try {
            $container = $this->app->getContainer();
            $session = $container->get('session');
            $skinManager = $container->get('skin.manager');
            $viewRenderer = $container->get('view');
            
            error_log("SkinMiddleware::updateSkinDataForCurrentUser - Got container services");
            
            // Get the active skin for the current user
            $activeSkin = null;
            $activeSkinName = '';
            
            if ($session->isLoggedIn()) {
                $userId = $session->getUserId();
                $activeSkin = $skinManager->getActiveSkinForUser($userId);
                $activeSkinName = $skinManager->getActiveSkinNameForUser($userId);
            } else {
                $activeSkin = $skinManager->getActiveSkin();
                $activeSkinName = $skinManager->getActiveSkinName();
            }
            
            // Prepare skin data
            $skinData = [
                'css' => $activeSkin ? $activeSkin->getCssContent() : '',
                'js' => $activeSkin ? $activeSkin->getJsContent() : '',
                'name' => $activeSkin ? $activeSkin->getName() : 'default',
                'version' => $activeSkin ? $activeSkin->getVersion() : '0.0.29',
                'config' => $activeSkin ? ($activeSkin->getConfig() ?? []) : [],
            ];
            
            // Update the view globals with current user's skin data
            $viewRenderer->addGlobals([
                'skin_css' => $skinData['css'],
                'skin_js' => $skinData['js'],
                'skin_name' => $skinData['name'],
                'skin_version' => $skinData['version'],
                'skin_config' => $skinData['config'],
                'active_skin' => $activeSkinName,
            ]);
            
            error_log("SkinMiddleware::updateSkinDataForCurrentUser - Added skin data to view globals");
            error_log("SkinMiddleware::updateSkinDataForCurrentUser - Skin CSS length: " . strlen($skinData['css']));
            
        } catch (\Throwable $e) {
            error_log("SkinMiddleware::updateSkinDataForCurrentUser - Error: " . $e->getMessage());
            
            // Fallback to default skin data
            $viewRenderer = $this->app->getContainer()->get('view');
            $viewRenderer->addGlobals([
                'skin_css' => '',
                'skin_js' => '',
                'skin_name' => 'default',
                'skin_version' => '0.0.29',
                'skin_config' => [],
                'active_skin' => 'bismillah',
            ]);
        }
    }
} 