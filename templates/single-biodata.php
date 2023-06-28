<?php

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}


// Get the header
get_header();

// Get the current user's ID
$user_id = get_current_user_id();


// Retrieve the user's existing details if available
$user_details = get_user_meta($user_id, 'biodata_user_details', true);






?>
<style>
.biodata-container {
  background-color: #f7f7f7;
  padding: 20px;
  border: 1px solid #ccc;
  border-radius: 4px;
  margin-bottom: 20px;
}

.biodata-container h2 {
  color: #333;
  font-size: 24px;
  margin-bottom: 10px;
}

.biodata-section {
  margin-bottom: 15px;
}

.biodata-section h3 {
  color: #666;
  font-size: 18px;
  margin-bottom: 5px;
}

.biodata-section p {
  margin: 0;
  line-height: 1.5;
}
</style>

<div class="biodata-container">
  <h2>Biodata</h2>

  <div class="biodata-section">
    <h3>Personal Information</h3>
    <p><?php echo esc_html($user_details['personal_info'] ?? ''); ?></p>
  </div>

  <div class="biodata-section">
    <h3>Education</h3>
    <p><?php echo esc_html($user_details['education'] ?? ''); ?></p>
  </div>

  <div class="biodata-section">
    <h3>Experience</h3>
    <p><?php echo esc_html($user_details['experience'] ?? ''); ?></p>
  </div>

  <div class="biodata-section">
    <h3>Occupation</h3>
    <p class="occupation"><?php echo esc_html($user_details['occupation'] ?? ''); ?></p>
  </div>

  <div class="biodata-section">
    <h3>About Me</h3>
    <p><?php echo esc_html($user_details['about_me'] ?? ''); ?></p>
  </div>
</div>




<?php

// Get the footer
get_footer();