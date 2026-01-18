# Simple Contact Form Plugin

A custom WordPress plugin that provides a lightweight contact form via shortcode. It handles form submission by sanitizing user input, sending an email notification to the administrator, and persisting the data to a custom MySQL table for permanent record-keeping.

## 🚀 Features

* **Shortcode Support:** Easily embed the form anywhere using `[simple_contact_form]`.
* **Database Persistence:** Automatically creates a custom SQL table upon activation to store all messages.
* **Admin Dashboard:** Includes a dedicated menu item ("Contact Submissions") to view a list of all received messages directly in the WordPress backend.
* **Email Notifications:** Sends an immediate email notification to the site administrator upon submission.
* **Security:** Implements rigorous data sanitization (`sanitize_text_field`, `sanitize_email`) and SQL escaping via `$wpdb`.

## 🛠️ Technical Details

* **Language:** PHP (Object-Oriented Structure)
* **Database:** Custom MySQL Table (`wp_simple_contact_form`) created using `dbDelta()`.
* **WordPress APIs Used:**
    * Shortcode API
    * Database API (`$wpdb`)
    * Options API
    * Admin Menu API (`add_menu_page`)

## 📦 Installation

1.  Download or clone this repository into your plugins folder:
    ```path
    /wp-content/plugins/simple-contact-form
    ```
2.  Log in to your WordPress Dashboard.
3.  Go to **Plugins** and activate **Simple Contact Form**.
4.  *Note: Upon activation, the plugin will automatically create the necessary database table.*

## ⚙️ Usage

### Displaying the Form
Add the following shortcode to any Page, Post, or Widget area:

```text
[simple_contact_form]
