<?php
declare(strict_types=1);

namespace IslamWiki\Extensions\GitIntegration;

use IslamWiki\Core\Extensions\Extension;
use IslamWiki\Core\Extensions\Hooks\HookManager;

/**
 * Git Integration Extension
 * 
 * Provides Git repository integration for automatic version control,
 * backup, and scholarly review workflows.
 */
class GitIntegration extends Extension
{
    /**
     * @var string Git repository path
     */
    private string $repositoryPath;

    /**
     * @var string Remote repository URL
     */
    private string $remoteUrl;

    /**
     * @var string Current branch
     */
    private string $branch;

    /**
     * @var bool Whether Git integration is enabled
     */
    protected bool $enabled;

    /**
     * Initialize the extension
     */
    protected function onInitialize(): void
    {
        $this->loadConfiguration();
        $this->registerHooks();
        
        if ($this->enabled) {
            $this->initializeRepository();
        }
    }

    /**
     * Load Git configuration
     */
    private function loadConfiguration(): void
    {
        $config = $this->getConfig();
        
        $this->enabled = $config['enabled'] ?? false;
        $this->repositoryPath = $config['repositoryPath'] ?? 'storage/git/content';
        $this->remoteUrl = $config['remoteUrl'] ?? '';
        $this->branch = $config['branch'] ?? 'main';
    }

    /**
     * Register extension hooks
     */
    protected function registerHooks(): void
    {
        $hookManager = $this->getHookManager();

        // Article save hook
        $hookManager->register('ArticleSave', [$this, 'onArticleSave'], 10);

        // Article delete hook
        $hookManager->register('ArticleDelete', [$this, 'onArticleDelete'], 10);

        // User login hook
        $hookManager->register('UserLogin', [$this, 'onUserLogin'], 10);

        // Content backup hook
        $hookManager->register('ContentBackup', [$this, 'onContentBackup'], 10);

        // Review request hook
        $hookManager->register('ReviewRequest', [$this, 'onReviewRequest'], 10);
    }

    /**
     * Initialize Git repository
     */
    private function initializeRepository(): void
    {
        $fullPath = $this->getRepositoryFullPath();
        
        // Create repository directory if it doesn't exist
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        // Initialize Git repository if it doesn't exist
        if (!is_dir($fullPath . '/.git')) {
            $this->runGitCommand('init', $fullPath);
            
            // Set up initial commit
            $this->runGitCommand('add .', $fullPath);
            $this->runGitCommand('commit -m "Initial commit"', $fullPath);
        }

        // Set up remote if provided
        if (!empty($this->remoteUrl)) {
            $this->runGitCommand("remote add origin {$this->remoteUrl}", $fullPath);
        }
    }

    /**
     * Article save hook
     *
     * @param array $articleData Article data
     * @param array $userData User data
     * @return array Modified article data
     */
    public function onArticleSave(array $articleData, array $userData): array
    {
        if (!$this->enabled) {
            return $articleData;
        }

        try {
            $this->commitArticle($articleData, $userData);
        } catch (\Exception $e) {
            error_log("Git commit failed: " . $e->getMessage());
        }

        return $articleData;
    }

    /**
     * Article delete hook
     *
     * @param array $articleData Article data
     * @param array $userData User data
     * @return array Modified article data
     */
    public function onArticleDelete(array $articleData, array $userData): array
    {
        if (!$this->enabled) {
            return $articleData;
        }

        try {
            $this->commitDeletion($articleData, $userData);
        } catch (\Exception $e) {
            error_log("Git deletion commit failed: " . $e->getMessage());
        }

        return $articleData;
    }

    /**
     * User login hook
     *
     * @param array $userData User data
     * @return array Modified user data
     */
    public function onUserLogin(array $userData): array
    {
        if (!$this->enabled) {
            return $userData;
        }

        // Configure Git user for commits
        $this->configureGitUser($userData);

        return $userData;
    }

    /**
     * Content backup hook
     *
     * @param array $backupData Backup data
     * @return array Modified backup data
     */
    public function onContentBackup(array $backupData): array
    {
        if (!$this->enabled) {
            return $backupData;
        }

        try {
            $this->createBackup($backupData);
        } catch (\Exception $e) {
            error_log("Git backup failed: " . $e->getMessage());
        }

        return $backupData;
    }

