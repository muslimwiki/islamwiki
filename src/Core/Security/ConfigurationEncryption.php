<?php
declare(strict_types=1);

/**
 * Configuration Encryption
 * 
 * Advanced encryption system for sensitive configuration values
 * with key rotation, secure key management, and audit logging.
 * 
 * @package IslamWiki\Core\Security
 * @version 0.0.21
 * @license AGPL-3.0-only
 */

namespace IslamWiki\Core\Security;

use IslamWiki\Core\Logging\Logger;

class ConfigurationEncryption
{
    /**
     * The encryption algorithm.
     */
    private const ALGORITHM = 'aes-256-gcm';

    /**
     * The logger instance.
     */
    private Logger $logger;

    /**
     * The encryption key.
     */
    private string $key;

    /**
     * The key identifier.
     */
    private string $keyId;

    /**
     * Create a new configuration encryption instance.
     */
    public function __construct(Logger $logger, string $key = null)
    {
        $this->logger = $logger;
        $this->key = $key ?? $this->generateKey();
        $this->keyId = $this->generateKeyId();
    }

    /**
     * Encrypt a configuration value.
     */
    public function encrypt(string $value): string
    {
        try {
            $iv = random_bytes(openssl_cipher_iv_length(self::ALGORITHM));
            $tag = '';
            
            $encrypted = openssl_encrypt(
                $value,
                self::ALGORITHM,
                $this->key,
                OPENSSL_RAW_DATA,
                $iv,
                $tag
            );
            
            if ($encrypted === false) {
                throw new \Exception('Encryption failed');
            }
            
            $encryptedData = base64_encode($iv . $tag . $encrypted);
            
            $this->logger->info('Configuration value encrypted successfully', [
                'key_id' => $this->keyId,
                'algorithm' => self::ALGORITHM
            ]);
            
            return $encryptedData;
        } catch (\Exception $e) {
            $this->logger->error('Configuration encryption failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Decrypt a configuration value.
     */
    public function decrypt(string $encryptedValue): string
    {
        try {
            $data = base64_decode($encryptedValue);
            
            $ivLength = openssl_cipher_iv_length(self::ALGORITHM);
            $tagLength = 16; // GCM tag length
            
            $iv = substr($data, 0, $ivLength);
            $tag = substr($data, $ivLength, $tagLength);
            $encrypted = substr($data, $ivLength + $tagLength);
            
            $decrypted = openssl_decrypt(
                $encrypted,
                self::ALGORITHM,
                $this->key,
                OPENSSL_RAW_DATA,
                $iv,
                $tag
            );
            
            if ($decrypted === false) {
                throw new \Exception('Decryption failed');
            }
            
            $this->logger->info('Configuration value decrypted successfully', [
                'key_id' => $this->keyId,
                'algorithm' => self::ALGORITHM
            ]);
            
            return $decrypted;
        } catch (\Exception $e) {
            $this->logger->error('Configuration decryption failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if a value is encrypted.
     */
    public function isEncrypted(string $value): bool
    {
        try {
            $data = base64_decode($value);
            return strlen($data) > openssl_cipher_iv_length(self::ALGORITHM) + 16;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Rotate encryption key.
     */
    public function rotateKey(): bool
    {
        try {
            $newKey = $this->generateKey();
            $newKeyId = $this->generateKeyId();
            
            // Store the new key securely
            $this->storeKey($newKey, $newKeyId);
            
            $this->key = $newKey;
            $this->keyId = $newKeyId;
            
            $this->logger->info('Encryption key rotated successfully', [
                'old_key_id' => $this->keyId,
                'new_key_id' => $newKeyId
            ]);
            
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Key rotation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate a new encryption key.
     */
    private function generateKey(): string
    {
        return base64_encode(random_bytes(32));
    }

    /**
     * Generate a key identifier.
     */
    private function generateKeyId(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Store the encryption key securely.
     */
    private function storeKey(string $key, string $keyId): void
    {
        // In a production environment, this would store the key in a secure key management system
        // For now, we'll store it in a file with restricted permissions
        $keyFile = __DIR__ . '/../../../../storage/keys/encryption.key';
        $keyDir = dirname($keyFile);
        
        if (!is_dir($keyDir)) {
            mkdir($keyDir, 0700, true);
        }
        
        $keyData = [
            'key' => $key,
            'key_id' => $keyId,
            'created_at' => date('Y-m-d H:i:s'),
            'algorithm' => self::ALGORITHM
        ];
        
        file_put_contents($keyFile, json_encode($keyData));
        chmod($keyFile, 0600);
    }

    /**
     * Load the encryption key from storage.
     */
    private function loadKey(): void
    {
        $keyFile = __DIR__ . '/../../../../storage/keys/encryption.key';
        
        if (file_exists($keyFile)) {
            $keyData = json_decode(file_get_contents($keyFile), true);
            $this->key = $keyData['key'];
            $this->keyId = $keyData['key_id'];
        }
    }

    /**
     * Get key information.
     */
    public function getKeyInfo(): array
    {
        return [
            'key_id' => $this->keyId,
            'algorithm' => self::ALGORITHM,
            'key_length' => strlen($this->key)
        ];
    }
} 