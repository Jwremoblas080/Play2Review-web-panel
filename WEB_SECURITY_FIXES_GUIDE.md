# Play2Review - Critical Security Fixes Guide

**URGENT**: These fixes must be implemented before production deployment

---

## 🚨 Critical Security Issues Summary

| Issue | Severity | Files Affected | Est. Time |
|-------|----------|----------------|-----------|
| No API Authentication | 🔴 CRITICAL | All API endpoints | 8-10 hours |
| SQL Injection | 🔴 CRITICAL | 20+ files | 10-12 hours |
| MD5 Password Hashing | 🔴 CRITICAL | admin/index.php | 4-6 hours |
| Mixed DB Drivers | 🔴 CRITICAL | configurations.php | 6-8 hours |
| No CSRF Protection | 🟡 HIGH | All forms | 4-6 hours |
| Weak Session Config | 🟡 HIGH | configurations.php | 2-3 hours |

**Total Estimated Time**: 34-45 hours (1 week full-time)

---

## Fix #1: Implement API Authentication (CRITICAL)

### Step 1: Install JWT Library

```bash
cd play2review
composer require firebase/php-jwt
```

### Step 2: Create Auth Helper

Create: `play2review/includes/auth.php`

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthHelper
{
    private const SECRET_KEY = 'YOUR_SECRET_KEY_HERE_CHANGE_THIS';  // Move to .env
    private const ALGORITHM = 'HS256';
    private const TOKEN_EXPIRY = 3600; // 1 hour
    
    /**
     * Generate JWT token for user
     */
    public static function generateToken($user_id, $username)
    {
        $issued_at = time();
        $expiration = $issued_at + self::TOKEN_EXPIRY;
        
        $payload = [
            'iat' => $issued_at,
            'exp' => $expiration,
            'user_id' => $user_id,
            'username' => $username
        ];
        
        return JWT::encode($payload, self::SECRET_KEY, self::ALGORITHM);
    }
    
    /**
     * Validate JWT token and return user_id
     */
    public static function validateToken($token)
    {
        try {
            $decoded = JWT::decode($token, new Key(self::SECRET_KEY, self::ALGORITHM));
            return $decoded->user_id;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Require authentication for API endpoint
     */
    public static function requireAuth()
    {
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? '';
        
        // Remove "Bearer " prefix if present
        $token = str_replace('Bearer ', '', $token);
        
        $user_id = self::validateToken($token);
        
        if (!$user_id) {
            http_response_code(401);
            die(json_encode([
                'success' => false,
                'message' => 'Unauthorized: Invalid or expired token'
            ]));
        }
        
        return $user_id;
    }
}
?>
```

### Step 3: Update signin.php to Return Token

```php
<?php
require_once('configurations/configurations.php');
require_once('includes/auth.php');

header('Content-Type: application/json');

// ... existing validation code ...

if (password_verify($password, $user['password'])) {
    // Generate JWT token
    $token = AuthHelper::generateToken($user['id'], $user['username']);
    
    // Log activity
    // ... existing logging code ...
    
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'token' => $token,  // NEW: Return token
        'playerName' => $user['player_name'],
        'gradeLevel' => $user['student_id'],
        // ... other user data ...
    ]);
}
?>
```

### Step 4: Protect API Endpoints

Update ALL API endpoints (example: `get_quiz_questions.php`):

```php
<?php
require_once('configurations/configurations.php');
require_once('includes/auth.php');

header("Content-Type: application/json; charset=UTF-8");

// NEW: Require authentication
$user_id = AuthHelper::requireAuth();

// Now proceed with normal logic
$subject_name = $_POST['subject_name'] ?? '';
// ... rest of code ...
?>
```

### Step 5: Update Unity Client

```csharp
// Store token after login
PlayerPrefs.SetString("AuthToken", loginResponse.token);

// Send token with requests
UnityWebRequest www = UnityWebRequest.Post(url, form);
www.SetRequestHeader("Authorization", "Bearer " + PlayerPrefs.GetString("AuthToken"));
yield return www.SendWebRequest();
```

---

## Fix #2: Prevent SQL Injection (CRITICAL)

### Step 1: Standardize on PDO

Update `configurations/configurations.php`:

```php
<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'play2review_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Create SINGLE PDO connection
try {
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => true
        ]
    );
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Start session securely
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);  // Requires HTTPS
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

