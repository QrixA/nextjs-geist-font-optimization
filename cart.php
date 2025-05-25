<?php
require_once 'includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php', __('login_required'), 'error');
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$conn = db_connect();

// Handle Remove from Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    verify_csrf_token($_POST['csrf_token']);
    $index = (int)$_POST['cart_index'];
    if (isset($_SESSION['cart'][$index])) {
        array_splice($_SESSION['cart'], $index, 1);
        redirect('cart.php', __('item_removed'));
    }
}

// Handle Update Billing Cycle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_billing'])) {
    verify_csrf_token($_POST['csrf_token']);
    $index = (int)$_POST['cart_index'];
    $billing_cycle = sanitize($_POST['billing_cycle']);
    
    if (isset($_SESSION['cart'][$index]) && in_array($billing_cycle, ['hour', 'month', 'year'])) {
        $_SESSION['cart'][$index]['billing_cycle'] = $billing_cycle;
        redirect('cart.php', __('billing_updated'));
    }
}

// Handle Apply Promo Code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_promo'])) {
    verify_csrf_token($_POST['csrf_token']);
    $promo_code = sanitize($_POST['promo_code']);
    
    $stmt = $conn->prepare("
        SELECT * FROM promo_codes 
        WHERE code = ? 
        AND expiry_date >= CURDATE()
        AND (usage_limit = 0 OR usage_count < usage_limit)
    ");
    $stmt->bind_param("s", $promo_code);
    $stmt->execute();
    $promo = $stmt->get_result()->fetch_assoc();
    
    if ($promo) {
        $_SESSION['promo_code'] = $promo;
        redirect('cart.php', __('promo_applied'));
    } else {
        redirect('cart.php', __('invalid_promo'), 'error');
    }
}

// Calculate total
$total = 0;
$cart_items = [];

foreach ($_SESSION['cart'] as $index => $item) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $item['product_id']);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    
    if ($product) {
        $price_field = 'price_' . $item['billing_cycle'];
        $price = $product[$price_field];
        
        $cart_items[] = [
            'index' => $index,
            'product' => $product,
            'billing_cycle' => $item['billing_cycle'],
            'price' => $price
        ];
        
        $total += $price;
    }
}

// Apply promo code discount if exists
if (isset($_SESSION['promo_code'])) {
    $promo = $_SESSION['promo_code'];
    if ($promo['discount_type'] === 'percentage') {
        $discount = $total * ($promo['discount_value'] / 100);
    } else {
        $discount = $promo['discount_value'];
    }
    $total = max(0, $total - $discount);
}

$conn->close();

require_once 'includes/header.php';
?>

