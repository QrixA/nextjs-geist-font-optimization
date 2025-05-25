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
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    if (empty($full_name)) {
        $errors[] = __('full_name_required');
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = __('invalid_email');
    }
    
    if (empty($phone) || !preg_match("/^[0-9]{10,15}$/", $phone)) {
        $errors[] = __('invalid_phone');
    }
    
    if (strlen($password) < 8) {
        $errors[] = __('password_too_short');
    }
    
    if ($password !== $confirm_password) {
        $errors[] = __('passwords_dont_match');
    }
    
    // Check if email already exists
    if (empty($errors)) {
        $conn = db_connect();
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = __('email_exists');
        } else {
            // Create user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $full_name, $email, $phone, $hashed_password);
            
            if ($stmt->execute()) {
                redirect('login.php', __('registration_success'));
            } else {
                $errors[] = __('registration_failed');
            }
        }
        $conn->close();
    }
}

require_once 'includes/header.php';
?>

<div class="card" style="max-width: 500px; margin: 2rem auto;">
    <h1 style="margin-bottom: 1.5rem; color: var(--text-color); font-size: 1.875rem; font-weight: 600; text-align: center;">
        <?php echo __('create_account'); ?>
    </h1>
    
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
            <label class="form-label"><?php echo __('full_name'); ?></label>
            <input type="text" name="full_name" class="form-input" 
                   value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" 
                   placeholder="<?php echo __('enter_full_name'); ?>" required>
        </div>
        
        <div class="form-group">
            <label class="form-label"><?php echo __('email'); ?></label>
            <input type="email" name="email" class="form-input" 
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                   placeholder="<?php echo __('enter_email'); ?>" required>
        </div>
        
        <div class="form-group">
            <label class="form-label"><?php echo __('phone'); ?></label>
            <input type="tel" name="phone" class="form-input" 
                   value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" 
                   placeholder="<?php echo __('enter_phone'); ?>" required>
        </div>
        
        <div class="form-group">
            <label class="form-label"><?php echo __('password'); ?></label>
            <input type="password" name="password" class="form-input" 
                   placeholder="<?php echo __('enter_password'); ?>" required>
            <small style="display: block; margin-top: 0.5rem; color: #6b7280;">
                <?php echo __('password_requirements'); ?>
            </small>
        </div>
        
        <div class="form-group">
            <label class="form-label"><?php echo __('confirm_password'); ?></label>
            <input type="password" name="confirm_password" class="form-input" 
                   placeholder="<?php echo __('confirm_password'); ?>" required>
        </div>
        
        <button type="submit" class="btn btn-primary" style="width: 100%;">
            <?php echo __('create_account'); ?>
        </button>
        
        <p style="margin-top: 1.5rem; text-align: center; color: #6b7280;">
            <?php echo __('already_have_account'); ?> 
            <a href="login.php" style="color: var(--primary-color); text-decoration: none; font-weight: 500;">
                <?php echo __('login'); ?>
            </a>
        </p>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
