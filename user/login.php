<?php
require __DIR__ . '/../config.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'login') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            // Check user credentials
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] == 'admin') {
                    header('Location: /admin/dashboard.php');
                } else {
                    header('Location: /user/dashboard.php');
                }
                exit;
            } else {
                $error = "Invalid credentials!";
            }
        } elseif ($_POST['action'] === 'signup') {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $password = $_POST['password'];

            // Check if email already exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "Email already exists!";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
                if ($stmt->execute([$name, $email, $hashed_password])) {
                    $_SESSION['user_id'] = $pdo->lastInsertId();
                    $_SESSION['role'] = 'user';
                    header('Location: /user/dashboard.php');
                    exit;
                } else {
                    $error = "Sign up failed!";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up & Log In</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-300 font-sans">

<div class="max-w-lg mx-auto mt-10 bg-gray-800 rounded-lg shadow-lg">
    <ul class="flex justify-center mb-6">
        <li class="w-1/2 text-center">
            <a href="#signup" class="block py-3 text-white bg-teal-500 rounded-tl-lg">Sign Up</a>
        </li>
        <li class="w-1/2 text-center">
            <a href="#login" class="block py-3 text-gray-400 hover:bg-teal-500 hover:text-white transition">Log In</a>
        </li>
    </ul>
    
    <div class="p-6 tab-content">
        <div id="signup" class="block">
            <h1 class="text-3xl text-center text-white mb-6">Sign Up for Free</h1>
            <form action="" method="post">
                <input type="hidden" name="action" value="signup">
                <div class="mt-6">
                    <label class="text-gray-400">Name<span class="text-red-500">*</span></label>
                    <input type="text" name="name" required autocomplete="off" class="w-full p-3 mt-2 bg-gray-700 text-white border border-gray-600 rounded focus:outline-none focus:border-teal-500">
                </div>
                <div class="mt-6">
                    <label class="text-gray-400">Email Address<span class="text-red-500">*</span></label>
                    <input type="email" name="email" required autocomplete="off" class="w-full p-3 mt-2 bg-gray-700 text-white border border-gray-600 rounded focus:outline-none focus:border-teal-500">
                </div>
                <div class="mt-6">
                    <label class="text-gray-400">Set A Password<span class="text-red-500">*</span></label>
                    <input type="password" name="password" required autocomplete="off" class="w-full p-3 mt-2 bg-gray-700 text-white border border-gray-600 rounded focus:outline-none focus:border-teal-500">
                </div>
                <button type="submit" class="mt-6 w-full py-3 bg-teal-500 text-white rounded-lg hover:bg-teal-600">Get Started</button>
            </form>
        </div>
        
        <div id="login" class="hidden">
            <h1 class="text-3xl text-center text-white mb-6">Welcome Back!</h1>
            <form action="" method="post">
                <input type="hidden" name="action" value="login">
                <div class="mt-6">
                    <label class="text-gray-400">Email Address<span class="text-red-500">*</span></label>
                    <input type="email" name="email" required autocomplete="off" class="w-full p-3 mt-2 bg-gray-700 text-white border border-gray-600 rounded focus:outline-none focus:border-teal-500">
                </div>
                <div class="mt-6">
                    <label class="text-gray-400">Password<span class="text-red-500">*</span></label>
                    <input type="password" name="password" required autocomplete="off" class="w-full p-3 mt-2 bg-gray-700 text-white border border-gray-600 rounded focus:outline-none focus:border-teal-500">
                </div>
                <div class="mt-6 text-center">
                    <a href="../user/forget_password.php" class="text-teal-500 hover:text-teal-700">Forgot Password?</a>
                </div>
                <button type="submit" class="mt-6 w-full py-3 bg-teal-500 text-white rounded-lg hover:bg-teal-600">Log In</button>
            </form>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div id="errorModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
  <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
      <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
        <div class="sm:flex sm:items-start">
          <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
            <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
              Error
            </h3>
            <div class="mt-2">
              <p class="text-sm text-gray-500" id="errorMessage">
                <?php echo $error; ?>
              </p>
            </div>
          </div>
        </div>
      </div>
      <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
        <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm" onclick="closeModal()">
          Close
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('ul li a').on('click', function(e) {
        e.preventDefault(); 
        
        var target = $(this).attr('href');

        if ($(target).is(':visible')) {
            return;
        }

        $('.tab-content > div:visible').fadeOut(300, function() {
            $(target).fadeIn(300);
        });

        $(this).addClass('bg-teal-500 text-white').removeClass('text-gray-400');
        $(this).parent().siblings().find('a').addClass('text-gray-400').removeClass('bg-teal-500 text-white');
    });

    <?php if ($error): ?>
    showModal();
    <?php endif; ?>
});

function showModal() {
    document.getElementById('errorModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('errorModal').classList.add('hidden');
}
</script>

</body>
</html>