<div style="max-width: 1200px; margin: 0 auto; padding: 2rem 1rem;">
    <h1 style="font-size: 2.25rem; font-weight: 700; color: var(--text-color); margin-bottom: 2rem;">
        <?php echo __('shopping_cart'); ?>
    </h1>
    
    <?php if (empty($cart_items)): ?>
        <div class="card" style="text-align: center;">
            <p style="color: #6b7280; margin-bottom: 1.5rem;">
                <?php echo __('cart_empty'); ?>
            </p>
            <a href="catalog.php" class="btn btn-primary">
                <?php echo __('browse_catalog'); ?>
            </a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
            <!-- Cart Items -->
            <div>
                <?php foreach ($cart_items as $item): ?>
                    <div class="card" style="margin-bottom: 1rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                            <h3 style="font-size: 1.25rem; font-weight: 600; color: var(--text-color);">
                                <?php echo htmlspecialchars($item['product']['name']); ?>
                            </h3>
                            <form method="POST" action="" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <input type="hidden" name="cart_index" value="<?php echo $item['index']; ?>">
                                <button type="submit" name="remove_item" class="btn" style="color: #ef4444; background: none; padding: 0;">
                                    ❌
                                </button>
                            </form>
                        </div>
                        
                        <?php 
                        $specs = json_decode($item['product']['specifications'], true);
                        foreach ($specs as $key => $value): 
                        ?>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span style="color: #6b7280;"><?php echo htmlspecialchars($key); ?></span>
                                <span style="font-weight: 500;"><?php echo htmlspecialchars($value); ?></span>
                            </div>
                        <?php endforeach; ?>
                        
                        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                            <form method="POST" action="" style="display: flex; gap: 1rem; align-items: center;">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <input type="hidden" name="cart_index" value="<?php echo $item['index']; ?>">
                                
                                <div class="form-group" style="margin: 0; flex-grow: 1;">
                                    <select name="billing_cycle" class="form-input" onchange="this.form.submit()">
                                        <option value="hour" <?php echo $item['billing_cycle'] === 'hour' ? 'selected' : ''; ?>>
                                            <?php echo __('hourly'); ?> - <?php echo format_currency($item['product']['price_hour']); ?>
                                        </option>
                                        <option value="month" <?php echo $item['billing_cycle'] === 'month' ? 'selected' : ''; ?>>
                                            <?php echo __('monthly'); ?> - <?php echo format_currency($item['product']['price_month']); ?>
                                        </option>
                                        <option value="year" <?php echo $item['billing_cycle'] === 'year' ? 'selected' : ''; ?>>
                                            <?php echo __('yearly'); ?> - <?php echo format_currency($item['product']['price_year']); ?>
                                        </option>
                                    </select>
                                </div>
                                <button type="submit" name="update_billing" class="btn btn-primary">
                                    <?php echo __('update'); ?>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Order Summary -->
            <div>
                <div class="card" style="position: sticky; top: 2rem;">
                    <h3 style="font-size: 1.25rem; font-weight: 600; color: var(--text-color); margin-bottom: 1.5rem;">
                        <?php echo __('order_summary'); ?>
                    </h3>
                    
                    <!-- Promo Code Form -->
                    <form method="POST" action="" style="margin-bottom: 1.5rem;">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label class="form-label"><?php echo __('promo_code'); ?></label>
                            <div style="display: flex; gap: 0.5rem;">
                                <input type="text" name="promo_code" class="form-input" 
                                       placeholder="<?php echo __('enter_promo_code'); ?>"
                                       value="<?php echo isset($_SESSION['promo_code']) ? $_SESSION['promo_code']['code'] : ''; ?>"
                                       <?php echo isset($_SESSION['promo_code']) ? 'disabled' : ''; ?>>
                                       
                                <?php if (isset($_SESSION['promo_code'])): ?>
                                    <button type="submit" name="remove_promo" class="btn btn-primary">
                                        <?php echo __('remove'); ?>
                                    </button>
                                <?php else: ?>
                                    <button type="submit" name="apply_promo" class="btn btn-primary">
                                        <?php echo __('apply'); ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Price Summary -->
                    <div style="margin-bottom: 1.5rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span style="color: #6b7280;"><?php echo __('subtotal'); ?></span>
                            <span style="font-weight: 500;">
                                <?php echo format_currency(array_sum(array_column($cart_items, 'price'))); ?>
                            </span>
                        </div>
                        
                        <?php if (isset($_SESSION['promo_code'])): ?>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span style="color: #6b7280;"><?php echo __('discount'); ?></span>
                                <span style="font-weight: 500; color: #059669;">
                                    -<?php echo format_currency($discount); ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        
                        <div style="display: flex; justify-content: space-between; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                            <span style="font-weight: 600;"><?php echo __('total'); ?></span>
                            <span style="font-weight: 600; font-size: 1.25rem;">
                                <?php echo format_currency($total); ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Current Balance -->
                    <div style="margin-bottom: 1.5rem; padding: 1rem; background: #f3f4f6; border-radius: 0.5rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span style="color: #6b7280;"><?php echo __('your_balance'); ?></span>
                            <span style="font-weight: 500;">
                                <?php echo format_currency($_SESSION['balance']); ?>
                            </span>
                        </div>
                        
                        <?php if ($_SESSION['balance'] < $total): ?>
                            <div style="color: #dc2626; font-size: 0.875rem; margin-top: 0.5rem;">
                                <?php echo __('insufficient_balance'); ?>
                            </div>
                            <a href="payment.php" class="btn btn-primary" style="width: 100%; margin-top: 0.5rem;">
                                <?php echo __('top_up_balance'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Checkout Button -->
                    <form method="POST" action="checkout.php">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <button type="submit" class="btn btn-primary" style="width: 100%;" 
                                <?php echo $_SESSION['balance'] < $total ? 'disabled' : ''; ?>>
                            <?php echo __('proceed_to_checkout'); ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