ob_start();
?>
```

### Step 2: Convert Queries to Prepared Statements

**BEFORE (VULNERABLE)**:
```php
$username = $_POST['username'];
$query = "SELECT * FROM users WHERE username = '$username'";
$result = mysqli_query($con, $query);
```

**AFTER (SECURE)**:
```php
$username = $_POST['username'];
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();
```

### Step 3: Fix admin/index.php Login

**BEFORE (VULNERABLE)**:
```php
$username = $_POST['username'];
$password = md5($_POST['password']);
$checkadmin = mysqli_query($con, "SELECT * FROM admin WHERE username = '$username' AND password = '$password'");
```

**AFTER (SECURE)**:
```php
$username = $_POST['username'];
$password = $_POST['password'];

// Check admin table
$stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
$stmt->execute([$username]);
$admin = $stmt->fetch();

if ($admin && password_verify($password, $admin['password'])) {
    // Login successful
    $_SESSION['logged'] = 'True';
    $_SESSION['priviledges'] = 'admin';
    // ... set other session variables ...
    header('Location: dashboard.php');
    exit();
}

// Check educators table
$stmt = $conn->prepare("SELECT * FROM educators WHERE email = ? AND status = 'active'");
$stmt->execute([$username]);
$educator = $stmt->fetch();

if ($educator && password_verify($password, $educator['password'])) {
    // Educator login successful
    $_SESSION['logged'] = 'True';
    $_SESSION['priviledges'] = 'educator';
    // ... set other session variables ...
    header('Location: educ_dashboard.php');
    exit();
}

// Login failed
$_SESSION['admin_loginresult'] = 'Invalid username/email or password';
```

---

## Fix #3: Upgrade Password Hashing (CRITICAL)

### Step 1: Create Password Migration Script

Create: `play2review/admin/migrate_passwords.php`

```php
<?php
require_once('../configurations/configurations.php');

// Check admin privileges
if(!isset($_SESSION['priviledges']) || $_SESSION['priviledges'] != 'admin') {
    die('Unauthorized');
}

echo "<h2>Password Migration Tool</h2>";
echo "<p>This will convert all MD5 passwords to bcrypt.</p>";

