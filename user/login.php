<?php
require __DIR__ . "/../config.php";
$error = "";

// Backend Password Validation Logic
function isValidPassword($password) {
    return preg_match('/[A-Z]/', $password) &&    
           preg_match('/[a-z]/', $password) &&     
           preg_match('/\d/', $password) &&        
           preg_match('/[^A-Za-z0-9]/', $password) && 
           strlen($password) >= 12;                
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
	if (isset($_POST["action"])) {
		if ($_POST["action"] === "login") {
			$email = $_POST["email"];
			$password = $_POST["password"];

			$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
			$stmt->execute([$email]);
			$user = $stmt->fetch();

			if ($user && password_verify($password, $user["password"])) {
				$_SESSION["user_id"] = $user["id"];
				$_SESSION["role"] = $user["role"];

				if ($user["role"] == "admin") {
					header("Location: /admin/dashboard.php");
				} else {
					header("Location: /user/dashboard.php");
				}
				exit();
			} else {
				$error = "Invalid credentials!";
			}
		} elseif ($_POST["action"] === "signup") {
			$name = $_POST["name"];
			$email = $_POST["email"];
			$password = $_POST["password"];

			// Check if email already exists
			$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
			$stmt->execute([$email]);
			if ($stmt->fetch()) {
				$error = "Email already exists!";
			} elseif (!isValidPassword($password)) {
				$error = "Password does not meet the criteria!";
			} else {
				$hashed_password = password_hash($password, PASSWORD_DEFAULT);
				$stmt = $pdo->prepare(
					"INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')",
				);
				if ($stmt->execute([$name, $email, $hashed_password])) {
					$_SESSION["user_id"] = $pdo->lastInsertId();
					$_SESSION["role"] = "user";
					header("Location: /user/dashboard.php");
					exit();
				} else {
					$error = "Sign up failed!";
				}
			}
		}
	}
}
?>


