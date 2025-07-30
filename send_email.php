use Google\Cloud\RecaptchaEnterprise\V1\Client\RecaptchaEnterpriseServiceClient;
use Google\Cloud\RecaptchaEnterprise\V1\Event;
use Google\Cloud\RecaptchaEnterprise\V1\Assessment;
use Google\Cloud\RecaptchaEnterprise\V1\CreateAssessmentRequest;
use Google\Cloud\RecaptchaEnterprise\V1\TokenProperties\InvalidReason;

<?php
header('Content-Type: application/json');

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

    // Google reCAPTCHA Enterprise validation (NEW)
    if (empty($recaptcha_response)) {
        $errors[] = "Please complete the security verification.";
    } else {
        require 'vendor/autoload.php';


        function verify_recaptcha_enterprise($recaptchaKey, $token, $project, $action) {
            $client = new RecaptchaEnterpriseServiceClient();
            $projectName = $client->projectName($project);

            $event = (new Event())
                ->setSiteKey($recaptchaKey)
                ->setToken($token);

            $assessment = (new Assessment())
                ->setEvent($event);

            $request = (new CreateAssessmentRequest())
                ->setParent($projectName)
                ->setAssessment($assessment);

            try {
                $response = $client->createAssessment($request);

                // Check if the token is valid.
                if ($response->getTokenProperties()->getValid() == false) {
                    return [
                        'success' => false,
                        'error' => 'The CreateAssessment() call failed because the token was invalid for the following reason: ' .
                            InvalidReason::name($response->getTokenProperties()->getInvalidReason())
                    ];
                }

                // Check if the expected action was executed.
                if ($response->getTokenProperties()->getAction() != $action) {
                    return [
                        'success' => false,
                        'error' => 'The action attribute in your reCAPTCHA tag does not match the action you are expecting to score'
                    ];
                }

                // Get the risk score (0.0 - 1.0, where 1.0 is very likely a good interaction)
                $score = $response->getRiskAnalysis()->getScore();
                return [
                    'success' => true,
                    'score' => $score
                ];
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'error' => 'CreateAssessment() call failed with the following error: ' . $e->getMessage()
                ];
            }
        }

        // Replace with your actual values
        $recaptchaKey = '6LcGL4orAAAAAGKDL7ird0LkgIiv570EWn4z2g9O';
        $projectId = 'bezlo-studio';
        $recaptchaAction = 'contact_form'; // This should match the action set on the client

        $recaptchaResult = verify_recaptcha_enterprise($recaptchaKey, $recaptcha_response, $projectId, $recaptchaAction);

        if (!$recaptchaResult['success']) {
            $errors[] = "reCAPTCHA verification failed. " . ($recaptchaResult['error'] ?? '');
        } elseif ($recaptchaResult['score'] < 0.5) {
            $errors[] = "reCAPTCHA score too low. Please try again.";
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

    session_start();
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
