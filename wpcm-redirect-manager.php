<?php
/*
Plugin Name: WPCM Redirect Manager
Plugin URI: https://rd5.com.br
Description: Professional 301 redirect manager for WordPress - Automatically handles redirects when post slugs change or content is deleted.
Version: 1.7
Author: Daniel Oliveira da Paixão
Author URI: https://rd5.com.br
License: GPL v2 or later
Text Domain: wpcm-redirect-manager
Domain Path: /languages
*/

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WPCM_REDIRECT_VERSION', '1.7');
define('WPCM_REDIRECT_PATH', plugin_dir_path(__FILE__));
define('WPCM_REDIRECT_URL', plugin_dir_url(__FILE__));
define('WPCM_REDIRECT_TABLE', 'wpcm_redirects');

class WPCM_Redirect_Manager {
    private static $instance = null;
    private $table_name;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . WPCM_REDIRECT_TABLE;

        // Initialization hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Action hooks
        add_action('init', array($this, 'load_textdomain'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('template_redirect', array($this, 'handle_redirects'));
        add_action('post_updated', array($this, 'check_for_slug_change'), 10, 3);
        add_action('admin_notices', array($this, 'show_delete_prompt'));

        // Filtros para validação e limpeza
        add_filter('wpcm_validate_url', array($this, 'validate_url'), 10, 1);
    }

    public function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            from_url text NOT NULL,
            to_url text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            hits int DEFAULT 0,
            last_accessed datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            INDEX from_url_idx (from_url(191))
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Versioning
        add_option('wpcm_redirect_version', WPCM_REDIRECT_VERSION);
    }

    public function deactivate() {
        // Não vamos deletar a tabela na desativação para preservar os dados
        // Em vez disso, adicionamos uma opção para limpar dados no desinstall
    }

    public function load_textdomain() {
        load_plugin_textdomain('wpcm-redirect-manager', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public function handle_redirects() {
        if (is_admin()) return;

        global $wpdb;
        $requested_url = esc_url_raw(home_url($_SERVER['REQUEST_URI']));

        $redirect = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE from_url = %s",
            $requested_url
        ));

        if ($redirect) {
            // Atualiza estatísticas
            $wpdb->update(
                $this->table_name,
                array(
                    'hits' => $redirect->hits + 1,
                    'last_accessed' => current_time('mysql')
                ),
                array('id' => $redirect->id)
            );

            wp_redirect($redirect->to_url, 301);
            exit;
        }
    }

    public function check_for_slug_change($post_ID, $post_after, $post_before) {
        if ($post_before->post_name !== $post_after->post_name) {
            $old_url = get_permalink($post_before);
            $new_url = get_permalink($post_after);
            $this->add_redirect($old_url, $new_url);
        }
    }

    public function add_redirect($from_url, $to_url) {
        global $wpdb;

        $from_url = apply_filters('wpcm_validate_url', $from_url);
        $to_url = apply_filters('wpcm_validate_url', $to_url);

        if (!$from_url || !$to_url) {
            return false;
        }

        return $wpdb->insert(
            $this->table_name,
            array(
                'from_url' => $from_url,
                'to_url'   => $to_url,
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s')
        );
    }

    public function validate_url($url) {
        $url = esc_url_raw($url);
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }
        return false;
    }

    public function add_admin_menu() {
        add_menu_page(
            __('WPCM Redirects', 'wpcm-redirect-manager'),
            __('WPCM Redirects', 'wpcm-redirect-manager'),
            'manage_options',
            'wpcm_redirect_manager',
            array($this, 'render_admin_page'),
            'dashicons-randomize',
            81
        );
    }

    public function enqueue_admin_assets($hook) {
        if ('toplevel_page_wpcm_redirect_manager' !== $hook) {
            return;
        }

        wp_enqueue_style(
            'wpcm-redirect-admin',
            WPCM_REDIRECT_URL . 'css/admin-style.css',
            array(),
            WPCM_REDIRECT_VERSION
        );

        wp_enqueue_script(
            'wpcm-redirect-admin',
            WPCM_REDIRECT_URL . 'js/admin-script.js',
            array('jquery'),
            WPCM_REDIRECT_VERSION,
            true
        );
    }

    public function show_delete_prompt() {
        if (isset($_GET['action']) && $_GET['action'] == 'trash' && isset($_GET['post'])) {
            $post_id = intval($_GET['post']);
            $old_url = get_permalink($post_id);

            printf(
                '<div class="notice notice-warning is-dismissible"><p>%s <a href="%s">%s</a></p></div>',
                esc_html__('Post moved to trash. Set up a redirect?', 'wpcm-redirect-manager'),
                esc_url(admin_url('admin.php?page=wpcm_redirect_manager&from_url=' . urlencode($old_url))),
                esc_html__('Yes, configure redirect', 'wpcm-redirect-manager')
            );
        }
    }

    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        // Handle form submissions
        if (isset($_POST['wpcm_add_redirect']) && check_admin_referer('wpcm_redirect_nonce')) {
            $from_url = isset($_POST['from_url']) ? $_POST['from_url'] : '';
            $to_url = isset($_POST['to_url']) ? $_POST['to_url'] : '';

            if ($this->add_redirect($from_url, $to_url)) {
                add_settings_error(
                    'wpcm_redirect_notices',
                    'redirect_added',
                    __('Redirect added successfully.', 'wpcm-redirect-manager'),
                    'success'
                );
            }
        }

        // Handle deletions
        if (isset($_GET['action']) && $_GET['action'] == 'delete'
            && isset($_GET['redirect_id']) && check_admin_referer('delete_redirect')) {
            global $wpdb;
            $redirect_id = intval($_GET['redirect_id']);
            $wpdb->delete($this->table_name, array('id' => $redirect_id));

            add_settings_error(
                'wpcm_redirect_notices',
                'redirect_deleted',
                __('Redirect deleted successfully.', 'wpcm-redirect-manager'),
                'success'
            );
        }

        // Fetch redirects with pagination
        $per_page = 20;
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($current_page - 1) * $per_page;

        global $wpdb;
        $redirects = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name} ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $per_page,
            $offset
        ));

        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM {$this->table_name}");
        $total_pages = ceil($total_items / $per_page);

        // Include the admin template
        include WPCM_REDIRECT_PATH . 'templates/admin-page.php';
    }
}

// Initialize the plugin
function WPCM_Redirect_Manager() {
    return WPCM_Redirect_Manager::get_instance();
}

// Start the plugin
WPCM_Redirect_Manager();
