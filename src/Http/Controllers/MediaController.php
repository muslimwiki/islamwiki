<?php

declare(strict_types=1);

namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Core\Http\Exceptions\HttpException;
use IslamWiki\Core\Media\MediaManager;
use Psr\Log\LoggerInterface;

/**
 * Media Controller
 * 
 * Handles media file uploads, management, and serving.
 * Provides MediaWiki-compatible media handling functionality.
 */
class MediaController extends BaseController
{
    private MediaManager $mediaManager;
    private array $allowedTypes = [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
        'audio' => ['mp3', 'ogg', 'wav', 'm4a'],
        'video' => ['mp4', 'webm', 'ogg', 'avi'],
        'document' => ['pdf', 'doc', 'docx', 'txt', 'md']
    ];
    
    private int $maxFileSize = 50 * 1024 * 1024; // 50MB
    
    public function __construct(
        \IslamWiki\Core\Database\Connection $db,
        \IslamWiki\Core\Container\Container $container
    ) {
        parent::__construct($db, $container);
        $this->mediaManager = new MediaManager($db, $container);
    }
    
    /**
     * Show media upload form
     */
    public function upload(Request $request): Response
    {
        try {
            if (!$this->isLoggedIn()) {
                return $this->redirect('/login?redirect=' . urlencode('/media/upload'), 302);
            }
            
            return $this->view('media/upload', [
                'title' => 'Upload Media - IslamWiki',
                'user' => $this->getCurrentUser(),
                'allowedTypes' => $this->allowedTypes,
                'maxFileSize' => $this->maxFileSize
            ]);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to show media upload form', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            throw new HttpException(500, 'Failed to load upload form');
        }
    }
    
