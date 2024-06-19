<?php

/**
 * SpecialOccasionBanner Class
 *
 * This class is responsible for displaying a special occasion banner in the WordPress admin.
 *
 * @package YourVendor\SpecialOccasionPlugin
 *
 * @since 7.3.18
 */
class Rex_Feed_Special_Occasion_Banner {

	/**
	 * The occasion identifier.
	 *
	 * @var string
	 *
	 * @since 7.3.18
	 */
	private $occasion;

	/**
	 * The start date and time for displaying the banner.
	 *
	 * @var int
	 *
	 * @since 7.3.18
	 */
	private $start_date;

	/**
	 * The end date and time for displaying the banner.
	 *
	 * @var int
	 *
	 * @since 7.3.18
	 */
	private $end_date;

	/**
	 * Constructor method for SpecialOccasionBanner class.
	 *
	 * @param string $occasion The occasion identifier.
	 * @param string $start_date The start date and time for displaying the banner.
	 * @param string $end_date The end date and time for displaying the banner.
	 *
	 * @since 7.3.18
	 */
	public function __construct( $occasion, $start_date, $end_date ) {
		$this->occasion   = "rex_feed_{$occasion}";
		$this->start_date = strtotime( $start_date );
		$this->end_date   = strtotime( $end_date );
	}

	/**
	 * Controls the initialization of certain admin-related functionalities based on conditions.
	 * It checks the current screen, defined allowed screens, product feed version availability,
	 * and date conditions to determine whether to display a banner and enqueue styles.
	 *
	 * @since 7.3.18
	 */
	public function init() {
		$current_date_time = current_time( 'timestamp' );

		if (
			'hidden' !== get_option( $this->occasion, '' )
			&& !defined( 'REX_PRODUCT_FEED_PRO_VERSION' )
			&& ( $current_date_time >= $this->start_date && $current_date_time <= $this->end_date )
		) {
			// Add styles
			add_action( 'admin_head', [ $this, 'enqueue_css' ] );
			// Hook into the admin_notices action to display the banner
			add_action( 'admin_notices', [ $this, 'display_banner' ] );
		}
	}
    

