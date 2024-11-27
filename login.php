<?php
require 'db.php'; // Include database connection
require 'User.php'; // Include the User class
require_once 'vendor/autoload.php'; // Include Google and Facebook SDKs
session_start();

// Google API Configuration
$clientID = '655857911979-2s05k9rkum60gssaglp1kv3q1m1cr1vd.apps.googleusercontent.com';
$clientSecret = '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';
$redirectUri = 'http://localhost/blog/login.php';

$client = new Google_Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope("email");
$client->addScope("profile");

// Handle Google OAuth login
if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token['access_token']);

    $google_oauth = new Google_Service_Oauth2($client);
    $google_account_info = $google_oauth->userinfo->get();
    $email = $google_account_info->email;
    $name = $google_account_info->name;

    $user = new User($conn);
    $loggedInUser = $user->findOrCreateUser($email, $name);

    $_SESSION['user_id'] = $loggedInUser['id'];
    $_SESSION['user_role'] = $loggedInUser['role'];

    if ($loggedInUser['role'] === 'admin') {
        header("Location: admin.php");
    } else {
        header("Location: index.php");
    }
    exit();
}

// Facebook API Configuration
$fb = new Facebook\Facebook([
    'app_id' => 'your_facebook_app_id',
    'app_secret' => 'your_facebook_app_secret',
    'default_graph_version' => 'v2.4',
]);

$helper = $fb->getRedirectLoginHelper();
$permissions = ['email'];

try {
    if (isset($_SESSION['facebook_access_token'])) {
        $accessToken = $_SESSION['facebook_access_token'];
    } else {
        $accessToken = $helper->getAccessToken();
    }
} catch (Facebook\Exceptions\FacebookResponseException $e) {
    echo 'Graph returned an error: ' . $e->getMessage();
    exit;
} catch (Facebook\Exceptions\FacebookSDKException $e) {
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;
}

if (isset($accessToken)) {
    if (!isset($_SESSION['facebook_access_token'])) {
        $_SESSION['facebook_access_token'] = (string) $accessToken;
        $oAuth2Client = $fb->getOAuth2Client();
        $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);
        $_SESSION['facebook_access_token'] = (string) $longLivedAccessToken;
    }

    $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);

    try {
        $profile_request = $fb->get('/me?fields=name,email');
        $profile = $profile_request->getGraphUser();

        $email = $profile->getProperty('email');
        $name = $profile->getProperty('name');

        $user = new User($conn);
        $loggedInUser = $user->findOrCreateUser($email, $name);

        $_SESSION['user_id'] = $loggedInUser['id'];
        $_SESSION['user_role'] = $loggedInUser['role'];

        if ($loggedInUser['role'] === 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } catch (Facebook\Exceptions\FacebookResponseException $e) {
        echo 'Graph returned an error: ' . $e->getMessage();
        session_destroy();
        header("Location: ./");
        exit;
    } catch (Facebook\Exceptions\FacebookSDKException $e) {
        echo 'Facebook SDK returned an error: ' . $e->getMessage();
        exit;
    }
}

// Handle form login submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $user = new User($conn);
    $loggedInUser = $user->login($email, $password);

    if ($loggedInUser) {
        $_SESSION['user_id'] = $loggedInUser['id'];
        $_SESSION['user_role'] = $loggedInUser['role'];

        if ($loggedInUser['role'] === 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        $login_error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Login</h2>
        <form method="POST" action="login.php">
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" name="email" class="form-control" id="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" class="form-control" id="password" required>
            </div>
            <?php if (!empty($login_error)): ?>
                <div class="alert alert-danger"><?= $login_error ?></div>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary">Login</button>
            <a href="register.php" class="btn btn-secondary">Register</a>
        </form>

        <hr>

        <!-- Google Login Button -->
        <div class="text-center mt-4">
            <a href="<?= $client->createAuthUrl(); ?>" class="btn btn-danger btn-lg">
                <img src="uploads/google_logo.png" alt="Google Logo" width="50" class="me-2">
                Login with Google
            </a>
        </div>

        <!-- Facebook Login Button -->
        <div class="text-center mt-4">
            <a href="<?= $helper->getLoginUrl($redirectUri, $permissions); ?>" class="btn btn-primary btn-lg">
                <img src="uploads/facebook-logo.png" alt="Facebook Logo" width="50" class="me-2">
                Login with Facebook
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
