<?php
session_start();
require_once "./db/config.php";

// Function to detect SQL Injection
function detectSQLInjection($input) {
    $patterns = ["/--/", "/;/", "/\bOR\b/i", "/\bAND\b/i", "/'/", "/\bUNION\b/i", "/\bSELECT\b/i"];
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $input)) {
            return true;
        }
    }
}

// Get IP address
$ip_address = $_SERVER['REMOTE_ADDR'];

// Check if IP is blocked
$sql = "SELECT COUNT(*) AS attempt_count FROM login_attempts WHERE ip_address = ? AND status = 'failed' AND attempt_time >= NOW() - INTERVAL 10 MINUTE";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $ip_address);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if ($row["attempt_count"] >= 5) {
    echo "⚠️ Too many failed login attempts! Try again later.";
    exit;
}

// Define variables
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Process login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check if username is empty
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter username.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Check if password is empty
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Detect SQL Injection attempt
    if (detectSQLInjection($username) || detectSQLInjection($password)) {
        $status = "hacker_attempt";
        $sql = "INSERT INTO login_attempts (username_attempted, ip_address, status) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $ip_address, $status);
        $stmt->execute();
        $stmt->close();

        echo "<script>alert('🚨 SQL Injection Attempt Detected! 🚨');</script>";
        exit;
    }

    // After the password verification and session handling
if (password_verify($password, $row["password"])) {
    // Check if the login is from a hacker
    if ($row["username"] === "hacker") {  // Example: You can modify this to match hacker conditions.
        // Log this as a successful hacker login
        $status = "hacker_login";
        $sql = "INSERT INTO login_attempts (username_attempted, ip_address, status) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $ip_address, $status);
        $stmt->execute();
        $stmt->close();

        // Trigger hacker login modal notification
        echo "<script>window.location.href = 'dashboard.php?alert=hacker';</script>"; // Redirect to dashboard with hacker alert
    }

    session_start();

    // Store session data
    $_SESSION["loggedin"] = true;
    $_SESSION["id"] = $row["id"];
    $_SESSION["username"] = $row["username"];
    $_SESSION["user_type"] = $row["user_type"];
    
    // Redirect user
    header("location: " . ($row["user_type"] === "admin" ? "./admin/dashboard.php" : "./user/home.php"));
    exit;
}


    // Validate credentials
    if (empty($username_err) && empty($password_err)) {
        // Prepare SQL query
        $sql = "SELECT id, username, password, user_type FROM users WHERE username = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                if (password_verify($password, $row["password"])) {
                    session_start();

                    // Store session data
                    $_SESSION["loggedin"] = true;
                    $_SESSION["id"] = $row["id"];
                    $_SESSION["username"] = $row["username"];
                    $_SESSION["user_type"] = $row["user_type"];

                    // Update last login time
                    $update_sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
                    if ($update_stmt = $conn->prepare($update_sql)) {
                        $update_stmt->bind_param("i", $row["id"]);
                        $update_stmt->execute();
                        $update_stmt->close();
                    }

                    // Log login attempt
                    $log_sql = "INSERT INTO login_logs (user_id, login_time) VALUES (?, NOW())";
                    if ($log_stmt = $conn->prepare($log_sql)) {
                        $log_stmt->bind_param("i", $row["id"]);
                        $log_stmt->execute();
                        $log_stmt->close();
                    }

                    // Redirect user
                    header("location: " . ($row["user_type"] === "admin" ? "./admin/dashboard.php" : "./user/home.php"));
                    exit;
                } else {
                    $login_err = "Invalid username or password.";
                    $status = "failed";
                }
            } else {
                $login_err = "Invalid username or password.";
                $status = "failed";
            }

            // Log failed login
            $sql = "INSERT INTO login_attempts (username_attempted, ip_address, status) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $username, $ip_address, $status);
            $stmt->execute();
            $stmt->close();

            $stmt->close();
        }
    }

    $conn->close();
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font: 14px sans-serif; }
        .wrapper { width: 360px; padding: 20px; margin: 0 auto; margin-top: 50px; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <!-- Login Form -->
                <div id="loginForm" class="mb-5">
                    <h2 class="text-center mb-4">Login</h2>
                    <?php
                    if (!empty($login_err)) {
                        echo '<div class="alert alert-danger">' . $login_err . '</div>';
                    }
                    ?>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="mb-3">
                            <label for="loginUsername" class="form-label">Username</label>
                            <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" id="loginUsername" placeholder="Enter username" value="<?php echo $username; ?>">
                            <span class="invalid-feedback"><?php echo $username_err; ?></span>
                        </div>
                        <div class="mb-3">
                            <label for="loginPassword" class="form-label">Password</label>
                            <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" id="loginPassword" placeholder="Enter password">
                            <span class="invalid-feedback"><?php echo $password_err; ?></span>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                        <p class="text-center mt-3">Don't have an account? <a href="register.php">Sign up</a></p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>