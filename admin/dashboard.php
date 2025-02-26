<?php
require_once "../db/config.php";
session_start();

// Redirect if not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ./index.php");
    exit;
}

// Function to get user statistics
function getUserStatistics($conn) {
    $stats = ["admin" => 0, "user" => 0, "temp-user" => 0, "hacker" => 0, "total" => 0];

    $sql = "SELECT user_type, COUNT(*) as count FROM users GROUP BY user_type";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $type = $row["user_type"];
            $stats[$type] = isset($stats[$type]) ? $row["count"] : 0;
        }
    }

    // Count detected hackers from login_attempts table
    $hackerQuery = "SELECT COUNT(*) AS count FROM login_attempts WHERE status = 'hacker_attempt'";
    $hackerResult = mysqli_query($conn, $hackerQuery);
    if ($hackerRow = mysqli_fetch_assoc($hackerResult)) {
        $stats["hacker"] = $hackerRow["count"];
    }

    // Total users
    $stats["total"] = $stats["admin"] + $stats["user"] + $stats["temp-user"] + $stats["hacker"];
    return $stats;
}

// Fetch user statistics
$userStats = getUserStatistics($conn);

// Fetch user accounts
$userAccounts = [];
$sql = "SELECT username, user_type, created_at FROM users ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $userAccounts[] = $row;
    }
}

// Fetch recent login attempts (including hacker attempts)
$loginAttempts = [];
$sql = "SELECT username_attempted, ip_address, status, attempt_time FROM login_attempts ORDER BY attempt_time DESC";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $loginAttempts[] = $row;
    }
}


