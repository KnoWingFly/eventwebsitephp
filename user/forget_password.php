<?php
require __DIR__ . '/../config.php';
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Check if email exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Generate a token and store it in the database
        $token = bin2hex(random_bytes(50));  
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = ?");
        $stmt->execute([$token, $email]);

        $reset_link = "http://localhost:8000/user/reset_password.php?token=" . $token;

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();                                         
            $mail->Host       = 'smtp.gmail.com';                    
            $mail->SMTPAuth   = true;                              
            $mail->Username   = 'unknownowl26@gmail.com';            
            $mail->Password   = 'dbst pvbk dasu xmmb';                  
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;        
            $mail->Port       = 587;                                   

            //Recipients
            $mail->setFrom('your-email@gmail.com', 'Event Website');
            $mail->addAddress($email);                                

            // Content
            $mail->isHTML(true);                              
            $mail->Subject = 'Password Reset Request';
            $mail->Body    = "Click the following link to reset your password: <a href='$reset_link'>$reset_link</a>";

            $mail->send();
            $success = "A password reset link has been sent to your email.";
        } catch (Exception $e) {
            $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $error = "Email not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-300 font-sans">
<div class="max-w-lg mx-auto mt-10 bg-gray-800 rounded-lg shadow-lg">
    <div class="p-6">
        <h1 class="text-3xl text-center text-white mb-6">Forgot Password</h1>
        <form action="" method="post">
            <div class="mt-6">
                <label class="text-gray-400">Email Address<span class="text-red-500">*</span></label>
                <input type="email" name="email" required autocomplete="off" class="w-full p-3 mt-2 bg-gray-700 text-white border border-gray-600 rounded focus:outline-none focus:border-teal-500">
            </div>
            <button type="submit" class="mt-6 w-full py-3 bg-teal-500 text-white rounded-lg hover:bg-teal-600">Send Reset Link</button>
        </form>

        <?php if ($error): ?>
            <p class="mt-4 text-red-500 text-center"><?= $error; ?></p>
        <?php elseif ($success): ?>
            <p class="mt-4 text-green-500 text-center"><?= $success; ?></p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
