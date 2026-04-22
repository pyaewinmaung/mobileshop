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
                    <div class="ml-3"><p class="text-sm text-green-700 font-medium"><?php echo htmlspecialchars($success_msg); ?></p></div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mt-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3"><p class="text-sm text-red-700"><?php echo $error; ?></p></div>
                </div>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" action="login.php" method="POST">
            <div class="space-y-4">
                <div>
                    <label for="email" class="text-sm font-medium text-gray-700 block mb-1">Email address</label>
                    <input id="email" name="email" type="email" autocomplete="email" required class="appearance-none rounded-md relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-brand-500 focus:border-brand-500 sm:text-sm" placeholder="you@example.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                <div>
                    <label for="password" class="text-sm font-medium text-gray-700 block mb-1">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required class="appearance-none rounded-md relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-brand-500 focus:border-brand-500 sm:text-sm" placeholder="••••••••">
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

<?php include '../includes/footer.php'; ?>
