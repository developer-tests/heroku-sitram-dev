<?php
/**
 * Plugin Name: Poll, Survey & Form Builder by Zigpoll
 * Plugin URI: https://www.zigpoll.com/integrations/wordpress
 * Description: Add a super engaging survey, poll, post-purchase survey, or contact-us form to your site. You can easily add the survey, poll or form to any post or page.
 * Version: 1.1
 * Author: Zigpoll.com
 * Author URI: https://www.zigpoll.com
 */

/* Enqueue Scripts */
function zigpoll_add_scripts() {
  $account_id = esc_attr(get_option('account_id', ''));

  wp_enqueue_script('zigpoll', 'https://cdn.zigpoll.com/zigpoll-wordpress-embed.js?accountId='.$account_id);
}

add_action( 'wp_enqueue_scripts', 'zigpoll_add_scripts' );

/* Settings page */
function zigpoll_settings_page() {
  add_menu_page(
    'Zigpoll Settings', // title of the settings page
    'Zigpoll', // title of the submenu
    'manage_options', // capability of the user to see this page
    'zigpoll-settings', // slug of the settings page
    'zigpoll_settings_page_html', // callback function to be called when rendering the page
    'dashicons-admin-settings'
  );
  add_action('admin_init', 'zigpoll_settings_init');
}
add_action('admin_menu', 'zigpoll_settings_page');

function zigpoll_settings_init() {

  add_settings_section(
    'settings-section', // id of the section
    '', // title to be displayed
    'zigpoll_section_cb', // callback function to be called when opening section
    'zigpoll-settings' // page on which to display the section, this should be the same as the slug used in add_submenu_page()
  );

  // register the setting
  register_setting(
    'zigpoll-settings', // option group
    'account_id'
  );

  add_settings_field(
    'accound_id', // id of the settings field
    'Account ID', // title
    'zigpoll_settings_cb', // callback function
    'zigpoll-settings', // page on which settings display
    'settings-section' // section on which to show settings
  );
}

function zigpoll_section_cb() {
  ?>
    <p>Add your Account ID and your <a href="https://app.zigpoll.com" target="_blank">Zigpoll</a> will be embedded into all of your pages.<br/>For help finding your Account ID, please <a href="https://docs.zigpoll.com/wordpress" target="_blank">see our wordpress installation guide</a>.</p>
  <?php
}

function zigpoll_settings_cb() {
  $account_id = esc_attr(get_option('account_id', ''));
  ?>
    <input id="title" type="text" name="account_id" class="regular-text" value="<?php echo $account_id; ?>">
    <p class="description"><a target="_blank" href="https://docs.zigpoll.com/accounts/general-settings#account-id">Need help finding your Account ID?</a></p>
  <?php
}

function zigpoll_settings_page_html() {
  // check user capabilities
  if (!current_user_can('manage_options')) {
    return;
  }
  ?>
    <div class="wrap">
      <h1><img src="<?php echo plugin_dir_url( __FILE__) . 'assets/zigpoll-icon.png' ?>" style="height: 30px; width: auto; margin-right: 15px; vertical-align: middle; top: -2px; position: relative;"/><?php echo esc_html( get_admin_page_title() ); ?></h1>

      <?php settings_errors();?>
      <form method="POST" action="options.php">
      <?php settings_fields('zigpoll-settings') ?>
      <?php do_settings_sections('zigpoll-settings') ?>
      <?php submit_button();?>
      </form>
    </div>
    

    <?php
}
