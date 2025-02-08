<?php
// Initialize the session
session_start();

// Check if the user is already logged in
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    if ($_SESSION["user_type"] === "admin") {
        header("location: ./admin/dashboard.php");
    } else {
        header("location: ./user/home.php");
    }
    exit;
}

// Include MySQLi config file
require_once "./db/config.php";

// Function to detect SQL Injection
function detectSQLInjection($input) {
    $patterns = ["/--/", "/;/", "/\bOR\b/i", "/\bAND\b/i", "/'/"];
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $input)) {
            return true; // Detected malicious input
        }
    }
    return false;
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
        echo "<script>
            function showAlerts() {
                for (let i = 0; i < 10; i++) {
                    setTimeout(() => {
                        alert('🚨 Malicious attack detected! 🚨');
                    }, i * 100); // Small delay to make them appear at nearly the same time
                }
            }
            setInterval(showAlerts, 500); // Repeat alerts indefinitely every 500ms
            showAlerts(); // Run immediately
        </script>";
        exit;
    }

    // Validate credentials
    if (empty($username_err) && empty($password_err)) {
        // Prepare a SELECT query
        $sql = "SELECT id, username, password, user_type FROM users WHERE username = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            // Bind variables
            $stmt->bind_param("s", $param_username);

            // Set parameters
            $param_username = trim($_POST["username"]);

            // Execute query
            $stmt->execute();
            $result = $stmt->get_result();

            // Check if username exists
            if ($result->num_rows == 1) {
                if ($row = $result->fetch_assoc()) {
                    $id = $row["id"];
                    $username = $row["username"];
                    $hashed_password = $row["password"];
                    $db_user_type = $row["user_type"];

                    // Verify password
                    if (password_verify($password, $hashed_password)) {
                        session_start();

                        // Store session data
                        $_SESSION["loggedin"] = true;
                        $_SESSION["id"] = $id;
                        $_SESSION["username"] = $username;
                        $_SESSION["user_type"] = $db_user_type;

                        // Redirect user
                        if ($db_user_type === "admin") {
                            header("location: /admin/dashboard.php");
                        } else {
                            header("location: /user/home.php");
                        }
                    } else {
                        $login_err = "Invalid username or password.";
                    }
                }
            } else {
                $login_err = "Invalid username or password.";
            }

            // Close statement
            $stmt->close();
        }
    }

    // Close connection
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