    /**
     * Handle media file upload
     */
    public function store(Request $request): Response
    {
        try {
            if (!$this->isLoggedIn()) {
                throw new HttpException(401, 'Authentication required');
            }
            
            $uploadedFiles = $request->getUploadedFiles();
            if (empty($uploadedFiles)) {
                throw new HttpException(400, 'No files uploaded');
            }
            
            $results = [];
            $user = $this->getCurrentUser();
            
            foreach ($uploadedFiles as $file) {
                if ($file->getError() !== UPLOAD_ERR_OK) {
                    $results[] = [
                        'success' => false,
                        'filename' => $file->getClientFilename(),
                        'error' => $this->getUploadErrorMessage($file->getError())
                    ];
                    continue;
                }
                
                // Validate file
                $validation = $this->validateFile($file);
                if (!$validation['valid']) {
                    $results[] = [
                        'success' => false,
                        'filename' => $file->getClientFilename(),
                        'error' => $validation['error']
                    ];
                    continue;
                }
                
                // Upload file
                try {
                    $mediaInfo = $this->mediaManager->uploadFile($file, $user['id']);
                    $results[] = [
                        'success' => true,
                        'filename' => $file->getClientFilename(),
                        'media_id' => $mediaInfo['id'],
                        'url' => $mediaInfo['url'],
                        'message' => 'File uploaded successfully'
                    ];
                } catch (\Exception $e) {
                    $results[] = [
                        'success' => false,
                        'filename' => $file->getClientFilename(),
                        'error' => 'Upload failed: ' . $e->getMessage()
                    ];
                }
            }
            
            // Return JSON response for AJAX requests
            if ($request->getHeaderLine('Accept') === 'application/json') {
                return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                    'success' => true,
                    'results' => $results
                ]));
            }
            
            // Return view for regular requests
            return $this->view('media/upload_result', [
                'title' => 'Upload Results - IslamWiki',
                'user' => $user,
                'results' => $results
            ]);
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Media upload failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'user_id' => $this->getCurrentUser()['id'] ?? null
                ]);
            }
            
            if ($request->getHeaderLine('Accept') === 'application/json') {
                return new Response(500, ['Content-Type' => 'application/json'], json_encode([
                    'success' => false,
                    'error' => $e->getMessage()
                ]));
            }
            
            throw new HttpException(500, 'Upload failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Show media file
     */
    public function show(Request $request, string $filename): Response
    {
        try {
            $media = $this->mediaManager->getMediaByFilename($filename);
            if (!$media) {
                throw new HttpException(404, 'Media file not found');
            }
            
            // Increment view count
            $this->mediaManager->incrementViewCount($media['id']);
            
            return $this->view('media/show', [
                'title' => $media['title'] . ' - IslamWiki',
                'user' => $this->getCurrentUser(),
                'media' => $media
            ]);
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to show media file', [
                    'error' => $e->getMessage(),
                    'filename' => $filename
                ]);
            }
            throw new HttpException(500, 'Failed to load media file');
        }
    }
    
    /**
     * Serve media file
     */
    public function serve(Request $request, string $filename): Response
    {
        try {
            $media = $this->mediaManager->getMediaByFilename($filename);
            if (!$media) {
                throw new HttpException(404, 'Media file not found');
            }
            
            $filePath = $this->mediaManager->getFilePath($media);
            if (!file_exists($filePath)) {
                throw new HttpException(404, 'File not found on disk');
            }
            
            // Set appropriate headers
            $headers = [
                'Content-Type' => $media['mime_type'],
                'Content-Length' => filesize($filePath),
                'Cache-Control' => 'public, max-age=31536000', // 1 year
                'Last-Modified' => gmdate('D, d M Y H:i:s', strtotime($media['updated_at'])) . ' GMT'
            ];
            
            // Handle range requests for large files
            $range = $request->getHeaderLine('Range');
            if ($range) {
                $headers = array_merge($headers, $this->handleRangeRequest($range, $filePath));
            }
            
            $content = file_get_contents($filePath);
            return new Response(200, $headers, $content);
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to serve media file', [
                    'error' => $e->getMessage(),
                    'filename' => $filename
                ]);
            }
            throw new HttpException(500, 'Failed to serve media file');
        }
    }
    
    /**
     * Show media gallery
     */
    public function gallery(Request $request): Response
    {
        try {
            $page = (int) ($request->getQueryParam('page', 1));
            $perPage = (int) ($request->getQueryParam('per_page', 24));
            $category = $request->getQueryParam('category');
            $search = $request->getQueryParam('search');
            
            $media = $this->mediaManager->getMediaList($page, $perPage, $category, $search);
            
            return $this->view('media/gallery', [
                'title' => 'Media Gallery - IslamWiki',
                'user' => $this->getCurrentUser(),
                'media' => $media['items'],
                'pagination' => $media['pagination'],
                'category' => $category,
                'search' => $search
            ]);
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to show media gallery', [
                    'error' => $e->getMessage()
                ]);
            }
            throw new HttpException(500, 'Failed to load media gallery');
        }
    }
    
    /**
     * Edit media metadata
     */
    public function edit(Request $request, string $filename): Response
    {
        try {
            if (!$this->isLoggedIn()) {
                return $this->redirect('/login?redirect=' . urlencode('/media/' . $filename . '/edit'), 302);
            }
            
            $media = $this->mediaManager->getMediaByFilename($filename);
            if (!$media) {
                throw new HttpException(404, 'Media file not found');
            }
            
            // Check permissions
            if (!$this->canEditMedia($media)) {
                throw new HttpException(403, 'Permission denied');
            }
            
            if ($request->getMethod() === 'POST') {
                return $this->update($request, $filename);
            }
            
            return $this->view('media/edit', [
                'title' => 'Edit Media - ' . $media['title'] . ' - IslamWiki',
                'user' => $this->getCurrentUser(),
                'media' => $media
            ]);
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to show media edit form', [
                    'error' => $e->getMessage(),
                    'filename' => $filename
                ]);
            }
            throw new HttpException(500, 'Failed to load edit form');
        }
    }
    
    /**
     * Update media metadata
     */
    public function update(Request $request, string $filename): Response
    {
        try {
            if (!$this->isLoggedIn()) {
                throw new HttpException(401, 'Authentication required');
            }
            
            $media = $this->mediaManager->getMediaByFilename($filename);
            if (!$media) {
                throw new HttpException(404, 'Media file not found');
            }
            
            // Check permissions
            if (!$this->canEditMedia($media)) {
                throw new HttpException(403, 'Permission denied');
            }
            
            $data = $request->getParsedBody();
            $user = $this->getCurrentUser();
            
            $updateData = [
                'title' => $data['title'] ?? $media['title'],
                'description' => $data['description'] ?? $media['description'],
                'categories' => $data['categories'] ?? [],
                'tags' => $data['tags'] ?? [],
                'updated_by' => $user['id']
            ];
            
            $this->mediaManager->updateMedia($media['id'], $updateData);
            
            return $this->redirect('/media/' . $filename, 302);
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to update media', [
                    'error' => $e->getMessage(),
                    'filename' => $filename
                ]);
            }
            throw new HttpException(500, 'Failed to update media');
        }
    }
    
    /**
     * Delete media file
     */
    public function destroy(Request $request, string $filename): Response
    {
        try {
            if (!$this->isLoggedIn()) {
                throw new HttpException(401, 'Authentication required');
            }
            
            $media = $this->mediaManager->getMediaByFilename($filename);
            if (!$media) {
                throw new HttpException(404, 'Media file not found');
            }
            
            // Check permissions
            if (!$this->canDeleteMedia($media)) {
                throw new HttpException(403, 'Permission denied');
            }
            
            $this->mediaManager->deleteMedia($media['id']);
            
            if ($request->getHeaderLine('Accept') === 'application/json') {
                return new Response(200, ['Content-Type' => 'application/json'], json_encode([
                    'success' => true,
                    'message' => 'Media deleted successfully'
                ]));
            }
            
            return $this->redirect('/media/gallery', 302);
            
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error('Failed to delete media', [
                    'error' => $e->getMessage(),
                    'filename' => $filename
                ]);
            }
            throw new HttpException(500, 'Failed to delete media');
        }
    }
    
    /**
     * Validate uploaded file
     */
    private function validateFile($file): array
    {
        // Check file size
        if ($file->getSize() > $this->maxFileSize) {
            return [
                'valid' => false,
                'error' => 'File size exceeds maximum allowed size of ' . ($this->maxFileSize / 1024 / 1024) . 'MB'
            ];
        }
        
        // Check file type
        $extension = strtolower(pathinfo($file->getClientFilename(), PATHINFO_EXTENSION));
        $allowed = false;
        
        foreach ($this->allowedTypes as $type => $extensions) {
            if (in_array($extension, $extensions)) {
                $allowed = true;
                break;
            }
        }
        
        if (!$allowed) {
            return [
                'valid' => false,
                'error' => 'File type not allowed. Allowed types: ' . implode(', ', array_merge(...array_values($this->allowedTypes)))
            ];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Get upload error message
     */
    private function getUploadErrorMessage(int $error): string
    {
        switch ($error) {
            case UPLOAD_ERR_INI_SIZE:
                return 'File exceeds upload_max_filesize directive';
            case UPLOAD_ERR_FORM_SIZE:
                return 'File exceeds MAX_FILE_SIZE directive';
            case UPLOAD_ERR_PARTIAL:
                return 'File was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by extension';
            default:
                return 'Unknown upload error';
        }
    }
    
    /**
     * Handle range requests for large files
     */
    private function handleRangeRequest(string $range, string $filePath): array
    {
        $fileSize = filesize($filePath);
        $range = str_replace('bytes=', '', $range);
        
        if (preg_match('/^(\d+)-(\d*)$/', $range, $matches)) {
            $start = (int) $matches[1];
            $end = $matches[2] ? (int) $matches[2] : $fileSize - 1;
            
            if ($start < $fileSize && $end < $fileSize && $start <= $end) {
                return [
                    'Content-Range' => "bytes {$start}-{$end}/{$fileSize}",
                    'Accept-Ranges' => 'bytes'
                ];
            }
        }
        
        return [];
    }
    
    /**
     * Check if user can edit media
     */
    private function canEditMedia(array $media): bool
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }
        
        // Media owner can edit
        if ($media['uploaded_by'] == $user['id']) {
            return true;
        }
        
        // Admins and editors can edit
        if (in_array($user['role'], ['admin', 'editor'])) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if user can delete media
     */
    private function canDeleteMedia(array $media): bool
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }
        
        // Only admins can delete
        if ($user['role'] === 'admin') {
            return true;
        }
        
        return false;
    }
} 