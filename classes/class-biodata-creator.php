<?php



class BiodataCreator
{

    public function __construct()
    {
        // Add hooks
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_shortcode('biodata_register_form', array($this, 'render_registration_form'));
        add_shortcode('biodata_login_form', array($this, 'render_login_form'));
        add_action('wp', array($this, 'process_registration'));
        add_action('wp', array($this, 'process_login'));
        // Register the custom post type for user biodata
        add_action('init', array($this, 'register_biodata_post_type'));
        // Register custom taxonomy for Occupation Type
        add_action('init', array($this, 'register_occupation_taxonomy'));
    }


    public function init()
    {


        // Register shortcode for user details form
        add_shortcode('biodata_user_details_form', array($this, 'render_user_details_form'));
        add_action('wp', array($this, 'process_user_details'));

        add_action('admin_init', array($this, 'auto_select_custom_taxonomy_term'));
    }

    public function enqueue_scripts()
    {
        // Enqueue plugin's CSS and JavaScript files
        wp_enqueue_style('biodata-plugin-style', plugins_url('../css/form.css', __FILE__));
        wp_enqueue_script('biodata-plugin-script', plugins_url('../js/script.js', __FILE__), array('jquery'), '1.0.0', true);
        // Localize the script and pass the AJAX URL
        wp_localize_script('biodata-plugin-script', 'ajax_object', array('ajaxurl' => admin_url('admin-ajax.php')));
    }

    public function render_registration_form()
    {


        // Check if the registration form was submitted
        if (isset($_POST['register_submit'])) {
            $registration_result = $this->process_registration();
            if ($registration_result === true) {
                // Registration successful, redirect to login page
                wp_redirect(home_url('/login'));
                exit;
            } else {
                // Registration failed, display error message
                $form_html = '<p class="error-message">' . esc_html($registration_result) . '</p>';
            }
        } else {
            // Check if registration was previously completed
            $registration_completed = get_user_meta(get_current_user_id(), 'biodata_registration_completed', true);
            if ($registration_completed) {
                // Registration already completed, redirect to login page
                wp_redirect(home_url('/login'));
                exit;
            }

            // Render the registration form HTML
            $form_html = '<form id="biodata-registration-form" method="post" action="' . esc_url($_SERVER['REQUEST_URI']) . '">';
            $form_html .= '<label for="username">Username:</label>';
            $form_html .= '<input type="text" name="username" id="username" required>';

            $form_html .= '<label for="email">Email:</label>';
            $form_html .= '<input type="email" name="email" id="email" required>';

            $form_html .= '<label for="password">Password:</label>';
            $form_html .= '<input type="password" name="password" id="password" required>';

            // ...

            $form_html .= '<input type="submit" name="register_submit" value="Register">';
            $form_html .= '</form>';
        }

        return $form_html;
    }



    public function process_registration()
    {
        // Check if the registration form was submitted
        if (isset($_POST['register_submit'])) {
            // Validate and sanitize the form data
            $username = sanitize_user($_POST['username']);
            $email = sanitize_email($_POST['email']);
            $password = $_POST['password'];

            // Perform additional validation if needed

            // Check if the username or email already exists
            $user_exists = username_exists($username);
            $email_exists = email_exists($email);

            if ($user_exists || $email_exists) {
                // User already exists, handle the error
                $error_message = 'Username or email is already registered.';
                return $error_message;
            } else {
                // Register the user
                $user_id = wp_create_user($username, $password, $email);

                if (!is_wp_error($user_id)) {
                    // User registration successful, return true
                    // Set a flag indicating successful registration
                    update_user_meta($user_id, 'biodata_registration_completed', true);
                    $post_data = array(
                        'post_title'   =>  $username,
                        'post_content' => '', // Add content if necessary
                        'post_status'  => 'publish',
                        'post_author'  => $user_id,
                        'post_type'    => 'biodata',
                    );

                    $post_id = wp_insert_post($post_data);

                    // Assign the desired capabilities to the user role
                    $user = new WP_User($user_id);
                    $user->set_role('editor');



                    wp_redirect(home_url('/login'));
                    return true;
                } else {
                    // User registration failed, handle the error
                    $error_message = $user_id->get_error_message();
                    return $error_message;
                }
            }
        }
    }




    public function render_login_form()
    {
        // Check if user is already logged in
        if (is_user_logged_in()) {
            return 'You are already logged in.';
        }

        // Render the login form HTML here
        $form_html = '<form id="biodata-login-form" method="post" action="' . esc_url($_SERVER['REQUEST_URI']) . '">';
        $form_html .= '<label for="username">Username:</label>';
        $form_html .= '<input type="text" name="username" id="username" required>';

        $form_html .= '<label for="password">Password:</label>';
        $form_html .= '<input type="password" name="password" id="password" required>';

        // ...

        $form_html .= '<input type="submit" name="login_submit" value="Login">';
        $form_html .= '</form>';

        return $form_html;
    }

