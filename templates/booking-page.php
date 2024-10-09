<div class="bms_booking_wrapper">
    <?php
    if (is_user_logged_in()) {
        echo '<h3>You are already logged in. Start booking your service.</h3>';

        if (isset($_POST['service'])) {
            $selected_service = sanitize_text_field($_POST['service']);
            $user_id = get_current_user_id();

            $booking_data = array(
                'user_id'       => $user_id,
                'service'       => $selected_service,
                'booking_time'  => current_time('mysql'),
            );

            global $wpdb;
            $table_name = $wpdb->prefix . 'bookings';

            $wpdb->query("CREATE TABLE IF NOT EXISTS $table_name (
            id BIGINT(20) NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) NOT NULL,
            service VARCHAR(255) NOT NULL,
            booking_time DATETIME NOT NULL,
            PRIMARY KEY (id)
        )");

            $wpdb->insert($table_name, $booking_data);

            echo '<div class="updated"><p>Your booking for "' . esc_html($selected_service) . '" has been successfully saved.</p></div>';
        }

        $services = get_option('bms_services', array());

        echo '<form method="post" action="">
            <p>Select your service:</p>
            <select name="service">';

        if (!empty($services)) {
            foreach ($services as $service) {
                echo '<option value="' . esc_attr($service) . '">' . esc_html($service) . '</option>';
            }
        } else {
            echo '<option value="">No services available</option>';
        }

        echo '  </select>
            <p><input type="submit" value="Book Service"></p>
          </form>';

        echo '<p><a href="' . wp_logout_url(home_url('/booking')) . '">Logout</a></p>';
    } else {
    ?>

        <div class="login-signup-switch">
            <span class="login active">Login</span>
            <span class="signup">Sign Up</span>
        </div>

        <div class="login-section">
            <h3>Login</h3>
            <form method="post" action="<?php echo esc_url(wp_login_url()); ?>" id="login-form">
                <p>
                    <label for="login_email">Email</label>
                    <input type="email" name="log" id="login_email" class="input" value="" size="20" required />
                </p>
                <p>
                    <label for="user_pass">Password</label>
                    <input type="password" name="pwd" id="user_pass" class="input" value="" size="20" required />
                </p>
                <p>
                    <input type="submit" value="Log In" />
                </p>
            </form>
        </div>


        <div class="signup-section">
            <h3>Sign Up</h3>
            <form method="post" action="">
                <p>
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" required />
                </p>
                <p>
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required />
                </p>
                <p>
                    <label for="phone">Phone Number</label>
                    <input type="text" name="phone" id="phone" required />
                </p>
                <p>
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required />
                </p>
                <p>
                    <input type="submit" name="register" value="Sign Up" />
                </p>
            </form>
        </div>

        <script>
            jQuery(document).ready(function($) {

                $('.signup-section').hide();

                $('.login-signup-switch .login').on('click', function() {
                    $('.login-section').show();
                    $('.signup-section').hide();
                    $(this).addClass("active");
                    $('.login-signup-switch .signup').removeClass('active');
                });

                $('.login-signup-switch .signup').on('click', function() {
                    $('.login-section').hide();
                    $('.signup-section').show();
                    $(this).addClass("active");
                    $('.login-signup-switch .login').removeClass('active');
                });

                $('#login-form').on('submit', function(e) {
                    const email = $('#login_email').val().trim();
                    const phone = $('#login_phone').val().trim();
                    const password = $('#user_pass').val().trim();

                    if (!email && !phone) {
                        e.preventDefault();
                        alert('Please enter either email or phone number to log in.');
                    }
                    if (!password) {
                        e.preventDefault();
                        alert('Please enter your password.');
                    }
                });
            });
        </script>

    <?php
    }
    ?>
</div>