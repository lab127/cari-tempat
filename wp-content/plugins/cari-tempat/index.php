<?php

/*
Plugin Name: Cari tempat 
Description: Cari tempat pakai google map
Author: Ronie Rush
Version: 1.0
Author URI: https://lab127.github.io
*/

/**
 * Add Menus
 */

add_action('admin_menu', 'tempat2_admin_page');
function tempat2_admin_page()
{
  add_menu_page(
    'Tempat2 Admin',
    'Tempat2 Admin',
    'manage_options',
    'ch-tempat2-options',
    'tempat2_tab_index'
  );
}

add_action('admin_init', 'tempat_setting_option');
function tempat_setting_option()
{

  add_settings_section(
    'tempat2_api_key',
    'API Setting',
    'callback_section_fn',
    '_secret_api_key'
  );

  add_settings_field(
    'id_api_key',
    'API Key',
    'callback_setting_field',
    '_secret_api_key',
    'tempat2_api_key',
    array(
      'api_key'
    )
  );

  register_setting('_secret_api_key', '_secret_api_key');
}

/**
 * Call Back
 */
function callback_section_fn()
{
  echo '<p>API Display Setting:</p>';
}


function callback_setting_field($args)
{

  $options = get_option('_secret_api_key');

  $input_value = ($options) ? $options[''  . $args[0] . ''] : "";

  echo '<input type="text" id="'  . $args[0] . '" name="_secret_api_key['  . $args[0] . ']" placeholder="api-key" value="' . $input_value . '"></input>';
}

function fetch_map_content()
{
  include plugin_dir_path(__FILE__) . 'components/fetch-map-options.php';
}

function api_setting_content()
{
  include plugin_dir_path(__FILE__) . 'components/api-setting.php';
}

/**
 * Display Page
 */

function tempat2_tab_index()
{
  if (!current_user_can('manage_options')) {
    return;
  }
?>
  <div class="wrap">
    <div id="icon-themes" class="icon32"></div>
    <h2>Tempat2 Admin Options</h2>
    <?php settings_errors(); ?>

    <?php
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'fetch_map_options';
    ?>

    <h2 class="nav-tab-wrapper">
      <a href="?page=ch-tempat2-options&tab=fetch_map_options" class="nav-tab <?php echo $active_tab == 'fetch_map_options' ? 'nav-tab-active' : ''; ?>">Fetch Map</a>
      <a href="?page=ch-tempat2-options&tab=api_page_setting" class="nav-tab <?php echo $active_tab == 'api_page_setting' ? 'nav-tab-active' : ''; ?>">API Setting</a>
    </h2>

    <?php
    switch ($active_tab) {
      case 'api_page_setting':
        api_setting_content();
        break;
      case 'fetch_map_options':
        fetch_map_content();
        break;

      default:
        api_setting_content();
        break;
    }

    ?>

  </div>
<?php
}