    public function process_login()
    {
        // Check if the login form was submitted
        if (isset($_POST['login_submit'])) {
            // Validate and sanitize the form data


            $username = sanitize_user($_POST['username']);
            $password = $_POST['password'];

            // Perform additional validation if needed

            // Attempt to log in the user
            $login_data = array(
                'user_login' => $username,
                'user_password' => $password,
                'remember' => true
            );

            $user = wp_signon($login_data, false);

            if (!is_wp_error($user)) {
                // User login successful, redirect to biodata page or any desired location
                wp_redirect(home_url('/biodata'));
                exit;
            } else {
                // User login failed, handle the error
                $error_message = $user->get_error_message();
                // Display or log the error message
            }
        }
    }

    public function render_user_details_form()
    {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return 'You need to be logged in to access this section.';
        }

        // Get the current user's ID
        $user_id = get_current_user_id();

        // Retrieve the user's existing details if available
        $user_details = get_user_meta($user_id, 'biodata_user_details', true);




        // Form HTML
        $form_html = '<form id="biodata-user-details-form" method="post" action="' . esc_url($_SERVER['REQUEST_URI']) . '">';

        // Add fields for personal information, education, experience, about me, etc.
        $form_html .= '<label for="personal_info">Personal Information:</label>';
        $form_html .= '<input type="text" name="personal_info" id="personal_info" value="' . esc_attr($user_details['personal_info'] ?? '') . '">';

        $form_html .= '<label for="education">Education:</label>';
        $form_html .= '<input type="text" name="education" id="education" value="' . esc_attr($user_details['education'] ?? '') . '">';

        $form_html .= '<label for="experience">Experience:</label>';
        $form_html .= '<input type="text" name="experience" id="experience" value="' . esc_attr($user_details['experience'] ?? '') . '">';

        // Add occupation select option
        $occupation_terms = get_terms(array(
            'taxonomy' => 'occupation_type',
            'hide_empty' => false,
        ));
        if (!empty($occupation_terms)) {
            $form_html .= '<label for="occupation">Occupation:</label>';
            $form_html .= wp_dropdown_categories(array(
                'taxonomy' => 'occupation_type',
                'name' => 'occupation',
                'id' => 'occupation',
                'class' => 'occupation-select',
                'show_option_none' => 'Select Occupation',
                'selected' => $user_details['occupation'] ?? '',
                'value_field'  =>  'name',
                'hide_empty' => false,
                'echo' => false,
            ));
        }

        $form_html .= '<label for="about_me">About Me:</label>';
        $form_html .= '<textarea name="about_me" id="about_me">' . esc_textarea($user_details['about_me'] ?? '') . '</textarea>';


        // ...

        $form_html .= '<input type="submit" name="save_details_submit" value="Create BioData">';
        $form_html .= '</form>';