	/**
	 * Displays the special occasion banner if the current date and time are within the specified range.
	 *
	 * @since 7.3.18
	 */
	public function display_banner() {
		$screen          = get_current_screen();
		$allowed_screens = [ 'dashboard', 'plugins', 'product-feed' ];
        $time_remaining  = $this->end_date - current_time( 'timestamp' );

        $btn_link = 'https://rextheme.com/best-woocommerce-product-feed/pricing/?utm_source=plugin-CTA&utm_medium=plugin&utm_campaign=eid-fitr-campaign-24';

		if ( in_array( $screen->base, $allowed_screens ) || in_array( $screen->parent_base, $allowed_screens ) || in_array( $screen->post_type, $allowed_screens ) || in_array( $screen->parent_file, $allowed_screens ) ) {
        echo '<input type="hidden" id="rexfeed_special_occasion" name="rexfeed_special_occasion" value="'.$this->occasion.'">';
        ?>

            <!-- Name: Eid Ul Fitr Notification Banner -->

            <div class="rex-feed-tb__notification" id="rex_deal_notification">

                <div class="banner-overflow">
                    <div class="rextheme-eid__container-area">

                        <div class="rextheme-eid__image rextheme-eid__image--one">
                            <figure>
                                <img src="<?php echo plugin_dir_url( __FILE__ ) .'./assets/icon/eid-ul-fitr/eid-mubark.webp' ; ?>" alt="Eid Mubark Rextheme" />
                            </figure>
                        </div>

                        <div class="rextheme-eid__content-area">

                            <div class="rextheme-eid__image rextheme-eid__image--two">
                                <figure>
                                    <img src="<?php echo plugin_dir_url( __FILE__ ) .'./assets/icon/eid-ul-fitr/eid-mubarak-icon.webp'; ?>" alt="Eid Mubark Rextheme" />
                                </figure>
                            </div>

                            <div class="rextheme-eid__image--group">

                                <div class="rextheme-eid__image rextheme-eid__image--three">
                                    <figure>
                                        <img src="<?php echo plugin_dir_url( __FILE__ ) .'./assets/icon/eid-ul-fitr/celebrate.webp' ; ?>" alt="Celebrate Rextheme" />
                                    </figure>
                                </div>

                                <div class="rextheme-eid__image rextheme-eid__image--four">
                                    <figure>
                                        <img src="<?php echo plugin_dir_url( __FILE__ ) .'./assets/icon/eid-ul-fitr/eid-discount.webp' ; ?>" alt="25% discount"  />
                                    </figure>
                                </div>

                            </div>

                            <!-- .rextheme-eid__image end -->
                            <div class="rextheme-eid__btn-area">
                                <a href="<?php echo esc_url($btn_link); ?>" role="button" class="rextheme-eid__btn" target="_blank">
                                    Get <span class="rextheme-eid__stroke-font">25%</span> OFF
                                </a>

                            </div>

                        </div>

                        <div class="rextheme-eid__image rextheme-eid__image--five">
                            <figure>
                                <img src="<?php echo plugin_dir_url( __FILE__ ) . './assets/icon/eid-ul-fitr/masjid.webp' ; ?>" alt="Masjid"  />
                            </figure>
                        </div>

                    </div>

                </div>

                <div class="rex-feed-tb__cross-top" id="rex_deal_close">
                    <?php include WPFM_PLUGIN_ASSETS_FOLDER_PATH . 'icon/icon-svg/cross-top.php'; ?>
                </div>

            </div>
            <!-- .rex-feed-tb-notification end -->

            <script>
                rexfeed_deal_countdown_handler();
                /**
                 * Handles count down on deal notice
                 *
                 * @since 7.3.18
                 */
                function rexfeed_deal_countdown_handler() {
                    // Pass the calculated time remaining to JavaScript
                    let timeRemaining = <?php echo $time_remaining;?>;

                    // Update the countdown every second
                    setInterval(function() {
                        const daysElement = document.getElementById('rex-feed-tb__days');
                        const hoursElement = document.getElementById('rex-feed-tb__hours');
                        const minutesElement = document.getElementById('rex-feed-tb__mins');
                        //const secondsElement = document.getElementById('seconds');

                        timeRemaining--;

                        if ( daysElement && hoursElement && minutesElement ) {
                            // Decrease the remaining time

                            // Calculate new days, hours, minutes, and seconds
                            let days = Math.floor(timeRemaining / (60 * 60 * 24));
                            let hours = Math.floor((timeRemaining % (60 * 60 * 24)) / (60 * 60));
                            let minutes = Math.floor((timeRemaining % (60 * 60)) / 60);
                            //let seconds = timeRemaining % 60;

                            // Format values with leading zeros
                            days = (days < 10) ? '0' + days : days;
                            hours = (hours < 10) ? '0' + hours : hours;
                            minutes = (minutes < 10) ? '0' + minutes : minutes;
                            //seconds = (seconds < 10) ? '0' + seconds : seconds;

                            // Update the HTML
                            daysElement.textContent = days;
                            hoursElement.textContent = hours;
                            minutesElement.textContent = minutes;
                        }
                        // Check if the countdown has ended
                        if (timeRemaining <= 0) {
                            rexfeed_hide_deal_notice();
                        }
                    }, 1000); // Update every second
                }

                document.getElementById( 'rex_deal_close' ).addEventListener( 'click', rexfeed_hide_deal_notice );

                /**
                 * Hide deal notice and save parameter to keep it hidden for future
                 *
                 * @since 7.3.2
                 */
                function rexfeed_hide_deal_notice() {
                    document.getElementById( 'rex_deal_notification' ).style.display = 'none';
                    const payload = { occasion: document.getElementById( 'rexfeed_special_occasion' )?.value }

                    wpAjaxHelperRequest( 'rex-feed-hide-deal-notice', payload );
                }
            </script>

            <?php
		}
	}

