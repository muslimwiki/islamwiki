<?
declare(strict_types=1);
php\np



namespace IslamWiki\Http\Controllers\Auth;

use IslamWiki\Http\Controllers\Controller;
use IslamWiki\Models\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ForgotPasswordController extends Controller
{
    /**
     * Show the forgot password form.
     */
    public function showLinkRequestForm(Request $request): Response
    {
        if ($this->isAuthenticated($request)) {
            return $this->redirect(route('dashboard'));
        }

        return $this->view('auth.passwords.email', [
            'title' => 'Reset Password - IslamWiki',
            'status' => $request->getQueryParams()['status'] ?? null,
        ]);
    }

    /**
     * Send a password reset link to the given user.
     */
    public function sendResetLinkEmail(Request $request): Response
    {
        $data = $request->getParsedBody();
        $email = $data['email'] ?? '';
        
        // Validate email
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->view('auth.passwords.email', [
                'title' => 'Reset Password - IslamWiki',
                'errors' => ['email' => 'Please provide a valid email address.'],
                'email' => $email,
            ]);
        }

        // Find user by email
        $user = User::where('email', $email)->first();

        if ($user) {
            // Generate password reset token
            $token = bin2hex(random_bytes(32));
            $expires = time() + (60 * 60); // 1 hour
            
            // Store token in database
            $user->update([
                'password_reset_token' => password_hash($token, PASSWORD_DEFAULT),
                'password_reset_expires' => date('Y-m-d H:i:s', $expires),
            ]);

            // Send password reset email
            $this->sendPasswordResetEmail($user, $token);
        }

        // Always return success to prevent user enumeration
        return $this->redirect(route('password.request', [
            'status' => 'If your email exists in our system, we have sent you a password reset link.'
        ]));
    }

    /**
     * Send the password reset notification.
     */
    protected function sendPasswordResetEmail(User $user, string $token): void
    {
        // TODO: Implement email sending
        // $resetUrl = route('password.reset', [
        //     'token' => $token,
        //     'email' => $user->email,
        // ]);
        
        // Mail::to($user->email)->send(new ResetPassword($resetUrl));
    }
}
