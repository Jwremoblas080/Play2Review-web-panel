<!DOCTYPE html>
<html lang="en">
<?php
    require_once('../configurations/configurations.php');
    if(isset($_POST['adminlogin'])) {
        $username = $_POST['username'];
        $password = md5($_POST['password']);

        // First check admin table
        $checkadmin = mysqli_query($con, "SELECT * FROM admin WHERE username = '$username' AND password = '$password'");
        if (mysqli_num_rows($checkadmin) > 0) {
            // Admin login successful
            while ($adminrecord = mysqli_fetch_assoc($checkadmin)) {
                $_SESSION['logged'] = 'True';
                $_SESSION['priviledges'] = 'admin';
                $_SESSION['user_id'] = $adminrecord['admin_ID'];
                $_SESSION['firstName'] = $adminrecord['firstName'];
                $_SESSION['middleName'] = $adminrecord['middleName'];
                $_SESSION['lastName'] = $adminrecord['lastName'];
                $_SESSION['fullname'] = $_SESSION['firstName'].' '.$_SESSION['middleName'].'. '.$_SESSION['lastName'];      
                $_SESSION['username'] = $adminrecord['username'];
                $_SESSION['password'] = $adminrecord['password'];            
                $_SESSION['Profile_image'] = $adminrecord['profileImage'];          
            }
            
            $name = $_SESSION['fullname'];
            $email = $_SESSION['username'];
            $new_user_id = $_SESSION['user_id'];

            unset($_POST['adminlogin']);
            header('Location: dashboard.php');
            exit();
        } 
        // If not found in admin table, check educators table
        else {
            $checkeducator = mysqli_query($con, "SELECT * FROM educators WHERE email = '$username' AND password = '$password' AND status = 'active'");
            if (mysqli_num_rows($checkeducator) > 0) {
                // Educator login successful
                while ($educatorrecord = mysqli_fetch_assoc($checkeducator)) {
                    $_SESSION['logged'] = 'True';
                    $_SESSION['priviledges'] = 'educator';
                    $_SESSION['user_id'] = $educatorrecord['id'];
                    $_SESSION['firstName'] = $educatorrecord['teacher_name'];
                    $_SESSION['fullname'] = $educatorrecord['teacher_name'];      
                    $_SESSION['username'] = $educatorrecord['email'];
                    $_SESSION['email'] = $educatorrecord['email'];
                    $_SESSION['handled_subject'] = $educatorrecord['handled_subject'];
                    $_SESSION['status'] = $educatorrecord['status'];
                    $_SESSION['Profile_image'] = $educatorrecord['profileImage'];    
                }
                
                unset($_POST['adminlogin']);
                header('Location: educ_dashboard.php'); // Redirect to educator dashboard
                exit();
            }
            // If not found in either table
            else {
                $_SESSION['admin_loginresult'] = 'Invalid username/email or password';
            }
        }
    }

    $SYS_logo = 'logo123.png';
    $SYS_background = 'bgtest.png';
    $SYS_name = 'PLAY2REVIEW';