    /**
     * Review request hook
     *
     * @param array $reviewData Review data
     * @return array Modified review data
     */
    public function onReviewRequest(array $reviewData): array
    {
        if (!$this->enabled) {
            return $reviewData;
        }

        try {
            $this->createReviewBranch($reviewData);
        } catch (\Exception $e) {
            error_log("Git review branch creation failed: " . $e->getMessage());
        }

        return $reviewData;
    }

    /**
     * Commit article changes to Git
     *
     * @param array $articleData Article data
     * @param array $userData User data
     */
    private function commitArticle(array $articleData, array $userData): void
    {
        $fullPath = $this->getRepositoryFullPath();
        $fileName = $this->getArticleFileName($articleData);
        $filePath = $fullPath . '/' . $fileName;

        // Write article content to file
        $content = $this->formatArticleContent($articleData);
        file_put_contents($filePath, $content);

        // Stage the file
        $this->runGitCommand("add {$fileName}", $fullPath);

        // Create commit message
        $commitMessage = $this->formatCommitMessage($articleData, $userData);

        // Commit changes
        $this->runGitCommand("commit -m \"{$commitMessage}\"", $fullPath);

        // Push to remote if enabled
        if ($this->shouldAutoPush()) {
            $this->pushToRemote();
        }
    }

    /**
     * Commit article deletion to Git
     *
     * @param array $articleData Article data
     * @param array $userData User data
     */
    private function commitDeletion(array $articleData, array $userData): void
    {
        $fullPath = $this->getRepositoryFullPath();
        $fileName = $this->getArticleFileName($articleData);

        // Remove the file
        $this->runGitCommand("rm {$fileName}", $fullPath);

        // Create commit message
        $commitMessage = "Delete article: {$articleData['title']} by {$userData['username']}";

        // Commit deletion
        $this->runGitCommand("commit -m \"{$commitMessage}\"", $fullPath);

        // Push to remote if enabled
        if ($this->shouldAutoPush()) {
            $this->pushToRemote();
        }
    }

    /**
     * Configure Git user for commits
     *
     * @param array $userData User data
     */
    private function configureGitUser(array $userData): void
    {
        $fullPath = $this->getRepositoryFullPath();
        $name = $userData['name'] ?? $userData['username'] ?? 'Unknown User';
        $email = $userData['email'] ?? 'user@islamwiki.org';

        $this->runGitCommand("config user.name \"{$name}\"", $fullPath);
        $this->runGitCommand("config user.email \"{$email}\"", $fullPath);
    }

    /**
     * Create a backup of the repository
     *
     * @param array $backupData Backup data
     */
    private function createBackup(array $backupData): void
    {
        $fullPath = $this->getRepositoryFullPath();
        $backupPath = $backupData['path'] ?? $fullPath . '/../backup_' . date('Y-m-d_H-i-s');

        // Create backup directory
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        // Copy repository to backup location
        $this->runCommand("cp -r {$fullPath} {$backupPath}");

        // Create backup commit
        $this->runGitCommand("add .", $fullPath);
        $this->runGitCommand("commit -m \"Automatic backup: " . date('Y-m-d H:i:s') . "\"", $fullPath);
    }

    /**
     * Create a review branch for scholarly review
     *
     * @param array $reviewData Review data
     */
    private function createReviewBranch(array $reviewData): void
    {
        $fullPath = $this->getRepositoryFullPath();
        $branchName = "review/{$reviewData['article_id']}/{$reviewData['user_id']}";

        // Create and switch to review branch
        $this->runGitCommand("checkout -b {$branchName}", $fullPath);

        // Commit review changes
        $this->runGitCommand("add .", $fullPath);
        $this->runGitCommand("commit -m \"Review request: {$reviewData['title']}\"", $fullPath);

        // Push review branch to remote
        if (!empty($this->remoteUrl)) {
            $this->runGitCommand("push origin {$branchName}", $fullPath);
        }
    }

    /**
     * Get the full repository path
     *
     * @return string Full repository path
     */
    private function getRepositoryFullPath(): string
    {
        $app = $this->getContainer()->get('app');
        return $app->basePath($this->repositoryPath);
    }

