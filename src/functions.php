<?php

if (!function_exists('jamstack_webhook_get_options')) {
    /**
     * Return the plugin settings/options
     *
     * @return array
     */
    function jamstack_webhook_get_options() {
        return get_option(THEROYALS_JAMSTACK_WEBHOOK_OPTIONS_KEY);
    }
}

if (!function_exists('jamstack_webhook_get_webhook_url')) {
    /**
     * Return the webhook url
     *
     * @return string|null
     */
    function jamstack_webhook_get_webhook_url() {
        $options = jamstack_webhook_get_options();

        if (isset($_SERVER['WP_JAMSTACK_WEBHOOK_URL'])) {
            return $_SERVER['WP_JAMSTACK_WEBHOOK_URL'];
        }

        return isset($options['webhook_url']) ? $options['webhook_url'] : null;
    }
}

if (!function_exists('jamstack_webhook_get_webhook_key')) {
    /**
     * Return the webhook url
     *
     * @return string|null
     */
    function jamstack_webhook_get_webhook_key() {
        $options = jamstack_webhook_get_options();

        if (isset($_SERVER['WP_JAMSTACK_WEBHOOK_KEY'])) {
            return $_SERVER['WP_JAMSTACK_WEBHOOK_KEY'];
        }

        return isset($options['webhook_secret_key']) ? $options['webhook_secret_key'] : null;
    }
}

if (!function_exists('jamstack_webhook_get_webhook_method')) {
    /**
     * Return the webhook method (get/post)
     *
     * @return string
     */
    function jamstack_webhook_get_webhook_method() {
        $options = jamstack_webhook_get_options();
        $method = isset($options['webhook_method']) ? $options['webhook_method'] : 'post';
        return mb_strtolower($method);
    }
}

if (!function_exists('jamstack_webhook_fire_webhook')) {
    /**
     * Fire a request to the webhook.
     *
     * @return void
     */
    function jamstack_webhook_fire_webhook() {
        \Theroyals\JAMstackWebhook\WebhookTrigger::fireWebhook();
    }
}

if (!function_exists('jamstack_webhook_force_fire_webhook')) {
    /**
     * Fire a request to the webhook immediately.
     *
     * @return void
     */
    function jamstack_webhook_force_fire_webhook() {
        \Theroyals\JAMstackWebhook\WebhookTrigger::fireWebhook();
    }
}

if (!function_exists('jamstack_webhook_fire_webhook_save_post')) {
    /**
     * Fire a request to the webhook when a post has been saved.
     *
     * @param int $id
     * @param WP_Post $post
     * @param boolean $update
     * @return void
     */
    function jamstack_webhook_fire_webhook_save_post($id, $post, $update) {
        \Theroyals\JAMstackWebhook\WebhookTrigger::triggerSavePost($id, $post, $update);
    }
    add_action('save_post', 'jamstack_webhook_fire_webhook_save_post', 10, 3);
}

if (!function_exists('jamstack_webhook_fire_webhook_created_term')) {
    /**
     * Fire a request to the webhook when a term has been created.
     *
     * @param int $id
     * @param int $post
     * @param string $tax_slug
     * @return void
     */
    function jamstack_webhook_fire_webhook_created_term($id, $tax_id, $tax_slug) {
        \Theroyals\JAMstackWebhook\WebhookTrigger::triggerSaveTerm($id, $tax_id, $tax_slug);
    }
    add_action('created_term', 'jamstack_webhook_fire_webhook_created_term', 10, 3);
}

if (!function_exists('jamstack_webhook_fire_webhook_delete_term')) {
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
    function jamstack_webhook_fire_webhook_delete_term($id, $tax_id, $tax_slug, $term, $object_ids) {
        \Theroyals\JAMstackWebhook\WebhookTrigger::triggerSaveTerm($id, $tax_id, $tax_slug, $term, $object_ids);
    }
    add_action('delete_term', 'jamstack_webhook_fire_webhook_delete_term', 10, 5);
}

if (!function_exists('jamstack_webhook_fire_webhook_edit_term')) {
    /**
     * Fire a request to the webhook when a term has been modified.
     *
     * @param int $id
     * @param int $post
     * @param string $tax_slug
     * @return void
     */
    function jamstack_webhook_fire_webhook_edit_term($id, $tax_id, $tax_slug) {
        \Theroyals\JAMstackWebhook\WebhookTrigger::triggerEditTerm($id, $tax_id, $tax_slug);
    }
    add_action('edit_term', 'jamstack_webhook_fire_webhook_edit_term', 10, 3);
}
