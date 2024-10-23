<?php
session_start();
require "../config.php";

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php?page=login");
    exit();
}

$user_id = $_SESSION["user_id"];
$error = "";
$success = "";

// Fetch current user info
$stmt_user = $pdo->prepare("SELECT name, email, profile_picture FROM users WHERE id = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);
$previous_picture = $user['profile_picture'];  // Store the previous picture

// Password validation function
function isValidPassword($password) {
    return preg_match('/[A-Z]/', $password) &&     
           preg_match('/[a-z]/', $password) &&  
           preg_match('/\d/', $password) &&     
           preg_match('/[^A-Za-z0-9]/', $password) && 
           strlen($password) >= 12;                
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $profile_picture = $previous_picture;  // Default to the existing profile picture

    // Check if a new profile picture is uploaded
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['profile_picture']['tmp_name'];
        $file_name = $_FILES['profile_picture']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png'];

        // Validate file extension
        if (!in_array($file_ext, $allowed_ext)) {
            $error = "Invalid file type. Only JPG, JPEG, and PNG files are allowed.";
            header("Location: edit_profile.php?error=" . urlencode($error));
            exit();
        }

        // Generate a unique name for the new profile picture
        $new_file_name = uniqid() . '.' . $file_ext;
        $upload_dir = "../images/";

        // Move the new file to the server
        if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
            // Delete the old profile picture if it exists and is not the default
            if ($previous_picture && file_exists($upload_dir . $previous_picture)) {
                unlink($upload_dir . $previous_picture);
            }

            // Set the new profile picture name
            $profile_picture = $new_file_name;
        } else {
            $error = "Failed to upload the new profile picture.";
            header("Location: edit_profile.php?error=" . urlencode($error));
            exit();
        }
    }

    // Update profile
    if (empty($error)) {
        if ($password && $password === $confirm_password) {
            if (!isValidPassword($password)) {
                $error = "Password does not meet the criteria!";
                header("Location: edit_profile.php?error=" . urlencode($error));
                exit();
            }
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt_update = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ?, profile_picture = ? WHERE id = ?");
            $stmt_update->execute([$name, $email, $hashed_password, $profile_picture, $user_id]);
        } elseif (!$password) {
            $stmt_update = $pdo->prepare("UPDATE users SET name = ?, email = ?, profile_picture = ? WHERE id = ?");
            $stmt_update->execute([$name, $email, $profile_picture, $user_id]);
        } else {
            $error = "Passwords do not match!";
        }

        if (!$error) {
            header("Location: profile.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
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
        <h1 class="text-white text-center font-semibold text-2xl mb-6">Edit Profile</h1>

        <!-- Display error or success messages -->
        <?php if (isset($_GET['error'])): ?>
            <div class="mb-4 p-3 bg-red-500 text-white rounded-lg">
                <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data">
            <div class="mb-4">
                <label for="name" class="block text-gray-300 mb-1">Name</label>
                <input type="text" name="name" id="name" required value="<?= htmlspecialchars($user["name"]) ?>" 
                    class="block w-full rounded-lg p-3 bg-gray-700 text-white focus:ring-purple-500 focus:ring-2 focus:outline-none">
            </div>
            
            <div class="mb-4">
                <label for="email" class="block text-gray-300 mb-1">Email</label>
                <input type="email" name="email" id="email" required value="<?= htmlspecialchars($user["email"]) ?>" 
                    class="block w-full rounded-lg p-3 bg-gray-700 text-white focus:ring-purple-500 focus:ring-2 focus:outline-none">
            </div>

            <div class="mb-4">
                <label for="profile_picture" class="block text-gray-300 mb-1">Profile Picture</label>
                <input type="file" name="profile_picture" id="profile_picture" accept=".jpg,.jpeg,.png" 
                    class="block w-full rounded-lg p-3 bg-gray-700 text-white focus:ring-purple-500 focus:ring-2 focus:outline-none">
                <?php if ($user['profile_picture']): ?>
                    <img src="../images/<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile Picture" class="mt-2 w-20 h-20 rounded-full">
                <?php endif; ?>
            </div>

            <div class="mb-4 relative">
                <label for="password" class="block text-gray-300 mb-1">New Password</label>
                <input type="password" name="password" id="password" class="block w-full rounded-lg p-3 bg-gray-700 text-white focus:ring-purple-500 focus:ring-2 focus:outline-none">
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
                <label for="confirm_password" class="block text-gray-300 mb-1">Confirm New Password</label>
                <input type="password" name="confirm_password" id="confirm_password" 
                    class="block w-full rounded-lg p-3 bg-gray-700 text-white focus:ring-purple-500 focus:ring-2 focus:outline-none">
                <div id="confirm-popup" class="popup bg-base-100">
                    <p id="confirm-message" class="requirement-not-met">Passwords do not match</p>
                </div>
            </div>

            <button type="submit" id="submit-btn" class="w-full p-3 bg-purple-600 hover:bg-purple-700 rounded-lg text-white font-semibold btn-disabled" disabled>
                Update Profile
            </button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#submit-btn').prop('disabled', false).removeClass('btn-disabled');

            const passwordField = $('#password');
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
                }, 200);  // Delay to allow clicking on the popup without it disappearing too quickly
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

            // Check Form Validity (password and confirm password must match)
            function checkFormValidity() {
                const password = passwordField.val();
                const confirmPassword = confirmPasswordField.val();
                const isFormValid = (
                    password === '' || 
                    (
                        password.length >= 12 &&
                        /[A-Z]/.test(password) &&
                        /[a-z]/.test(password) &&
                        /\d/.test(password) &&
                        /[^A-Za-z0-9]/.test(password) &&
                        password === confirmPassword
                    )
                );

                // Button should only be disabled when there's input in the new password field and it doesn't meet the criteria
                submitBtn.prop('disabled', password !== '' && !isFormValid);
                submitBtn.toggleClass('btn-disabled', password !== '' && !isFormValid);
            }
        });
    </script>
</body> 
</html>
