</div><!-- End of container -->
    
    <footer class="footer" style="background: var(--card-bg); padding: 2rem 0; margin-top: 4rem; border-top: 1px solid #e5e7eb;">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <a href="/" class="nav-logo" style="margin-bottom: 1rem; display: inline-block;">
                        🌸 SakuraCloudID
                    </a>
                    <p style="color: #6b7280; max-width: 400px;">
                        <?php echo __('footer_description'); ?>
                    </p>
                </div>
                
                <div style="display: flex; gap: 3rem;">
                    <div>
                        <h3 style="color: var(--text-color); font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">
                            <?php echo __('products'); ?>
                        </h3>
                        <ul style="list-style: none; padding: 0;">
                            <li><a href="/catalog.php?category=vps" class="nav-link">VPS</a></li>
                            <li><a href="/catalog.php?category=hosting" class="nav-link"><?php echo __('hosting'); ?></a></li>
                            <li><a href="/catalog.php?category=domain" class="nav-link"><?php echo __('domain'); ?></a></li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3 style="color: var(--text-color); font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">
                            <?php echo __('support'); ?>
                        </h3>
                        <ul style="list-style: none; padding: 0;">
                            <li><a href="/tickets.php" class="nav-link"><?php echo __('tickets'); ?></a></li>
                            <li><a href="/faq.php" class="nav-link"><?php echo __('faq'); ?></a></li>
                            <li><a href="/contact.php" class="nav-link"><?php echo __('contact'); ?></a></li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3 style="color: var(--text-color); font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">
                            <?php echo __('legal'); ?>
                        </h3>
                        <ul style="list-style: none; padding: 0;">
                            <li><a href="/terms.php" class="nav-link"><?php echo __('terms'); ?></a></li>
                            <li><a href="/privacy.php" class="nav-link"><?php echo __('privacy'); ?></a></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #e5e7eb; text-align: center; color: #6b7280;">
                <p>&copy; <?php echo date('Y'); ?> SakuraCloudID. <?php echo __('all_rights_reserved'); ?></p>
                
                <!-- Language Selector -->
                <div style="margin-top: 1rem;">
                    <form method="GET" style="display: inline-flex; gap: 0.5rem; align-items: center; justify-content: center;">
                        <input type="hidden" name="redirect" value="<?php echo $_SERVER['PHP_SELF']; ?>">
                        <select name="lang" onchange="this.form.submit()" 
                                style="padding: 0.5rem; border-radius: 4px; border: 1px solid #e5e7eb; background: var(--card-bg);">
                            <option value="en" <?php echo ($_SESSION['lang'] ?? 'en') === 'en' ? 'selected' : ''; ?>>English</option>
                            <option value="id" <?php echo ($_SESSION['lang'] ?? 'en') === 'id' ? 'selected' : ''; ?>>Bahasa Indonesia</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
