<?php

namespace Theroyals\CacheClearWebhook\UI;

class SettingsScreen
{
    /**
     * Register the requred hooks for the admin screen
     *
     * @return void
     */
    public static function init()
    {
        add_action('admin_menu', [__CLASS__, 'addMenu']);
    }

    /**
     * Register an tools/management menu for the admin area
     *
     * @return void
     */
    public static function addMenu()
    {
        add_options_page(
            'Cache Clear Webhooks (Settings)',
            'Cache Clear Webhook',
            'manage_options',
            'wp-cache-clear-webhook-settings',
            [__CLASS__, 'renderPage']
        );
    }

    /**
     * Render the management/tools page
     *
     * @return void
     */
    public static function renderPage()
    {
        ?><div class="wrap">

            <h2><?= get_admin_page_title(); ?></h2>

            <form method="post" action="<?= esc_url(admin_url('options.php')); ?>">
                <?php

                settings_fields(THEROYALS_CACHE_CLEAR_WEBHOOK_OPTIONS_KEY);
                do_settings_sections(THEROYALS_CACHE_CLEAR_WEBHOOK_OPTIONS_KEY);

                submit_button('Save Settings', 'primary', 'submit', false);

                $uri = wp_nonce_url(
                    admin_url('admin.php?page=wp-cache-clear-webhook-settings&action=cache-clear-webhook'),
                    'theroyals_cache_clear_webhook',
                    'theroyals_cache_clear_webhook'
                );

                ?>

                <p>You must save your settings before testing.</p>
                <a href="<?= esc_url($uri); ?>" class="button">Test Webhook</a>

            </form>

        </div><?php
    }
}
