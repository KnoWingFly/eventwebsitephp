<?php
session_start();
require "../config.php";

$token = isset($_GET['token']) ? $_GET['token'] : '';
$error = "";
$success = "";

// Password validation function
function isValidPassword($password) {
    return preg_match('/[A-Z]/', $password) &&     
           preg_match('/[a-z]/', $password) &&  
           preg_match('/\d/', $password) &&     
           preg_match('/[^A-Za-z0-9]/', $password) && 
           strlen($password) >= 12;                
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];

    if ($new_password === $confirm_password) {
        if (!isValidPassword($new_password)) {
            $error = "Password does not meet the requirements!";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
            $stmt->execute([$token]);
            $user = $stmt->fetch();

            if ($user) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?");
                
                if ($stmt->execute([$hashed_password, $token])) {
                    $success = "Password successfully updated! You can now log in.";
                } else {
                    $error = "Failed to update password!";
                }
            } else {
                $error = "Invalid or expired token!";
            }
        }
    } else {
        $error = "Passwords do not match!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="../css/output.css" rel="stylesheet">
    <style>
        .popup {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            z-index: 50;
            width: 100%;
            padding: 1rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .popup.active {
            display: block;
        }

        .requirement-met {
            color: green;
        }

        .requirement-not-met {
            color: red;
        }

        .btn-disabled {
            background-color: gray !important;
            cursor: not-allowed !important;
            opacity: 0.6;
        }
    </style>
</head>
<body class="flex justify-center items-center min-h-screen bg-gray-900">
    <div class="bg-gray-800 rounded-lg shadow-lg p-8 max-w-md w-full">
        <h1 class="text-white text-center font-semibold text-2xl mb-6">Reset Password</h1>

        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-red-500 text-white rounded-lg">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="mb-4 p-3 bg-green-500 text-white rounded-lg">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            
            <div class="mb-4 relative">
                <label for="new_password" class="block text-gray-300 mb-1">New Password</label>
                <input type="password" name="new_password" id="new_password" required
                    class="block w-full rounded-lg p-3 bg-gray-700 text-white focus:ring-purple-500 focus:ring-2 focus:outline-none">
                <div id="password-popup" class="popup bg-base-100">
                    <ul id="password-requirements-list" class="list-disc list-inside">
                        <li id="min-length" class="requirement-not-met">At least 12 characters</li>
                        <li id="uppercase" class="requirement-not-met">At least one uppercase letter</li>
                        <li id="lowercase" class="requirement-not-met">At least one lowercase letter</li>
                        <li id="number" class="requirement-not-met">At least one number</li>
                        <li id="special-char" class="requirement-not-met">At least one special character</li>
                    </ul>
                </div>
            </div>

            <div class="mb-4 relative">
                <label for="confirm_password" class="block text-gray-300 mb-1">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" required
                    class="block w-full rounded-lg p-3 bg-gray-700 text-white focus:ring-purple-500 focus:ring-2 focus:outline-none">
                <div id="confirm-popup" class="popup bg-base-100">
                    <p id="confirm-message" class="requirement-not-met">Passwords do not match</p>
                </div>
            </div>

            <button type="submit" id="submit-btn" class="w-full p-3 bg-purple-600 hover:bg-purple-700 rounded-lg text-white font-semibold btn-disabled" disabled>
                Reset Password
            </button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            const passwordField = $('#new_password');
            const confirmPasswordField = $('#confirm_password');
            const submitBtn = $('#submit-btn');
            const passwordPopup = $('#password-popup');
            const confirmPopup = $('#confirm-popup');
            const confirmMessage = $('#confirm-message');

            // Show password requirements popup on focus
            passwordField.on('focus', function() {
                passwordPopup.addClass('active');
            });

            // Hide password popup on blur
            passwordField.on('blur', function() {
                setTimeout(function() {
                    passwordPopup.removeClass('active');
                }, 200);
            });

            // Show confirm password popup on focus
            confirmPasswordField.on('focus', function() {
                confirmPopup.addClass('active');
            });

            // Hide confirm password popup on blur
            confirmPasswordField.on('blur', function() {
                setTimeout(function() {
                    confirmPopup.removeClass('active');
                }, 200);
            });

            // Password Requirements Validation on Keyup
            passwordField.on('keyup', function() {
                const password = $(this).val();
                const isMinLength = password.length >= 12;
                const hasUppercase = /[A-Z]/.test(password);
                const hasLowercase = /[a-z]/.test(password);
                const hasNumber = /\d/.test(password);
                const hasSpecialChar = /[^A-Za-z0-9]/.test(password);

                $('#min-length').toggleClass('requirement-met', isMinLength).toggleClass('requirement-not-met', !isMinLength);
                $('#uppercase').toggleClass('requirement-met', hasUppercase).toggleClass('requirement-not-met', !hasUppercase);
                $('#lowercase').toggleClass('requirement-met', hasLowercase).toggleClass('requirement-not-met', !hasLowercase);
                $('#number').toggleClass('requirement-met', hasNumber).toggleClass('requirement-not-met', !hasNumber);
                $('#special-char').toggleClass('requirement-met', hasSpecialChar).toggleClass('requirement-not-met', !hasSpecialChar);

                checkFormValidity();
            });

            // Check if passwords match on keyup
            confirmPasswordField.on('keyup', function() {
                const password = passwordField.val();
                const confirmPassword = $(this).val();
                
                if (password === confirmPassword) {
                    confirmMessage.text("Passwords match").removeClass('requirement-not-met').addClass('requirement-met');
                } else {
                    confirmMessage.text("Passwords do not match").removeClass('requirement-met').addClass('requirement-not-met');
                }

                checkFormValidity();
            });

            // Check Form Validity
            function checkFormValidity() {
                const password = passwordField.val();
                const confirmPassword = confirmPasswordField.val();
                const isFormValid = (
                    password.length >= 12 &&
                    /[A-Z]/.test(password) &&
                    /[a-z]/.test(password) &&
                    /\d/.test(password) &&
                    /[^A-Za-z0-9]/.test(password) &&
                    password === confirmPassword
                );

                submitBtn.prop('disabled', !isFormValid);
                submitBtn.toggleClass('btn-disabled', !isFormValid);
            }
        });
    </script>
</body>
</html>