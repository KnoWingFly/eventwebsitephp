<?php
require __DIR__ . "/../config.php";
$error = "";

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
    <link href="https://cdn.jsdelivr.net/npm/daisyui@2.51.5/dist/full.css" rel="stylesheet" type="text/css" />
    <link href="../css/output.css" rel="stylesheet">
</head>
<body class="bg-base-300 min-h-screen flex items-center justify-center">

<div class="card w-96 bg-base-100 shadow-xl">
    <div class="card-body"> 
        <div class="tabs tabs-boxed">
            <a class="tab tab-active" href="#signup">Sign Up</a>
            <a class="tab" href="#login">Log In</a>
        </div>
        
        <div class="tab-content mt-6">
            <div id="signup" class="block">
                <h2 class="card-title justify-center mb-4">Sign Up for Free</h2>
                <form action="" method="post">
                    <input type="hidden" name="action" value="signup">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Name</span>
                        </label>
                        <input type="text" name="name" required autocomplete="off" class="input input-bordered">
                    </div>
                    <div class="form-control mt-4">
                        <label class="label">
                            <span class="label-text">Email Address</span>
                        </label>
                        <input type="email" name="email" required autocomplete="off" class="input input-bordered">
                    </div>
                    <div class="form-control mt-4">
                        <label class="label">
                            <span class="label-text">Set A Password</span>
                        </label>
                        <input type="password" name="password" required autocomplete="off" class="input input-bordered">
                    </div>
                    <div class="form-control mt-6">
                        <button type="submit" class="btn btn-primary">Get Started</button>
                    </div>
                </form>
            </div>
            
            <div id="login" class="hidden">
                <h2 class="card-title justify-center mb-4">Welcome Back!</h2>
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
                        <button type="submit" class="btn btn-primary">Log In</button>
                    </div>
                </form>
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
    $('.tabs a').on('click', function(e) {
        e.preventDefault();
        
        var target = $(this).attr('href');

        if ($(target).is(':visible')) {
            return;
        }

        $('.tab-content > div:visible').hide();
        $(target).show();

        $(this).addClass('tab-active').siblings().removeClass('tab-active');
    });
});
</script>

</body>
</html>