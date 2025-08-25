<?php

declare(strict_types=1);

namespace IslamWiki\Http\Views;

use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Container\Container;

/**
 * Enhanced View class for rendering HTML responses with Twig templates and skin integration
 */
class View
{
    private static ?Container $container = null;
    
    /**
     * Set the container for accessing services
     */
    public static function setContainer(Container $container): void
    {
        self::$container = $container;
    }
    
    /**
     * Get the container
     */
    private static function getContainer(): Container
    {
        if (!self::$container) {
            throw new \RuntimeException('Container not set. Call View::setContainer() first.');
        }
        return self::$container;
    }
    
    /**
     * Get the TwigRenderer from the container
     */
    private static function getTwigRenderer()
    {
        $container = self::getContainer();
        if ($container->has('view')) {
            return $container->get('view');
        }
        throw new \RuntimeException('TwigRenderer not found in container');
    }
    
    /**
     * Get user information from session
     */
    private static function getUserFromSession(): ?array
    {
        // Only start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check if user is authenticated
        if (isset($_SESSION['is_authenticated']) && $_SESSION['is_authenticated'] === true) {
            return [
                'id' => $_SESSION['user_id'] ?? null,
                'username' => $_SESSION['username'] ?? 'Unknown',
                'role' => $_SESSION['role'] ?? 'User',
                'edit_count' => $_SESSION['edit_count'] ?? 0,
                'created_at' => $_SESSION['created_at'] ?? 'Recently',
                'is_authenticated' => true
            ];
        }
        
        return null;
    }
    
    /**
     * Get common template data including user information
     */
    private static function getCommonTemplateData(): array
    {
        try {
            return [
                'current_language' => self::getCurrentLanguage(),
                'current_language_flag' => self::getCurrentLanguageFlag(),
                'current_language_name' => self::getCurrentLanguageName(),
                'current_language_native' => self::getCurrentLanguageNativeName(),
                'supported_languages' => self::getSupportedLanguages(),
                'user' => self::getUserFromSession(),
                'current_path' => $_SERVER['REQUEST_URI'] ?? '/',
            ];
        } catch (\Exception $e) {
            // Fallback to basic data if there's an error
            error_log("Error getting common template data: " . $e->getMessage());
            return [
                'current_language' => 'en',
                'current_language_flag' => '🇺🇸',
                'current_language_name' => 'English',
                'current_language_native' => 'English',
                'supported_languages' => ['en', 'ar'],
                'user' => null,
                'current_path' => $_SERVER['REQUEST_URI'] ?? '/',
            ];
        }
    }
    
    /**
     * Render a template with data
     */
    public static function render(string $template, array $data = []): Response
    {
        $twig = self::getTwigRenderer();
        
        $templateData = array_merge(self::getCommonTemplateData(), $data, [
            'title' => ($data['title'] ?? 'Page') . ' - IslamWiki',
            'page_type' => 'custom'
        ]);
        
        $html = $twig->render($template, $templateData);
        return new Response(200, ["Content-Type" => "text/html; charset=UTF-8"], $html);
    }
    
    /**
     * Render home page
     */
    public static function home(array $data = []): Response
    {
        $twig = self::getTwigRenderer();
        
        $templateData = array_merge(self::getCommonTemplateData(), $data, [
            'title' => 'Home - IslamWiki',
            'page_type' => 'home'
        ]);
        
        $html = $twig->render('pages/home.twig', $templateData);
        return new Response(200, ["Content-Type" => "text/html; charset=UTF-8"], $html);
    }
    
    /**
     * Render dashboard page
     */
    public static function dashboard(array $data = []): Response
    {
        $twig = self::getTwigRenderer();
        
        $templateData = array_merge(self::getCommonTemplateData(), $data, [
            'title' => 'Dashboard - IslamWiki',
            'page_type' => 'dashboard'
        ]);
        
        $html = $twig->render('dashboard/index.twig', $templateData);
        return new Response(200, ["Content-Type" => "text/html; charset=UTF-8"], $html);
    }
    
    /**
     * Render wiki page
     */
    public static function wikiPage(array $data = []): Response
    {
        $twig = self::getTwigRenderer();
        
        $templateData = array_merge(self::getCommonTemplateData(), $data, [
            'title' => ($data['title'] ?? 'Wiki Page') . ' - IslamWiki',
            'page_type' => 'wiki'
        ]);
        
        $html = $twig->render('wiki/page.twig', $templateData);
        return new Response(200, ["Content-Type" => "text/html; charset=UTF-8"], $html);
    }
    
    /**
     * Render wiki index page
     */
    public static function wikiIndex(array $data = []): Response
    {
        $twig = self::getTwigRenderer();
        
        $templateData = array_merge(self::getCommonTemplateData(), $data, [
            'title' => 'Wiki Index - IslamWiki',
            'page_type' => 'wiki_index'
        ]);
        
        $html = $twig->render('wiki/index.twig', $templateData);
        return new Response(200, ["Content-Type" => "text/html; charset=UTF-8"], $html);
    }
    
