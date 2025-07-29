<?
declare(strict_types=1);
php\np



namespace IslamWiki\Http\Controllers;

use IslamWiki\Core\Http\Request;
use IslamWiki\Core\Http\Response;
use IslamWiki\Models\User;

class UserController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm(Request $request): Response
    {
        if ($this->isAuthenticated($request)) {
            return $this->redirect('/');
        }
        
        return $this->view('auth.login', [
            'redirect' => $request->getQueryParam('redirect', '/')
        ]);
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request): Response
    {
        $data = $request->getParsedBody();
        $redirect = $data['redirect'] ?? '/';
        
        // Validate input
        if (empty($data['username']) || empty($data['password'])) {
            return $this->view('auth.login', [
                'error' => 'Please enter both username and password',
                'username' => $data['username'] ?? '',
                'redirect' => $redirect,
            ]);
        }
        
        // Find user by username or email
        $user = User::findByUsernameOrEmail($data['username'], $this->db);
        
        // Check if user exists and password is correct
        if (!$user || !$user->verifyPassword($data['password'])) {
            return $this->view('auth.login', [
                'error' => 'Invalid username or password',
                'username' => $data['username'],
                'redirect' => $redirect,
            ]);
        }
        
        // Check if user is active
        if (!$user->isActive()) {
            return $this->view('auth.login', [
                'error' => 'Your account has been deactivated. Please contact an administrator.',
                'username' => $data['username'],
                'redirect' => $redirect,
            ]);
        }
        
        // Update last login timestamp
        $user->recordLogin($request->getServerParam('REMOTE_ADDR'));
        
        // Set session
        $_SESSION['user_id'] = $user->getAttribute('id');
        
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        
        return $this->redirect($redirect);
    }
    
    /**
     * Log the user out.
     */
    public function logout(Request $request): Response
    {
        // Clear all session data
        $_SESSION = [];
        
        // Delete the session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        
        // Destroy the session
        session_destroy();
        
        return $this->redirect('/');
    }
    
    /**
     * Show the registration form.
     */
    public function showRegistrationForm(Request $request): Response
    {
        if ($this->isAuthenticated($request)) {
            return $this->redirect('/');
        }
        
        return $this->view('auth.register');
    }
    
    /**
     * Handle a registration request.
     */
    public function register(Request $request): Response
    {
        $data = $request->getParsedBody();
        
        // Validate input
        $errors = [];
        
        if (empty($data['username'])) {
            $errors['username'] = 'Username is required';
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $data['username'])) {
            $errors['username'] = 'Username may only contain letters, numbers, and underscores (3-50 characters)';
        } elseif (User::usernameExists($data['username'], $this->db)) {
            $errors['username'] = 'Username is already taken';
        }
        
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        } elseif (User::emailExists($data['email'], $this->db)) {
            $errors['email'] = 'Email is already registered';
        }
        
        if (empty($data['password'])) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }
        
        if ($data['password'] !== $data['password_confirmation']) {
            $errors['password_confirmation'] = 'Passwords do not match';
        }
        
        if (!empty($errors)) {
            return $this->view('auth.register', [
                'errors' => $errors,
                'input' => [
                    'username' => $data['username'] ?? '',
                    'email' => $data['email'] ?? '',
                ],
            ]);
        }
        
        // Create the user
        $user = new User($this->db, [
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
            'display_name' => $data['username'],
            'is_active' => true,
            'is_admin' => false,
        ]);
        
        $user->save();
        
        // Log the user in
        $_SESSION['user_id'] = $user->getAttribute('id');
        session_regenerate_id(true);
        
        return $this->redirect('/')
            ->with('success', 'Registration successful! Welcome to IslamWiki!');
    }
    
    /**
     * Show the user's profile.
     */
    public function showProfile(Request $request): Response
    {
        $user = $this->user($request);
        
        if (!$user) {
            return $this->redirect('/login?redirect=' . urlencode($request->getUri()->getPath()));
        }
        
        // Get user contributions
        $contributions = $this->db->table('page_revisions')
            ->select([
                'page_revisions.*',
                'pages.title as page_title',
                'pages.slug as page_slug',
            ])
            ->leftJoin('pages', 'page_revisions.page_id', '=', 'pages.id')
            ->where('page_revisions.user_id', '=', $user['id'])
            ->orderBy('page_revisions.created_at', 'desc')
            ->limit(50)
            ->get();
        
        return $this->view('user.profile', [
            'user' => $user,
            'contributions' => $contributions,
        ]);
    }
    
    /**
     * Update the user's profile.
     */
    public function updateProfile(Request $request): Response
    {
        $user = $this->user($request);
        
        if (!$user) {
            return $this->redirect('/login?redirect=' . urlencode($request->getUri()->getPath()));
        }
        
        $data = $request->getParsedBody();
        $errors = [];
        
        // Validate display name
        if (empty($data['display_name'])) {
            $errors['display_name'] = 'Display name is required';
        } elseif (strlen($data['display_name']) > 100) {
            $errors['display_name'] = 'Display name is too long (max 100 characters)';
        }
        
        // Validate website
        if (!empty($data['website']) && !filter_var($data['website'], FILTER_VALIDATE_URL)) {
            $errors['website'] = 'Please enter a valid URL';
        }
        
        // Validate email
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        } elseif (
            $data['email'] !== $user->getAttribute('email') && 
            User::emailExists($data['email'], $this->db)
        ) {
            $errors['email'] = 'Email is already registered';
        }
        
        // Validate password change if provided
        if (!empty($data['current_password'])) {
            if (!$user->verifyPassword($data['current_password'])) {
                $errors['current_password'] = 'Current password is incorrect';
            } elseif (empty($data['new_password'])) {
                $errors['new_password'] = 'New password is required';
            } elseif (strlen($data['new_password']) < 8) {
                $errors['new_password'] = 'New password must be at least 8 characters';
            } elseif ($data['new_password'] !== $data['new_password_confirmation']) {
                $errors['new_password_confirmation'] = 'New passwords do not match';
            }
        }
        
        if (!empty($errors)) {
            return $this->view('user.profile', [
                'user' => $user,
                'errors' => $errors,
                'input' => $data,
            ]);
        }
        
        // Update the user
        $userData = [
            'display_name' => $data['display_name'],
            'email' => $data['email'],
            'bio' => $data['bio'] ?? null,
            'website' => $data['website'] ?? null,
            'location' => $data['location'] ?? null,
            'timezone' => $data['timezone'] ?? 'UTC',
            'language' => $data['language'] ?? 'en',
        ];
        
        // Update password if changed
        if (!empty($data['new_password'])) {
            $userData['password'] = $data['new_password'];
        }
        
        $user->fill($userData);
        $user->save();
        
        return $this->redirect('/profile')
            ->with('success', 'Profile updated successfully!');
    }
}
