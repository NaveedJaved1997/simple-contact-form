# Simple Contact Form Plugin

A custom WordPress plugin that provides a lightweight contact form via shortcode. It features a complete database submission system and a **custom styling dashboard** that allows admins to customize colors without coding.

## 🚀 Features

* **Shortcode Support:** Easily embed the form anywhere using `[simple_contact_form]`.
* **Database Persistence:** Automatically saves all messages to a custom MySQL table.
* **Email Notifications:** Sends instant admin alerts on new submissions.
* **🎨 Style Customizer:** Includes a Settings API integration with **Color Pickers** to change:
    * Form Background Color
    * Font Color
    * Input Field Background
    * Button Color
* **Admin Dashboard:** View all submissions in a clean table view under "Contact Submissions".
* **Security:** Rigorous data sanitization (`sanitize_text_field`) and SQL escaping.

## 🛠️ Technical Details

* **Language:** PHP (Object-Oriented)
* **APIs Used:**
    * Settings API (register_setting)
    * Options API (get_option)
    * Database API ($wpdb)
    * Shortcode API
    * WordPress Color Picker (wp-color-picker script)

## 📦 Installation

1.  Download or clone this repository into:
    ```path
    /wp-content/plugins/simple-contact-form
    ```
2.  Activate the plugin in WordPress.
3.  The database table is created automatically.

## ⚙️ Usage

### 1. Embed the Form
Paste this shortcode on any Page or Post:
```text
[simple_contact_form]