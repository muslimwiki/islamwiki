<?php

declare(strict_types=1);

namespace Tests\Unit\Extensions\QuranExtension\Models;

use IslamWiki\Core\Database\Connection;
use IslamWiki\Extensions\QuranExtension\Models\QuranAyahRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \IslamWiki\Extensions\QuranExtension\Models\QuranAyahRepository
 */
class QuranAyahRepositoryTest extends TestCase
{
    /** @var Connection|MockObject */
    private $dbConnection;
    
    /** @var PDO|MockObject */
    private $pdo;
    
    /** @var QuranAyahRepository */
    private $repository;
    
    /** @var NullLogger */
    private $logger;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create mock PDO and Connection
        $this->pdo = $this->createMock(PDO::class);
        $this->dbConnection = $this->createMock(Connection::class);
        $this->dbConnection->method('getPdo')->willReturn($this->pdo);
        $this->dbConnection->method('isConnected')->willReturn(true);
        
        // Create a null logger (discards all log messages)
        $this->logger = new NullLogger();
        
        // Initialize repository with test configuration
        $this->repository = new QuranAyahRepository(
            $this->dbConnection,
            [
                'default_language' => 'en',
                'default_translator' => 'Test Translator',
                'per_page' => 10
            ],
            $this->logger
        );
    }
    
    /**
     * Test that the constructor initializes correctly
     */
    public function testConstructorInitialization(): void
    {
        $this->assertInstanceOf(QuranAyahRepository::class, $this->repository);
    }
    
    /**
     * Test getByReference with valid input
     */
    public function testGetByReferenceSuccess(): void
    {
        // Mock PDOStatement for the query
        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetch')->willReturn([
            'id' => 1,
            'surah_number' => 1,
            'ayah_number' => 1,
            'text_arabic' => 'بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ',
            'translation' => 'In the name of Allah, the Entirely Merciful, the Especially Merciful.',
            'translator' => 'Test Translator',
            'language' => 'en'
        ]);
        
        // Expect the query to be prepared and executed with correct parameters
        $this->pdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT'))
            ->willReturn($stmt);
            
        // Call the method
        $result = $this->repository->getByReference(1, 1, 'en', 'Test Translator');
        
        // Assert the expected result
        $this->assertIsArray($result);
        $this->assertEquals(1, $result['surah_number']);
        $this->assertEquals(1, $result['ayah_number']);
        $this->assertEquals('Test Translator', $result['translator']);
    }
    
    /**
     * Test getByReference with PDO exception
     */
    public function testGetByReferenceWithPdoException(): void
    {
        // Expect a PDO exception to be thrown
        $this->pdo->method('prepare')
            ->willThrowException(new \PDOException('Database error'));
            
        // Expect the exception to be rethrown
        $this->expectException(\PDOException::class);
        $this->expectExceptionMessage('Database error');
        
        $this->repository->getByReference(1, 1);
    }
    
    /**
     * Test getByReference with invalid surah number
     */
    public function testGetByReferenceWithInvalidSurah(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid surah number');
        
        $this->repository->getByReference(0, 1);
    }
    
    /**
     * Test getByReference with invalid ayah number
     */
    public function testGetByReferenceWithInvalidAyah(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid ayah number');
        
        $this->repository->getByReference(1, 0);
    }
}

// Mock Connection class for testing
namespace IslamWiki\Core\Database;

class Connection
{
    public function getPdo() {}
    public function isConnected(): bool { return true; }
}
