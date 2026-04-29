<?php
// auth/login.php
require_once '../config/db.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$error = '';
$success_msg = '';

if (isset($_SESSION['login_message'])) {
    $success_msg = $_SESSION['login_message'];
    unset($_SESSION['login_message']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password_hash, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password_hash'])) {
                // Login complete, set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    header("Location: ../admin/dashboard.php");
                } else {
                    header("Location: ../index.php");
                }
                exit;
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "No account found with that email address.";
        }
    }
}
include '../includes/header.php';
?>

<div class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white p-8 rounded-xl shadow-lg border border-gray-100">
        <div>
            <h2 class="mt-2 text-center text-3xl font-extrabold text-gray-900">Welcome back</h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Or <a href="register.php" class="font-medium text-brand-600 hover:text-brand-500">create a new account</a>
            </p>
        </div>

        <?php if ($success_msg): ?>
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mt-6 rounded-r-md">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700 font-medium"><?php echo htmlspecialchars($success_msg); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mt-6">
                <div class="flex">
                    <div class="shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700"><?php echo $error; ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <form id="login-form" class="mt-8 space-y-6" action="login.php" method="POST">
            <div class="space-y-4">
                <div>
                    <label for="email" class="text-sm font-medium text-gray-700 block mb-1">Email address</label>
                    <input id="email" name="email" type="email" autocomplete="email" required class="appearance-none rounded-md relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-brand-500 focus:border-brand-500 sm:text-sm" placeholder="you@gmail.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                <div>
                    <label for="password" class="text-sm font-medium text-gray-700 block mb-1">Password</label>
                    <div class="relative">
                        <input id="password" name="password" type="password" autocomplete="current-password" required class="appearance-none rounded-md relative block w-full px-3 py-3 pr-10 border border-gray-300 placeholder-gray-500/70 text-gray-900 focus:outline-none focus:ring-brand-500 focus:border-brand-500 sm:text-sm" placeholder="••••••••">
                        <button type="button" id="toggle-password" aria-label="Toggle password visibility"
                            class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400 hover:text-gray-600 focus:outline-none bg-transparent border-0 cursor-pointer">
                            <!-- Eye-off icon (shown when password is hidden) -->
                            <svg id="icon-eye-off" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                                <path fill="currentColor" d="M2 5.27L3.28 4L20 20.72L18.73 22l-3.08-3.08c-1.15.38-2.37.58-3.65.58c-5 0-9.27-3.11-11-7.5c.69-1.76 1.79-3.31 3.19-4.54zM12 9a3 3 0 0 1 3 3a3 3 0 0 1-.17 1L11 9.17A3 3 0 0 1 12 9m0-4.5c5 0 9.27 3.11 11 7.5a11.8 11.8 0 0 1-4 5.19l-1.42-1.43A9.86 9.86 0 0 0 20.82 12A9.82 9.82 0 0 0 12 6.5c-1.09 0-2.16.18-3.16.5L7.3 5.47c1.44-.62 3.03-.97 4.7-.97M3.18 12A9.82 9.82 0 0 0 12 17.5c.69 0 1.37-.07 2-.21L11.72 15A3.064 3.064 0 0 1 9 12.28L5.6 8.87c-.99.85-1.82 1.91-2.42 3.13" />
                            </svg>
                            <!-- Eye icon (shown when password is visible) -->
                            <svg id="icon-eye" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" class="hidden">
                                <path fill="currentColor" d="M12 9a3 3 0 0 1 3 3a3 3 0 0 1-3 3a3 3 0 0 1-3-3a3 3 0 0 1 3-3m0-4.5c5 0 9.27 3.11 11 7.5c-1.73 4.39-6 7.5-11 7.5S2.73 16.39 1 12c1.73-4.39 6-7.5 11-7.5M3.18 12a9.821 9.821 0 0 0 17.64 0a9.821 9.821 0 0 0-17.64 0" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between mt-4">
                <div class="flex items-center">
                    <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-brand-600 focus:ring-brand-500 border-gray-300 rounded">
                    <label for="remember-me" class="ml-2 block text-sm text-gray-900"> Remember me </label>
                </div>

                <div class="text-sm">
                    <a href="#" class="font-medium text-brand-600 hover:text-brand-500"> Forgot password? </a>
                </div>
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 shadow-md transition-colors mt-6">
                    Sign in
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // --- Show/Hide password toggle ---
    (function() {
        const toggleBtn = document.getElementById('toggle-password');
        const passwordInput = document.getElementById('password');
        const iconEyeOff = document.getElementById('icon-eye-off');
        const iconEye = document.getElementById('icon-eye');

        if (toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', function() {
                const isHidden = passwordInput.type === 'password';
                passwordInput.type = isHidden ? 'text' : 'password';
                iconEyeOff.classList.toggle('hidden', isHidden);
                iconEye.classList.toggle('hidden', !isHidden);
            });
        }
    })();
</script>

<script>
    (function() {
        const STORAGE_KEY = 'mobileshop_remember';
        const form = document.getElementById('login-form');
        const emailEl = document.getElementById('email');
        const passEl = document.getElementById('password');
        const rememberEl = document.getElementById('remember-me');

        // --- On page load: auto-fill if saved data exists ---
        try {
            const saved = localStorage.getItem(STORAGE_KEY);
            if (saved) {
                const data = JSON.parse(saved);
                // Only auto-fill when the server hasn't already set a value (e.g. after a failed POST)
                if (emailEl && !emailEl.value && data.email) {
                    emailEl.value = data.email;
                }
                if (passEl && !passEl.value && data.p) {
                    // Password is base64-encoded (obfuscation only, NOT real encryption)
                    passEl.value = atob(data.p);
                }
                if (rememberEl) {
                    rememberEl.checked = true;
                }
            }
        } catch (e) {
            // Corrupted data — clear it silently
            localStorage.removeItem(STORAGE_KEY);
        }

        // --- On form submit: save or clear based on checkbox ---
        if (form) {
            form.addEventListener('submit', function() {
                if (rememberEl && rememberEl.checked) {
                    const payload = {
                        email: emailEl.value,
                        p: btoa(passEl.value)
                    };
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
                } else {
                    localStorage.removeItem(STORAGE_KEY);
                }
            });
        }

        // --- Clear stored data if redirected from logout (?cleared=1) ---
        if (window.location.search.includes('cleared=1')) {
            localStorage.removeItem(STORAGE_KEY);
        }
    })();
</script>

<?php include '../includes/footer.php'; ?>