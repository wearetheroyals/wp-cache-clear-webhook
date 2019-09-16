<?php

namespace Theroyals\CacheClearWebhook;

class WebhookTrigger
{
    /**
     * Setup hooks for triggering the webhook
     *
     * @return void
     */
    public static function init()
    {
        add_action('admin_bar_menu', [__CLASS__, 'adminBarTriggerButton']);

        add_action('admin_footer', [__CLASS__, 'adminBarCssAndJs']);
        add_action('wp_footer', [__CLASS__, 'adminBarCssAndJs']);

        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueueScripts']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueueScripts']);

        add_action('wp_ajax_wp_cache_clear_webhook_manual_trigger', [__CLASS__, 'trigger']);
    }

    /**
     * When a post is saved or updated, fire this
     *
     * @param int $id
     * @param object $post
     * @param bool $update
     * @return void
     */
    public static function triggerSavePost($id, $post, $update)
    {
        if (wp_is_post_revision($id) || wp_is_post_autosave($id)) {
            return;
        }

        $statuses = apply_filters('cache_clear_webhook_post_statuses', ['publish', 'private', 'trash'], $id, $post);

        if (!in_array(get_post_status($id), $statuses, true)) {
            return;
        }

        $option = cache_clear_webhook_get_options();
        $post_types = apply_filters('cache_clear_webhook_post_types', $option['webhook_post_types'] ?: [], $id, $post);

        if (!in_array(get_post_type($id), $post_types, true)) {
            return;
        }

        self::fireWebhook($post->post_type, $post->post_name);
    }

    /**
     * Fire a request to the webhook when a term has been created.
     *
     * @param int $id
     * @param int $post
     * @param string $tax_slug
     * @return void
     */
    public static function triggerSaveTerm($id, $tax_id, $tax_slug)
    {
        if (!self::canFireForTaxonomy($id, $tax_id, $tax_slug)) {
            return;
        }

        self::fireWebhook($tax_slug, "");
    }

    /**
     * Fire a request to the webhook when a term has been removed.
     *
     * @param int $id
     * @param int $post
     * @param string $tax_slug
     * @param object $term
     * @param array $object_ids
     * @return void
     */
    public static function triggerDeleteTerm($id, $tax_id, $tax_slug, $term, $object_ids)
    {
        if (!self::canFireForTaxonomy($id, $tax_id, $tax_slug)) {
            return;
        }

        self::fireWebhook($tax_slug, "");
    }

    /**
     * Fire a request to the webhook when a term has been modified.
     *
     * @param int $id
     * @param int $post
     * @param string $tax_slug
     * @return void
     */
    public static function triggerEditTerm($id, $tax_id, $tax_slug)
    {
        if (!self::canFireForTaxonomy($id, $tax_id, $tax_slug)) {
            return;
        }

        self::fireWebhook($tax_slug, "");
    }

    /**
     * Check if the given taxonomy is one that should fire the webhook
     *
     * @param int $id
     * @param int $tax_id
     * @param string $tax_slug
     * @return boolean
     */
    protected static function canFireForTaxonomy($id, $tax_id, $tax_slug)
    {
        $option = cache_clear_webhook_get_options();
        $taxonomies = apply_filters('cache_clear_webhook_taxonomies', $option['webhook_taxonomies'] ?: [], $id, $tax_id);

        return in_array($tax_slug, $taxonomies, true);
    }

    /**
     * Show the admin bar css & js
     *
     * @todo move this somewhere else
     * @return void
     */
    public static function adminBarCssAndJs()
    {
        if (!is_admin_bar_showing()) {
            return;
        }

        ?><style>

        #wpadminbar .wp-cache-clear-webhook-button > a {
            background-color: rgba(255, 255, 255, .2) !important;
            color: #FFFFFF !important;
        }
        #wpadminbar .wp-cache-clear-webhook-button > a:hover,
        #wpadminbar .wp-cache-clear-webhook-button > a:focus {
            background-color: rgba(255, 255, 255, .25) !important;
        }

        #wpadminbar .wp-cache-clear-webhook-button svg {
            width: 12px;
            height: 12px;
            margin-left: 5px;
        }

        #wpadminbar .wp-cache-clear-webhook-badge > .ab-item {
            display: flex;
            align-items: center;
        }

        </style><?php
    }

    /**
     * Enqueue js to the admin & frontend
     *
     * @return void
     */
    public static function enqueueScripts()
    {
        wp_enqueue_script(
            'wp-cache-clear-webhook-adminbar',
            THEROYALS_CACHE_CLEAR_WEBHOOK_URL.'/assets/admin.js',
            ['jquery'],
            filemtime(THEROYALS_CACHE_CLEAR_WEBHOOK_PATH.'/assets/admin.js')
        );

        $button_nonce = wp_create_nonce('wp-cache-clear-webhook-button-nonce');

        wp_localize_script('wp-cache-clear-webhook-adminbar', 'wpjd', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'deployment_button_nonce' => $button_nonce,
        ]);
    }

    /**
     * Add a "trigger webhook" button to the admin bar
     *
     * @param object $bar
     * @return void
     */
    public static function adminBarTriggerButton($bar)
    {
        $option = cache_clear_webhook_get_options();

        $bar->add_node([
            'id' => 'wp-cache-clear-webhook',
            'title' => 'Clear Cache <svg aria-hidden="true" focusable="false" data-icon="upload" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M296 384h-80c-13.3 0-24-10.7-24-24V192h-87.7c-17.8 0-26.7-21.5-14.1-34.1L242.3 5.7c7.5-7.5 19.8-7.5 27.3 0l152.2 152.2c12.6 12.6 3.7 34.1-14.1 34.1H320v168c0 13.3-10.7 24-24 24zm216-8v112c0 13.3-10.7 24-24 24H24c-13.3 0-24-10.7-24-24V376c0-13.3 10.7-24 24-24h136v8c0 30.9 25.1 56 56 56h80c30.9 0 56-25.1 56-56v-8h136c13.3 0 24 10.7 24 24zm-124 88c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20zm64 0c0-11-9-20-20-20s-20 9-20 20 9 20 20 20 20-9 20-20z"></path></svg>',
            'parent' => 'top-secondary',
            'href' => 'javascript:void(0)',
            'meta' => [
                'class' => 'wp-cache-clear-webhook-button'
            ]
        ]);
    }

    /**
     * Trigger a request manually from the admin settings
     *
     * @return void
     */
    public static function trigger()
    {
        check_ajax_referer('wp-cache-clear-webhook-button-nonce', 'security');

        self::fireWebhook("all", "");

        echo 1;
        exit;
    }

    /**
     * Fire off a request to the webhook
     *
     * @return WP_Error|array
     */
    public static function fireWebhook($resource, $name)
    {
        $webhook = cache_clear_webhook_get_webhook_url();

        if (!$webhook) {
            return;
        }

        if (false === filter_var($webhook, FILTER_VALIDATE_URL)) {
            return;
        }

        $method = cache_clear_webhook_get_webhook_method();

        do_action('cache_clear_webhook_before_fire_webhook');

        if ($method === 'get') {
            $args = array(
                'headers' => array(
                    cache_clear_webhook_get_webhook_header() => cache_clear_webhook_get_webhook_key()
                ),
            );

            $return = wp_safe_remote_get($webhook, $args);
        } else {
            $body = array(
                'resource' => $resource,
                'name' => $name
            );

            $post_args = array(
                'headers' => array(
                    'Content-Type' => 'application/json; charset=utf-8',
                    cache_clear_webhook_get_webhook_header() => cache_clear_webhook_get_webhook_key()
                ),
                'body' => json_encode($body),
                'data_format' => 'body'
            );

            $return = wp_safe_remote_post($webhook, $post_args);
        }

        do_action('cache_clear_webhook_after_fire_webhook');

        return $return;
    }
}
