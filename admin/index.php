
<?php
session_start();
require_once "../includes/config.php";
include "template/head.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validate input
    if (empty($username) || empty($password)) {
        die("Username and password are required.");
    }

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

     $stmt = $db->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            echo "Login successful. Welcome, " . htmlspecialchars($user['username']) . "!";
            $_SESSION['admin_user'] = $user['username'];
            $_SESSION['admin_id'] = $user['id']; // Store user ID in session
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "User not found.";
    }


    $stmt->close();
}

$db->close();

?>
<!DOCTYPE html>
<html lang="en">


<body class="h-100">
    <div class="authincation h-100">
        <div class="container mt-5 h-100">
            <div class="row justify-content-center h-100 align-items-center">
                <div class="col-md-6">
                    <div class="authincation-content">
                        <div class="row no-gutters">
                            <div class="col-xl-12">
                                <div class="auth-form">
                                    <center>
                                        <img src="./assets/images/QAI_NEW_LOG.png" width="220" style="align-items: center;" height="150" alt="logo">
                                    </center>
                                    <form action="index.php" method="post">
                                        <div class="form-group">
                                            <label class="mb-1"><strong>User Name</strong></label>
                                            <input type="username" class="form-control" value="" name="username" required>
                                        </div>
                                        <div class="form-group position-relative">
                                            <label class="mb-1"><strong>Password</strong></label>
                                            <input type="password" id="dz-password" name="password" class="form-control" value="" required>
                                            <span class="show-pass eye">

                                                <i class="fa fa-eye-slash"></i>
                                                <i class="fa fa-eye"></i>

                                            </span>
                                        </div>
                                        <div class="row d-flex justify-content-between mt-4 mb-2">
                                            <div class="form-group">
                                                <!-- <div class="form-check custom-checkbox ms-1">
													<input type="checkbox" class="form-check-input" id="basic_checkbox_1">
													<label class="form-check-label" for="basic_checkbox_1">Remember my preference</label>
												</div> -->
                                            </div>
                                            <!-- <div class="form-group">
                                                <a href="page-forgot-password.php.html">Forgot Password?</a>
                                            </div> -->
                                        </div>
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-primary btn-block">Sign Me In</button>
                                        </div>
                                    </form>
                                    <!-- <div class="new-account mt-3">
                                        <p>Don't have an account? <a class="text-primary" href="page-register.php.html">Sign up</a></p>
                                    </div> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!--**********************************
        Scripts
    ***********************************-->
    <!-- Required vendors -->
    <script>
        var enableSupportButton = '1'
    </script>
    <script>
        var asset_url = 'assets/'
    </script>

    <script src="assets/vendor/global/global.min.js" type="text/javascript"></script>
    <script src="assets/vendor/bootstrap-select/dist/js/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="assets/js/custom.min.js" type="text/javascript"></script>
    <script src="assets/js/deznav-init.js" type="text/javascript"></script>
</body>

</html>