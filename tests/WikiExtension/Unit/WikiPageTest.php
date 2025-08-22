<?php

declare(strict_types=1);

namespace IslamWiki\Tests\WikiExtension\Unit;

use PHPUnit\Framework\TestCase;
use IslamWiki\Extensions\WikiExtension\Models\WikiPage;
use PDO;
use PDOStatement;

/**
 * Unit tests for WikiPage model
 */
class WikiPageTest extends TestCase
{
    private WikiPage $wikiPage;
    private PDO $mockPdo;
    private PDOStatement $mockStatement;

    protected function setUp(): void
    {
        $this->mockPdo = $this->createMock(PDO::class);
        $this->mockStatement = $this->createMock(PDOStatement::class);
        
        // Create WikiPage instance with mocked PDO
        $this->wikiPage = new WikiPage($this->mockPdo);
    }

    public function testGetBySlugReturnsPageData(): void
    {
        $expectedData = [
            'id' => 1,
            'title' => 'Test Page',
            'slug' => 'test-page',
            'content' => 'Test content',
            'status' => 'published'
        ];

        $this->mockStatement->method('fetch')
            ->willReturn($expectedData);

        $this->mockPdo->method('prepare')
            ->willReturn($this->mockStatement);

        $result = $this->wikiPage->getBySlug('test-page');

        $this->assertEquals($expectedData, $result);
    }

    public function testGetBySlugReturnsNullWhenNotFound(): void
    {
        $this->mockStatement->method('fetch')
            ->willReturn(false);

        $this->mockPdo->method('prepare')
            ->willReturn($this->mockStatement);

        $result = $this->wikiPage->getBySlug('non-existent');

        $this->assertNull($result);
    }

    public function testCreatePageReturnsPageId(): void
    {
        $pageData = [
            'title' => 'New Page',
            'slug' => 'new-page',
            'content' => 'New content',
            'meta_description' => 'Test description',
            'content_type' => 'page',
            'status' => 'published'
        ];

        $this->mockStatement->method('lastInsertId')
            ->willReturn('123');

        $this->mockPdo->method('prepare')
            ->willReturn($this->mockStatement);

        $result = $this->wikiPage->create($pageData);

        $this->assertEquals(123, $result);
    }

    public function testUpdatePageReturnsTrueOnSuccess(): void
    {
        $pageData = [
            'id' => 1,
            'title' => 'Updated Page',
            'content' => 'Updated content'
        ];

        $this->mockStatement->method('rowCount')
            ->willReturn(1);

        $this->mockPdo->method('prepare')
            ->willReturn($this->mockStatement);

        $result = $this->wikiPage->update(1, $pageData);

        $this->assertTrue($result);
    }

    public function testDeletePageReturnsTrueOnSuccess(): void
    {
        $this->mockStatement->method('rowCount')
            ->willReturn(1);

        $this->mockPdo->method('prepare')
            ->willReturn($this->mockStatement);

        $result = $this->wikiPage->delete(1);

        $this->assertTrue($result);
    }

    public function testGetFeaturedPagesReturnsArray(): void
    {
        $expectedData = [
            ['id' => 1, 'title' => 'Featured Page 1'],
            ['id' => 2, 'title' => 'Featured Page 2']
        ];

        $this->mockStatement->method('fetchAll')
            ->willReturn($expectedData);

        $this->mockPdo->method('prepare')
            ->willReturn($this->mockStatement);

        $result = $this->wikiPage->getFeaturedPages();

        $this->assertEquals($expectedData, $result);
    }

    public function testGetRecentPagesReturnsArray(): void
    {
        $expectedData = [
            ['id' => 1, 'title' => 'Recent Page 1', 'created_at' => '2025-01-20 10:00:00'],
            ['id' => 2, 'title' => 'Recent Page 2', 'created_at' => '2025-01-20 09:00:00']
        ];

        $this->mockStatement->method('fetchAll')
            ->willReturn($expectedData);

        $this->mockPdo->method('prepare')
            ->willReturn($this->mockStatement);

        $result = $this->wikiPage->getRecentPages(5);

        $this->assertEquals($expectedData, $result);
    }

    public function testSearchReturnsMatchingPages(): void
    {
        $expectedData = [
            ['id' => 1, 'title' => 'Search Result 1', 'relevance' => 0.95],
            ['id' => 2, 'title' => 'Search Result 2', 'relevance' => 0.85]
        ];

        $this->mockStatement->method('fetchAll')
            ->willReturn($expectedData);

        $this->mockPdo->method('prepare')
            ->willReturn($this->mockStatement);

        $result = $this->wikiPage->search('test query', ['type' => 'page']);

        $this->assertEquals($expectedData, $result);
    }

    public function testIncrementViewCountReturnsTrue(): void
    {
        $this->mockStatement->method('rowCount')
            ->willReturn(1);

        $this->mockPdo->method('prepare')
            ->willReturn($this->mockStatement);

        $result = $this->wikiPage->incrementViewCount(1);

        $this->assertTrue($result);
    }

    public function testGetWikiStatsReturnsStatistics(): void
    {
        $expectedStats = [
            'total_pages' => 100,
            'published_pages' => 85,
            'draft_pages' => 10,
            'archived_pages' => 5,
            'total_views' => 5000,
            'total_revisions' => 250
        ];

        $this->mockStatement->method('fetch')
            ->willReturn($expectedStats);

        $this->mockPdo->method('prepare')
            ->willReturn($this->mockStatement);

        $result = $this->wikiPage->getWikiStats();

        $this->assertEquals($expectedStats, $result);
    }

    public function testGetRelatedPagesReturnsArray(): void
    {
        $expectedData = [
            ['id' => 2, 'title' => 'Related Page 1', 'similarity' => 0.8],
            ['id' => 3, 'title' => 'Related Page 2', 'similarity' => 0.7]
        ];

        $this->mockStatement->method('fetchAll')
            ->willReturn($expectedData);

        $this->mockPdo->method('prepare')
            ->willReturn($this->mockStatement);

        $result = $this->wikiPage->getRelatedPages(1, 5);

        $this->assertEquals($expectedData, $result);
    }
} 