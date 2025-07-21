<?php
session_start();

// Generate a random captcha code
function generateCaptcha($length = 6) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $captcha = '';
    for ($i = 0; $i < $length; $i++) {
        $captcha .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $captcha;
}

// Generate new captcha
$captchaCode = generateCaptcha();
$_SESSION['captcha_code'] = $captchaCode;

// Return the captcha code as JSON
header('Content-Type: application/json');
echo json_encode(['captcha' => $captchaCode]);
?> 