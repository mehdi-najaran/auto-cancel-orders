<?php
// Check if WooCommerce is active before adding the custom admin menu page and settings
add_action('admin_init', 'auto_cancel_check_woocommerce_active');
function auto_cancel_check_woocommerce_active()
{
    if (!is_plugin_active('woocommerce/woocommerce.php')) {
        add_action('admin_notices', 'auto_cancel_show_woocommerce_notice');
    }
}

function auto_cancel_show_woocommerce_notice()
{
    echo '<div class="notice notice-error"><p>' . __('Auto Cancel Orders plugin requires WooCommerce to be active. Please activate WooCommerce to use this plugin.', 'auto-cancel-orders') . '</p></div>';
}

// Add a custom admin menu page for manual order cancellation and settings
add_action('admin_menu', 'auto_cancel_orders_admin_menu');

function auto_cancel_orders_admin_menu()
{
    // Don't add the menu if WooCommerce is not active
    if (!is_plugin_active('woocommerce/woocommerce.php')) {
        return;
    }

    add_menu_page(
        __('Cancel Orders', 'auto-cancel-orders'),
        __('Cancel Orders', 'auto-cancel-orders'),
        'manage_options',
        'auto_cancel_orders',
        'auto_cancel_orders_admin_page',
        'dashicons-warning',
        80
    );
}

// Callback function for the custom admin page
function auto_cancel_orders_admin_page()
{
    if (isset($_POST['cancel_pending_orders']) && check_admin_referer('auto_cancel_orders_nonce', 'auto_cancel_orders_nonce')) {
        auto_cancel_pending_orders();
        echo '<div class="notice notice-success"><p>' . __('Manually triggered cancellation process completed.', 'auto-cancel-orders') . '</p></div>';
    }

    if (isset($_GET['settings-updated'])) {
        echo '<div class="notice notice-success"><p>' . __('Settings saved successfully.', 'auto-cancel-orders') . '</p></div>';
    }

    // Check if WooCommerce is active
    if (!is_plugin_active('woocommerce/woocommerce.php')) {
        echo '<div class="notice notice-warning"><p>' . __('Auto Cancel Orders plugin requires WooCommerce to be active. Please activate WooCommerce to use this plugin.', 'auto-cancel-orders') . '</p></div>';
        return;
    }

    $pending_orders_count = auto_cancel_get_pending_orders_count();
?>
    <div class="wrap">
        <h1><?php echo esc_html__('Cancel Orders', 'auto-cancel-orders'); ?></h1>
        <section>
            <form method="post" action="options.php">
                <?php settings_fields('auto_cancel_orders'); ?>
                <?php do_settings_sections('auto_cancel_orders'); ?>
                <?php submit_button(); ?>
            </form>
        </section>
        <section>
            <h2><?php echo esc_html__('Auto Cancel All Orders', 'auto-cancel-orders'); ?></h2>
            <p><?php echo esc_html__('Click the button below to manually trigger the cancellation process for pending orders that are past the specified duration of pending payment.', 'auto-cancel-orders'); ?></p>
            <p><?php echo sprintf(esc_html__('There are currently %d orders in pending payment status.', 'auto-cancel-orders'), $pending_orders_count); ?></p>
            <form method="post">
                <?php wp_nonce_field('auto_cancel_orders_nonce', 'auto_cancel_orders_nonce'); ?>
                <p class="submit">
                    <button type="submit" name="cancel_pending_orders" class="button button-primary" id="submit"><?php echo esc_html__('Cancel Pending Orders', 'auto-cancel-orders'); ?></button>
                </p>
            </form>
        </section>
    </div>
<?php
}

// Function to get the number of orders in "pending payment" status
function auto_cancel_get_pending_orders_count()
{
    $pending_orders = wc_get_orders(array(
        'status' => 'pending',
        'limit' => -1,
    ));

    return count($pending_orders);
}

// Add more options to the admin panel
add_action('admin_init', 'auto_cancel_orders_settings_init');

function auto_cancel_orders_settings_init()
{
    add_settings_section(
        'auto_cancel_orders_section',
        __('Auto Cancel Orders Settings', 'auto-cancel-orders'),
        'auto_cancel_orders_settings_section_callback',
        'auto_cancel_orders'
    );

    add_settings_field(
        'auto_cancel_time',
        __('Time (in hours)', 'auto-cancel-orders'),
        'auto_cancel_orders_time_callback',
        'auto_cancel_orders',
        'auto_cancel_orders_section'
    );

    register_setting('auto_cancel_orders', 'auto_cancel_time', 'intval');
}

function auto_cancel_orders_settings_section_callback()
{
    echo '<p>' . __('Set the auto cancel time for pending orders in hours.', 'auto-cancel-orders') . '</p>';
}

function auto_cancel_orders_time_callback()
{
    $auto_cancel_time = get_option('auto_cancel_time', 2);
    echo '<input type="number" min="1" step="1" name="auto_cancel_time" value="' . $auto_cancel_time . '" />';
}

// Enqueue custom style file only on the plugin settings page
add_action('admin_enqueue_scripts', 'auto_cancel_enqueue_custom_styles');

function auto_cancel_enqueue_custom_styles($hook_suffix)
{
    // Check if we are on the plugin settings page
    if ($hook_suffix === 'toplevel_page_auto_cancel_orders') {
        wp_enqueue_style('auto_cancel_custom_styles', plugin_dir_url(__FILE__) . '../assets/css/style.css');
    }
}
