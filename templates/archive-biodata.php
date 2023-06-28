<?php

/**
 * Template Name: Biodata Archive
 * Template for displaying the archive page of the custom post type "biodata".
 */

get_header();





?>




<div class="biodata-filter">
  <form action="<?php echo esc_url(home_url('/')); ?>" method="get" class="biodata-filter-form">
    <div class="biodata-filter-wrapper">
      <label for="occupation-filter">Filter by Occupation:</label>
      <select name="occupation" id="occupation-filter">
        <option value="">All Occupations</option>
        <?php
        $role = 'editor';
        $users = get_users(array('role' => $role));

        $occupations = array();

        foreach ($users as $user) {
          $user_id = $user->ID;
          $user_details = get_user_meta($user_id, 'biodata_user_details', true);

          // Check if the user has biodata details and the status is approved
          if (!empty($user_details) && isset($user_details['status']) && $user_details['status'] === 'approved' && isset($user_details['occupation'])) {
            $occupation = $user_details['occupation'];
            $occupations[] = $occupation;
          }
        }

        // Remove duplicate occupations
        $occupations = array_unique($occupations);

        foreach ($occupations as $occupation) {
          echo '<option value="' . esc_attr($occupation) . '">' . esc_html($occupation) . '</option>';
        }
        ?>
      </select>

    </div>
  </form>
</div>

<div class="biodata-wrapper">

  <?php

  $role = 'editor';
  $users = get_users(array('role' => $role));

  $output = '';

  foreach ($users as $user) {
    $user_id = $user->ID;
    $user_details = get_user_meta($user_id, 'biodata_user_details', true);

    // Check if the user has biodata details and the status is approved
    if (!empty($user_details) && isset($user_details['status']) && $user_details['status'] === 'approved') {
      $personal_info = $user_details['personal_info'];
      $education = $user_details['education'];
      $experience = $user_details['experience'];
      $about_me = $user_details['about_me'];
      $occupation = $user_details['occupation'];
      $status = $user_details['status'];

  ?>
  <div class="biodata-card">
    <div class="biodata-card-header">
      <h3 class="biodata-card-title"><?php echo $personal_info; ?></h3>
      <span class="biodata-card-occupation"><?php echo $occupation; ?></span>
    </div>
    <div class="biodata-card-content">
      <p class="biodata-card-details">
        <strong>Education:</strong> <?php echo $education; ?><br>
        <strong>Experience:</strong> <?php echo $experience; ?><br>
        <strong>About Me:</strong> <?php echo $about_me; ?>
      </p>
    </div>
    <div class="biodata-card-footer">

    </div>


  </div>

  <?php


    } else {

      echo '<p class="margin-zero-auto">' . 'No any approved BioData Ask Admin to approve the Biodata' . '</p>';
    }
  }

  ?>

  <?php





  get_footer();