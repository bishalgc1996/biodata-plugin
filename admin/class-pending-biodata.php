<?php

/* Class to implement biodata pending */



// Create a BiodataPending class

class BiodataPending
{
  private $post_type = 'biodata';
  private $pending_status = 'pending';

  public function __construct()
  {
    add_action('init', array($this, 'register_pending_biodata_status'));
    add_action('admin_menu', array($this, 'add_biodata_pending_page'));
    add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
    add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    add_action('admin_footer', array($this, 'localize_script'));
    add_action('wp_ajax_approve_user', array($this, 'approve_user_callback'));
    add_action('wp_ajax_nopriv_approve_user', array($this, 'approve_user_callback'));
  }

  // Register the pending biodata status
  public function register_pending_biodata_status()
  {
    register_post_status($this->pending_status, array(
      'label'                     => _x('Pending Biodata', 'post'),
      'public'                    => false,
      'exclude_from_search'       => true,
      'show_in_admin_all_list'    => true,
      'show_in_admin_status_list' => true,
      'label_count'               => _n_noop('Pending Biodata <span class="count">(%s)</span>', 'Pending Biodata <span class="count">(%s)</span>'),
    ));
  }

  // Add the pending biodata admin page
  public function add_biodata_pending_page()
  {
    add_submenu_page(
      'edit.php?post_type=' . $this->post_type,
      'Pending Biodata',
      'Pending Biodata',
      'manage_options',
      'biodata-pending',
      array($this, 'display_biodata_pending_page')
    );
  }

  // Display the pending biodata admin page
  public function display_biodata_pending_page()
  {

    $role = 'editor';


    $users = get_users(array('role' => $role));



    echo '<table class="custom-table">';
    echo '<thead><tr><th>Personal Info</th><th>Education</th><th>Experience</th><th>About Me</th><th>Occupation</th><th>Status</th><th>Action</th></tr></thead>';
    echo '<tbody>';

    foreach ($users as $user) {
      $user_id = $user->ID;
      // Retrieve the user's existing details if available
      $user_details = get_user_meta($user_id, 'biodata_user_details', true);

      echo '<tr>';
      echo '<td>' . $user_details['personal_info'] . '</td>';
      echo '<td>' . $user_details['education'] . '</td>';
      echo '<td>' . $user_details['experience'] . '</td>';
      echo '<td>' . $user_details['about_me'] . '</td>';
      echo '<td>' . $user_details['occupation'] . '</td>';
      echo '<td>' . $user_details['status'] . '</td>';
      echo '<td><button class="approve-button " data-user-id="' . $user_id . '">Approve</button> </td>';
      echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
  }

  public  function enqueue_admin_styles()
  {
    wp_enqueue_style('admin-styles', plugin_dir_url(__FILE__) . '../admin/css/admin-styles.css', array(), '1.0.0', 'all');
  }

  public function enqueue_admin_scripts()
  {
    // Enqueue your custom admin script
    wp_enqueue_script('my-admin-script', plugin_dir_url(__FILE__) . '../admin/js/admin-script.js', array('jquery'), '1.0', true);

    // Localize the script with custom data
    wp_localize_script('my-admin-script', 'myAjax', array(
      'ajaxurl' => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce('my-ajax-nonce'),
    ));
  }


  public function localize_script()
  {
    // Output additional script or code, if needed
?>
<script type="text/javascript">
// Additional script or code
</script>
<?php
  }

  public function approve_user_callback()
  {
    if (isset($_POST['user_id'])) {
      $user_id = $_POST['user_id'];

      // Retrieve the existing user details meta
      $user_details = get_user_meta($user_id, 'biodata_user_details', true);

      // Update the 'status' key to 'approved' in the user details meta
      $user_details['status'] = 'approved';

      // Update the user meta with the modified data
      update_user_meta($user_id, 'biodata_user_details', $user_details);

      echo 'User status updated to "approved" successfully.';

      $current_page_url = admin_url() . $_SERVER['REQUEST_URI'];

      wp_redirect($current_page_url);
    }

    wp_die();
  }
}