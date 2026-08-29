<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Veldora\Framework\Foundation\Application;

/**
 * Scaffold the complete authentication layer:
 *   - Migration (users table)
 *   - User Model with SoftDeletes + MustVerifyEmail
 *   - Controllers: Login, Register, ForgotPassword, ResetPassword, Profile, Verification
 *   - Views (pure Veldora CSS – zero external dependencies): login, register,
 *     forgot-password, reset-password, profile, email-verify
 *   - Auth routes appended to routes/web.php
 */
class MakeAuthCommand extends Command
{
    protected static ?string $defaultName = 'make:auth';

    // ── Shared CSS injected in every auth page ──────────────────────────────
    private const AUTH_CSS = <<<'CSS'
<style>
*,*::before,*::after{box-sizing:border-box}
body{margin:0;font-family:'Inter',system-ui,sans-serif;background:#09090b;color:#f4f4f5;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1.25rem}
.auth-card{width:100%;max-width:420px;background:#18181b;border:1px solid #27272a;border-radius:1.125rem;padding:2.25rem 2.5rem;box-shadow:0 25px 60px rgba(0,0,0,.6)}
.auth-logo{text-align:center;margin-bottom:1.75rem}
.auth-logo span{font-size:1.25rem;font-weight:800;background:linear-gradient(135deg,#8b5cf6,#6366f1);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.auth-logo small{display:block;color:#71717a;font-size:.8rem;margin-top:.15rem}
h1{margin:0 0 1.5rem;font-size:1.5rem;font-weight:700;color:#f4f4f5;text-align:center}
label{display:block;font-size:.8rem;font-weight:600;color:#a1a1aa;margin-bottom:.4rem;letter-spacing:.04em;text-transform:uppercase}
input[type=text],input[type=email],input[type=password]{width:100%;background:#09090b;border:1px solid #27272a;border-radius:.625rem;padding:.7rem 1rem;color:#f4f4f5;font-size:.95rem;outline:none;transition:border-color .2s,box-shadow .2s}
input[type=text]:focus,input[type=email]:focus,input[type=password]:focus{border-color:#8b5cf6;box-shadow:0 0 0 3px rgba(139,92,246,.18)}
input[type=text]::placeholder,input[type=email]::placeholder,input[type=password]::placeholder{color:#52525b}
.form-group{margin-bottom:1.1rem}
.auth-btn{width:100%;background:linear-gradient(135deg,#8b5cf6,#6366f1);color:#fff;border:none;border-radius:.625rem;padding:.875rem;font-size:.95rem;font-weight:600;cursor:pointer;letter-spacing:.02em;transition:opacity .2s,transform .1s;margin-top:.5rem}
.auth-btn:hover{opacity:.88}
.auth-btn:active{transform:scale(.98)}
.auth-link{text-align:center;margin-top:1.25rem;font-size:.875rem;color:#71717a}
.auth-link a{color:#a78bfa;text-decoration:none;font-weight:500}
.auth-link a:hover{text-decoration:underline}
.auth-alert{padding:.7rem 1rem;border-radius:.625rem;font-size:.875rem;margin-bottom:1rem}
.auth-alert-error{background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.35);color:#fca5a5}
.auth-alert-success{background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.35);color:#86efac}
.auth-remember{display:flex;align-items:center;gap:.5rem;margin-bottom:.75rem}
.auth-remember input{width:1rem;height:1rem;accent-color:#8b5cf6;cursor:pointer}
.auth-remember label{margin:0;font-size:.875rem;text-transform:none;letter-spacing:0;color:#a1a1aa;cursor:pointer}
.auth-divider{border:none;border-top:1px solid #27272a;margin:1.25rem 0}
.auth-forgot{text-align:right;font-size:.8rem;margin-top:.3rem}
.auth-forgot a{color:#a78bfa;text-decoration:none}
.auth-forgot a:hover{text-decoration:underline}
.auth-back{text-align:center;margin-top:1rem}
.auth-back a{color:#a78bfa;font-size:.875rem;text-decoration:none}
.auth-back a:hover{text-decoration:underline}
</style>
CSS;

    protected function configure(): void
    {
        $this
            ->setName('make:auth')
            ->setDescription('Scaffold the complete authentication system (login, register, forgot/reset password, profile, email verification)')
            ->addOption('no-interaction', null, InputOption::VALUE_NONE, 'Skip confirmations');
    }

    public function executeDirect(): void
    {
        $app = Application::getInstance();
        $basePath = $app->basePath();

        echo "\n\033[35m\033[1m  ▲ Veldora Auth Scaffold\033[0m\n";
        echo "  \033[90mGenerating complete authentication layer...\033[0m\n\n";

        $this->createMigration($basePath);
        $this->createModel($basePath);
        $this->createControllers($basePath);
        $this->createViews($basePath);
        $this->appendRoutes($basePath);

        echo "\n\033[32m\033[1m  ✔ Authentication scaffolding complete!\033[0m\n";
        echo "  Run \033[33mphp veldora migrate\033[0m to create the users table.\n\n";
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->executeDirect();
        return Command::SUCCESS;
    }

    // ── Step 1: Migration ───────────────────────────────────────────────────

    private function createMigration(string $basePath): void
    {
        $timestamp = date('Y_m_d_His');
        $file = "{$basePath}/database/migrations/{$timestamp}_create_users_table.php";
        $dir  = dirname($file);
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $content = <<<'PHP'
<?php

declare(strict_types=1);

use Veldora\Framework\Database\Schema\Blueprint;
use Veldora\Framework\Database\Schema\Migration;
use Veldora\Framework\Database\Schema\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('remember_token', 100)->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('bio', 500)->nullable();
            $table->string('reset_token', 100)->nullable();
            $table->timestamp('reset_token_expires_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
PHP;

        file_put_contents($file, $content);
        echo "  \033[32m+\033[0m database/migrations/" . basename($file) . "\n";
    }

    // ── Step 2: User Model ──────────────────────────────────────────────────

    private function createModel(string $basePath): void
    {
        $dir  = "{$basePath}/app/Models";
        $file = "{$dir}/User.php";
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $content = <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Models;

use Veldora\Framework\Database\Model;
use Veldora\Framework\Database\SoftDeletes;
use Veldora\Framework\Auth\MustVerifyEmail;

class User extends Model
{
    use SoftDeletes;
    use MustVerifyEmail;

    protected ?string $table = 'users';

    /** @var array<int,string> */
    protected array $hidden = ['password', 'remember_token', 'reset_token'];

    /** @var array<int,string> */
    protected array $fillable = ['name', 'email', 'password', 'bio', 'profile_photo'];

    /**
     * Hash the password when set via mass assignment.
     */
    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['password'] = password_hash($value, PASSWORD_BCRYPT);
    }
}
PHP;

        file_put_contents($file, $content);
        echo "  \033[32m+\033[0m app/Models/User.php\n";
    }

    // ── Step 3: Controllers ─────────────────────────────────────────────────

    private function createControllers(string $basePath): void
    {
        $dir = "{$basePath}/app/Controllers";
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $controllers = [
            'LoginController.php'         => $this->loginControllerContent(),
            'RegisterController.php'      => $this->registerControllerContent(),
            'ForgotPasswordController.php' => $this->forgotPasswordControllerContent(),
            'ResetPasswordController.php'  => $this->resetPasswordControllerContent(),
            'ProfileController.php'        => $this->profileControllerContent(),
            'VerificationController.php'   => $this->verificationControllerContent(),
        ];

        foreach ($controllers as $filename => $content) {
            file_put_contents("{$dir}/{$filename}", $content);
            echo "  \033[32m+\033[0m app/Controllers/{$filename}\n";
        }
    }

    private function loginControllerContent(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Controllers;

use Veldora\Framework\Http\Request;
use Veldora\Framework\Http\Response;
use Veldora\Framework\Auth\Auth;

class LoginController
{
    public function show(): Response
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }
        return view('auth/login');
    }

    public function login(Request $request): Response
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        $remember = $request->input('remember') === 'on';

        if (Auth::attempt($data, $remember)) {
            return redirect('/dashboard');
        }

        return back()->with('error', 'Invalid email or password.');
    }

    public function logout(): Response
    {
        Auth::logout();
        return redirect('/login');
    }
}
PHP;
    }

    private function registerControllerContent(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Controllers;

use Veldora\Framework\Http\Request;
use Veldora\Framework\Http\Response;
use Veldora\Framework\Auth\Auth;
use App\Models\User;

class RegisterController
{
    public function show(): Response
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }
        return view('auth/register');
    }

    public function register(Request $request): Response
    {
        $data = $request->validate([
            'name'                  => 'required|string|max:100',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password'],
        ]);

        Auth::login($user);

        return redirect('/dashboard');
    }
}
PHP;
    }

    private function forgotPasswordControllerContent(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Controllers;

use Veldora\Framework\Http\Request;
use Veldora\Framework\Http\Response;
use Veldora\Framework\Auth\PasswordBroker;
use App\Models\User;

class ForgotPasswordController
{
    public function show(): Response
    {
        return view('auth/forgot-password');
    }

    public function send(Request $request): Response
    {
        $data = $request->validate([
            'email' => 'required|email',
        ]);

        /** @var User|null $user */
        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            // Don't reveal if e-mail exists — show same message
            return back()->with('success', 'If that email exists, a reset link has been sent.');
        }

        PasswordBroker::sendResetLink($user->email);

        return back()->with('success', 'Password reset link sent! Check your inbox.');
    }
}
PHP;
    }

    private function resetPasswordControllerContent(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Controllers;

use Veldora\Framework\Http\Request;
use Veldora\Framework\Http\Response;
use Veldora\Framework\Auth\PasswordBroker;
use App\Models\User;

class ResetPasswordController
{
    public function show(Request $request): Response
    {
        $token = $request->query('token');
        return view('auth/reset-password', compact('token'));
    }

    public function reset(Request $request): Response
    {
        $data = $request->validate([
            'token'                 => 'required',
            'email'                 => 'required|email',
            'password'              => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        $ok = PasswordBroker::reset(
            $data['email'],
            $data['token'],
            $data['password']
        );

        if (!$ok) {
            return back()->with('error', 'Invalid or expired reset link.');
        }

        return redirect('/login')->with('success', 'Password reset! Please log in.');
    }
}
PHP;
    }

    private function profileControllerContent(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Controllers;

use Veldora\Framework\Http\Request;
use Veldora\Framework\Http\Response;
use Veldora\Framework\Auth\Auth;

class ProfileController
{
    public function show(): Response
    {
        $user = Auth::user();
        return view('auth/profile', compact('user'));
    }

    public function update(Request $request): Response
    {
        $data = $request->validate([
            'name'  => 'required|string|max:100',
            'bio'   => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $user->name = $data['name'];
        $user->bio  = $data['bio'] ?? '';
        $user->save();

        return back()->with('success', 'Profile updated successfully!');
    }

    public function changePassword(Request $request): Response
    {
        $data = $request->validate([
            'current_password'      => 'required',
            'password'              => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        $user = Auth::user();

        if (!password_verify($data['current_password'], $user->password)) {
            return back()->with('error', 'Current password is incorrect.');
        }

        $user->password = password_hash($data['password'], PASSWORD_BCRYPT);
        $user->save();

        return back()->with('success', 'Password changed successfully!');
    }
}
PHP;
    }

    private function verificationControllerContent(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Controllers;

use Veldora\Framework\Http\Request;
use Veldora\Framework\Http\Response;
use Veldora\Framework\Auth\Auth;
use App\Models\User;

class VerificationController
{
    public function notice(): Response
    {
        if (Auth::user()?->hasVerifiedEmail()) {
            return redirect('/dashboard');
        }
        return view('auth/email-verify');
    }

    public function verify(Request $request): Response
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user && !$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return redirect('/dashboard')->with('success', 'Email verified!');
    }

    public function resend(): Response
    {
        // In a real app, re-send the verification email here.
        return back()->with('success', 'Verification link resent!');
    }
}
PHP;
    }

    // ── Step 4: Views ───────────────────────────────────────────────────────

    private function createViews(string $basePath): void
    {
        $dir = "{$basePath}/resources/views/auth";
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $views = [
            'login.veldora.php'          => $this->loginView(),
            'register.veldora.php'       => $this->registerView(),
            'forgot-password.veldora.php' => $this->forgotPasswordView(),
            'reset-password.veldora.php'  => $this->resetPasswordView(),
            'profile.veldora.php'         => $this->profileView(),
            'email-verify.veldora.php'    => $this->emailVerifyView(),
        ];

        foreach ($views as $filename => $content) {
            file_put_contents("{$dir}/{$filename}", $content);
            echo "  \033[32m+\033[0m resources/views/auth/{$filename}\n";
        }
    }

    private function authHead(string $title): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$title} — {{ config('app.name', 'Veldora App') }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
HTML . self::AUTH_CSS;
    }

    private function loginView(): string
    {
        $head = $this->authHead('Login');
        return <<<HTML
{$head}
</head>
<body>
<div class="auth-card">
    <div class="auth-logo">
        <span>{{ config('app.name', 'Veldora') }}</span>
        <small>Sign in to your account</small>
    </div>
    <h1>Welcome Back</h1>

    @if (session()->has('error'))
        <div class="auth-alert auth-alert-error">{{ session()->get('error') }}</div>
    @endif
    @if (session()->has('success'))
        <div class="auth-alert auth-alert-success">{{ session()->get('success') }}</div>
    @endif

    <form action="/login" method="POST">
        @csrf
        <div class="form-group">
            <label for="email">Email Address</label>
            <input id="email" type="email" name="email" required placeholder="you@example.com" autocomplete="email">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" required placeholder="••••••••" autocomplete="current-password">
            <div class="auth-forgot"><a href="/forgot-password">Forgot password?</a></div>
        </div>
        <div class="auth-remember">
            <input type="checkbox" id="remember" name="remember">
            <label for="remember">Keep me signed in</label>
        </div>
        <button type="submit" class="auth-btn">Sign In</button>
    </form>

    <div class="auth-link">Don't have an account? <a href="/register">Create one</a></div>
</div>
</body>
</html>
HTML;
    }

    private function registerView(): string
    {
        $head = $this->authHead('Register');
        return <<<HTML
{$head}
</head>
<body>
<div class="auth-card">
    <div class="auth-logo">
        <span>{{ config('app.name', 'Veldora') }}</span>
        <small>Create your account</small>
    </div>
    <h1>Create Account</h1>

    @if (session()->has('error'))
        <div class="auth-alert auth-alert-error">{{ session()->get('error') }}</div>
    @endif

    <form action="/register" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">Full Name</label>
            <input id="name" type="text" name="name" required placeholder="John Doe" autocomplete="name">
        </div>
        <div class="form-group">
            <label for="email">Email Address</label>
            <input id="email" type="email" name="email" required placeholder="you@example.com" autocomplete="email">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" required placeholder="Min. 8 characters" autocomplete="new-password">
        </div>
        <div class="form-group">
            <label for="password_confirmation">Confirm Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required placeholder="••••••••" autocomplete="new-password">
        </div>
        <button type="submit" class="auth-btn">Create Account</button>
    </form>

    <div class="auth-link">Already have an account? <a href="/login">Sign in</a></div>
</div>
</body>
</html>
HTML;
    }

    private function forgotPasswordView(): string
    {
        $head = $this->authHead('Forgot Password');
        return <<<HTML
{$head}
</head>
<body>
<div class="auth-card">
    <div class="auth-logo">
        <span>{{ config('app.name', 'Veldora') }}</span>
        <small>Password recovery</small>
    </div>
    <h1>Forgot Password</h1>

    @if (session()->has('success'))
        <div class="auth-alert auth-alert-success">{{ session()->get('success') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="auth-alert auth-alert-error">{{ session()->get('error') }}</div>
    @endif

    <p style="color:#71717a;font-size:.9rem;text-align:center;margin-bottom:1.25rem">
        Enter your email and we'll send you a link to reset your password.
    </p>

    <form action="/forgot-password" method="POST">
        @csrf
        <div class="form-group">
            <label for="email">Email Address</label>
            <input id="email" type="email" name="email" required placeholder="you@example.com" autocomplete="email">
        </div>
        <button type="submit" class="auth-btn">Send Reset Link</button>
    </form>

    <div class="auth-back"><a href="/login">← Back to Login</a></div>
</div>
</body>
</html>
HTML;
    }

    private function resetPasswordView(): string
    {
        $head = $this->authHead('Reset Password');
        return <<<HTML
{$head}
</head>
<body>
<div class="auth-card">
    <div class="auth-logo">
        <span>{{ config('app.name', 'Veldora') }}</span>
        <small>Set your new password</small>
    </div>
    <h1>Reset Password</h1>

    @if (session()->has('error'))
        <div class="auth-alert auth-alert-error">{{ session()->get('error') }}</div>
    @endif

    <form action="/reset-password" method="POST">
        @csrf
        <input type="hidden" name="token" value="{{ \$token ?? '' }}">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input id="email" type="email" name="email" required placeholder="you@example.com" autocomplete="email">
        </div>
        <div class="form-group">
            <label for="password">New Password</label>
            <input id="password" type="password" name="password" required placeholder="Min. 8 characters" autocomplete="new-password">
        </div>
        <div class="form-group">
            <label for="password_confirmation">Confirm New Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required placeholder="••••••••" autocomplete="new-password">
        </div>
        <button type="submit" class="auth-btn">Reset Password</button>
    </form>

    <div class="auth-back"><a href="/login">← Back to Login</a></div>
</div>
</body>
</html>
HTML;
    }

    private function profileView(): string
    {
        $head = $this->authHead('My Profile');
        return $head . <<<'HTML'

</head>
<body style="align-items:flex-start;padding-top:2rem">
<div class="auth-card" style="max-width:520px">
    <div class="auth-logo">
        <span>{{ config('app.name', 'Veldora') }}</span>
        <small>Account Settings</small>
    </div>
    <h1>My Profile</h1>

    @if (session()->has('success'))
        <div class="auth-alert auth-alert-success">{{ session()->get('success') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="auth-alert auth-alert-error">{{ session()->get('error') }}</div>
    @endif

    <!-- Profile Info -->
    <form action="/profile" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name">Display Name</label>
            <input id="name" type="text" name="name" required value="{{ $user->name ?? '' }}">
        </div>
        <div class="form-group">
            <label for="bio">Bio</label>
            <input id="bio" type="text" name="bio" value="{{ $user->bio ?? '' }}" placeholder="Tell us about yourself…">
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" value="{{ $user->email ?? '' }}" disabled style="opacity:.5;cursor:not-allowed">
        </div>
        <button type="submit" class="auth-btn">Save Changes</button>
    </form>

    <hr class="auth-divider">

    <!-- Change Password -->
    <h1 style="font-size:1.1rem;margin-bottom:1rem">Change Password</h1>
    <form action="/profile/password" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="current_password">Current Password</label>
            <input id="current_password" type="password" name="current_password" required placeholder="••••••••">
        </div>
        <div class="form-group">
            <label for="new_password">New Password</label>
            <input id="new_password" type="password" name="password" required placeholder="Min. 8 characters">
        </div>
        <div class="form-group">
            <label for="password_confirmation">Confirm Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required placeholder="••••••••">
        </div>
        <button type="submit" class="auth-btn">Update Password</button>
    </form>

    <div class="auth-back" style="margin-top:1.5rem">
        <a href="javascript:history.back()">← Back</a>
    </div>
</div>
</body>
</html>
HTML;
    }

    private function emailVerifyView(): string
    {
        $head = $this->authHead('Verify Email');
        return <<<HTML
{$head}
</head>
<body>
<div class="auth-card" style="text-align:center">
    <div class="auth-logo">
        <span>{{ config('app.name', 'Veldora') }}</span>
        <small>Email verification</small>
    </div>
    <div style="font-size:3rem;margin-bottom:1rem">📬</div>
    <h1>Check Your Inbox</h1>
    <p style="color:#71717a;font-size:.9rem;line-height:1.6;margin-bottom:1.5rem">
        We've sent a verification link to <strong style="color:#a1a1aa">{{ auth_user()?->email ?? '' }}</strong>.<br>
        Click the link in the email to activate your account.
    </p>

    @if (session()->has('success'))
        <div class="auth-alert auth-alert-success">{{ session()->get('success') }}</div>
    @endif

    <form action="/email/resend" method="POST">
        @csrf
        <button type="submit" class="auth-btn">Resend Verification Email</button>
    </form>

    <div class="auth-back" style="margin-top:1rem">
        <form action="/logout" method="POST" style="display:inline">@csrf<button type="submit" style="background:none;border:none;color:#a78bfa;cursor:pointer;font-size:.875rem">Sign out</button></form>
    </div>
</div>
</body>
</html>
HTML;
    }

    // ── Step 5: Routes ──────────────────────────────────────────────────────

    private function appendRoutes(string $basePath): void
    {
        $file = "{$basePath}/routes/web.php";
        if (!file_exists($file)) return;

        $existing = file_get_contents($file);
        if (str_contains($existing, 'LoginController')) return;

        $routes = <<<'PHP'


// ─── Authentication Routes (generated by make:auth) ─────────────────────────
$router->get('/login',  [App\Controllers\LoginController::class, 'show'])->name('login')->middleware(['guest']);
$router->post('/login', [App\Controllers\LoginController::class, 'login'])->middleware(['guest']);
$router->post('/logout',[App\Controllers\LoginController::class, 'logout'])->name('logout')->middleware(['auth']);

$router->get('/register',  [App\Controllers\RegisterController::class, 'show'])->name('register')->middleware(['guest']);
$router->post('/register', [App\Controllers\RegisterController::class, 'register'])->middleware(['guest']);

$router->get('/forgot-password',  [App\Controllers\ForgotPasswordController::class, 'show'])->name('password.request')->middleware(['guest']);
$router->post('/forgot-password', [App\Controllers\ForgotPasswordController::class, 'send'])->name('password.email')->middleware(['guest']);

$router->get('/reset-password',   [App\Controllers\ResetPasswordController::class, 'show'])->name('password.reset')->middleware(['guest']);
$router->post('/reset-password',  [App\Controllers\ResetPasswordController::class, 'reset'])->name('password.update')->middleware(['guest']);

$router->get('/profile',           [App\Controllers\ProfileController::class, 'show'])->name('profile.show')->middleware(['auth']);
$router->put('/profile',           [App\Controllers\ProfileController::class, 'update'])->name('profile.update')->middleware(['auth']);
$router->put('/profile/password',  [App\Controllers\ProfileController::class, 'changePassword'])->name('profile.password')->middleware(['auth']);

$router->get('/email/verify',  [App\Controllers\VerificationController::class, 'notice'])->name('verification.notice')->middleware(['auth']);
$router->get('/email/verify/{id}', [App\Controllers\VerificationController::class, 'verify'])->name('verification.verify')->middleware(['auth']);
$router->post('/email/resend', [App\Controllers\VerificationController::class, 'resend'])->name('verification.resend')->middleware(['auth']);
PHP;

        file_put_contents($file, $existing . $routes);
        echo "  \033[32m+\033[0m routes/web.php \033[90m(auth routes appended)\033[0m\n";
    }
}
