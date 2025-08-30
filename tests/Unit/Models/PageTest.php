<?php

namespace Tests\Unit\Models;

use BaseTestCase;
use App\Models\Page;
use Illuminate\Database\QueryException;

class PageTest extends BaseTestCase
{
    public function test_page_has_title()
    {
        $page = new Page([
            'title' => 'Test Page',
            'content' => 'This is a test page content',
            'slug' => 'test-page'
        ]);
        
        $this->assertEquals('Test Page', $page->title);
    }

    public function test_page_has_slug()
    {
        $page = new Page([
            'title' => 'Test Page',
            'content' => 'This is a test page content',
            'slug' => 'test-page'
        ]);
        
        $this->assertEquals('test-page', $page->slug);
    }

    public function test_page_can_be_created_in_database()
    {
        $page = Page::create([
            'title' => 'Test Page',
            'content' => 'This is a test page content',
            'slug' => 'test-page-1',
            'is_published' => true
        ]);
        
        $this->assertDatabaseHas('pages', [
            'title' => 'Test Page',
            'slug' => 'test-page-1',
            'is_published' => 1
        ]);
    }

    public function test_page_requires_title()
    {
        $this->expectException(QueryException::class);
        
        Page::create([
            'content' => 'This is a test page content',
            'slug' => 'test-page-2',
            'is_published' => true
        ]);
    }

    public function test_page_requires_unique_slug()
    {
        Page::create([
            'title' => 'First Page',
            'content' => 'First page content',
            'slug' => 'duplicate-slug'
        ]);
        
        $this->expectException(QueryException::class);
        
        Page::create([
            'title' => 'Second Page',
            'content' => 'Second page content',
            'slug' => 'duplicate-slug'
        ]);
    }
}