        return $form_html;
    }

    public function process_user_details()
    {
        // Check if the user details form was submitted
        if (isset($_POST['save_details_submit'])) {
            // Check if user is logged in
            if (!is_user_logged_in()) {
                return; // You may handle the error here or redirect to login page
            }

            // Get the current user's ID
            $user_id = get_current_user_id();

            // Sanitize and retrieve the form data
            $personal_info = sanitize_text_field($_POST['personal_info']);
            $education = sanitize_text_field($_POST['education']);
            $experience = sanitize_text_field($_POST['experience']);
            $about_me = sanitize_textarea_field($_POST['about_me']);
            // Sanitize and save the occupation value
            $occupation = isset($_POST['occupation']) ? sanitize_text_field($_POST['occupation']) : '';





            // Create an array of user details
            $user_details = array(
                'personal_info' => $personal_info,
                'education' => $education,
                'experience' => $experience,
                'about_me' => $about_me,
                'occupation' => $occupation,
                'status'     => 'pending'

                // Add more fields as needed
            );

            // Save the user details in user meta
            update_user_meta($user_id, 'biodata_user_details', $user_details);





            // Optionally, you can redirect the user to a success page or display a success message
            wp_redirect(home_url('/user-details'));
            exit;
        }
    }

    public function register_biodata_post_type()
    {
        $labels = array(
            'name'                  => 'Biodata', // Plural name of the post type
            'singular_name'         => 'Biodatum', // Singular name of the post type
            'menu_name'             => 'Biodata', // Label displayed in the WordPress dashboard menu
            'all_items'             => 'All Biodata', // Label for displaying all items in the dashboard
            'add_new'               => 'Add New', // Label for adding a new item
            'add_new_item'          => 'Add New Biodatum', // Label for adding a new item (singular)
            'edit_item'             => 'Edit Biodatum', // Label for editing an item
            'new_item'              => 'New Biodatum', // Label for creating a new item
            'view_item'             => 'View Biodatum', // Label for viewing an item
            'search_items'          => 'Search Biodata', // Label for searching for items
            'not_found'             => 'No biodata found', // Label displayed when no items are found
            'not_found_in_trash'    => 'No biodata found in trash', // Label displayed when no items are found in the trash
            'parent_item_colon'     => 'Parent Biodatum:', // Label displayed before the parent item
            'featured_image'        => 'Featured Image', // Label for the featured image
            'set_featured_image'    => 'Set featured image', // Label for setting the featured image
            'remove_featured_image' => 'Remove featured image', // Label for removing the featured image
            'use_featured_image'    => 'Use as featured image', // Label for using the featured image
            'archives'              => 'Biodata archives', // Label for the archives page
            'insert_into_item'      => 'Insert into biodatum', // Label for inserting into an item
            'uploaded_to_this_item' => 'Uploaded to this biodatum', // Label for items uploaded to this item
            'filter_items_list'     => 'Filter biodata list', // Label for filtering the list of items
            'items_list_navigation' => 'Biodata list navigation', // Label for the items list navigation
            'items_list'            => 'Biodata list', // Label for the items list
        );


        $args = array(
            'labels'              => $labels,
            'public'              => true,
            'exclude_from_search' => true,
            'publicly_queryable'  => true,
            'rewrite'             => array(
                'slug'       => 'biodata', // Update the slug to 'biodata'
                'with_front' => false,    // Remove the post type prefix from the URL
            ),
            'show_ui'             => true,
            'show_in_menu'        => true,
            'has_archive'         => true,
            'capability_type'     => 'post',
            'supports'            => array('title', 'editor'),
            'menu_icon'           => 'dashicons-businessman', // Set the appropriate icon
        );

        register_post_type('biodata', $args);


        register_post_type('biodata', $args);
    }

    public function register_occupation_taxonomy()
    {
        $labels = array(
            'name'                       => 'Occupation Types',
            'singular_name'              => 'Occupation Type',
            'menu_name'                  => 'Occupation Types',
            'all_items'                  => 'All Occupation Types',
            'parent_item'                => 'Parent Occupation Type',
            'parent_item_colon'          => 'Parent Occupation Type:',
            'new_item_name'              => 'New Occupation Type',
            'add_new_item'               => 'Add New Occupation Type',
            'edit_item'                  => 'Edit Occupation Type',
            'update_item'                => 'Update Occupation Type',
            'separate_items_with_commas' => 'Separate occupation types with commas',
            'search_items'               => 'Search Occupation Types',
            'add_or_remove_items'        => 'Add or remove occupation types',
            'choose_from_most_used'      => 'Choose from the most used occupation types',
        );

        $args = array(
            'labels'            => $labels,
            'public'            => true,
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'capabilities'      => array(
                'manage_terms' => 'edit_posts', // Capability required to manage terms
                'edit_terms'   => 'edit_posts', // Capability required to edit terms
                'delete_terms' => 'edit_posts', // Capability required to delete terms
                'assign_terms' => 'edit_posts', // Capability required to assign terms
            ),
            'rewrite'           => array('slug' => 'occupation'), // Customize the slug as per your preference
        );

        register_taxonomy('occupation_type', 'biodata', $args);
    }

    /**
     * Automatically select custom taxonomy term based on user meta
     */
    public function auto_select_custom_taxonomy_term()
    {
        // Check if it's the desired custom post type
        global $pagenow;
        if ('post.php' !== $pagenow || empty($_GET['post'])) {
            return;
        }

        $post_id = $_GET['post'];
        $post_type = get_post_type($post_id);

        // Replace 'your_custom_post_type' with the slug or name of your custom post type
        if ('biodata' !== $post_type) {
            return;
        }

        // Get the current user ID
        $user_id = get_current_user_id();

        // Get the user's occupation from user meta
        $user_details = get_user_meta($user_id, 'biodata_user_details', true);
        $occupation = isset($user_details['occupation']) ? $user_details['occupation'] : '';

        // Check if occupation exists and is not empty
        if (!empty($occupation)) {
            // Get the taxonomy term based on the occupation
            $term = get_term_by('slug', $occupation, 'occupation_type');

            // Check if the term exists
            if ($term) {
                // Add the term to the post
                wp_set_object_terms($post_id, $term->term_id, 'occupation_type');
            }
        }
    }







    // ...
}