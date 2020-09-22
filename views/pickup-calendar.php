<?php

    $frequence = [
        1,
        7
    ];
    if(WC()->cart->get_cart_contents_count() > 0){
        $meta = get_post_meta(array_values(WC()->cart->get_cart())[0]['product_id'], '_woo_recursive_freq', true);
        $frequence = explode( ':', $meta );
    }

    unset(WC()->session->woo_locker_choosen_locker);

    require_once( WOO_LOCKER_API_CLASSES_DIR . 'class.woo-locker-api-service.php' );

    wp_enqueue_style( 'woo-locker-api', plugins_url('assets/css/woo-locker-api.css', WOO_LOCKER_API_ASSETS_DIR ) , '', '', false);
    wp_enqueue_script( 'jquery' );
    wp_enqueue_style( 'fontawesome', plugins_url('assets/fontawesome/css/all.min.css', WOO_LOCKER_API_ASSETS_DIR) , '', '', false);
    wp_enqueue_style( 'fullcalendar', plugins_url('assets/calendar/main.css', WOO_LOCKER_API_ASSETS_DIR) , '', '', false);
    wp_enqueue_script( 'woo-locker-scripts', plugins_url('assets/js/woo-locker-scripts.js', WOO_LOCKER_API_ASSETS_DIR) , '', '', false);
    wp_enqueue_script( 'calendar-main.js', plugins_url('assets/calendar/main.js', WOO_LOCKER_API_ASSETS_DIR), '', '', false);
    wp_enqueue_script( 'calendar-fr-local.js', plugins_url('assets/calendar/locales/fr.js', WOO_LOCKER_API_ASSETS_DIR), '', '', false);

    /* Inline script printed out in the footer */
    add_action('wp_footer', 'woo_locker_choose_availability');
    function woo_locker_choose_availability() {
        ?>
            <script language="javascript">
                jQuery(document).ready(function(){
                    jQuery("#place_order").attr('disabled', true).css("pointer-events", "none");
                });
                function set_woo_locker_checkout(value){
                    var locker = WOO_LOCKERS[value]['lockers'][Math.floor(Math.random() * WOO_LOCKERS[value]['lockers'].length)];
                    jQuery.ajax({
                        type: "POST",
                        url: '<?php echo esc_url( plugins_url( 'ajax-interceptor.php', dirname(__FILE__) ) ); ?>',
                        dataType: "json",
                        data: {
                            action: "set_locker",
                            locker: locker
                        },
                        beforeSend: function(){
                            jQuery("#woo_locker_delivery_locker_info_content").addClass('woo-locker-hidden');
                            jQuery("#woo_locker_delivery_locker_info_content-spinner").removeClass('woo-locker-hidden');
                        },
                        success: function(response){
                            //jQuery('body').trigger('update_checkout');
                            //jQuery("#woo_locker_delivery_locker_id").val(response['locker']['woo_locker_choosen_locker_id']);
                            jQuery("#woo_locker_delivery_locker_name").text(response['locker']['woo_locker_choosen_locker_name']);
                            jQuery("#woo_locker_delivery_locker_address").text(response['locker']['woo_locker_choosen_locker_address']);
                            jQuery("#place_order").css("pointer-events", "").removeAttr('disabled');
                            jQuery("#woo_locker_delivery_locker_info_content-spinner").addClass('woo-locker-hidden');
                            jQuery("#woo_locker_delivery_locker_info_content").removeClass('woo-locker-hidden');
                        }
                    });
                }
            </script>
        <?php
    }

    woo_locker_pickup_shipping_method_init();
    $wooLockerApiShippingMethod = new WooLockerApiShippingMethod();
    $settings = $wooLockerApiShippingMethod->settings;

    $date = new DateTime(date('Y-m-d') . ' + '.$settings['woo_locker_api_delivery_delay'].' day');
    $initialDateCalendar = $date->format('Y-m-d');
