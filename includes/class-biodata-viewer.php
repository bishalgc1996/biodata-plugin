<?php


class BiodataViewer
{
    public function displayBiodataCards()
    {
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

                $output .= $this->getBiodataCardHtml($personal_info, $education, $experience, $about_me, $occupation, $status);
            }
        }

        return $output;
    }

    private function getBiodataCardHtml($personal_info, $education, $experience, $about_me, $occupation, $status)
    {
        ob_start();
        include plugin_dir_path(__FILE__) . '../templates/archive-biodata.php';
        return ob_get_clean();
    }
}