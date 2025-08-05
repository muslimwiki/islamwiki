<?php
/**
 * Debug Dropdown Test
 * 
 * Simple test page to verify the user dropdown menu functionality
 * 
 * @package IslamWiki
 * @version 0.0.44
 * @license AGPL-3.0-only
 */

// Simulate a logged-in user
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';

// Include the main application
require_once __DIR__ . '/../public/app.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dropdown Test - IslamWiki</title>
    
    <!-- Safa CSS Framework -->
    <link rel="stylesheet" href="/css/safa.css">
    
    <!-- Skin CSS -->
    <link rel="stylesheet" href="/skins/Muslim/css/muslim.css">
    
    <!-- ZamZam.js -->
    <script src="/js/zamzam.js"></script>
</head>
<body>
    <div class="container">
        <h1>Dropdown Test</h1>
        <p>This page tests the user dropdown menu functionality.</p>
        
        <!-- Test dropdown -->
        <div class="user-dropdown" z-data='{"open": false}'>
            <button z-click="open = !open" class="user-button" aria-label="User menu for admin">
                <div class="user-avatar">
                    <svg class="avatar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <span class="user-name">admin</span>
                <svg class="dropdown-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            
            <div z-show="open" 
                 z-click-away="open = false"
                 class="user-dropdown-menu">
                <a href="/dashboard" class="dropdown-item">
                    <svg class="dropdown-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                    </svg>
                    Dashboard
                </a>
                <a href="/profile" class="dropdown-item">
                    <svg class="dropdown-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Profile
                </a>
                <a href="/settings" class="dropdown-item">
                    <svg class="dropdown-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Settings
                </a>
                <div class="dropdown-divider"></div>
                <form action="/logout" method="POST" class="dropdown-form">
                    <button type="submit" class="dropdown-item logout-item">
                        <svg class="dropdown-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </div>
        
        <div style="margin-top: 20px;">
            <h3>Instructions:</h3>
            <ul>
                <li>Click the user button to toggle the dropdown</li>
                <li>Click outside the dropdown to close it</li>
                <li>Check the browser console for ZamZam.js logs</li>
            </ul>
        </div>
    </div>
</body>
</html> 