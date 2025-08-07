<?php

/**
 * Skin Middleware
 *
 * Updates skin data dynamically for each request based on the current user.
 *
 * @category  IslamWiki
 * @package   Http\Middleware
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.29
 */

declare(strict_types=1);

namespace IslamWiki\Http\Middleware;

use IslamWiki\Core\NizamApplication;
use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Skins\SkinManager;

/**
 * SkinMiddleware - Middleware for dynamic skin management
 *
 * @category  IslamWiki
 * @package   Http\Middleware
 * @author    IslamWiki Development Team
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPL-3.0-only
 * @link      https://islam.wiki
 * @since     0.0.29
 */
class SkinMiddleware
{
    /**
     * @var NizamApplication The application instance
     */
    private NizamApplication $_app;

    /**
     * Constructor
     *
     * @param NizamApplication $app The application instance
     */
    public function __construct(NizamApplication $app)
    {
        $this->_app = $app;
    }

    /**
     * Handle the request and update skin data
     *
     * @param Request $request The incoming request
     * @param callable $next The next middleware in the stack
     *
     * @return Response
     */
    public function handle(Request $request, callable $next): Response
    {
        error_log("SkinMiddleware::handle - Starting skin middleware execution");
        error_log("SkinMiddleware::handle - Request URI: " . $request->getUri()->getPath());
        error_log("SkinMiddleware::handle - Middleware stack executing SkinMiddleware");

        // Skip skin middleware for authentication routes to prevent session interference
        $authRoutes = ['/login', '/register', '/forgot-password', '/logout'];
        $currentPath = $request->getUri()->getPath();

        if (in_array($currentPath, $authRoutes)) {
            error_log("SkinMiddleware::handle - Skipping skin middleware for auth route: " . $currentPath);
            return $next($request);
        }

        // Update skin data for the current user
        $this->updateSkinDataForCurrentUser($request);

        // Update user authentication state
        error_log("SkinMiddleware::handle - About to call updateUserAuthenticationState");
        try {
            $this->updateUserAuthenticationState($request);
            error_log("SkinMiddleware::handle - Completed updateUserAuthenticationState successfully");
        } catch (\Throwable $e) {
            error_log("SkinMiddleware::handle - Error in updateUserAuthenticationState: " . $e->getMessage());
            error_log("SkinMiddleware::handle - Error trace: " . $e->getTraceAsString());
        }

        error_log("SkinMiddleware::handle - Skin middleware execution completed");

        // Continue with the request
        $response = $next($request);

        error_log("SkinMiddleware::handle - Response status: " . $response->getStatusCode());

        return $response;
    }

