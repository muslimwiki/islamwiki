<?php

/**
 * Entry point for development server
 * 
 * This file ensures consistency between development (PHP built-in server) 
 * and production (Apache with .htaccess) environments by delegating to app.php
 */

// For production environment, .htaccess routes everything to app.php
// For development environment, this file delegates to app.php for consistency

require_once __DIR__ . '/app.php';
