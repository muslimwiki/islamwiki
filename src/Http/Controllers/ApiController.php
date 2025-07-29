<?
declare(strict_types=1);
php\np


namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Models\Page;

class ApiController extends Controller
{
    // List all pages
    public function listPages(Request $request): array
    {
        $query = $this->db->table('pages')
            ->select(['id', 'title', 'slug', 'namespace'])
            ->whereNull('deleted_at');
            
        if ($search = $request->getQueryParam('q')) {
            $query->where('title', 'LIKE', "%{$search}%");
        }
        
        return ['data' => $query->get()];
    }

    // Get a single page
    public function getPage(Request $request, string $slug): array
    {
        if (!$page = Page::findBySlug($slug, $this->db)) {
            $this->abort(404, 'Page not found');
        }
        
        return ['data' => [
            'id' => $page->getAttribute('id'),
            'title' => $page->getAttribute('title'),
            'content' => $page->getAttribute('content'),
            'slug' => $page->getAttribute('slug')
        ]];
    }

    // Create a new page
    public function createPage(Request $request): array
    {
        if (!$this->user($request)) $this->abort(401);
        
        $data = $request->getParsedBody();
        if (empty($data['title']) || empty($data['content'])) {
            $this->abort(400, 'Title and content are required');
        }
        
        $page = new Page($this->db, [
            'title' => $data['title'],
            'content' => $data['content'],
            'slug' => $this->generateSlug('', $data['title']),
        ]);
        
        $page->save();
        
        return [
            'data' => ['id' => $page->getAttribute('id')],
            'message' => 'Page created'
        ];
    }
    
    // Update a page
    public function updatePage(Request $request, string $slug): array
    {
        if (!($user = $this->user($request))) $this->abort(401);
        if (!($page = Page::findBySlug($slug, $this->db))) $this->abort(404);
        
        $data = $request->getParsedBody();
        if (!empty($data['content'])) {
            $page->setAttribute('content', $data['content']);
            $page->save();
            return ['message' => 'Page updated'];
        }
        
        $this->abort(400, 'No content provided');
    }
    
    // Delete a page
    public function deletePage(Request $request, string $slug): array
    {
        if (!$this->isAdmin($request)) $this->abort(403);
        if ($page = Page::findBySlug($slug, $this->db)) {
            $page->setAttribute('deleted_at', date('Y-m-d H:i:s'))->save();
        }
        return ['message' => 'Page deleted'];
    }
    
    // Search pages
    public function search(Request $request): array
    {
        $q = $request->getQueryParam('q');
        if (empty($q)) return ['data' => []];
        
        $results = $this->db->table('pages')
            ->select(['id', 'title', 'slug'])
            ->where('title', 'LIKE', "%{$q}%")
            ->orWhere('content', 'LIKE', "%{$q}%")
            ->get();
            
        return ['data' => $results];
    }
    
    // Get current user
    public function getCurrentUser(Request $request): array
    {
        return ['data' => $this->user($request)];
    }
    
    // Helper to generate slugs
    private function generateSlug(string $namespace, string $title): string
    {
        $slug = strtolower(preg_replace('/[^\pL\d]+/u', '-', $title));
        $slug = trim($slug, '-');
        return $namespace ? "{$namespace}:{$slug}" : $slug;
    }
}
