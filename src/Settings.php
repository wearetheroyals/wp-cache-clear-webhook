<?php

namespace Theroyals\JAMstackWebhook;

class Settings
{
    /**
     * Setup required hooks for the Settings
     *
     * @return void
     */
    public static function init()
    {
        add_action('admin_init', [__CLASS__, 'register']);
    }

    /**
     * Register settings & fields
     *
     * @return void
     */
    public static function register()
    {
        $key = THEROYALS_JAMSTACK_WEBHOOK_OPTIONS_KEY;

        register_setting($key, $key, [__CLASS__, 'sanitize']);
        add_settings_section('general', 'General', '__return_empty_string', $key);

        // ...

        $option = jamstack_webhook_get_options();

        add_settings_field('webhook_url', 'Webhook URL', ['Theroyals\JAMstackWebhook\Field', 'input'], $key, 'general', [
            'name' => "{$key}[webhook_url]",
            'value' => jamstack_webhook_get_webhook_url(),
            'description' => 'Your webhook URL. This will be static if the "WP_JAMSTACK_WEBHOOK_URL" environment variable is set.',
            'disabled' => isset($_SERVER['WP_JAMSTACK_WEBHOOK_URL']),
            'type' => 'url'
        ]);

        add_settings_field('webhook_secret_key', 'Webhook Secret Key', ['Theroyals\JAMstackWebhook\Field', 'input'], $key, 'general', [
            'name' => "{$key}[webhook_secret_key]",
            'value' => jamstack_webhook_get_webhook_key(),
            'description' => '(Optional) A secret key to send with your webhook POST. This will be static if the "WP_JAMSTACK_WEBHOOK_KEY" environment variable is set.',
            'disabled' => isset($_SERVER['WP_JAMSTACK_WEBHOOK_KEY']),
            'type' => 'password'
        ]);

        add_settings_field('webhook_method', 'Webhook Method', ['Theroyals\JAMstackWebhook\Field', 'select'], $key, 'general', [
            'name' => "{$key}[webhook_method]",
            'value' => jamstack_webhook_get_webhook_method(),
            'choices' => [
                'post' => 'POST',
                'get' => 'GET'
            ],
            'default' => 'post',
            'description' => 'Set either GET or POST for the webhook request. Defaults to POST.'
        ]);

        add_settings_field('webhook_post_types', 'Post Types', ['Theroyals\JAMstackWebhook\Field', 'checkboxes'], $key, 'general', [
            'name' => "{$key}[webhook_post_types]",
            'value' => isset($option['webhook_post_types']) ? $option['webhook_post_types'] : [],
            'choices' => self::getPostTypes(),
            'description' => 'Only selected post types will trigger a deployment when created, updated or deleted.',
            'legend' => 'Post Types'
        ]);

        add_settings_field('webhook_taxonomies', 'Taxonomies', ['Theroyals\JAMstackWebhook\Field', 'checkboxes'], $key, 'general', [
            'name' => "{$key}[webhook_taxonomies]",
            'value' => isset($option['webhook_taxonomies']) ? $option['webhook_taxonomies'] : [],
            'choices' => self::getTaxonomies(),
            'description' => 'Only selected taxonomies will trigger a deployment when their terms are created, updated or deleted.',
            'legend' => 'Taxonomies'
        ]);
    }

    /**
     * Get an array of post types in name > label format
     *
     * @return array
     */
    protected static function getPostTypes()
    {
        $return = [];

        foreach (get_post_types(null, 'objects') as $choice) {
            $return[$choice->name] = $choice->labels->name;
        }

        return $return;
    }

    /**
     * Get an array of taxonomies in name > label format
     *
     * @return array
     */
    protected static function getTaxonomies()
    {
        $return = [];

        foreach (get_taxonomies(null, 'objects') as $choice) {
            $return[$choice->name] = $choice->labels->name;
        }

        return $return;
    }

    /**
     * Sanitize user input
     *
     * @var array $input
     * @return array
     */
    public static function sanitize($input)
    {
        if (!empty($input['webhook_url'])) {
            $input['webhook_url'] = sanitize_text_field($input['webhook_url']);
        }

        if (isset($input['webhook_method']) && !in_array($input['webhook_method'], ['get', 'post'])) {
            $input['webhook_method'] = 'post';
        }

        if (!isset($input['webhook_post_types']) || !is_array($input['webhook_post_types'])) {
            $input['webhook_post_types'] = [];
        }

        if (!isset($input['webhook_taxonomies']) || !is_array($input['webhook_taxonomies'])) {
            $input['webhook_taxonomies'] = [];
        }

        return $input;
    }
}