?>
    <div id="woo_locker_delivery_calendar"><div class="woo-locker-spinner"><img src="<?php echo esc_url( plugins_url('assets/img/spinner.gif', WOO_LOCKER_API_ASSETS_DIR) ); ?>" alt="chargement..." /></div></div>

    <script language="javascript">jQuery(document).ready(function(){
        var calendarEl = document.getElementById("woo_locker_delivery_calendar");
        var initialDateCalendar = new Date("<?php echo $initialDateCalendar; ?>");
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialDate: "<?php echo $initialDateCalendar; ?>",
            initialView: "dayGridMonth",
            themeSystem: "bootstrap",
            locale: "fr",
            weekends: <?php echo ($settings['woo_locker_api_include_weekend'] == 'yes' ? "true" : "false"); ?>,
            headerToolbar: {
                start: "title",
                center: "",
                end: "prev,next"
            },
            displayEventTime : false,
            dayCellDidMount: function(cell) {
                if(wooLockerFormatDate(cell.date) < wooLockerFormatDate(initialDateCalendar)){
                    cell.el.childNodes[0].childNodes[0].style.opacity = "0.3";
                } else {
                    cell.el.style.cursor = "pointer";
                }
            },
            dateClick: function(info) {
                var selectedDate = new Date(info.dateStr);
                if(wooLockerFormatDate(selectedDate) >= wooLockerFormatDate(initialDateCalendar) && !jQuery(info.dayEl).hasClass("woo-locker-selected"))
                {
                    jQuery(".fc-day").each(function(index, element) {
                        jQuery(element).find(".woo-locker-icon-container").remove();
                        jQuery(element).removeClass("woo-locker-selected");
                        jQuery(element).find(".fc-daygrid-day-frame").css("top", "");
                        jQuery(element).find(".fc-daygrid-day-events").css("min-height", "");
                    });
                    jQuery(info.dayEl).addClass("woo-locker-selected").prepend("<div class='woo-locker-icon-container'><i class='fa fa-check-square' aria-hidden='true'></i></div>");
                    jQuery(info.dayEl).find(".fc-daygrid-day-frame").css("top", "-24px");
                    jQuery(info.dayEl).find(".fc-daygrid-day-events").css("min-height", "0.4rem");

                    jQuery.ajax({
                        type: "GET",
                        dataType: "json",
                        url: "<?php echo esc_url( plugins_url( 'ajax-interceptor.php', dirname(__FILE__) ) ); ?>",
                        data: {
                            action: "get_availabilties",
                            date: info.dateStr
                        },
                        beforeSend: function() {
                            jQuery("#woo_locker_delivery_locker_info_content").addClass('woo-locker-hidden');
                            jQuery([document.documentElement, document.body]).animate({
                                scrollTop: jQuery("#woo-schedule-checker-fieldset").offset().top - 200
                            }, 200);
                            jQuery("#woo_locker_delivery_calendar").css("pointer-events", "none").css("opacity", "0.5");
                            jQuery("#woo-locker-schedule-checker-container").addClass("woo-locker-hidden");
                            jQuery("#woo-locker-spinner").removeClass("woo-locker-hidden");
                        },
                        success: function (response) {
                            WOO_LOCKERS = response;
                            jQuery("#woo_locker_delivery_date").val(info.dateStr);
                            jQuery("#woo-morning-reserved-label").remove();
                            jQuery("#woo-evening-reserved-label").remove();
                            jQuery("#schedule_woo_locker_evening").removeAttr("disabled").attr("checked", false);
                            jQuery("#schedule_woo_locker_morning").removeAttr("disabled").attr("checked", false);
                            var disableOrderButton = true;
                            if(response.morning.available == false){
                                jQuery("#schedule_woo_locker_morning").attr("disabled", true);
                                jQuery(".woocommerce-input-wrapper label[for='schedule_woo_locker_morning']").css("cursor", "default").append('<span id="woo-morning-reserved-label" class="woo-booked-label"> ( <?php echo __('horaire déjà reservé', 'woolockerapi'); ?> )</span>');
                            } else {
                                jQuery(".woocommerce-input-wrapper label[for='schedule_woo_locker_morning']").css("cursor", "");
                                disableOrderButton = false;
                            }
                            if(response.evening.available == false){
                                jQuery("#schedule_woo_locker_evening").attr("disabled", true);
                                jQuery(".woocommerce-input-wrapper label[for='schedule_woo_locker_evening']").css("cursor", "default").append('<span id="woo-evening-reserved-label" class="woo-booked-label"> ( <?php __('horaire déjà reservé', 'woolockerapi'); ?> )</span>');
                            } else {
                                jQuery(".woocommerce-input-wrapper label[for='schedule_woo_locker_evening']").css("cursor", "")
                                disableOrderButton = false;
                            }
                            if(disableOrderButton = true){
                                jQuery("#place_order").attr("disabled", true).css("pointer-events", "none");
                            }

                            var segment = response.morning.date.split(" ");
                            jQuery(".woo-delivery-date-choice").text(response.morning.date);
                            jQuery(".delivery-list-item-chosen__form__list__item__note").text('Puis tous les '+segment[0]+'s aux mêms horaires.');

                            jQuery("#woo_locker_delivery_calendar").css("pointer-events", "").css("opacity", "");
                            jQuery("#woo-locker-spinner").addClass("woo-locker-hidden");
                            jQuery("#woo-locker-schedule-checker-container").removeClass("woo-locker-hidden");
                        }
                    });
                }
            }
        });
        calendar.render();
    });</script>