<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up & Log In</title>
    <!-- <link href="https://cdn.jsdelivr.net/npm/daisyui@2.51.5/dist/full.css" rel="stylesheet" type="text/css" /> -->
    <link href="../css/output.css" rel="stylesheet">
    <style>
        .card-container {
            perspective: 1000px;
            width: 90%; 
            max-width: 800px;
            margin: 0 auto;
            padding-top: 2rem; /* Add padding to move up */
        }
        
        .card-flip {
            position: relative;
            width: 100%;
            transition: transform 0.8s;
            transform-style: preserve-3d;
        }
        
        .card-flip.flipped {
            transform: rotateY(180deg);
        }
        
        .card-face {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .card-back {
            transform: rotateY(180deg);
        }
        
        .split-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .info-section {
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background-color: rgba(139, 92, 246, 0.1);
        }
        
        .form-section {
            padding: 2rem;
        }

        .animate-fade {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @media (min-width: 600px) {
            .split-container {
                grid-template-columns: 1fr 1fr;
            }
        }

        .form-section, .info-section {
            padding: 1rem;
        }

        @media (min-width: 600px) {
            .form-section, .info-section {
                padding: 2rem;
            }
        }

        /* Remove the forced 100vh to allow natural flow upwards */
        .min-h-screen {
            display: flex;
            justify-content: center;
            align-items: flex-start; /* Align to top */
            min-height: 100vh; /* Minimum screen height for responsiveness */
            padding-top: 2rem; 
        }

        .password-requirements {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            z-index: 50;
            width: 100%;
            /* background-color: rgba(255, 255, 255, 0.9); */
            padding: 1rem;
            border-radius: 0.5rem;
        }

        .password-requirements.active {
            display: block;
        }

        .requirement-met {
            color: green;
        }

        .requirement-not-met {
            color: red;
        }

        .btn-disabled {
            cursor: not-allowed !important;
            opacity: 1;
        }

    </style>
</head>
<body class="bg-base-300 min-h-screen flex items-center justify-center">
    <div class="card-container relative">
        <div class="card-flip">
            <!-- Sign Up Face -->
            <div class="card-face card-front">
                <div class="card bg-base-100 shadow-xl w-full h-full">
                    <div class="split-container">
                        <div class="info-section text-center">
                            <h2 class="text-2xl font-bold mb-4">Already have an account?</h2>
                            <p class="mb-6">Sign in to access your account</p>
                            <button class="btn btn-outline btn-primary flip-button w-full">Log In</button>
                        </div>
                        <div class="form-section">
                            <h2 class="text-2xl font-bold mb-6 text-center">Sign Up for Free</h2>
                            <form action="" method="post">
                                <input type="hidden" name="action" value="signup">
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text">Name</span>
                                    </label>
                                    <input type="text" id="name" name="name" required autocomplete="off" class="input input-bordered">
                                </div>
                                <div class="form-control mt-4">
                                    <label class="label">
                                        <span class="label-text">Email Address</span>
                                    </label>
                                    <input type="email" id="email" name="email" required autocomplete="off" class="input input-bordered">
                                </div>
                                <div class="form-control mt-4 relative">
                                    <label class="label">
                                        <span class="label-text">Set A Password</span>
                                    </label>
                                    <input type="password" id="password" name="password" required autocomplete="off" class="input input-bordered">
                                    <div id="password-popup" class="password-requirements absolute bg-base-100">
                                        <ul id="password-requirements-list" class="list-disc list-inside">
                                            <li id="min-length" class="requirement-not-met">At least 12 characters</li>
                                            <li id="uppercase" class="requirement-not-met">At least one uppercase letter</li>
                                            <li id="lowercase" class="requirement-not-met">At least one lowercase letter</li>
                                            <li id="number" class="requirement-not-met">At least one number</li>
                                            <li id="special-char" class="requirement-not-met">At least one special character</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="form-control mt-6">
                                    <button type="submit" id="submit-btn" class="btn btn-primary w-full btn-disabled" disabled>Get Started</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Login Face -->
            <div class="card-face card-back">
                <div class="card bg-base-100 shadow-xl w-full h-full">
                    <div class="split-container">
                        <div class="info-section text-center">
                            <h2 class="text-2xl font-bold mb-4">New here?</h2>
                            <p class="mb-6">Sign up and discover our platform</p>
                            <button class="btn btn-outline btn-primary flip-button w-full">Sign Up</button>
                        </div>
                        <div class="form-section">
                            <h2 class="text-2xl font-bold mb-6 text-center">Welcome Back!</h2>
                            <form action="" method="post">
                                <input type="hidden" name="action" value="login">
                                <div class="form-control">
                                    <label class="label">
                                        <span class="label-text">Email Address</span>
                                    </label>
                                    <input type="email" name="email" required autocomplete="off" class="input input-bordered">
                                </div>
                                <div class="form-control mt-4">
                                    <label class="label">
                                        <span class="label-text">Password</span>
                                    </label>
                                    <input type="password" name="password" required autocomplete="off" class="input input-bordered">
                                </div>
                                <div class="form-control mt-2">
                                    <label class="label justify-end">
                                        <a href="../user/forget_password.php" class="label-text-alt link link-hover">Forgot password?</a>
                                    </label>
                                </div>
                                <div class="form-control mt-6">
                                    <button type="submit" class="btn btn-primary w-full">Log In</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <input type="checkbox" id="error-modal" class="modal-toggle" <?php echo $error ? 'checked' : ''; ?>>
    <div class="modal">
        <div class="modal-box relative">
            <label for="error-modal" class="btn btn-sm btn-circle absolute right-2 top-2">✕</label>
            <h3 class="text-lg font-bold">Error</h3>
            <p class="py-4"><?php echo $error; ?></p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.flip-button').on('click', function() {
                $('.card-flip').toggleClass('flipped');
            });

            const nameField = $('#name');
            const emailField = $('#email');
            const passwordField = $('#password');
            const submitBtn = $('#submit-btn');
            const passwordPopup = $('#password-popup');

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

            // Check Form Validity (name, email, and password must be valid)
            function checkFormValidity() {
                const isFormValid = nameField.val().trim() !== '' && emailField.val().trim() !== '' &&
                                    passwordField.val().length >= 12 &&
                                    /[A-Z]/.test(passwordField.val()) &&
                                    /[a-z]/.test(passwordField.val()) &&
                                    /\d/.test(passwordField.val()) &&
                                    /[^A-Za-z0-9]/.test(passwordField.val());

                submitBtn.prop('disabled', !isFormValid);
                submitBtn.toggleClass('btn-disabled', !isFormValid);
            }

            // Monitor changes in name and email fields
            nameField.on('keyup', checkFormValidity);
            emailField.on('keyup', checkFormValidity);
        });
    </script>

</body>
</html>