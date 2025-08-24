<?php

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Database\Connection;
use Container;;
use IslamWiki\Core\Http\Response;
use IslamWiki\Extensions\EnhancedMarkdown\Managers\TemplateManager;

/**
 * Template Controller
 * 
 * Handles template management operations following MediaWiki's approach
 * where templates are stored as namespace pages at /wiki/Template:Name
 * 
 * @version 0.0.3.0
 * @author IslamWiki Development Team
 */
class TemplateController extends Controller
{
    private TemplateManager $templateManager;
    
    public function __construct(Connection $db, Container $container)
    {
        parent::__construct($db, $container);
        
        // Get TemplateManager from container
        try {
            $this->templateManager = $container->get('EnhancedMarkdown.TemplateManager');
        } catch (\Exception $e) {
            // Fallback to creating a new instance
            $this->templateManager = new TemplateManager($db);
        }
    }
    
    /**
     * Display a template
     */
    public function show(string $templateName, string $locale = 'en'): Response
    {
        try {
            // Load template content
            $templateContent = $this->templateManager->loadTemplate($templateName);
            
            if (!$templateContent) {
                return $this->view('errors/404', [
                    'message' => "Template '{$templateName}' not found",
                    'locale' => $locale
                ], 404);
            }
            
            // Get template metadata
            $metadata = $this->templateManager->getTemplateMetadata($templateName);
            
            return $this->view('templates/show', [
                'templateName' => $templateName,
                'templateContent' => $templateContent,
                'metadata' => $metadata,
                'locale' => $locale
            ]);
            
        } catch (\Exception $e) {
            error_log("Error displaying template '{$templateName}': " . $e->getMessage());
            return $this->view('errors/500', [
                'message' => 'Error loading template',
                'locale' => $locale
            ], 500);
        }
    }
    
    /**
     * Show template edit form
     */
    public function edit(string $templateName, string $locale = 'en'): Response
    {
        try {
            // Load existing template content
            $templateContent = $this->templateManager->loadTemplate($templateName);
            
            if (!$templateContent) {
                // Template doesn't exist, show create form
                return $this->view('templates/edit', [
                    'templateName' => $templateName,
                    'templateContent' => '',
                    'isNew' => true,
                    'locale' => $locale
                ]);
            }
            
            // Get template metadata
            $metadata = $this->templateManager->getTemplateMetadata($templateName);
            
            return $this->view('templates/edit', [
                'templateName' => $templateName,
                'templateContent' => $templateContent,
                'isNew' => false,
                'metadata' => $metadata,
                'locale' => $locale
            ]);
            
        } catch (\Exception $e) {
            error_log("Error editing template '{$templateName}': " . $e->getMessage());
            return $this->view('errors/500', [
                'message' => 'Error loading template for editing',
                'locale' => $locale
            ], 500);
        }
    }
    
    /**
     * Save template changes
     */
    public function update(Request $request, string $templateName, string $locale = 'en'): Response
    {
        try {
            $data = $request->getParsedBody();
            $content = $data['content'] ?? '';
            $comment = $data['comment'] ?? 'Updated template';
            
            if (empty($content)) {
                return $this->view('errors/400', [
                    'message' => 'Template content cannot be empty',
                    'locale' => $locale
                ], 400);
            }
            
            // Save template
            $success = $this->templateManager->saveTemplate($templateName, $content, $comment);
            
            if (!$success) {
                return $this->view('errors/500', [
                    'message' => 'Failed to save template',
                    'locale' => $locale
                ], 500);
            }
            
            // Redirect to template view
            return $this->redirect("/wiki/Template:{$templateName}");
            
        } catch (\Exception $e) {
            error_log("Error updating template '{$templateName}': " . $e->getMessage());
            return $this->view('errors/500', [
                'message' => 'Error saving template',
                'locale' => $locale
            ], 500);
        }
    }
    
    /**
     * List all templates
     */
    public function index(string $locale = 'en'): Response
    {
        try {
            $templates = $this->templateManager->listTemplates();
            
            return $this->view('templates/index', [
                'templates' => $templates,
                'locale' => $locale
            ]);
            
        } catch (\Exception $e) {
            error_log("Error listing templates: " . $e->getMessage());
            return $this->view('errors/500', [
                'message' => 'Error loading template list',
                'locale' => $locale
            ], 500);
        }
    }
    
    /**
     * Delete a template
     */
    public function destroy(string $templateName, string $locale = 'en'): Response
    {
        try {
            // Check if template exists
            if (!$this->templateManager->templateExists($templateName)) {
                return $this->view('errors/404', [
                    'message' => "Template '{$templateName}' not found",
                    'locale' => $locale
                ], 404);
            }
            
            // Delete template
            $success = $this->templateManager->deleteTemplate($templateName);
            
            if (!$success) {
                return $this->view('errors/500', [
                    'message' => 'Failed to delete template',
                    'locale' => $locale
                ], 500);
            }
            
            // Redirect to template list
            return $this->redirect('/wiki/Special:Templates');
            
        } catch (\Exception $e) {
            error_log("Error deleting template '{$templateName}': " . $e->getMessage());
            return $this->view('errors/500', [
                'message' => 'Error deleting template',
                'locale' => $locale
            ], 500);
        }
    }
} 