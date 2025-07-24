<?php
header('Content-Type: application/json');
// session_start(); // No longer needed for captcha

// Allow CORS for AJAX requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input data
    $name = trim(htmlspecialchars($_POST['name'] ?? ''));
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $message = trim(htmlspecialchars($_POST['message'] ?? ''));
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';

    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }
    
    if (empty($message)) {
        $errors[] = "Message is required";
    }

    // Google reCAPTCHA validation
    if (empty($recaptcha_response)) {
        $errors[] = "Please complete the security verification.";
    } else {
        $recaptcha_secret = '6LcGL4orAAAAAGKDL7ird0LkgIiv570EWn4z2g9O'; // Replace with your secret key
        $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
        $recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response . '&remoteip=' . $_SERVER['REMOTE_ADDR']);
        $recaptcha = json_decode($recaptcha);
        if (!$recaptcha || !$recaptcha->success) {
            $errors[] = "reCAPTCHA verification failed. Please try again.";
        }
    }

    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Please fix the following errors: ' . implode(', ', $errors)
        ]);
        exit;
    }

    // Rate limiting - check if too many requests from same IP
    $ip = $_SERVER['REMOTE_ADDR'];
    $timeWindow = 3600; // 1 hour
    $maxRequests = 5; // Max 5 requests per hour
    
    if (!isset($_SESSION['request_count'])) {
        $_SESSION['request_count'] = 1;
        $_SESSION['first_request_time'] = time();
    } else {
        $timeDiff = time() - $_SESSION['first_request_time'];
        if ($timeDiff > $timeWindow) {
            // Reset counter if time window has passed
            $_SESSION['request_count'] = 1;
            $_SESSION['first_request_time'] = time();
        } else {
            $_SESSION['request_count']++;
            if ($_SESSION['request_count'] > $maxRequests) {
                http_response_code(429);
                echo json_encode([
                    'success' => false,
                    'message' => 'Too many requests. Please try again later.'
                ]);
                exit;
            }
        }
    }

    // Email configuration
    $to = "bezlostudio@gmail.com"; // Replace with your email
    $subject = "New Project Inquiry from Bezlo Studio Website";
    
    // Email headers
    $headers = "From: $email\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    
    // Email body
    $emailBody = "
    <html>
    <head>
        <title>New Project Inquiry</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
            .content { background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; color: #555; }
            .value { margin-top: 5px; }
            .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>New Project Inquiry from Bezlo Studio Website</h2>
            </div>
            <div class='content'>
                <div class='field'>
                    <div class='label'>Name:</div>
                    <div class='value'>$name</div>
                </div>
                <div class='field'>
                    <div class='label'>Email:</div>
                    <div class='value'>$email</div>
                </div>
                <div class='field'>
                    <div class='label'>Message:</div>
                    <div class='value'>" . nl2br($message) . "</div>
                </div>
            </div>
            <div class='footer'>
                <p><strong>Submission Details:</strong></p>
                <p>IP Address: $ip</p>
                <p>Date: " . date('Y-m-d H:i:s') . "</p>
                <p>This message was sent from the contact form on bezlostudio.com</p>
            </div>
        </div>
    </body>
    </html>
    ";

    // Try to send email
    $mailSent = mail($to, $subject, $emailBody, $headers);
    
    if ($mailSent) {
        echo json_encode([
            'success' => true,
            'message' => "Thank you for contacting us, $name! We will get back to you shortly."
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Sorry, there was an error sending your message. Please try again or contact us directly.'
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}
?>
