<?php

namespace Tests\Feature;

use Tests\Http\HttpTestCase;
use App\Models\Page;
use App\Http\SimpleRouter;
use App\Http\RequestHandler;
use App\Http\Controllers\PageController;

class PageControllerTest extends HttpTestCase
{
    private RequestHandler $handler;
    private SimpleRouter $router;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->router = new SimpleRouter();
        $this->handler = new RequestHandler($this->router);
        
        // Register routes
        $this->router->addRoute('GET', '/pages/{slug}', [PageController::class, 'show']);
    }

    /** @test */
    public function it_can_view_page_by_slug()
    {
        // Create a test page
        $page = Page::create([
            'title' => 'About Us',
            'slug' => 'about-us',
            'content' => 'This is the about us page',
            'is_published' => true
        ]);

        // Make a request to view the page
        $request = $this->createRequest('GET', "/pages/{$page->slug}");
        $response = $this->handler->handle($request);

        // Assert the response
        $this->assertResponseStatus($response, 200);
        
        $data = json_decode((string) $response->getBody(), true);
        $this->assertEquals($page->title, $data['title']);
        $this->assertEquals($page->content, $data['content']);
        $this->assertEquals($page->slug, $data['slug']);
    }

    /** @test */
    public function it_returns_404_for_non_existent_page()
    {
        $request = $this->createRequest('GET', '/pages/non-existent-page');
        $response = $this->handler->handle($request);
        
        $this->assertResponseStatus($response, 404);
    }
}