    /**
     * Get article file name
     *
     * @param array $articleData Article data
     * @return string File name
     */
    private function getArticleFileName(array $articleData): string
    {
        $title = $articleData['title'] ?? 'untitled';
        $slug = $articleData['slug'] ?? $this->slugify($title);
        
        return $slug . '.md';
    }

    /**
     * Format article content for Git storage
     *
     * @param array $articleData Article data
     * @return string Formatted content
     */
    private function formatArticleContent(array $articleData): string
    {
        $content = "# {$articleData['title']}\n\n";
        $content .= $articleData['content'] ?? '';
        $content .= "\n\n---\n";
        $content .= "Last modified: " . date('Y-m-d H:i:s') . "\n";
        $content .= "Author: " . ($articleData['author'] ?? 'Unknown') . "\n";
        
        return $content;
    }

    /**
     * Format commit message
     *
     * @param array $articleData Article data
     * @param array $userData User data
     * @return string Commit message
     */
    private function formatCommitMessage(array $articleData, array $userData): string
    {
        $config = $this->getConfig();
        $template = $config['commitMessageTemplate'] ?? 'Wiki update: {title} by {user}';
        
        $message = str_replace(
            ['{title}', '{user}'],
            [$articleData['title'] ?? 'Untitled', $userData['username'] ?? 'Unknown'],
            $template
        );
        
        return $message;
    }

    /**
     * Check if auto-push is enabled
     *
     * @return bool True if auto-push is enabled
     */
    private function shouldAutoPush(): bool
    {
        $config = $this->getConfig();
        return $config['autoPush'] ?? false;
    }

    /**
     * Push changes to remote repository
     */
    private function pushToRemote(): void
    {
        if (empty($this->remoteUrl)) {
            return;
        }

        $fullPath = $this->getRepositoryFullPath();
        $this->runGitCommand("push origin {$this->branch}", $fullPath);
    }

    /**
     * Run a Git command
     *
     * @param string $command Git command
     * @param string $workingDir Working directory
     * @return string Command output
     */
    private function runGitCommand(string $command, string $workingDir): string
    {
        $fullCommand = "cd {$workingDir} && git {$command} 2>&1";
        return $this->runCommand($fullCommand);
    }

    /**
     * Run a system command
     *
     * @param string $command Command to run
     * @return string Command output
     */
    private function runCommand(string $command): string
    {
        $output = [];
        $returnCode = 0;
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \Exception("Command failed: {$command}. Output: " . implode("\n", $output));
        }
        
        return implode("\n", $output);
    }

    /**
     * Convert string to slug
     *
     * @param string $string String to convert
     * @return string Slug
     */
    private function slugify(string $string): string
    {
        $string = strtolower($string);
        $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
        $string = preg_replace('/[\s-]+/', '-', $string);
        return trim($string, '-');
    }

    /**
     * Get repository status
     *
     * @return array Repository status
     */
    public function getRepositoryStatus(): array
    {
        if (!$this->enabled) {
            return ['enabled' => false];
        }

        try {
            $fullPath = $this->getRepositoryFullPath();
            $status = $this->runGitCommand('status --porcelain', $fullPath);
            $branch = $this->runGitCommand('rev-parse --abbrev-ref HEAD', $fullPath);
            $lastCommit = $this->runGitCommand('log -1 --format="%H|%an|%ad|%s"', $fullPath);

            return [
                'enabled' => true,
                'repository_path' => $fullPath,
                'branch' => trim($branch),
                'status' => $status ? explode("\n", trim($status)) : [],
                'last_commit' => $this->parseLastCommit($lastCommit),
                'remote_url' => $this->remoteUrl,
            ];
        } catch (\Exception $e) {
            return [
                'enabled' => true,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Parse last commit information
     *
     * @param string $commitInfo Commit information
     * @return array Parsed commit info
     */
    private function parseLastCommit(string $commitInfo): array
    {
        $parts = explode('|', $commitInfo);
        
        return [
            'hash' => $parts[0] ?? '',
            'author' => $parts[1] ?? '',
            'date' => $parts[2] ?? '',
            'message' => $parts[3] ?? '',
        ];
    }

    /**
     * Check if Git integration is enabled
     *
     * @return bool True if enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
} 