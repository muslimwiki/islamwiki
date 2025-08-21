<?php

/**
 * Test page creation form without authentication
 */

// Load LocalSettings.php
require_once __DIR__ . '/../LocalSettings.php';

// Get active skin
global $wgActiveSkin;
$activeSkinName = $wgActiveSkin ?? 'Bismillah';

// Get skin CSS
$skinPath = __DIR__ . '/../skins/' . $activeSkinName;
$cssPath = $skinPath . '/css/bismillah.css';
$cssContent = file_exists($cssPath) ? file_get_contents($cssPath) : '/* CSS not found */';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Page Test - IslamWiki</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Safa CSS Framework -->
    <link rel="stylesheet" href="/css/safa.css">
    
    <!-- SimpleMDE for Markdown Editor -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.css">
    
    <!-- Bismillah Skin CSS -->
    <style>
        <?php echo $cssContent; ?>
    </style>
    
    <style>
        .page-creation-form {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: var(--settings-card-bg);
            border-radius: 1rem;
            box-shadow: var(--settings-shadow);
            border: 1px solid var(--settings-border);
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .form-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--settings-text);
            margin-bottom: 0.5rem;
        }
        
        .form-subtitle {
            color: var(--settings-text-secondary);
            margin: 0;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            color: var(--settings-text);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--settings-border);
            border-radius: 0.5rem;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--settings-primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        .form-select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--settings-border);
            border-radius: 0.5rem;
            font-size: 0.9rem;
            background: white;
            transition: all 0.3s ease;
        }
        
        .form-select:focus {
            outline: none;
            border-color: var(--settings-primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid var(--settings-border);
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background: var(--settings-primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--settings-primary-hover);
            transform: translateY(-1px);
            box-shadow: var(--settings-shadow-hover);
        }
        
        .btn-secondary {
            background: var(--settings-bg);
            color: var(--settings-text);
            border: 1px solid var(--settings-border);
        }
        
        .btn-secondary:hover {
            background: var(--settings-card-bg);
            border-color: var(--settings-primary);
        }
        
        .CodeMirror {
            border: 1px solid var(--settings-border);
            border-radius: 0.5rem;
            min-height: 400px;
        }
        
        .editor-toolbar {
            border: 1px solid var(--settings-border);
            border-bottom: none;
            border-radius: 0.5rem 0.5rem 0 0;
        }
        
        .CodeMirror {
            border-radius: 0 0 0.5rem 0.5rem;
        }
    </style>
</head>
<body>
    <div class="page-creation-form">
        <div class="form-header">
            <h1 class="form-title">Create New Page</h1>
            <p class="form-subtitle">Start writing and sharing knowledge on IslamWiki</p>
        </div>
        
        <form method="POST" action="/pages" id="pageForm">
            <div class="form-group">
                <label for="title" class="form-label">Page Title *</label>
                <input type="text" name="title" id="title" required
                       class="form-control"
                       placeholder="Enter the page title"
                       value="">
            </div>
            
            <div class="form-group">
                <label for="namespace" class="form-label">Namespace (optional)</label>
                <select name="namespace" id="namespace" class="form-select">
                    <option value="">(Main)</option>
                    <option value="Help">Help</option>
                    <option value="User">User</option>
                    <option value="Template">Template</option>
                    <option value="Category">Category</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="content" class="form-label">Content *</label>
                <textarea id="content" name="content" class="hidden"></textarea>
                <div id="editor"></div>
                <p class="form-text">Use Markdown syntax for formatting</p>
            </div>
            
            <div class="form-group">
                <label for="comment" class="form-label">Edit Summary</label>
                <input type="text" name="comment" id="comment" 
                       class="form-control"
                       placeholder="Briefly describe what this page is about">
            </div>
            
            <div class="form-group">
                <label class="form-label">
                    <input type="checkbox" name="is_minor_edit" id="is_minor_edit">
                    This is a minor edit
                </label>
            </div>
            
            <div class="form-group">
                <label class="form-label">
                    <input type="checkbox" name="watch" id="watch" checked>
                    Watch this page
                </label>
            </div>
            
            <div class="form-actions">
                <a href="/" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Page</button>
            </div>
        </form>
    </div>
    
    <!-- SimpleMDE Script -->
    <script src="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize SimpleMDE editor
        var simplemde = new SimpleMDE({
            element: document.getElementById("content"),
            spellChecker: false,
            placeholder: "Write your page content here using Markdown syntax...\n\n# Heading 1\n## Heading 2\n\n**Bold text** and *italic text*\n\n- List item 1\n- List item 2\n\n```\nCode block\n```",
            toolbar: [
                "bold", "italic", "heading", "|",
                "quote", "unordered-list", "ordered-list", "|",
                "link", "image", "|",
                "preview", "side-by-side", "fullscreen", "|",
                "guide"
            ]
        });
        
        // Form submission
        document.getElementById('pageForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get the content from the editor
            var content = simplemde.value();
            document.getElementById('content').value = content;
            
            // Show a message (in a real app, this would submit to the server)
            alert('Page creation form submitted! In a real application, this would create the page.\n\nTitle: ' + document.getElementById('title').value + '\nContent length: ' + content.length + ' characters');
        });
        
        // Auto-save draft functionality
        var autoSaveTimer;
        function autoSave() {
            var title = document.getElementById('title').value;
            var content = simplemde.value();
            var namespace = document.getElementById('namespace').value;
            
            if (title || content) {
                localStorage.setItem('page_draft', JSON.stringify({
                    title: title,
                    content: content,
                    namespace: namespace,
                    timestamp: new Date().toISOString()
                }));
                console.log('Draft auto-saved');
            }
        }
        
        // Auto-save every 30 seconds
        setInterval(autoSave, 30000);
        
        // Load draft on page load
        var draft = localStorage.getItem('page_draft');
        if (draft) {
            try {
                var draftData = JSON.parse(draft);
                var draftAge = new Date() - new Date(draftData.timestamp);
                
                // Only load draft if it's less than 1 hour old
                if (draftAge < 3600000) {
                    document.getElementById('title').value = draftData.title || '';
                    simplemde.value(draftData.content || '');
                    document.getElementById('namespace').value = draftData.namespace || '';
                    console.log('Draft loaded');
                } else {
                    localStorage.removeItem('page_draft');
                }
            } catch (e) {
                localStorage.removeItem('page_draft');
            }
        }
    });
    </script>
</body>
</html> 