<div class="bms_booking_wrapper">
    <?php
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
    ?>
        <div class="tab-switch booked-switch">
            <span class="book-your-service active">Book your Service</span>
            <span class="bookings">Bookings</span>
        </div>

        <div class="booking-menu-wrapper">
            <div class="booked-services-wrapper">
                <?php

                echo '<h3>Your Booked Services:</h3>';
                $user_booked_services = bms_get_user_booked_services($user_id);

                if (!empty($user_booked_services)) {
                    echo '<ul class="services-list">';
                    foreach ($user_booked_services as $service) {
                        echo '<li class="service-item">';
                        echo '<div class="service-item-title">' . esc_html($service->service) . '</div>';
                        echo '<div class="service-item-info">';
                        echo '<span class="date-time">' . esc_html(date("g:i A, d-m-Y", strtotime($service->booking_time))) . '</span>';
                        echo '<span class="booking-status ' . esc_attr(strtolower($service->status)) . '">' . esc_html($service->status) . '</span>';
                        echo '</div></li>';
                    }
                    echo '</ul>';
                } else {
                    echo '<p>You have not booked any services yet.</p>';
                }

                echo '</div>';
                echo '<div class="book-your-service-wrapper">';

                echo '<h3>Book your service.</h3>';

                if (isset($_POST['service'])) {
                    $selected_service = sanitize_text_field($_POST['service']);
                    $message = sanitize_textarea_field($_POST['message']);

                    $booking_data = array(
                        'user_id'       => $user_id,
                        'service'       => $selected_service,
                        'booking_time'  => current_time('mysql'),
                        'message' => $message,
                    );

                    global $wpdb;
                    $table_name = $wpdb->prefix . 'bookings';

                    $wpdb->query("CREATE TABLE IF NOT EXISTS $table_name (
            id BIGINT(20) NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) NOT NULL,
            service VARCHAR(255) NOT NULL,
            booking_time DATETIME NOT NULL,
            message  TEXT NOT NULL,
            status VARCHAR(255) NOT NULL DEFAULT 'pending',
            PRIMARY KEY (id)
        )");

                    $wpdb->insert($table_name, $booking_data);
                    bms_send_booking_email($booking_data);

                    // form_success();
                }



                $services = get_option('bms_services', array());

                echo '<form method="post" action="">
            <label for="service">Select your service:</label>
            <select name="service">';

                if (!empty($services)) {
                    foreach ($services as $service) {
                        echo '<option value="' . esc_attr($service) . '">' . esc_html($service) . '</option>';
                    }
                } else {
                    echo '<option value="">No services available</option>';
                }

                echo '  </select>
        <p>
            <label for="message">Additional Message</label>
            <textarea name="message" id="message" rows="5" cols="40"></textarea>
        </p>
            <p><input type="submit" value="Book Service"></p>
          </form>';

                echo '<p style="margin-top: 2em"><a class="logout_btn" href="' . wp_logout_url(home_url('/booking')) . '">Logout</a></p>';
                echo '</div>';
                ?>
                <script>
                    jQuery(document).ready(function($) {
                        $('.booked-services-wrapper').hide();

                        $('.booked-switch .book-your-service').on('click', function() {
                            $('.book-your-service-wrapper').show();
                            $('.booked-services-wrapper').hide();
                            $(this).addClass("active");
                            $('.booked-switch .bookings').removeClass('active');
                        });

                        $('.booked-switch .bookings').on('click', function() {
                            $('.book-your-service-wrapper').hide();
                            $('.booked-services-wrapper').show();
                            $(this).addClass("active");
                            $('.booked-switch .book-your-service').removeClass('active');
                        });

                        $('.book-your-service-wrapper form').on('submit', function(event) {
                            event.preventDefault();
                            let isValid = true;
                            $('.error-message').remove();

                            const message = $('#message').val().trim();
                            if (message === '') {
                                $('<span class="error-message" style="color: red;">Message is required.</span>').insertAfter('#message');
                                isValid = false;
                            }

                            const selectedService = $('.nice-select .current').text();
                            if (!selectedService) {
                                $('<span class="error-message" style="color: red;">Please select a service.</span>').insertAfter('.nice-select');
                                isValid = false;
                            }

                            if (!isValid) {
                                return;
                            }

                            // $('.book-your-service-wrapper input[type="submit"]').prop('disabled', true);
                            $('.book-your-service-wrapper form')[0].reset();
                            $('.nice-select .current').text('Service 1');
                            $('.nice-select .option').removeClass('selected').filter('[data-value="Service 1"]').addClass('selected');

                            const successMessage = $('<p class="success-message"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>Your booking for "' + selectedService + '" has been successfully submitted.</p>');
                            $('.book-your-service-wrapper').prepend(successMessage);

                            setTimeout(function() {
                                $('.success-message').fadeOut('slow', function() {
                                    $(this).remove();
                                });
                            }, 10000);
                        });

                        $('.nice-select .list .option').on('click', function() {
                            const selectedService = $(this).data('value');
                            $('select[name="service"]').val(selectedService);
                            $('.nice-select .current').text(selectedService);
                            $('.nice-select .option').removeClass('selected');
                            $(this).addClass('selected');
                        });

                        // $('.book-your-service-wrapper form').on('submit', function(event) {
                        //     event.preventDefault();
                        //     let form = $(this);
                        //     let formData = form.serialize();

                        //     $.ajax({
                        //         url: form.attr('action'),
                        //         type: 'POST',
                        //         data: formData,
                        //         success: function(response) {
                        //             if (response.success) {
                        //                 form[0].reset();
                        //                 $('.nice-select .current').text('Service 1');
                        //                 $('.nice-select .option').removeClass('selected').filter('[data-value="Service 1"]').addClass('selected');

                        //                 const successMessage = $('<div class="updated"><p>' + response.data.message + '</p></div>');
                        //                 $('.book-your-service-wrapper').prepend(successMessage);

                        //                 setTimeout(function() {
                        //                     successMessage.fadeOut('slow', function() {
                        //                         $(this).remove();
                        //                     });
                        //                 }, 5000);
                        //             }
                        //         },
                        //         error: function(xhr) {
                        //             console.error('AJAX Error:', xhr.responseText);
                        //             alert('There was an error processing your request. Please try again later.');
                        //         }
                        //     });
                        // });
                    });
                </script>
            </div>
        <?php
    } else {
        ?>
            <div class="auth-wrapper">
                <div class="login-signup-switch tab-switch">
                    <span class="login active">Login</span>
                    <span class="signup">Sign Up</span>
                </div>

                <div class="login-section">
                    <form method="post" action="<?php echo esc_url(wp_login_url()); ?>" id="login-form">
                        <p>
                            <label for="login_email">Email</label>
                            <input type="email" name="log" id="login_email" class="input" value="" size="20" />
                        </p>
                        <p>
                            <label for="user_pass">Password</label>
                            <input type="password" name="pwd" id="user_pass" class="input" value="" size="20" />
                        </p>
                        <p>
                            <input type="submit" value="Log In" />
                        </p>
                    </form>
                </div>


                <div class="signup-section">
                    <form method="post" action="">
                        <p>
                            <label for="name">Name</label>
                            <input type="text" name="name" id="name" />
                        </p>
                        <p>
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" />
                        </p>
                        <p>
                            <label for="phone">Phone Number</label>
                            <input type="tel" name="phone" id="phone" />
                        </p>
                        <p>
                            <label for="password">Password</label>
                            <input type="password" name="password" id="password" />
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

                        $('.signup-section form').on('submit', function(event) {
                            let isValid = true;
                            $('.error-message').remove(); // Remove any existing error messages

                            // Name validation
                            const name = $('#name').val();
                            if (name === '') {
                                const errorMessage = $('<span class="error-message" style="color: red;">Name is required.</span>');
                                $('#name').after(errorMessage);
                                isValid = false;
                            }

                            // Email validation
                            const email = $('#email').val();
                            const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                            if (email === '') {
                                const errorMessage = $('<span class="error-message" style="color: red;">Email is required.</span>');
                                $('#email').after(errorMessage);
                                isValid = false;
                            } else if (!emailPattern.test(email)) {
                                const errorMessage = $('<span class="error-message" style="color: red;">Invalid email format.</span>');
                                $('#email').after(errorMessage);
                                isValid = false;
                            }

                            // Phone validation (only numbers)
                            const phone = $('#phone').val();
                            const phonePattern = /^[0-9]+$/;
                            if (phone === '') {
                                const errorMessage = $('<span class="error-message" style="color: red;">Phone number is required.</span>');
                                $('#phone').after(errorMessage);
                                isValid = false;
                            } else if (!phonePattern.test(phone)) {
                                const errorMessage = $('<span class="error-message" style="color: red;">Phone number should only contain numbers.</span>');
                                $('#phone').after(errorMessage);
                                isValid = false;
                            }

                            // Password validation
                            const password = $('#password').val();
                            if (password === '') {
                                const errorMessage = $('<span class="error-message" style="color: red;">Password is required.</span>');
                                $('#password').after(errorMessage);
                                isValid = false;
                            }

                            if (!isValid) {
                                event.preventDefault(); // Prevent form submission if validation fails
                            }
                        });

                        $('.login-section form').on('submit', function(event) {
                            let isValid = true;
                            $('.error-message').remove(); // Remove any existing error messages

                            // Email validation
                            const email = $('#login_email').val();
                            const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                            if (email === '') {
                                const errorMessage = $('<span class="error-message" style="color: red;">Email is required.</span>');
                                $('#login_email').after(errorMessage);
                                isValid = false;
                            } else if (!emailPattern.test(email)) {
                                const errorMessage = $('<span class="error-message" style="color: red;">Invalid email format.</span>');
                                $('#login_email').after(errorMessage);
                                isValid = false;
                            }

                            // Password validation
                            const password = $('#user_pass').val();
                            if (password === '') {
                                const errorMessage = $('<span class="error-message" style="color: red;">Password is required.</span>');
                                $('#user_pass').after(errorMessage);
                                isValid = false;
                            }

                            if (!isValid) {
                                event.preventDefault(); // Prevent form submission if validation fails
                            }
                        });
                    });
                </script>
            </div>

        <?php
    }
        ?>
        </div>