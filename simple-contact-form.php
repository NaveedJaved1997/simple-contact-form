<?php
/*
Plugin Name: Simple Contact Form
Description: A simple contact form that saves submissions to the database and displays them in the admin dashboard.
Version: 1.1
Author: NAVEED JAVED
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class SimpleContactForm {

    public function __construct() {
        // 1. Create Database Table on Activation
        register_activation_hook( __FILE__, array( $this, 'create_table' ) );

        // 2. Register Shortcode
        add_shortcode( 'simple_contact_form', array( $this, 'render_form' ) );

        // 3. Add Admin Menu
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
    }

    /**
     * Create the DB Table
     */
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
     * Add Menu Item to WordPress Dashboard
     */
    public function add_admin_menu() {
        add_menu_page(
            'Contact Form Submissions', // Page Title
            'Contact Submissions',      // Menu Title
            'manage_options',           // Capability (Admins only)
            'simple-contact-form',      // Menu Slug
            array( $this, 'render_admin_page' ), // Callback function
            'dashicons-email',          // Icon
            26                          // Position
        );
    }

    /**
     * Display the Admin Page (List of Submissions + Instructions)
     */
    public function render_admin_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'simple_contact_form';
        
        // Fetch results from DB
        $results = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY submitted_at DESC" );
        ?>
        <div class="wrap">
            <h1>Contact Form Submissions</h1>
            
            <div style="background: #fff; border-left: 4px solid #46b450; padding: 15px; margin: 20px 0; box-shadow: 0 1px 1px rgba(0,0,0,0.1);">
                <h3>How to use this plugin:</h3>
                <p>Copy and paste this shortcode onto any Page or Post to display the form:</p>
                <code style="font-size: 1.2em; background: #f0f0f1; padding: 5px;">[simple_contact_form]</code>
            </div>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th width="50">ID</th>
                        <th width="150">Date</th>
                        <th width="150">Name</th>
                        <th width="200">Email</th>
                        <th>Message</th>
                    </tr>
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
                        <tr>
                            <td colspan="5">No submissions yet. Go ahead and test the form!</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Render the Form (Front-end)
     */
    public function render_form() {
        ob_start();

        if ( isset( $_POST['scf_submit'] ) ) {
            $this->process_form();
        }

        ?>
        <div class="scf-container" style="max-width: 500px; padding: 20px; border: 1px solid #ddd; background: #f9f9f9; border-radius: 5px;">
            <form method="post" action="">
                <p>
                    <label>Name</label><br>
                    <input type="text" name="scf_name" required style="width: 100%;">
                </p>
                <p>
                    <label>Email</label><br>
                    <input type="email" name="scf_email" required style="width: 100%;">
                </p>
                <p>
                    <label>Message</label><br>
                    <textarea name="scf_message" required style="width: 100%; height: 100px;"></textarea>
                </p>
                <p>
                    <input type="submit" name="scf_submit" value="Send Message" class="button button-primary">
                </p>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Process Form Data
     */
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
        
        echo '<div style="color: green; margin-bottom: 10px;">Thanks! Your message has been sent.</div>';
    }
}

new SimpleContactForm();