	/**
	 * Adds internal CSS styles for the special occasion banners.
	 *
	 * @since 7.3.18
	 */
	public function enqueue_css() {
        $plugin_dir_url = plugin_dir_url(__FILE__ );
		?>
		<style type="text/css">
            /* notification var css */

            @font-face {
                font-family: 'Lexend Deca';
                src: url(<?php echo "{$plugin_dir_url}assets/fonts/eid-ul-fitr-campaign-font/LexendDeca-SemiBold.woff2";?>) format('woff2'),
                    url(<?php echo "{$plugin_dir_url}assets/fonts/eid-ul-fitr-campaign-font/LexendDeca-SemiBold.woff";?>) format('woff');
                font-weight: 600;
                font-style: normal;
                font-display: swap;
            }

            @font-face {
                font-family: 'Lexend Deca';
                src: url(<?php echo "{$plugin_dir_url}assets/fonts/eid-ul-fitr-campaign-font/LexendDeca-Bold.woff2";?>) format('woff2'),
                    url(<?php echo "{$plugin_dir_url}assets/fonts/eid-ul-fitr-campaign-font/LexendDeca-Bold.woff";?>) format('woff');
                font-weight: bold;
                font-style: normal;
                font-display: swap;
            }
        

        .rex-feed-tb__notification, 
        .rex-feed-tb__notification * {
            box-sizing: border-box;
        }
                
        .rex-feed-tb__notification {
            background-color: #d6e4ff;
            width: calc(100% - 20px);
            margin: 50px 0 20px;
            background-image: url(<?php echo "{$plugin_dir_url}assets/icon/eid-ul-fitr/notification-bar-bg.webp"; ?>);
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            position: relative;
            border: none;
            box-shadow: none;
            display: block;
            max-height: 110px;
        }

        .rex-feed-tb__notification .banner-overflow {
            overflow: hidden;
            position: relative;
            width: 100%;
        }
       
        .rex-feed-tb__notification .rex-feed-tb__cross-top {
            position: absolute;
            top: -10px;
            right: -9px;
            background: #fff;
            border: none;
            padding: 4px 4px;
            border-radius: 50%;
            cursor: pointer;
            z-index: 9;
        }

        .rex-feed-tb__notification .rex-feed-tb__cross-top svg {
            display: block;
            width: 15px;
            height:15px;
        }

        .rextheme-eid__container {
            width: 100%;
            margin: 0 auto;
            max-width: 1640px;
            position: relative;
            padding-right: 15px;
            padding-left: 15px;
        }

        .rextheme-eid__container-area {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .rextheme-eid__content-area {
            width: 100%;
            display: flex;
            align-items: center;
            max-width: 1340px;
            position: relative;
            padding-right: 15px;
            padding-left: 15px;
            margin: 0 auto;
        }
        
        .rextheme-eid__image--group {
            display: flex;
            align-items: center;
            gap: 40px;
            padding: 0 140px 0 170px;
        }
      
        .rextheme-eid__image--one img {
            width: 100%;
            max-width: 85px;
            margin-left: 30px;
        }
        .rextheme-eid__image--two img {
            width: 100%;
            max-width: 154px;
        }
        .rextheme-eid__image--three img {
            width: 100%;
            max-width: 225px;
        }
        .rextheme-eid__image--four img {
            width: 100%;
            max-width: 362px;
        }
        .rextheme-eid__image--five img {
            width: 100%;
            max-width: 78px;
            margin-right: 30px;
        }
        .rextheme-eid__image figure {
            margin: 0;
        }
        .rextheme-eid__text-container {
            position: relative;
            max-width: 330px;
        }
        .rextheme-eid__campaign-text-icon {
            position: absolute;
            top: -10px;
            right: -15px;
            max-width: 100%;
            max-height: 24px;
        }
        .rextheme-eid__btn-area {
            display: flex;
            align-items: flex-end;
            justify-content: flex-end;
            position: relative;
        }
        .rextheme-eid__btn {
            position: relative;
            font-family: 'Lexend Deca';
            font-size: 18px;
            font-style: normal;
            font-weight: 600;
            color: #fff;
            line-height: 1;
            text-align: center;
            border-radius: 30px;
            background: linear-gradient(180deg, #6460fe 11.67%, #211cfd 100%);
            box-shadow: 0px 8px 20px rgba(12, 10, 81, 0.25);
            padding: 17px 28px;
            display: inline-block;
            cursor: pointer;
            text-transform: uppercase;
            transition: all 0.3s ease;
            text-decoration: none;
            margin-top: -6px;
        }

        .rextheme-eid__btn-area a:focus {
            color: #fff;
            box-shadow: none;
            outline: 0px solid transparent;
        }

        .rextheme-eid__btn::before {
            content: url(<?php echo "{$plugin_dir_url}assets/icon/eid-ul-fitr/pattern-vectors.webp"; ?>);
            position: absolute;
            top: -38%;
            right: -40px;
        }
        .rextheme-eid__btn:hover {
            background-color: #201cfe;
            color: #fff;
        }
        .rextheme-eid__btn-icon {
            position: absolute;
            top: -14px;
            right: -23px;
            width: 40px;
            height: 35px;
        }
        .rextheme-eid__stroke-font {
            font-size: 26px;
            font-weight: 700;
        }

        @media only screen and (max-width: 1440px) {
            .rextheme-eid__image--group {
                gap: 20px;
                padding: 0 30px 0 40px;
            }

            .rextheme-eid__btn {
                margin-top: -7px;
            }

            .rex-feed-tb__notification {
                max-height: 90px;
            }



            .wpvr-promotional-banner {
                max-height: 90px;
            }

            .rextheme-eid__image--one img {
                max-width: 75px;
                margin-left: 30px;
            }

            .rextheme-eid__stroke-font {
                font-size: 20px;
                font-weight: 700;
            }

            .rextheme-eid__image--five img {
                max-width: 60px;
                margin-right: 30px;
            }

            .rextheme-eid__image--two img {
                max-width: 110px;
            }

            .rextheme-eid__content-area {
                max-width: 900px;
            }

            .rextheme-eid__image--four img {
                width: 100%;
                max-width: 300px;
            }

            .rextheme-eid__image--three img {
                width: 100%;
                max-width: 175px;
            }

            .rextheme-eid__btn {
                font-size: 16px;
                font-weight: 600;
                line-height: 34px;
                border-radius: 30px;
                padding: 8px 27px;
            }

            .rextheme-td__btn-icon {
                position: absolute;
                top: -10px;
                right: -25px;
                max-height: 32px;
            }
           
        }

        @media only screen and (max-width: 1399px) {

            .rextheme-eid__btn::before {
                top: -38%;
                right: -20px;
            }

            .rextheme-eid__btn {
                margin-top: -6px;
            }

            .rextheme-eid__image--five img {
                margin-right: 20px;
            }

            .rextheme-eid__image--one img {
                margin-left: 20px;
            }
            
        }

        @media only screen and (max-width: 1024px) {

            .rex-feed-tb__notification{
                max-height: 63px;
            }

            .rextheme-eid__btn {
                margin-top: 0px;
            }

            .rextheme-eid__content-area {
                max-width: 653px;
            }

            .rextheme-eid__image--five img {
                 max-width: 46px;
            }

            .rextheme-eid__image--one img {
                max-width: 56px;
            }

            .rextheme-eid__image--five img {
                max-width: 45px;
            }
            .rextheme-eid__image--two img {
                max-width: 85px;
            }
            .rextheme-eid__image--group {
                gap: 20px;
                padding: 0 20px 0 25px;
            }
            .rextheme-eid__image--four img {
                max-width: 200px;
            }
            .rextheme-eid__image--three img {
                max-width: 130px;
            }
            .rextheme-eid__btn::before {
                display: none;
            }
            .rextheme-eid__btn {
                font-size: 12px;
                line-height: 26px;
                padding: 8px 21px;
                font-weight: 400;
            }
            .rextheme-eid__stroke-font {
                font-size: 18px;
            }

            .rextheme-eid__btn-area {
                margin-top: -5px;
            }

            .rextheme-eid__btn {
                box-shadow: none;
            }

        }

        @media only screen and (max-width: 768px) {

            .rextheme-eid__btn-area {
                margin-top: -6px;
            }

            .rex-feed-tb__notification {
                margin: 79px 0 20px;
            }

            .rextheme-eid__content-area {
                max-width: 690px;
            }
            .wpvr-promotional-banner {
                 max-height: 62px;
            }

            .rextheme-eid__image--one img {
                display: none;
            }

            .rextheme-eid__image--two img {
                max-width: 84px;
            }

            .rextheme-eid__image--group {
                gap: 15px;
                padding: 0px 50px 0 66px;
            }

            .rextheme-eid__image--three img {
                max-width: 110px;
                margin-bottom: -6px;
            }
            .rextheme-eid__image--four img {
                max-width: 200px;
            }
            .rextheme-eid__image--five img {
                display: none;
            }
            .rextheme-eid__btn {
                font-size: 12px;
                line-height: 1;
                font-weight: 400;
                padding: 13px 20px;
                margin-left: 0;
                box-shadow: none;
            }

            .rextheme-eid__stroke-font {
                font-size: 18px;
            }
        }

        @media only screen and (max-width: 767px) {
            .wpvr-promotional-banner {
                padding-top: 20px;
                padding-bottom: 30px;
                max-height: none;
            }

            .wpvr-promotional-banner {
                max-height: none;
            }

            .rextheme-eid__image--two {
                display: flex;
                align-items: center;
                gap: 10px;
                flex-direction: row-reverse;
            }
           
            .rextheme-eid__image--five, .rextheme-eid__image--one {
                display: none;
            }
            .rextheme-eid__image--four img {
                margin-right: -25px;
            }
           
            .rextheme-eid__stroke-font {
                font-size: 16px;
            }
            .rextheme-eid__content-area {
                flex-direction: column;
                gap: 25px;
                text-align: center;
                align-items: center;
            }
            .rextheme-eid__btn-area {
                justify-content: center;
                padding-top: 5px;
            }
            .rextheme-eid__btn {
                font-size: 12px;
                padding: 15px 24px;
            }
            .rextheme-eid__image--group {
                gap: 10px;
                padding: 0;
            }
        }

		</style>

		<?php
	}
}