<?
declare(strict_types=1);
php\np



namespace IslamWiki\Http\Controllers\Auth;

use IslamWiki\Http\Controllers\Controller;
use IslamWiki\Models\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegistrationForm(Request $request): Response
    {
        if ($this->isAuthenticated($request)) {
            return $this->redirect(route('dashboard'));
        }

        return $this->view('auth.register', [
            'title' => 'Register - IslamWiki',
            'errors' => [],
            'old' => $request->getQueryParams()
        ]);
    }

    /**
     * Handle a registration request.
     */
    public function register(Request $request): Response
    {
        $data = $request->getParsedBody();
        $errors = $this->validateRegistration($data);

        if (!empty($errors)) {
            return $this->view('auth.register', [
                'title' => 'Register - IslamWiki',
                'errors' => $errors,
                'old' => $data,
            ]);
        }

        // Create the user
        $user = User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'display_name' => $data['display_name'] ?? $data['username'],
            'is_active' => true,
            'email_verified_at' => null,
            'last_login_at' => null,
            'last_login_ip' => $request->getServerParams()['REMOTE_ADDR'] ?? null,
        ]);

        // Log the user in
        $session = $request->getAttribute('session');
        $session->set('user_id', $user->id);

        // Send verification email
        $this->sendVerificationEmail($user);

        // Redirect to dashboard with success message
        $session->setFlash('success', 'Registration successful! Welcome to IslamWiki.');
        
        return $this->redirect(route('dashboard'));
    }

    /**
     * Validate the registration data.
     */
    protected function validateRegistration(array $data): array
    {
        $errors = [];

        // Username validation
        if (empty($data['username'])) {
            $errors['username'] = 'Username is required.';
        } elseif (strlen($data['username']) < 3) {
            $errors['username'] = 'Username must be at least 3 characters.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $data['username'])) {
            $errors['username'] = 'Username may only contain letters, numbers, and underscores.';
        } elseif (User::where('username', $data['username'])->exists()) {
            $errors['username'] = 'Username is already taken.';
        }

        // Email validation
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        } elseif (User::where('email', $data['email'])->exists()) {
            $errors['email'] = 'Email is already registered.';
        }

        // Password validation
        if (empty($data['password'])) {
            $errors['password'] = 'Password is required.';
        } elseif (strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        } elseif ($data['password'] !== ($data['password_confirmation'] ?? '')) {
            $errors['password_confirmation'] = 'Passwords do not match.';
        }

        // Terms agreement
        if (empty($data['terms'])) {
            $errors['terms'] = 'You must agree to the terms and conditions.';
        }

        return $errors;
    }

    /**
     * Send the email verification notification.
     */
    protected function sendVerificationEmail(User $user): void
    {
        // Generate verification token
        $token = bin2hex(random_bytes(32));
        $expires = time() + (24 * 60 * 60); // 24 hours
        
        // Store verification token
        $user->update([
            'email_verification_token' => password_hash($token, PASSWORD_DEFAULT),
            'email_verification_expires' => date('Y-m-d H:i:s', $expires),
        ]);

        // TODO: Implement email sending
        // $verificationUrl = route('verification.verify', [
        //     'id' => $user->id,
        //     'token' => $token,
        // ]);
        
        // Mail::to($user->email)->send(new VerifyEmail($verificationUrl));
    }
}
