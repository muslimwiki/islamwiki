<?
declare(strict_types=1);
php\np



namespace IslamWiki\Http\Controllers\Auth;

use IslamWiki\Http\Controllers\Controller;
use IslamWiki\Models\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpNotFoundException;

class ResetPasswordController extends Controller
{
    /**
     * Show the password reset form.
     */
    public function showResetForm(Request $request, array $args): Response
    {
        if ($this->isAuthenticated($request)) {
            return $this->redirect(route('dashboard'));
        }

        $token = $args['token'] ?? '';
        $email = $request->getQueryParams()['email'] ?? '';

        if (empty($token) || empty($email)) {
            throw new HttpBadRequestException($request, 'Invalid password reset link.');
        }

        // Verify token
        $user = User::where('email', $email)->first();
        if (!$user || !$this->verifyToken($user, $token, 'password_reset')) {
            throw new HttpNotFoundException($request, 'Invalid or expired password reset link.');
        }

        return $this->view('auth.passwords.reset', [
            'title' => 'Reset Password - IslamWiki',
            'token' => $token,
            'email' => $email,
            'errors' => [],
        ]);
    }

    /**
     * Reset the user's password.
     */
    public function reset(Request $request): Response
    {
        $data = $request->getParsedBody();
        $email = $data['email'] ?? '';
        $token = $data['token'] ?? '';
        $password = $data['password'] ?? '';
        $passwordConfirmation = $data['password_confirmation'] ?? '';

        // Validate input
        $errors = $this->validateReset($data);
        if (!empty($errors)) {
            return $this->view('auth.passwords.reset', [
                'title' => 'Reset Password - IslamWiki',
                'token' => $token,
                'email' => $email,
                'errors' => $errors,
            ]);
        }

        // Find user and verify token
        $user = User::where('email', $email)->first();
        if (!$user || !$this->verifyToken($user, $token, 'password_reset')) {
            throw new HttpNotFoundException($request, 'Invalid or expired password reset link.');
        }

        // Update password and clear reset token
        $user->update([
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'password_reset_token' => null,
            'password_reset_expires' => null,
        ]);

        // Log the user in
        $session = $request->getAttribute('session');
        $session->set('user_id', $user->id);

        // Redirect to dashboard with success message
        $session->setFlash('success', 'Your password has been reset successfully!');
        
        return $this->redirect(route('dashboard'));
    }

    /**
     * Validate the password reset data.
     */
    protected function validateReset(array $data): array
    {
        $errors = [];

        if (empty($data['email'])) {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        if (empty($data['token'])) {
            $errors['token'] = 'Invalid reset token.';
        }

        if (empty($data['password'])) {
            $errors['password'] = 'Password is required.';
        } elseif (strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        } elseif ($data['password'] !== $data['password_confirmation']) {
            $errors['password_confirmation'] = 'Passwords do not match.';
        }

        return $errors;
    }

    /**
     * Verify a token from the database.
     */
    protected function verifyToken(User $user, string $token, string $type): bool
    {
        $tokenField = "{$type}_token";
        $expiryField = "{$type}_expires";

        if (empty($user->$tokenField) || empty($user->$expiryField)) {
            return false;
        }

        $isValid = password_verify($token, $user->$tokenField);
        $isExpired = strtotime($user->$expiryField) < time();

        return $isValid && !$isExpired;
    }
}
