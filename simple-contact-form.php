<?php
/*
Plugin Name: Simple Contact Form
Description: A contact form with database submissions and customizable styling options.
Version: 1.2
Author: NAVEED JAVED
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SimpleContactForm {

    public function __construct() {
        // 1. Create Database Table
        register_activation_hook( __FILE__, array( $this, 'create_table' ) );

        // 2. Register Shortcode
        add_shortcode( 'simple_contact_form', array( $this, 'render_form' ) );

        // 3. Add Admin Menus
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

        // 4. Register Settings (NEW)
        add_action( 'admin_init', array( $this, 'register_settings' ) );

        // 5. Load Color Picker Scripts (NEW)
        add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_scripts' ) );
    }

    public function create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'simple_contact_form';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name tinytext NOT NULL,
            email varchar(100) NOT NULL,
            message text NOT NULL,
            submitted_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    /**
     * Add Menu and Submenu
     */
    public function add_admin_menu() {
        // Main Menu (Submissions)
        add_menu_page(
            'Contact Form Submissions', 
            'Contact Submissions', 
            'manage_options', 
            'simple-contact-form', 
            array( $this, 'render_submissions_page' ), 
            'dashicons-email', 
            26
        );

        // Submenu (Settings) - NEW
        add_submenu_page(
            'simple-contact-form',       // Parent slug
            'Form Styling Settings',     // Page Title
            'Settings',                  // Menu Title
            'manage_options',            // Capability
            'simple-contact-form-settings', // Slug
            array( $this, 'render_settings_page' ) // Callback
        );
    }

    /**
     * NEW: Load WordPress Color Picker styles/scripts
     */
    public function load_admin_scripts( $hook ) {
        // Only load on our settings page
        if ( 'contact-submissions_page_simple-contact-form-settings' !== $hook ) {
            return;
        }
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'scf-script', false, array( 'wp-color-picker' ), false, true );
    }

    /**
     * NEW: Register the 4 styling options
     */
    public function register_settings() {
        register_setting( 'scf_options_group', 'scf_styles' );
    }

    /**
     * NEW: Render the Settings Page with Color Pickers
     */
    public function render_settings_page() {
        // Get saved options (or defaults)
        $options = get_option( 'scf_styles' );
        $bg_color = isset($options['bg_color']) ? $options['bg_color'] : '#f9f9f9';
        $text_color = isset($options['text_color']) ? $options['text_color'] : '#333333';
        $field_bg = isset($options['field_bg']) ? $options['field_bg'] : '#ffffff';
        $btn_color = isset($options['btn_color']) ? $options['btn_color'] : '#2271b1';
        ?>
        <div class="wrap">
            <h1>Contact Form Styling</h1>
            <form method="post" action="options.php">
                <?php 
                settings_fields( 'scf_options_group' ); 
                do_settings_sections( 'simple-contact-form-settings' );
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Form Background Color</th>
                        <td><input type="text" name="scf_styles[bg_color]" value="<?php echo esc_attr( $bg_color ); ?>" class="my-color-field" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Font Color</th>
                        <td><input type="text" name="scf_styles[text_color]" value="<?php echo esc_attr( $text_color ); ?>" class="my-color-field" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Input Fields Background</th>
                        <td><input type="text" name="scf_styles[field_bg]" value="<?php echo esc_attr( $field_bg ); ?>" class="my-color-field" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Button Color</th>
                        <td><input type="text" name="scf_styles[btn_color]" value="<?php echo esc_attr( $btn_color ); ?>" class="my-color-field" /></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
            
            <script>
            jQuery(document).ready(function($){
                $('.my-color-field').wpColorPicker();
            });
            </script>
        </div>
        <?php
    }

    public function render_submissions_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'simple_contact_form';
        $results = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY submitted_at DESC" );
        ?>
        <div class="wrap">
            <h1>Contact Form Submissions</h1>
            <div style="background: #fff; border-left: 4px solid #46b450; padding: 15px; margin: 20px 0; box-shadow: 0 1px 1px rgba(0,0,0,0.1);">
                <h3>Usage:</h3>
                <p>Use this shortcode: <code style="font-size: 1.2em; background: #f0f0f1; padding: 5px;">[simple_contact_form]</code></p>
                <p><a href="<?php echo admin_url('admin.php?page=simple-contact-form-settings'); ?>">Click here to customize colors</a></p>
            </div>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr><th width="50">ID</th><th width="150">Date</th><th width="150">Name</th><th width="200">Email</th><th>Message</th></tr>
                </thead>
                <tbody>
                    <?php if ( ! empty( $results ) ) : ?>
                        <?php foreach ( $results as $row ) : ?>
                            <tr>
                                <td><?php echo esc_html( $row->id ); ?></td>
                                <td><?php echo esc_html( $row->submitted_at ); ?></td>
                                <td><strong><?php echo esc_html( $row->name ); ?></strong></td>
                                <td><a href="mailto:<?php echo esc_attr( $row->email ); ?>"><?php echo esc_html( $row->email ); ?></a></td>
                                <td><?php echo nl2br( esc_html( $row->message ) ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr><td colspan="5">No submissions yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public function render_form() {
        ob_start();
        
        // Handle Submission
        if ( isset( $_POST['scf_submit'] ) ) {
            $this->process_form();
        }

        // NEW: Fetch Custom Styles
        $options = get_option( 'scf_styles' );
        $bg_color   = !empty($options['bg_color'])   ? $options['bg_color']   : '#f9f9f9';
        $text_color = !empty($options['text_color']) ? $options['text_color'] : '#333333';
        $field_bg   = !empty($options['field_bg'])   ? $options['field_bg']   : '#ffffff';
        $btn_color  = !empty($options['btn_color'])  ? $options['btn_color']  : '#2271b1';

        // NEW: Output CSS Variables
        ?>
        <style>
            .scf-container {
                background-color: <?php echo esc_attr($bg_color); ?> !important;
                color: <?php echo esc_attr($text_color); ?> !important;
                border: 1px solid #ddd;
                padding: 20px;
                border-radius: 5px;
                max-width: 500px;
            }
            .scf-container input[type="text"], 
            .scf-container input[type="email"], 
            .scf-container textarea {
                background-color: <?php echo esc_attr($field_bg); ?> !important;
                color: <?php echo esc_attr($text_color); ?> !important;
                width: 100%;
                padding: 8px;
                margin-bottom: 10px;
                border: 1px solid #ccc;
            }
            .scf-container input[type="submit"] {
                background-color: <?php echo esc_attr($btn_color); ?> !important;
                color: #ffffff !important;
                padding: 10px 20px;
                border: none;
                cursor: pointer;
            }
            .scf-container input[type="submit"]:hover {
                opacity: 0.9;
            }
        </style>

        <div class="scf-container">
            <form method="post" action="">
                <p>
                    <label>Name</label><br>
                    <input type="text" name="scf_name" required>
                </p>
                <p>
                    <label>Email</label><br>
                    <input type="email" name="scf_email" required>
                </p>
                <p>
                    <label>Message</label><br>
                    <textarea name="scf_message" required style="height: 100px;"></textarea>
                </p>
                <p>
                    <input type="submit" name="scf_submit" value="Send Message">
                </p>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    private function process_form() {
        global $wpdb;
        $name    = sanitize_text_field( $_POST['scf_name'] );
        $email   = sanitize_email( $_POST['scf_email'] );
        $message = sanitize_textarea_field( $_POST['scf_message'] );
        $table_name = $wpdb->prefix . 'simple_contact_form';
        
        $wpdb->insert( 
            $table_name, 
            array( 
                'name' => $name, 
                'email' => $email, 
                'message' => $message,
                'submitted_at' => current_time( 'mysql' ) 
            ) 
        );
        
        // Email Admin
        wp_mail( get_option('admin_email'), 'New Contact Submission', "Name: $name\nMessage: $message" );

        echo '<div style="color: green; margin-bottom: 10px;">Thanks! Your message has been sent.</div>';
    }
}

new SimpleContactForm();