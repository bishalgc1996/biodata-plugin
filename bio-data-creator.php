<?php

/**
 * Plugin Name: Bio Data Creator
 * Plugin URI: http://bishalgc.com.np/bio-data-creator/
 * Description: Plugin that allows logged in users to create bio data and view it.
 * Version: 1.0
 * Author: Bishal GC
 * Author URI: http://bishalgc.com.np
 * Text Domain: biodatacreator
 * Requires at least: 6.2
 * Requires PHP: 8.2
 *
 * @package BioDataCreator
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}


// require_once plugin_dir_path( __FILE__ ) . 'classes/class-biodata-creator.php';

if (!class_exists('BiodataPlugin')) :


  class BiodataPlugin
  {


    /** Singleton *************************************************************/

    private static $instance;



    private function __construct()
    {
      /* Do nothing here */
    }


    public static function instance()
    {

      if (
        !isset(self::$instance)
        && !(self::$instance instanceof BiodataPlugin)
      ) {
        self::$instance = new BiodataPlugin();
        self::$instance->setup_constants();
        add_action(
          'wp_enqueue_scripts',
          array(self::$instance, 'bdc_enqueue_style')
        );
        add_action(
          'wp_enqueue_scripts',
          array(self::$instance, 'bdc_enqueue_script')
        );
        self::$instance->includes();
        self::$instance->biodatacreator = new BiodataCreator();
        self::$instance->biodatacreatorpending = new BiodataPending();
        self::$instance->biodataviewer = new BiodataViewer();
      }

      return self::$instance;
    }

    /**
     * Setup plugins constants.
     *
     * @access private
     * @return void
     * @since  1.0.0
     */
    private function setup_constants()
    {
      // Plugin version.
      if (!defined('BDC_VERSION')) {
        define('BDC_VERSION', '1.0');
      }

      // Plugin folder Path.
      if (!defined('BDC_PLUGIN_DIR')) {
        define('BDC_PLUGIN_DIR', plugin_dir_path(__FILE__));
      }

      // Plugin folder URL.
      if (!defined('BDC_PLUGIN_URL')) {
        define('BDC_PLUGIN_URL', plugin_dir_url(__FILE__));
      }

      // Plugin root file.
      if (!defined('BDC_PLUGIN_FILE')) {
        define('BDC_PLUGIN_FILE', __FILE__);
      }

      // Options.
      if (!defined('ED_OPTIONS')) {
        define('BDC_OPTIONS', 'event_display_options');
      }
    }


    /**
     * Include required files.
     *
     * @access private
     * @return void
     * @since  1.0.0
     */

    private function includes()
    {
      require_once BDC_PLUGIN_DIR . 'includes/class-biodata-creator.php';
      require_once BDC_PLUGIN_DIR . 'admin/class-pending-biodata.php';
      require_once BDC_PLUGIN_DIR . 'includes/class-biodata-viewer.php';
    }

    public function bdc_enqueue_style()
    {
      $css_dir = BDC_PLUGIN_URL . 'css/';
      //	wp_enqueue_style( 'biodata-creator-front',$css_dir . 'form.css', false, '' );

    }

    /**
     * Enqueue script front-end
     *
     * @access public
     * @return void
     * @since  1.0.0
     */
    public function bdc_enqueue_script()
    {

      $js_dir = BDC_PLUGIN_URL . 'js/';
      //	wp_enqueue_script( 'biodata-creator-front-js', $js_dir . 'script.js', false, '' );

    }
  }

endif;

function run_biodata()
{
  return BiodataPlugin::instance();
}

global $biodata_plugin;
$biodata_plugin = run_biodata();

/**
 * Get Import events setting options
 *
 * @param  string  $type  Option type.
 *
 * @return array|bool Options.
 * @since 1.0
 */
function bdc_get_import_options($type = '')
{
  $bdc_options = get_option(BDC_OPTIONS);
  return $bdc_options;
}


// Define a function to load the custom template file
function load_custom_biodata_template($template)
{
  if (is_singular('biodata')) {
    // Check if the current post is of the 'biodata' post type
    $template_file = 'templates/single-biodata.php';

    if ($template_file) {
      $template = plugin_dir_path(__FILE__) . $template_file;
    }
  }

  return $template;
}

// Hook the function to the 'template_include' filter
add_filter('template_include', 'load_custom_biodata_template');


// Define the custom template loader function for the archive page
function load_custom_biodata_archive_template($template)
{
  if (is_post_type_archive('biodata')) {
    // Check if the current page is the archive page of the 'biodata' post type
    $template_file = 'templates/archive-biodata.php';

    if ($template_file) {
      $template = plugin_dir_path(__FILE__) . $template_file;
    }
  }

  return $template;
}

// Hook the function to the 'template_include' filter
add_filter('template_include', 'load_custom_biodata_archive_template');
// Add this code in your plugin's main file or functions.php




function filter_biodata_cards_callback()
{
  if (isset($_POST['occupation'])) {
    $selectedOccupation = sanitize_text_field($_POST['occupation']);

    $role = 'editor';
    $users = get_users(array('role' => $role));

    $output = '';

    foreach ($users as $user) {
      $user_id = $user->ID;
      $user_details = get_user_meta($user_id, 'biodata_user_details', true);

      // Check if the user has biodata details and the status is approved and matches the selected occupation
      if (
        !empty($user_details) && isset($user_details['status']) && $user_details['status'] === 'approved' &&
        isset($user_details['occupation']) && $user_details['occupation'] === $selectedOccupation
      ) {
        $personal_info = $user_details['personal_info'];
        $education = $user_details['education'];
        $experience = $user_details['experience'];
        $about_me = $user_details['about_me'];
        $occupation = $user_details['occupation'];
        $status = $user_details['status'];

        // Build the HTML output for the biodata card
        $output .= '<div class="biodata-card">';
        $output .= '<div class="biodata-card-header">';
        $output .= '<h3 class="biodata-card-title">' . esc_html($personal_info) . '</h3>';
        $output .= '<span class="biodata-card-occupation">' . esc_html($occupation) . '</span>';
        $output .= '</div>';
        $output .= '<div class="biodata-card-content">';
        $output .= '<p class="biodata-card-details">';
        $output .= '<strong>Education:</strong> ' . esc_html($education) . '<br>';
        $output .= '<strong>Experience:</strong> ' . esc_html($experience) . '<br>';
        $output .= '<strong>About Me:</strong> ' . esc_html($about_me);
        $output .= '</p>';
        $output .= '</div>';
        $output .= '<div class="biodata-card-footer">';
        $output .= '</div>';
        $output .= '</div>';
      }
    }

    echo $output;
  }

  wp_die();
}

// AJAX callback function to filter biodata cards based on occupation
add_action('wp_ajax_filter_biodata_cards', 'filter_biodata_cards_callback');
add_action('wp_ajax_nopriv_filter_biodata_cards', 'filter_biodata_cards_callback');


// Define the plugin deactivation hook
register_deactivation_hook(__FILE__, 'my_plugin_deactivation');

// Deactivation hook callback function
function my_plugin_deactivation()
{
  // Remove the shortcodes when the plugin is deactivated
  remove_shortcode('biodata_register_form');
  remove_shortcode('biodata_login_form');
  // Remove the shortcode when the plugin is deactivated
  remove_shortcode('biodata_user_details_form');
}