<?php
require_once 'includes/functions.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('login.php', __('login_required'), 'error');
}

$conn = db_connect();

// Get active services count
$stmt = $conn->prepare("
    SELECT COUNT(*) as active_services 
    FROM services s 
    JOIN orders o ON s.order_id = o.id 
    WHERE o.user_id = ? AND s.status = 'active'
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$active_services = $stmt->get_result()->fetch_assoc()['active_services'];

// Get open tickets count
$stmt = $conn->prepare("
    SELECT COUNT(*) as open_tickets 
    FROM tickets 
    WHERE user_id = ? AND status = 'open'
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$open_tickets = $stmt->get_result()->fetch_assoc()['open_tickets'];

// Get active announcement
$stmt = $conn->prepare("
    SELECT message 
    FROM announcements 
    WHERE start_date <= CURDATE() 
    AND end_date >= CURDATE() 
    ORDER BY id DESC 
    LIMIT 1
");
$stmt->execute();
$announcement = $stmt->get_result()->fetch_assoc();

// Get recent services
$stmt = $conn->prepare("
    SELECT s.*, o.billing_cycle, p.name as product_name, p.specifications
    FROM services s 
    JOIN orders o ON s.order_id = o.id 
    JOIN products p ON o.product_id = p.id
    WHERE o.user_id = ? 
    ORDER BY s.created_at DESC 
    LIMIT 5
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$recent_services = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get recent tickets
$stmt = $conn->prepare("
    SELECT * FROM tickets 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$recent_tickets = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$conn->close();

require_once 'includes/header.php';
?>

<div style="max-width: 1200px; margin: 0 auto; padding: 2rem 1rem;">
    <!-- Welcome Section -->
    <div style="margin-bottom: 3rem;">
        <h1 style="font-size: 2.25rem; font-weight: 700; color: var(--text-color); margin-bottom: 0.5rem;">
            <?php echo __('welcome_user', ['name' => htmlspecialchars($_SESSION['full_name'])]); ?>
        </h1>
        <p style="color: #6b7280; font-size: 1.125rem;">
            <?php echo __('dashboard_subtitle'); ?>
        </p>
    </div>

    <?php if ($announcement): ?>
    <!-- Announcement -->
    <div class="card" style="background: var(--primary-color); color: white; margin-bottom: 2rem;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <span style="font-size: 1.5rem;">📢</span>
            <div>
                <h3 style="margin-bottom: 0.5rem; font-weight: 600;">
                    <?php echo __('announcement'); ?>
                </h3>
                <p><?php echo htmlspecialchars($announcement['message']); ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Stats Grid -->
    <div class="grid" style="margin-bottom: 3rem;">
        <!-- Balance Card -->
        <div class="stats-card">
            <span class="stats-title"><?php echo __('current_balance'); ?></span>
            <span class="stats-value"><?php echo format_currency($_SESSION['balance']); ?></span>
            <a href="payment.php" class="btn btn-primary" style="margin-top: 1rem;">
                <?php echo __('top_up_balance'); ?>
            </a>
        </div>

        <!-- Active Services Card -->
        <div class="stats-card">
            <span class="stats-title"><?php echo __('active_services'); ?></span>
            <span class="stats-value"><?php echo $active_services; ?></span>
            <a href="services.php" class="btn btn-primary" style="margin-top: 1rem;">
                <?php echo __('view_all_services'); ?>
            </a>
        </div>

        <!-- Support Tickets Card -->
        <div class="stats-card">
            <span class="stats-title"><?php echo __('open_tickets'); ?></span>
            <span class="stats-value"><?php echo $open_tickets; ?></span>
            <a href="tickets.php" class="btn btn-primary" style="margin-top: 1rem;">
                <?php echo __('view_all_tickets'); ?>
            </a>
        </div>
    </div>

    <!-- Recent Services -->
    <div style="margin-bottom: 3rem;">
        <h2 style="font-size: 1.5rem; font-weight: 600; color: var(--text-color); margin-bottom: 1.5rem;">
            <?php echo __('recent_services'); ?>
        </h2>
        
        <?php if (empty($recent_services)): ?>
            <div class="card" style="text-align: center; color: #6b7280;">
                <p><?php echo __('no_services_yet'); ?></p>
                <a href="catalog.php" class="btn btn-primary" style="margin-top: 1rem;">
                    <?php echo __('browse_catalog'); ?>
                </a>
            </div>
        <?php else: ?>
            <div class="grid">
                <?php foreach ($recent_services as $service): ?>
                    <div class="card">
                        <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">
                            <?php echo htmlspecialchars($service['product_name']); ?>
                        </h3>
                        
                        <?php 
                        $specs = json_decode($service['specifications'], true);
                        foreach ($specs as $key => $value): 
                        ?>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span style="color: #6b7280;"><?php echo htmlspecialchars($key); ?></span>
                                <span style="font-weight: 500;"><?php echo htmlspecialchars($value); ?></span>
                            </div>
                        <?php endforeach; ?>
                        
                        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span style="color: #6b7280;"><?php echo __('status'); ?></span>
                                <span class="badge" style="
                                    padding: 0.25rem 0.75rem;
                                    border-radius: 9999px;
                                    font-size: 0.875rem;
                                    font-weight: 500;
                                    <?php echo $service['status'] === 'active' ? 
                                        'background: #d1fae5; color: #065f46;' : 
                                        'background: #fee2e2; color: #991b1b;'; 
                                    ?>
                                ">
                                    <?php echo __(strtolower($service['status'])); ?>
                                </span>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span style="color: #6b7280;"><?php echo __('billing_cycle'); ?></span>
                                <span><?php echo __(strtolower($service['billing_cycle'])); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recent Tickets -->
    <div>
        <h2 style="font-size: 1.5rem; font-weight: 600; color: var(--text-color); margin-bottom: 1.5rem;">
            <?php echo __('recent_tickets'); ?>
        </h2>
        
        <?php if (empty($recent_tickets)): ?>
            <div class="card" style="text-align: center; color: #6b7280;">
                <p><?php echo __('no_tickets_yet'); ?></p>
                <a href="tickets.php" class="btn btn-primary" style="margin-top: 1rem;">
                    <?php echo __('create_ticket'); ?>
                </a>
            </div>
        <?php else: ?>
            <div class="card">
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th style="text-align: left; padding: 1rem; border-bottom: 1px solid #e5e7eb;">
                                    <?php echo __('ticket_id'); ?>
                                </th>
                                <th style="text-align: left; padding: 1rem; border-bottom: 1px solid #e5e7eb;">
                                    <?php echo __('subject'); ?>
                                </th>
                                <th style="text-align: left; padding: 1rem; border-bottom: 1px solid #e5e7eb;">
                                    <?php echo __('status'); ?>
                                </th>
                                <th style="text-align: left; padding: 1rem; border-bottom: 1px solid #e5e7eb;">
                                    <?php echo __('created_at'); ?>
                                </th>
                                <th style="text-align: right; padding: 1rem; border-bottom: 1px solid #e5e7eb;">
                                    <?php echo __('action'); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_tickets as $ticket): ?>
                                <tr>
                                    <td style="padding: 1rem; border-bottom: 1px solid #e5e7eb;">
                                        #<?php echo $ticket['id']; ?>
                                    </td>
                                    <td style="padding: 1rem; border-bottom: 1px solid #e5e7eb;">
                                        <?php echo htmlspecialchars($ticket['subject']); ?>
                                    </td>
                                    <td style="padding: 1rem; border-bottom: 1px solid #e5e7eb;">
                                        <span class="badge" style="
                                            padding: 0.25rem 0.75rem;
                                            border-radius: 9999px;
                                            font-size: 0.875rem;
                                            font-weight: 500;
                                            <?php echo $ticket['status'] === 'open' ? 
                                                'background: #d1fae5; color: #065f46;' : 
                                                'background: #fee2e2; color: #991b1b;'; 
                                            ?>
                                        ">
                                            <?php echo __(strtolower($ticket['status'])); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 1rem; border-bottom: 1px solid #e5e7eb;">
                                        <?php echo date('M j, Y', strtotime($ticket['created_at'])); ?>
                                    </td>
                                    <td style="padding: 1rem; border-bottom: 1px solid #e5e7eb; text-align: right;">
                                        <a href="ticket.php?id=<?php echo $ticket['id']; ?>" 
                                           class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                            <?php echo __('view'); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
