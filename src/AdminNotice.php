<?php

namespace WCKalkulator;

/**
 * Class AdminNotice
 *
 * @package WCKalkulator
 * @author Krzysztof Piątkowski
 * @license GPLv2
 * @since 1.2.2
 */
class AdminNotice
{
    const NONCE = "wckalkulator-ajax-admin-notice-nonce";
    const TASK = "wck_admin_notice_schedule";
    const STATE_OPTION = "wck_admin_notice_state";
    
    /**
     * Add actions and filters
     *
     * @return void
     * @since 1.2.2
     */
    public static function init()
    {
        add_filter('cron_schedules', array(__CLASS__, 'cron_interval'));
        if ((int)get_option(AdminNotice::STATE_OPTION, 1) === 1) {
            add_action('admin_notices', array(__CLASS__, 'notice_donate'));
            add_action('admin_notices', array(__CLASS__, 'notice_rate'));
        }
        add_action('wp_ajax_wck_notice_dismiss', array(__CLASS__, 'dismiss'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'));
        add_action(AdminNotice::TASK, array(__CLASS__, 'set_notice_option'));
    }
    
    /**
     * Schedula an event
     *
     * @return void
     * @since 1.2.2
     */
    public static function schedule()
    {
        if (!wp_next_scheduled(AdminNotice::TASK)) {
            wp_schedule_event(time(), 'twice_a_week', AdminNotice::TASK);
        }
    }
    
    /**
     * Add js script and set ajax object
     *
     * @return void
     * @since 1.2.2
     */
    public static function enqueue_scripts()
    {
        wp_enqueue_script(
            'wck-admin-notice-script',
            Plugin::url() . '/assets/js/notice.js',
            ['jquery'],
            Plugin::VERSION
        );
        
        wp_localize_script(
            'wck-admin-notice-script',
            'wck_ajax_object',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                '_wck_ajax_nonce' => wp_create_nonce(AdminNotice::NONCE),
            )
        );
    }
    
    /**
     * Add custom cron interval
     *
     * @param array $schedules
     * @return array
     * @since 1.2.2
     */
    public static function cron_interval($schedules)
    {
        $schedules['twice_a_week'] = array(
            'interval' => 259200,
            'display' => esc_html__('Twice a week'),);
        return $schedules;
    }
    
    /**
     * Set notice option to "1" (enable)
     *
     * @return void
     * @since 1.2.2
     */
    public static function set_notice_option()
    {
        update_option(AdminNotice::STATE_OPTION, 1);
    }
    
    /**
     * Ajax action to dismiss notice
     *
     * @return void
     * @since 1.2.2
     */
    public static function dismiss()
    {
        if (!wp_verify_nonce($_POST['_wck_ajax_nonce'], AdminNotice::NONCE) || !isset($_POST["_wck_notice_dismiss"])) {
            wp_send_json(array(
                'status' => 'bad request'
            ));
        }
        
        self::unset_notice_option();
        
        wp_send_json(array(
            'status' => 'success'
        ));
    }
    
    /**
     * Set notice option to "0" (disable)
     *
     * @return void
     * @since 1.2.2
     */
    public static function unset_notice_option()
    {
        update_option(AdminNotice::STATE_OPTION, 0);
    }
    
    /**
     * Display Html for notice (rate us)
     *
     * @return void
     * @since 1.2.2
     */
    public static function notice_rate()
    {
        ?>
        <div class="notice notice-info is-dismissible wck-rate wck-notice">
            <p><?php _e('Thank you for using <strong>WC Kalkulator</strong>! Please <a href="https://wordpress.org/support/plugin/wc-kalkulator/reviews/#new-post" target="_blank">rate us &#9733;&#9733;&#9733;&#9733;&#9733;</a> and remember that WCK will always be for free!', 'wc-kalkulator'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Display Html for notice (donate)
     *
     * @return void
     * @since 1.2.2
     */
    public static function notice_donate()
    {
        ?>
        <div class="notice notice-info is-dismissible wck-donate wck-notice">
            <p><?php _e('Hello ! Remember that <strong>WC Kalkulator</strong> will always be for free, so it would be nice if you could <strong>donate</strong> a small amount of money via PayPal :) - <a href="https://www.paypal.com/donate/?hosted_button_id=5DNZK72H5YCBY"><strong>Yes, I\'d like to support!</strong></a>', 'wc-kalkulator'); ?></p>
        </div>
        <?php
    }
}