<?php

namespace IslamWiki\Extensions\EnhancedMarkdown\Managers;

/**
 * Template Manager
 * 
 * Manages templates as namespace pages following MediaWiki's approach.
 * Templates are stored at /wiki/Template:TemplateName and can be modified by users.
 * 
 * @version 0.0.3.0
 * @author IslamWiki Development Team
 */
class TemplateManager
{
    private $connection;
    private array $templateCache = [];
    
    public function __construct($connection)
    {
        $this->connection = $connection;
    }
    
    /**
     * Load a template from the database
     * 
     * @param string $templateName The template name (e.g., "Good article")
     * @return string|null Template content or null if not found
     */
    public function loadTemplate(string $templateName): ?string
    {
        // Check cache first
        if (isset($this->templateCache[$templateName])) {
            return $this->templateCache[$templateName];
        }
        
        try {
            // Query the database for the template
            $query = "SELECT content FROM wiki_pages WHERE title = ? AND namespace = 'Template' AND status = 'published'";
            $stmt = $this->connection->prepare($query);
            $stmt->execute(['Template:' . $templateName]);
            
            $result = $stmt->fetch();
            
            if ($result) {
                $content = $result['content'];
                // Cache the template
                $this->templateCache[$templateName] = $content;
                return $content;
            }
            
            return null;
            
        } catch (\Exception $e) {
            // Log error and return null
            error_log("Error loading template '$templateName': " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Save a template to the database
     * 
     * @param string $templateName The template name
     * @param string $content The template content
     * @param string $comment Edit comment
     * @return bool Success status
     */
    public function saveTemplate(string $templateName, string $content, string $comment = 'Updated template'): bool
    {
        try {
            // Check if template exists
            $existingTemplate = $this->loadTemplate($templateName);
            
            if ($existingTemplate !== null) {
                // Update existing template
                $query = "UPDATE wiki_pages SET content = ?, updated_at = NOW() WHERE title = ? AND namespace = 'Template'";
                $stmt = $this->connection->prepare($query);
                $stmt->execute([$content, 'Template:' . $templateName]);
            } else {
                // Create new template
                $query = "INSERT INTO wiki_pages (title, namespace, content, slug, status, created_at, updated_at) VALUES (?, 'Template', ?, ?, 'published', NOW(), NOW())";
                $stmt = $this->connection->prepare($query);
                $slug = $this->generateSlug('Template:' . $templateName);
                $stmt->execute(['Template:' . $templateName, $content, $slug]);
            }
            
            // Clear cache
            unset($this->templateCache[$templateName]);
            
            return true;
            
        } catch (\Exception $e) {
            error_log("Error saving template '$templateName': " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete a template
     * 
     * @param string $templateName The template name
     * @return bool Success status
     */
    public function deleteTemplate(string $templateName): bool
    {
        try {
            $query = "DELETE FROM wiki_pages WHERE title = ? AND namespace = 'Template'";
            $stmt = $this->connection->prepare($query);
            $stmt->execute(['Template:' . $templateName]);
            
            // Clear cache
            unset($this->templateCache[$templateName]);
            
            return true;
            
        } catch (\Exception $e) {
            error_log("Error deleting template '$templateName': " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * List all available templates
     * 
     * @return array List of template names
     */
    public function listTemplates(): array
    {
        try {
            $query = "SELECT title FROM wiki_pages WHERE namespace = 'Template' AND status = 'published' ORDER BY title";
            $stmt = $this->connection->prepare($query);
            $stmt->execute();
            
            $templates = [];
            while ($row = $stmt->fetch()) {
                // Remove "Template:" prefix
                $templateName = str_replace('Template:', '', $row['title']);
                $templates[] = $templateName;
            }
            
            return $templates;
            
        } catch (\Exception $e) {
            error_log("Error listing templates: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check if a template exists
     * 
     * @param string $templateName The template name
     * @return bool True if template exists
     */
    public function templateExists(string $templateName): bool
    {
        return $this->loadTemplate($templateName) !== null;
    }
    
    /**
     * Get template metadata (creation date, last modified, etc.)
     * 
     * @param string $templateName The template name
     * @return array|null Template metadata or null if not found
     */
    public function getTemplateMetadata(string $templateName): ?array
    {
        try {
            $query = "SELECT created_at, updated_at, creator_id, last_editor_id FROM wiki_pages WHERE title = ? AND namespace = 'Template'";
            $stmt = $this->connection->prepare($query);
            $stmt->execute(['Template:' . $templateName]);
            
            $result = $stmt->fetch();
            return $result ?: null;
            
        } catch (\Exception $e) {
            error_log("Error getting template metadata for '$templateName': " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Generate a URL-friendly slug
     */
    private function generateSlug(string $title): string
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    }
    
    /**
     * Clear template cache
     */
    public function clearCache(): void
    {
        $this->templateCache = [];
    }
} 