    /**
     * Update skin data for the current user
     *
     * @param Request $request The incoming request
     *
     * @return void
     */
    private function updateSkinDataForCurrentUser(Request $request): void
    {
        error_log("SkinMiddleware::updateSkinDataForCurrentUser - Starting");

        try {
            $container = $this->_app->getContainer();
            $skinManager = $container->get('skin.manager');
            $viewRenderer = $container->get('view');

            error_log("SkinMiddleware::updateSkinDataForCurrentUser - Got container services");

            // Check for URL parameter skin override
            $urlSkinOverride = $request->getQueryParam('skin');
            error_log("SkinMiddleware::updateSkinDataForCurrentUser - Query params: " . json_encode($request->getQueryParams()));
            error_log("SkinMiddleware::updateSkinDataForCurrentUser - URL skin override value: " . ($urlSkinOverride ?? 'null'));
            if ($urlSkinOverride) {
                $urlSkinOverride = strtolower(trim($urlSkinOverride));
                error_log("SkinMiddleware::updateSkinDataForCurrentUser - URL skin override detected: " . $urlSkinOverride);

                // Validate the skin exists
                if ($skinManager->skinExists($urlSkinOverride)) {
                    error_log("SkinMiddleware::updateSkinDataForCurrentUser - Valid skin override: " . $urlSkinOverride);
                    $skinManager->setActiveSkin($urlSkinOverride);
                    error_log("SkinMiddleware::updateSkinDataForCurrentUser - Set active skin to: " . $urlSkinOverride);
                } else {
                    error_log("SkinMiddleware::updateSkinDataForCurrentUser - Invalid skin override: " . $urlSkinOverride);
                }
            }

            // Get current user's skin preference
            $session = $container->get('session');
            $user = $session->getUser();

            if ($user) {
                error_log("SkinMiddleware::updateSkinDataForCurrentUser - User found: " . $user['id']);
                $userSkin = $user['skin'] ?? null;
                error_log("SkinMiddleware::updateSkinDataForCurrentUser - User skin preference: " . ($userSkin ?? 'null'));

                if ($userSkin && $skinManager->skinExists($userSkin)) {
                    error_log("SkinMiddleware::updateSkinDataForCurrentUser - Setting user skin: " . $userSkin);
                    $skinManager->setActiveSkin($userSkin);
                }
            } else {
                error_log("SkinMiddleware::updateSkinDataForCurrentUser - No user found");
            }

            // Get active skin data
            $activeSkin = $skinManager->getActiveSkin();
            error_log("SkinMiddleware::updateSkinDataForCurrentUser - Active skin: " . ($activeSkin ? $activeSkin->getName() : 'null'));

            // Update view renderer with skin data
            if ($viewRenderer instanceof \IslamWiki\Core\View\TwigRenderer && $activeSkin) {
                $skinData = [
                    'name' => $activeSkin->getName(),
                    'css' => $activeSkin->getCssContent(),
                    'js' => $activeSkin->getJsContent(),
                    'css_url' => '/skins/' . $activeSkin->getName() . '/css/' . strtolower($activeSkin->getName()) . '.css',
                    'js_url' => '/skins/' . $activeSkin->getName() . '/js/' . strtolower($activeSkin->getName()) . '.js',
                    'version' => $activeSkin->getVersion(),
                    'config' => $activeSkin->getConfig()
                ];

                $viewRenderer->addGlobals([
                    'skin_css' => $skinData['css'],
                    'skin_js' => $skinData['js'],
                    'skin_css_url' => $skinData['css_url'],
                    'skin_js_url' => $skinData['js_url'],
                    'active_skin' => $skinData['name'],
                    'skin_version' => $skinData['version'],
                    'skin_config' => $skinData['config']
                ]);

                error_log("SkinMiddleware::updateSkinDataForCurrentUser - Updated view renderer with skin data");
            }

            error_log("SkinMiddleware::updateSkinDataForCurrentUser - Completed successfully");

        } catch (\Throwable $e) {
            error_log("SkinMiddleware::updateSkinDataForCurrentUser - Error: " . $e->getMessage());
            error_log("SkinMiddleware::updateSkinDataForCurrentUser - Error trace: " . $e->getTraceAsString());
        }
    }

    /**
     * Update user authentication state
     *
     * @param Request $request The incoming request
     *
     * @return void
     */
    private function updateUserAuthenticationState(Request $request): void
    {
        error_log("SkinMiddleware::updateUserAuthenticationState - Starting");

        try {
            $container = $this->_app->getContainer();
            $session = $container->get('session');
            $viewRenderer = $container->get('view');

            // Get current user
            $user = $session->getUser();
            $isLoggedIn = $user !== null;

            error_log("SkinMiddleware::updateUserAuthenticationState - User logged in: " . ($isLoggedIn ? 'yes' : 'no'));

            // Update view renderer with user authentication state
            if ($viewRenderer instanceof \IslamWiki\Core\View\TwigRenderer) {
                $viewRenderer->addGlobals([
                    'user' => $user,
                    'is_logged_in' => $isLoggedIn,
                    'user_id' => $user['id'] ?? null,
                    'username' => $user['username'] ?? null,
                    'user_email' => $user['email'] ?? null,
                    'user_role' => $user['role'] ?? 'guest'
                ]);

                error_log("SkinMiddleware::updateUserAuthenticationState - Updated view renderer with user state");
            }

            error_log("SkinMiddleware::updateUserAuthenticationState - Completed successfully");

        } catch (\Throwable $e) {
            error_log("SkinMiddleware::updateUserAuthenticationState - Error: " . $e->getMessage());
            error_log("SkinMiddleware::updateUserAuthenticationState - Error trace: " . $e->getTraceAsString());
        }
    }
}