?>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin & Educator Login</title>
    <link rel="stylesheet" href="../style/css/bootstrap.min.css">
    <script defer src="../style/js/bootstrap.bundle.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/myStyle.css">
    <link rel="icon" type="image/png" href="images/<?php echo $SYS_logo; ?>">
    <style>
   :root {
    --primary-color: #0A5F38;
    --secondary-color: #FFA726;
    --accent-color: #4CAF50;
    --dark-color: #1B5E20;
    --light-color: #E8F5E8;
    --success-color: #2E7D32;
    --warning-color: #F57C00;
    --danger-color: #C62828;
    --info-color: #0277BD;
    --background-color: #F8FDF8;
    --text-dark: #1A1A1A;
    --text-light: #666666;
    --border-color: #C8E6C9;
}
    body.login-page {
        min-height: 100vh;
background: linear-gradient(rgba(10, 95, 56, 0.85), rgba(21, 115, 71, 0.85)),
                    url(images/<?php echo $SYS_background; ?>) no-repeat center center;
        background-size: cover;
        display: flex;
        align-items: center;
        padding: 20px;
    }
    
    .login-container {
        max-width: 500px;
        width: 100%;
        margin: 0 auto;
        animation: fadeIn 0.5s ease-in-out;
    }
    
    .login-card {
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        overflow: hidden;
        border: none;
    }
    
    .login-header {
        background: var(--primary-color);
        color: white;
        padding: 25px;
        text-align: center;
        position: relative;
    }
    
    .login-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--secondary-color);
    }
    
    .login-body {
        padding: 30px;
        background: white;
    }
    
    .system-logo {
        width: 32vh;
        height: 15vh;
        object-fit: contain;
        margin-bottom: 15px;
    }
    
    .form-control {
        border-radius: 8px;
        padding: 12px 15px;
        border: 1px solid #ddd;
        transition: all 0.3s;
    }
    
    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(63, 81, 181, 0.25);
    }
    
    .input-group-text {
        background-color: var(--primary-color);
        color: white;
        border: none;
        border-radius: 8px 0 0 8px !important;
    }
    
    .btn-login {
        background: var(--primary-color);
        border: none;
        padding: 12px;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: all 0.3s;
        border-radius: 8px;
    }
    
    .btn-login:hover {
        background: #344395;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(63, 81, 181, 0.3);
    }
    
    .error-message {
        background: #ffebee;
        color: #c62828;
        padding: 10px;
        border-radius: 5px;
        margin-bottom: 20px;
        border-left: 4px solid #c62828;
    }
    
    .password-toggle {
        cursor: pointer;
        background: #f8f9fa;
        border: 1px solid #ddd;
        border-left: none;
        border-radius: 0 8px 8px 0;
    }
    
    .password-toggle:hover {
        background: #e9ecef;
    }
    
    .login-info {
        background: #e3f2fd;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
        border-left: 4px solid var(--primary-color);
    }
    
    .login-info h6 {
        color: var(--primary-color);
        margin-bottom: 10px;
    }
    
    .login-info ul {
        margin-bottom: 0;
        padding-left: 20px;
    }
    
    .login-info li {
        margin-bottom: 5px;
        font-size: 0.9rem;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Responsive adjustments */
    @media (max-width: 576px) {
        .login-body {
            padding: 20px;
        }
        
        .login-header {
            padding: 20px;
        }
    }
    </style>
</head>
<body class="login-page">
    <div class="login-container">
        <div class="card login-card">
            <div class="card-header login-header">
                <img src="images/<?php echo $SYS_logo; ?>" alt="System Logo" class="system-logo">
                <h5 class="mb-1">Play2Review Portal</h5>
                <p class="mb-0">Administrator & Educator Access</p>
            </div>
            <div class="card-body login-body">
                <?php if(isset($_SESSION['admin_loginresult']) && $_SESSION['admin_loginresult'] != '') { ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo $_SESSION['admin_loginresult']; $_SESSION['admin_loginresult'] = ''; ?>
                    </div>
                <?php } ?>
                
                <div class="login-info">
                    <h6><i class="fas fa-info-circle me-2"></i>Login Instructions</h6>
                    <ul>
                        <li><strong>Admins:</strong> Use your admin username</li>
                        <li><strong>Educators:</strong> Use your registered email address</li>
                        <li>Ensure your educator account is <strong>active</strong></li>
                    </ul>
                </div>
                
                <form action="" method="post" class="needs-validation" novalidate>
                    <div class="mb-4">
                        <label for="username" class="form-label">Username / Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Enter username or email" required>
                        </div>
                        <small class="form-text text-muted">Use username for admin, email for educators</small>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                            <span class="input-group-text password-toggle" id="togglePassword" style="color:black;">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-primary btn-login" name="adminlogin">
                            <i class="fas fa-sign-in-alt me-2"></i> Login to Portal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Password toggle functionality
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const icon = this.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });
    
    // Form validation
    (function() {
        'use strict';
        const forms = document.querySelectorAll('.needs-validation');
        
        Array.prototype.slice.call(forms)
            .forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    
                    form.classList.add('was-validated');
                }, false);
            });
    })();
    </script>
</body>
</html>