// Fetch recent logins (includes hackers)
$recentLogins = [];
$sql = "SELECT u.username, u.user_type, l.login_time FROM login_logs l JOIN users u ON l.user_id = u.id 
        UNION 
        SELECT username_attempted AS username, 'Hacker' AS user_type, attempt_time AS login_time FROM login_attempts 
        WHERE status = 'hacker_attempt'
        ORDER BY login_time DESC";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $recentLogins[] = $row;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <!--Datatables-->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css"></script>
    <script src="https://cdn.datatables.net/2.2.1/css/dataTables.bootstrap5.css"></script>

    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.2.1/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.2.1/js/dataTables.bootstrap5.js"></script>

    <style>
        .flex-container {
            display: flex;
            flex-direction: wrap;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 15px;
        }

        .flex-container > div {
            background-color: #f1f1f1;
            width: 400px;
            margin-left: 2rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">Home</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <!--
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Link</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Dropdown
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">Action</a></li>
                            <li><a class="dropdown-item" href="#">Another action</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#">Something else here</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link disabled" aria-disabled="true">Disabled</a>
                    </li>
                    -->
                </ul>
                <form class="d-flex" role="search">
                    <a href="../logout.php" class="btn btn-danger">Logout</a>
                </form>
            </div>
        </div>
    </nav>
    <h1 style="margin-left:20px">Hi, <b><?php echo htmlspecialchars($_SESSION["username"]); ?></b>. Welcome to the dashboard.</h1>
    
<div id="dashboardContent">
    <!--Start Dashboard-->
    <div class="flex-container">
        <!-- Card 1: Total Admin Users -->
        <div class="card text-bg-success mb-3">
            <div class="card-body">
                <h5 class="card-title">Admin Users</h5>
                <h1 id="totalAdmins"><?php echo $userStats['admin']; ?></h1>
            </div>
        </div>

        <!-- Card 2: Total Users -->
        <div class="card text-bg-primary mb-3">
            <div class="card-body">
                <h5 class="card-title">Users</h5>
                <h1 id="totalUsers"><?php echo $userStats['user']; ?></h1>
            </div>
        </div>

        <!-- Card 3: Temp Users -->
        <div class="card text-bg-danger text-white mb-3">
            <div class="card-body">
                <h5 class="card-title">Temp Users</h5>
                <h1 id="totalTempUsers"><?php echo $userStats['temp-user']; ?></h1>
            </div>
        </div>

        <!-- Card 4: Total User Accounts -->
        <div class="card text-bg-warning text-white mb-3">
            <div class="card-body">
                <h5 class="card-title">Total Users</h5>
                <h1 id="totalAllUsers"><?php echo $userStats['total']; ?></h1>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <h3>User Accounts</h3>
                        <table id="userAccounts" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Registration Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($userAccounts as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['user_type']); ?></td>
                                    <td><?php echo date("Y-m-d H:i:s", strtotime($user['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div> 
            </div>
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <h3>Recent Logins</h3>
                        <table id="recentLogin" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Login Timestamp</th>
                                    <th>Time Elapsed</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentLogins as $login): ?>
                                <tr data-login-time="<?php echo htmlspecialchars($login['login_time']); ?>">
                                    <td><?php echo htmlspecialchars($login['username']); ?></td>
                                    <td><?php echo htmlspecialchars($login['user_type']); ?></td>
                                    <td><?php echo date("Y-m-d H:i:s", strtotime($login['login_time'])); ?></td>
                                    <td class="time-elapsed"></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
    <div class="card">
        <div class="card-body">
            <h3>Login Attempts (Including Hackers)</h3>
            <table id="loginAttempts" class="table table-bordered">
                <thead>
                    <tr>
                        <th>Username Attempted</th>
                        <th>IP Address</th>
                        <th>Status</th>
                        <th>Login Timestamp</th>
                        <th>Time Elapsed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($loginAttempts as $attempt): ?>
                    <tr data-attempt-time="<?php echo htmlspecialchars($attempt['attempt_time']); ?>">
                        <td><?php echo htmlspecialchars($attempt['username_attempted']); ?></td>
                        <td><?php echo htmlspecialchars($attempt['ip_address']); ?></td>
                        <td><?php echo htmlspecialchars($attempt['status']); ?></td>
                        <td><?php echo date("Y-m-d H:i:s", strtotime($attempt['attempt_time'])); ?></td>
                        <td class="time-elapsed"></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

        <button class="btn btn-primary" onclick="printToPDF()">Print to PDF</button>
    </div>
</div>
<script>
    function printToPDF() {
        const element = document.getElementById("dashboardContent"); // Capture only the dashboard content
        html2canvas(element, { scale: 2 }).then((canvas) => {
            const imgData = canvas.toDataURL("image/png");
            const pdf = new jspdf.jsPDF("landscape", "mm", "a4"); // Set PDF to landscape

            const imgWidth = 287; // A4 width in landscape (210mm x 297mm)
            const imgHeight = (canvas.height * imgWidth) / canvas.width;

            pdf.addImage(imgData, "PNG", 5, 5, imgWidth - 10, imgHeight);
            pdf.save("dashboard.pdf");
        });
    }



    function timeElapsed(timestamp) {
    const currentTime = Date.now() / 1000; // current time in seconds
    const timeDiff = currentTime - new Date(timestamp).getTime() / 1000; // time difference in seconds

    const intervals = {
        year: 31536000,
        month: 2592000,
        week: 604800,
        day: 86400,
        hour: 3600,
        minute: 60,
        second: 1
    };

    for (const [unit, seconds] of Object.entries(intervals)) {
        const elapsed = timeDiff / seconds;
        if (elapsed >= 1) {
            const rounded = Math.floor(elapsed);
            return `${rounded} ${unit}${rounded > 1 ? 's' : ''} ago`;
        }
    }

    return 'Just now';
}

// Apply the time elapsed to the table rows
window.onload = function() {
    // For recent logins
    const recentLoginRows = document.querySelectorAll('#recentLogin tbody tr');
    recentLoginRows.forEach(row => {
        const loginTime = row.getAttribute('data-login-time');
        const timeElapsedStr = timeElapsed(loginTime);
        row.querySelector('.time-elapsed').textContent = timeElapsedStr;
    });

    // For login attempts
    const loginAttemptRows = document.querySelectorAll('#loginAttempts tbody tr');
    loginAttemptRows.forEach(row => {
        const attemptTime = row.getAttribute('data-attempt-time');
        const timeElapsedStr = timeElapsed(attemptTime);
        row.querySelector('.time-elapsed').textContent = timeElapsedStr;
    });

    // Initialize DataTables only once when the page is loaded
    if (!$.fn.DataTable.isDataTable('#recentLogin')) {
        new DataTable('#recentLogin');
    }

    if (!$.fn.DataTable.isDataTable('#loginAttempts')) {
        new DataTable('#loginAttempts');
    }

    if (!$.fn.DataTable.isDataTable('#userAccounts')) {
        new DataTable('#userAccounts');
    }
};
</script>
</body>
</html>