if (isset($_POST['migrate'])) {
    // Migrate admin passwords
    $stmt = $conn->query("SELECT admin_ID, password FROM admin");
    $admins = $stmt->fetchAll();
    
    foreach ($admins as $admin) {
        // Check if already bcrypt (starts with $2y$)
        if (substr($admin['password'], 0, 4) !== '$2y$') {
            // This is MD5, we can't convert it directly
            // Set a temporary password and force reset
            $temp_password = bin2hex(random_bytes(16));
            $new_hash = password_hash($temp_password, PASSWORD_BCRYPT, ['cost' => 12]);
            
            $update = $conn->prepare("UPDATE admin SET password = ?, must_reset_password = 1 WHERE admin_ID = ?");
            $update->execute([$new_hash, $admin['admin_ID']]);
            
            echo "Admin ID {$admin['admin_ID']}: Temporary password set: $temp_password<br>";
        }
    }
    
    // Migrate educator passwords
    $stmt = $conn->query("SELECT id, password FROM educators");
    $educators = $stmt->fetchAll();
    
    foreach ($educators as $educator) {
        if (substr($educator['password'], 0, 4) !== '$2y$') {
            $temp_password = bin2hex(random_bytes(16));
            $new_hash = password_hash($temp_password, PASSWORD_BCRYPT, ['cost' => 12]);
            
            $update = $conn->prepare("UPDATE educators SET password = ?, must_reset_password = 1 WHERE id = ?");
            $update->execute([$new_hash, $educator['id']]);
            
            echo "Educator ID {$educator['id']}: Temporary password set: $temp_password<br>";
        }
    }
    
    echo "<p><strong>Migration complete! Save the temporary passwords and send them to users.</strong></p>";
} else {
    ?>
    <form method="POST">
        <button type="submit" name="migrate" onclick="return confirm('Are you sure? This will reset all passwords!')">
            Migrate Passwords
        </button>
    </form>
    <?php
}
?>
```

### Step 2: Add must_reset_password Column

```sql
ALTER TABLE admin ADD COLUMN must_reset_password TINYINT(1) DEFAULT 0;
ALTER TABLE educators ADD COLUMN must_reset_password TINYINT(1) DEFAULT 0;
```

### Step 3: Force Password Reset on Login

Update `admin/index.php`:

```php
if ($admin && password_verify($password, $admin['password'])) {
    // Check if password reset required
    if ($admin['must_reset_password'] == 1) {
        $_SESSION['temp_user_id'] = $admin['admin_ID'];
        $_SESSION['temp_user_type'] = 'admin';
        header('Location: reset_password.php');
        exit();
    }
    
    // Normal login
    // ... existing code ...
}
```

---

## Fix #4: Add CSRF Protection

### Step 1: Create CSRF Helper

Create: `play2review/includes/csrf.php`

```php
<?php
class CSRF
{
    /**
     * Generate CSRF token
     */
    public static function generateToken()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateToken($token)
    {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Require valid CSRF token
     */
    public static function requireToken()
    {
        $token = $_POST['csrf_token'] ?? '';
        
        if (!self::validateToken($token)) {
            http_response_code(403);
            die('CSRF token validation failed');
        }
    }
    
    /**
     * Get HTML input field
     */
    public static function getInputField()
    {
        $token = self::generateToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
}
?>
```

### Step 2: Add to Forms

```php
<?php require_once('includes/csrf.php'); ?>

<form method="POST">
    <?php echo CSRF::getInputField(); ?>
    
    <input type="text" name="username">
    <input type="password" name="password">
    <button type="submit">Login</button>
</form>
```

### Step 3: Validate on Submission

```php
<?php
require_once('includes/csrf.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::requireToken();  // Dies if invalid
    
    // Process form
}
?>
```

---

## Fix #5: Implement Rate Limiting

### Step 1: Create Rate Limiter

Create: `play2review/includes/rate_limiter.php`

```php
<?php
class RateLimiter
{
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_TIME = 900; // 15 minutes
    
    /**
     * Check if IP is rate limited
     */
    public static function checkLimit($action = 'default')
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $key = "rate_limit_{$action}_{$ip}";
        
        // Get current attempts
        $attempts = $_SESSION[$key] ?? 0;
        $lockout_until = $_SESSION["{$key}_lockout"] ?? 0;
        
        // Check if locked out
        if ($lockout_until > time()) {
            $remaining = $lockout_until - time();
            http_response_code(429);
            die(json_encode([
                'success' => false,
                'message' => "Too many attempts. Try again in " . ceil($remaining / 60) . " minutes."
            ]));
        }
        
        // Increment attempts
        $_SESSION[$key] = $attempts + 1;
        
        // Check if limit exceeded
        if ($_SESSION[$key] >= self::MAX_ATTEMPTS) {
            $_SESSION["{$key}_lockout"] = time() + self::LOCKOUT_TIME;
            http_response_code(429);
            die(json_encode([
                'success' => false,
                'message' => "Too many attempts. Account locked for 15 minutes."
            ]));
        }
    }
    
    /**
     * Reset rate limit on successful action
     */
    public static function resetLimit($action = 'default')
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $key = "rate_limit_{$action}_{$ip}";
        unset($_SESSION[$key]);
        unset($_SESSION["{$key}_lockout"]);
    }
}
?>
```

### Step 2: Apply to Login

```php
<?php
require_once('includes/rate_limiter.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    RateLimiter::checkLimit('login');
    
    // ... login logic ...
    
    if ($login_successful) {
        RateLimiter::resetLimit('login');
    }
}
?>
```

---

## Testing Checklist

### Security Tests

- [ ] API endpoints require authentication
- [ ] SQL injection attempts fail
- [ ] Password hashing uses bcrypt
- [ ] CSRF tokens validated
- [ ] Rate limiting works
- [ ] Session security configured

### Functionality Tests

- [ ] Login still works
- [ ] Quiz questions load
- [ ] Progress tracking works
- [ ] Admin panel accessible
- [ ] Educator panel accessible

### Performance Tests

- [ ] Response times acceptable
- [ ] Database queries optimized
- [ ] No memory leaks

---

## Deployment Checklist

### Before Deployment

- [ ] All security fixes implemented
- [ ] Database backed up
- [ ] .env file configured
- [ ] HTTPS enabled
- [ ] Error logging configured
- [ ] Monitoring setup

### After Deployment

- [ ] Test all endpoints
- [ ] Monitor error logs
- [ ] Check performance metrics
- [ ] Verify security headers
- [ ] Test from Unity client

---

## Emergency Rollback Plan

If issues occur after deployment:

1. **Restore database backup**:
```bash
mysql -u root play2review_db < backup_YYYYMMDD.sql
```

2. **Revert code**:
```bash
git revert HEAD
git push origin main
```

3. **Clear sessions**:
```php
session_destroy();
```

4. **Notify users** of temporary issues

---

## Support Resources

### Documentation
- JWT: https://jwt.io/introduction
- PDO: https://www.php.net/manual/en/book.pdo.php
- Password Hashing: https://www.php.net/manual/en/function.password-hash.php

### Security
- OWASP Top 10: https://owasp.org/www-project-top-ten/
- PHP Security: https://www.php.net/manual/en/security.php

---

**CRITICAL**: Do not deploy to production until ALL security fixes are implemented and tested!

**Document Version**: 1.0  
**Last Updated**: March 9, 2026  
**Status**: URGENT - Implement Immediately
