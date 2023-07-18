<?php
// Schedule the event to run every 5 minutes
add_action('init', 'auto_cancel_orders_schedule_event');

function auto_cancel_orders_schedule_event()
{
    if (!wp_next_scheduled('auto_cancel_pending_orders')) {
        wp_schedule_event(time(), '5_minutes', 'auto_cancel_pending_orders');
    }
}

// Function to cancel pending orders after a specified duration
function auto_cancel_pending_orders()
{
    $auto_cancel_time = get_option('auto_cancel_time', 2);
    $pending_orders = wc_get_orders(array(
        'status' => 'pending',
        'date_created' => '<' . (time() - $auto_cancel_time * HOUR_IN_SECONDS), // Specified duration in seconds
        'limit' => -1,
    ));

    if (!empty($pending_orders)) {
        foreach ($pending_orders as $order) {
            $order->update_status('cancelled', __('Order automatically cancelled after a specified duration of pending payment.', 'auto-cancel-orders'));
        }
    }
}

// Custom cron interval for running the event every 5 minutes
function auto_cancel_orders_custom_cron_intervals($schedules)
{
    $schedules['5_minutes'] = array(
        'interval' => 5 * MINUTE_IN_SECONDS,
        'display'  => __('Every 5 Minutes', 'auto-cancel-orders'),
    );
    return $schedules;
}
add_filter('cron_schedules', 'auto_cancel_orders_custom_cron_intervals');
