<?php
require_once 'includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php', __('login_required'), 'error');
}

$conn = db_connect();

// Get categories
$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Get products for each category
$products_by_category = [];
foreach ($categories as $category) {
    $stmt = $conn->prepare("
        SELECT * FROM products 
        WHERE category_id = ? 
        ORDER BY price_month ASC
    ");
    $stmt->bind_param("i", $category['id']);
    $stmt->execute();
    $products_by_category[$category['id']] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    verify_csrf_token($_POST['csrf_token']);
    
    $product_id = (int)$_POST['product_id'];
    $billing_cycle = sanitize($_POST['billing_cycle']);
    
    // Validate billing cycle
    if (!in_array($billing_cycle, ['hour', 'month', 'year'])) {
        redirect('catalog.php', __('invalid_billing_cycle'), 'error');
    }
    
    // Add to cart session
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    $_SESSION['cart'][] = [
        'product_id' => $product_id,
        'billing_cycle' => $billing_cycle
    ];
    
    redirect('cart.php', __('added_to_cart'));
}

$conn->close();

require_once 'includes/header.php';
?>

<div style="max-width: 1200px; margin: 0 auto; padding: 2rem 1rem;">
    <!-- Hero Section -->
    <div style="text-align: center; margin-bottom: 4rem;">
        <h1 style="font-size: 2.25rem; font-weight: 700; color: var(--text-color); margin-bottom: 1rem;">
            <?php echo __('our_products'); ?>
        </h1>
        <p style="color: #6b7280; font-size: 1.125rem; max-width: 600px; margin: 0 auto;">
            <?php echo __('products_description'); ?>
        </p>
    </div>

    <?php foreach ($categories as $category): ?>
        <section style="margin-bottom: 4rem;">
            <h2 style="font-size: 1.875rem; font-weight: 600; color: var(--text-color); margin-bottom: 2rem;">
                <?php echo htmlspecialchars($category['name']); ?>
            </h2>
            
            <?php if (empty($products_by_category[$category['id']])): ?>
                <div class="card" style="text-align: center; color: #6b7280;">
                    <p><?php echo __('no_products_in_category'); ?></p>
                </div>
            <?php else: ?>
                <div class="grid">
                    <?php foreach ($products_by_category[$category['id']] as $product): ?>
                        <div class="card">
                            <div style="margin-bottom: 1.5rem;">
                                <h3 style="font-size: 1.5rem; font-weight: 600; color: var(--text-color); margin-bottom: 0.5rem;">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </h3>
                                <p style="color: #6b7280;">
                                    <?php echo htmlspecialchars($product['description']); ?>
                                </p>
                            </div>

                            <!-- Specifications -->
                            <?php 
                            $specs = json_decode($product['specifications'], true);
                            foreach ($specs as $key => $value): 
                            ?>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span style="color: #6b7280;"><?php echo htmlspecialchars($key); ?></span>
                                    <span style="font-weight: 500;"><?php echo htmlspecialchars($value); ?></span>
                                </div>
                            <?php endforeach; ?>

                            <!-- Region -->
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin: 1rem 0; padding: 1rem 0; border-top: 1px solid #e5e7eb;">
                                <span style="color: #6b7280;">📍</span>
                                <span><?php echo htmlspecialchars($product['region']); ?></span>
                            </div>

                            <!-- Pricing -->
                            <div style="margin-bottom: 1.5rem;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span style="color: #6b7280;"><?php echo __('hourly'); ?></span>
                                    <span style="font-weight: 500;"><?php echo format_currency($product['price_hour']); ?></span>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span style="color: #6b7280;"><?php echo __('monthly'); ?></span>
                                    <span style="font-weight: 500;"><?php echo format_currency($product['price_month']); ?></span>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span style="color: #6b7280;"><?php echo __('yearly'); ?></span>
                                    <span style="font-weight: 500;"><?php echo format_currency($product['price_year']); ?></span>
                                </div>
                            </div>

                            <!-- Add to Cart Form -->
                            <form method="POST" action="">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                
                                <div class="form-group">
                                    <label class="form-label"><?php echo __('billing_cycle'); ?></label>
                                    <select name="billing_cycle" class="form-input">
                                        <option value="hour"><?php echo __('hourly'); ?></option>
                                        <option value="month" selected><?php echo __('monthly'); ?></option>
                                        <option value="year"><?php echo __('yearly'); ?></option>
                                    </select>
                                </div>
                                
                                <button type="submit" name="add_to_cart" class="btn btn-primary" style="width: 100%;">
                                    <?php echo __('add_to_cart'); ?>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    <?php endforeach; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
