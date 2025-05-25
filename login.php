<?php
require_once 'includes/functions.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    verify_csrf_token($_POST['csrf_token']);
    
    // Sanitize inputs
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    // Validate inputs
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = __('invalid_email');
    }
    
    if (empty($password)) {
        $errors[] = __('password_required');
    }
    
    if (empty($errors)) {
        $conn = db_connect();
        $stmt = $conn->prepare("SELECT id, full_name, password, is_admin, balance FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['is_admin'] = $user['is_admin'];
                $_SESSION['balance'] = $user['balance'];
                
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                // Redirect to dashboard
                redirect('dashboard.php', __('login_success'));
            } else {
                $errors[] = __('invalid_credentials');
            }
        } else {
            $errors[] = __('invalid_credentials');
        }
        
        $conn->close();
    }
}

require_once 'includes/header.php';
?>

<div class="card" style="max-width: 500px; margin: 2rem auto;">
    <div style="text-align: center; margin-bottom: 2rem;">
        <h1 style="color: var(--text-color); font-size: 1.875rem; font-weight: 600; margin-bottom: 0.5rem;">
            <?php echo __('welcome_back'); ?>
        </h1>
        <p style="color: #6b7280;">
            <?php echo __('login_to_continue'); ?>
        </p>
    </div>
    
    <?php if (!empty($errors)): ?>
        <div class="message message-error">
            <ul style="margin: 0; padding-left: 1.5rem;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="" style="margin-top: 2rem;">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        
        <div class="form-group">
            <label class="form-label"><?php echo __('email'); ?></label>
            <input type="email" name="email" class="form-input" 
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                   placeholder="<?php echo __('enter_email'); ?>" required>
        </div>
        
        <div class="form-group">
            <label class="form-label"><?php echo __('password'); ?></label>
            <input type="password" name="password" class="form-input" 
                   placeholder="<?php echo __('enter_password'); ?>" required>
        </div>
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <label style="display: flex; align-items: center; gap: 0.5rem; color: #6b7280;">
                <input type="checkbox" name="remember" style="border-radius: 4px;">
                <?php echo __('remember_me'); ?>
            </label>
            
            <a href="forgot-password.php" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">
                <?php echo __('forgot_password'); ?>
            </a>
        </div>
        
        <button type="submit" class="btn btn-primary" style="width: 100%;">
            <?php echo __('login'); ?>
        </button>
        
        <p style="margin-top: 1.5rem; text-align: center; color: #6b7280;">
            <?php echo __('dont_have_account'); ?> 
            <a href="register.php" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">
                <?php echo __('create_account'); ?>
            </a>
        </p>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