    /**
     * Render login page
     */
    public static function login(array $data = []): Response
    {
        $twig = self::getTwigRenderer();
        
        $templateData = array_merge(self::getCommonTemplateData(), $data, [
            'title' => 'Login - IslamWiki',
            'page_type' => 'login'
        ]);
        
        $html = $twig->render('auth/login.twig', $templateData);
        return new Response(200, ["Content-Type" => "text/html; charset=UTF-8"], $html);
    }
    
    /**
     * Render register page
     */
    public static function register(array $data = []): Response
    {
        $twig = self::getTwigRenderer();
        
        $templateData = array_merge(self::getCommonTemplateData(), $data, [
            'title' => 'Register - IslamWiki',
            'page_type' => 'register'
        ]);
        
        $html = $twig->render('auth/register.twig', $templateData);
        return new Response(200, ["Content-Type" => "text/html; charset=UTF-8"], $html);
    }
    
    /**
     * Render settings page
     */
    public static function settings(array $data = []): Response
    {
        $twig = self::getTwigRenderer();
        
        $templateData = array_merge(self::getCommonTemplateData(), $data, [
            'title' => 'Settings - IslamWiki',
            'page_type' => 'settings'
        ]);
        
        $html = $twig->render('settings/index.twig', $templateData);
        return new Response(200, ["Content-Type" => "text/html; charset=UTF-8"], $html);
    }
    
    /**
     * Render a search page with Twig templates
     */
    public static function search(string $query = '', array $results = []): Response
    {
        $twig = self::getTwigRenderer();
        
        $templateData = [
            'title' => 'Search',
            'query' => $query,
            'results' => $results,
            'current_language' => self::getCurrentLanguage(),
            'current_path' => $_SERVER['REQUEST_URI'] ?? '/',
            'page_type' => 'search'
        ];
        
        $html = $twig->render('layouts/app.twig', $templateData);
        return new Response(200, ["Content-Type" => "text/html; charset=UTF-8"], $html);
    }
    
    /**
     * Render an error page with Twig templates
     */
    public static function error(int $statusCode, string $title, string $message): Response
    {
        $twig = self::getTwigRenderer();
        
        $templateData = [
            'title' => $title,
            'error_code' => $statusCode,
            'error_message' => $message,
            'current_language' => self::getCurrentLanguage(),
            'current_path' => $_SERVER['REQUEST_URI'] ?? '/',
            'page_type' => 'error'
        ];
        
        $html = $twig->render('layouts/app.twig', $templateData);
        return new Response($statusCode, ["Content-Type" => "text/html; charset=UTF-8"], $html);
    }
    
    /**
     * Get current language
     */
    public static function getCurrentLanguage(): string
    {
        // Extract language from current URL path
        $currentPath = $_SERVER['REQUEST_URI'] ?? '/';
        if (preg_match('/^\/([a-z]{2})\//', $currentPath, $matches)) {
            return $matches[1];
        }
        return 'en'; // Default fallback
    }
    
    /**
     * Get current language flag
     */
    public static function getCurrentLanguageFlag(): string
    {
        $lang = self::getCurrentLanguage();
        $flags = ['en' => '🇺🇸', 'ar' => '🇸🇦'];
        return $flags[$lang] ?? '🇺🇸';
    }
    
    /**
     * Get current language name
     */
    public static function getCurrentLanguageName(): string
    {
        $lang = self::getCurrentLanguage();
        $names = ['en' => 'English', 'ar' => 'العربية'];
        return $names[$lang] ?? 'English';
    }
    
    /**
     * Get current language native name
     */
    public static function getCurrentLanguageNativeName(): string
    {
        $lang = self::getCurrentLanguage();
        $nativeNames = ['en' => 'English', 'ar' => 'العربية'];
        return $nativeNames[$lang] ?? 'English';
    }
    
    /**
     * Get supported languages array
     */
    public static function getSupportedLanguages(): array
    {
        return [
            'en' => [
                'name' => 'English',
                'native' => 'English',
                'flag' => '🇺🇸',
                'code' => 'en'
            ],
            'ar' => [
                'name' => 'العربية',
                'native' => 'Arabic',
                'flag' => '🇸🇦',
                'code' => 'ar'
            ]
        ];
    }
    
    /**
     * Get language-switched URL for current page
     */
    public static function getLanguageSwitchedUrl(string $targetLanguage): string
    {
        $currentPath = $_SERVER['REQUEST_URI'] ?? '/';
        $currentLang = self::getCurrentLanguage();
        
        // Replace the current language prefix with the target language
        $switchedPath = preg_replace('/^\/' . preg_quote($currentLang, '/') . '\//', '/' . $targetLanguage . '/', $currentPath);
        
        // If no replacement was made, append the target language
        if ($switchedPath === $currentPath) {
            $switchedPath = '/' . $targetLanguage . $currentPath;
        }
        
        return $switchedPath;
    }
} 