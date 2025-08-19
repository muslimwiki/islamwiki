<?php

declare(strict_types=1);

namespace IslamWiki\Core\Media;

use IslamWiki\Core\Database\Connection;
use IslamWiki\Core\Container\AsasContainer;
use Psr\Log\LoggerInterface;

/**
 * Media Manager
 * 
 * Handles media file operations including upload, storage,
 * metadata management, and file serving.
 */
class MediaManager
{
    private Connection $db;
    private AsasContainer $container;
    private string $uploadPath;
    private string $publicPath;
    private LoggerInterface $logger;
    
    public function __construct(Connection $db, AsasContainer $container)
    {
        $this->db = $db;
        $this->container = $container;
        $this->uploadPath = $container->get('config')->get('media.upload_path', 'storage/media');
        $this->publicPath = $container->get('config')->get('media.public_path', 'public/media');
        $this->logger = $container->get('logger');
        
        // Ensure upload directory exists
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }
    
    /**
     * Upload a file and create media record
     */
    public function uploadFile($file, int $userId): array
    {
        try {
            // Generate unique filename
            $filename = $this->generateUniqueFilename($file->getClientFilename());
            $extension = strtolower(pathinfo($file->getClientFilename(), PATHINFO_EXTENSION));
            
            // Determine media type
            $mediaType = $this->getMediaType($extension);
            
            // Move file to upload directory
            $filePath = $this->uploadPath . '/' . $filename;
            $file->moveTo($filePath);
            
            // Get file metadata
            $fileSize = filesize($filePath);
            $mimeType = $this->getMimeType($filePath, $extension);
            
            // Create media record
            $mediaId = $this->createMediaRecord([
                'filename' => $filename,
                'original_name' => $file->getClientFilename(),
                'title' => pathinfo($file->getClientFilename(), PATHINFO_FILENAME),
                'description' => '',
                'media_type' => $mediaType,
                'mime_type' => $mimeType,
                'file_size' => $fileSize,
                'extension' => $extension,
                'uploaded_by' => $userId,
                'upload_path' => $filePath
            ]);
            
            // Generate public URL
            $url = $this->getPublicUrl($filename);
            
            $this->logger->info('Media file uploaded successfully', [
                'media_id' => $mediaId,
                'filename' => $filename,
                'user_id' => $userId,
                'file_size' => $fileSize
            ]);
            
            return [
                'id' => $mediaId,
                'filename' => $filename,
                'url' => $url,
                'title' => pathinfo($file->getClientFilename(), PATHINFO_FILENAME)
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to upload media file', [
                'error' => $e->getMessage(),
                'filename' => $file->getClientFilename(),
                'user_id' => $userId
            ]);
            throw $e;
        }
    }
    
    /**
     * Get media by filename
     */
    public function getMediaByFilename(string $filename): ?array
    {
        try {
            $stmt = $this->db->getPdo()->prepare('
                SELECT m.*, u.username as uploader_name
                FROM media m
                LEFT JOIN users u ON m.uploaded_by = u.id
                WHERE m.filename = ? AND m.deleted_at IS NULL
            ');
            $stmt->execute([$filename]);
            
            $media = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($media) {
                $media['url'] = $this->getPublicUrl($filename);
                $media['categories'] = $this->getMediaCategories($media['id']);
                $media['tags'] = $this->getMediaTags($media['id']);
            }
            
            return $media ?: null;
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get media by filename', [
                'error' => $e->getMessage(),
                'filename' => $filename
            ]);
            return null;
        }
    }
    
    /**
     * Get media list with pagination
     */
    public function getMediaList(int $page = 1, int $perPage = 24, ?string $category = null, ?string $search = null): array
    {
        try {
            $offset = ($page - 1) * $perPage;
            $whereConditions = ['m.deleted_at IS NULL'];
            $params = [];
            
            if ($category) {
                $whereConditions[] = 'mc.category = ?';
                $params[] = $category;
            }
            
            if ($search) {
                $whereConditions[] = '(m.title LIKE ? OR m.description LIKE ?)';
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
            }
            
            $whereClause = implode(' AND ', $whereConditions);
            
            // Get total count
            $countSql = "SELECT COUNT(DISTINCT m.id) FROM media m";
            if ($category) {
                $countSql .= " LEFT JOIN media_categories mc ON m.id = mc.media_id";
            }
            $countSql .= " WHERE {$whereClause}";
            
            $countStmt = $this->db->getPdo()->prepare($countSql);
            $countStmt->execute($params);
            $total = (int) $countStmt->fetchColumn();
            
            // Get media items
            $sql = "
                SELECT DISTINCT m.*, u.username as uploader_name
                FROM media m
                LEFT JOIN users u ON m.uploaded_by = u.id
            ";
            
            if ($category) {
                $sql .= " LEFT JOIN media_categories mc ON m.id = mc.media_id";
            }
            
            $sql .= " WHERE {$whereClause} ORDER BY m.created_at DESC LIMIT ? OFFSET ?";
            $params[] = $perPage;
            $params[] = $offset;
            
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute($params);
            $media = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Enrich media items
            foreach ($media as &$item) {
                $item['url'] = $this->getPublicUrl($item['filename']);
                $item['categories'] = $this->getMediaCategories($item['id']);
                $item['tags'] = $this->getMediaTags($item['id']);
            }
            
            return [
                'items' => $media,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => ceil($total / $perPage),
                    'from' => $offset + 1,
                    'to' => min($offset + $perPage, $total)
                ]
            ];
            
        } catch (\Exception $e) {
            $this->logger->error('Failed to get media list', [
                'error' => $e->getMessage(),
                'page' => $page,
                'per_page' => $perPage
            ]);
            return ['items' => [], 'pagination' => []];
        }
    }
    
    /**
     * Update media metadata
     */
    public function updateMedia(int $mediaId, array $data): bool
    {
        try {
            $this->db->getPdo()->beginTransaction();
            
            // Update main media record
            $updateFields = [];
            $params = [];
            
            if (isset($data['title'])) {
                $updateFields[] = 'title = ?';
                $params[] = $data['title'];
            }
            
            if (isset($data['description'])) {
                $updateFields[] = 'description = ?';
                $params[] = $data['description'];
            }
            
            if (isset($data['updated_by'])) {
                $updateFields[] = 'updated_by = ?';
                $params[] = $data['updated_by'];
            }
            
            if (!empty($updateFields)) {
                $updateFields[] = 'updated_at = CURRENT_TIMESTAMP';
                $sql = "UPDATE media SET " . implode(', ', $updateFields) . " WHERE id = ?";
                $params[] = $mediaId;
                
                $stmt = $this->db->getPdo()->prepare($sql);
                $stmt->execute($params);
            }
            
            // Update categories
            if (isset($data['categories'])) {
                $this->updateMediaCategories($mediaId, $data['categories']);
            }
            
            // Update tags
            if (isset($data['tags'])) {
                $this->updateMediaTags($mediaId, $data['tags']);
            }
            
            $this->db->getPdo()->commit();
            
            $this->logger->info('Media updated successfully', [
                'media_id' => $mediaId,
                'updated_by' => $data['updated_by'] ?? null
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            $this->db->getPdo()->rollBack();
            $this->logger->error('Failed to update media', [
                'error' => $e->getMessage(),
                'media_id' => $mediaId
            ]);
            throw $e;
        }
    }
    
    /**
     * Delete media file
     */
    public function deleteMedia(int $mediaId): bool
    {
        try {
            $this->db->getPdo()->beginTransaction();
            
            // Get media info
            $media = $this->getMediaById($mediaId);
            if (!$media) {
                throw new \Exception('Media not found');
            }
            
            // Soft delete media record
            $stmt = $this->db->getPdo()->prepare('
                UPDATE media SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?
            ');
            $stmt->execute([$mediaId]);
            
            // Delete categories and tags
            $this->db->getPdo()->prepare('DELETE FROM media_categories WHERE media_id = ?')->execute([$mediaId]);
            $this->db->getPdo()->prepare('DELETE FROM media_tags WHERE media_id = ?')->execute([$mediaId]);
            
            // Remove physical file
            if (file_exists($media['upload_path'])) {
                unlink($media['upload_path']);
            }
            
            $this->db->getPdo()->commit();
            
            $this->logger->info('Media deleted successfully', [
                'media_id' => $mediaId,
                'filename' => $media['filename']
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            $this->db->getPdo()->rollBack();
            $this->logger->error('Failed to delete media', [
                'error' => $e->getMessage(),
                'media_id' => $mediaId
            ]);
            throw $e;
        }
    }
    
    /**
     * Increment view count
     */
    public function incrementViewCount(int $mediaId): bool
    {
        try {
            $stmt = $this->db->getPdo()->prepare('
                UPDATE media SET view_count = view_count + 1 WHERE id = ?
            ');
            return $stmt->execute([$mediaId]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to increment view count', [
                'error' => $e->getMessage(),
                'media_id' => $mediaId
            ]);
            return false;
        }
    }
    
    /**
     * Get file path for media
     */
    public function getFilePath(array $media): string
    {
        return $media['upload_path'];
    }
    
    /**
     * Create media record in database
     */
    private function createMediaRecord(array $data): int
    {
        $stmt = $this->db->getPdo()->prepare('
            INSERT INTO media (
                filename, original_name, title, description, media_type,
                mime_type, file_size, extension, uploaded_by, upload_path
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');
        
        $stmt->execute([
            $data['filename'],
            $data['original_name'],
            $data['title'],
            $data['description'],
            $data['media_type'],
            $data['mime_type'],
            $data['file_size'],
            $data['extension'],
            $data['uploaded_by'],
            $data['upload_path']
        ]);
        
        return (int) $this->db->getPdo()->lastInsertId();
    }
    
    /**
     * Generate unique filename
     */
    private function generateUniqueFilename(string $originalName): string
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        
        $filename = $baseName . '.' . $extension;
        $counter = 1;
        
        while (file_exists($this->uploadPath . '/' . $filename)) {
            $filename = $baseName . '_' . $counter . '.' . $extension;
            $counter++;
        }
        
        return $filename;
    }
    
    /**
     * Get media type from extension
     */
    private function getMediaType(string $extension): string
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        $audioExtensions = ['mp3', 'ogg', 'wav', 'm4a'];
        $videoExtensions = ['mp4', 'webm', 'ogg', 'avi'];
        $documentExtensions = ['pdf', 'doc', 'docx', 'txt', 'md'];
        
        if (in_array($extension, $imageExtensions)) {
            return 'image';
        } elseif (in_array($extension, $audioExtensions)) {
            return 'audio';
        } elseif (in_array($extension, $videoExtensions)) {
            return 'video';
        } elseif (in_array($extension, $documentExtensions)) {
            return 'document';
        }
        
        return 'other';
    }
    
    /**
     * Get MIME type for file
     */
    private function getMimeType(string $filePath, string $extension): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);
        
        if ($mimeType === false || $mimeType === 'application/octet-stream') {
            // Fallback to extension-based MIME types
            $mimeTypes = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'svg' => 'image/svg+xml',
                'mp3' => 'audio/mpeg',
                'ogg' => 'audio/ogg',
                'wav' => 'audio/wav',
                'mp4' => 'video/mp4',
                'webm' => 'video/webm',
                'pdf' => 'application/pdf',
                'txt' => 'text/plain',
                'md' => 'text/markdown'
            ];
            
            return $mimeTypes[$extension] ?? 'application/octet-stream';
        }
        
        return $mimeType;
    }
    
    /**
     * Get public URL for media file
     */
    private function getPublicUrl(string $filename): string
    {
        return '/' . $this->publicPath . '/' . $filename;
    }
    
    /**
     * Get media by ID
     */
    private function getMediaById(int $mediaId): ?array
    {
        $stmt = $this->db->getPdo()->prepare('SELECT * FROM media WHERE id = ?');
        $stmt->execute([$mediaId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * Get media categories
     */
    private function getMediaCategories(int $mediaId): array
    {
        $stmt = $this->db->getPdo()->prepare('
            SELECT category FROM media_categories WHERE media_id = ?
        ');
        $stmt->execute([$mediaId]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
    
    /**
     * Get media tags
     */
    private function getMediaTags(int $mediaId): array
    {
        $stmt = $this->db->getPdo()->prepare('
            SELECT tag FROM media_tags WHERE media_id = ?
        ');
        $stmt->execute([$mediaId]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
    
    /**
     * Update media categories
     */
    private function updateMediaCategories(int $mediaId, array $categories): void
    {
        // Remove existing categories
        $stmt = $this->db->getPdo()->prepare('DELETE FROM media_categories WHERE media_id = ?');
        $stmt->execute([$mediaId]);
        
        // Add new categories
        if (!empty($categories)) {
            $stmt = $this->db->getPdo()->prepare('
                INSERT INTO media_categories (media_id, category) VALUES (?, ?)
            ');
            
            foreach ($categories as $category) {
                $category = trim($category);
                if (!empty($category)) {
                    $stmt->execute([$mediaId, $category]);
                }
            }
        }
    }
    
    /**
     * Update media tags
     */
    private function updateMediaTags(int $mediaId, array $tags): void
    {
        // Remove existing tags
        $stmt = $this->db->getPdo()->prepare('DELETE FROM media_tags WHERE media_id = ?');
        $stmt->execute([$mediaId]);
        
        // Add new tags
        if (!empty($tags)) {
            $stmt = $this->db->getPdo()->prepare('
                INSERT INTO media_tags (media_id, tag) VALUES (?, ?)
            ');
            
            foreach ($tags as $tag) {
                $tag = trim($tag);
                if (!empty($tag)) {
                    $stmt->execute([$mediaId, $tag]);
                }
            }
        }
    }
} 