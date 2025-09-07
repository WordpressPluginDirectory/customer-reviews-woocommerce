<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'CR_Import_Admin_Menu' ) ):

class CR_Import_Admin_Menu {

    /**
     * @var string URL to admin reviews import page
     */
    protected $page_url;

    /**
     * @var string The slug identifying this menu
     */
    protected $menu_slug;

    /**
     * @var string The slug of the currently displayed tab
     */
    protected $current_tab = 'import';

    /**
     * @var string The slug of this tab
     */
    protected $tab;

    public function __construct() {
        $this->menu_slug = 'cr-import-export';

        $this->page_url = add_query_arg( array(
            'page' => $this->menu_slug
        ), admin_url( 'admin.php' ) );

        if ( isset( $_GET['tab'] ) ) {
            $this->current_tab = $_GET['tab'];
        }

        $this->tab = 'import';

        add_filter( 'cr_import_export_tabs', array( $this, 'register_tab' ) );
        add_action( 'admin_menu', array( $this, 'register_import_menu' ), 11 );
        add_action( 'admin_init', array( $this, 'handle_template_download' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'include_scripts' ) );
    }

    public function register_import_menu() {
        add_submenu_page(
            'cr-reviews',
            __( 'Import / Export', 'customer-reviews-woocommerce' ),
            __( 'Import / Export', 'customer-reviews-woocommerce' ),
            'manage_options',
            $this->menu_slug,
            array( $this, 'display_import_admin_page' )
        );
    }

    public function register_tab( $tabs ) {
        $tabs[$this->tab] = __( 'Import Reviews', 'customer-reviews-woocommerce' );
        return $tabs;
    }

    public function display_import_admin_page() {
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <hr class="wp-header-end">
        <?php

        $tabs = apply_filters( 'cr_import_export_tabs', array() );

        if ( is_array( $tabs ) && sizeof( $tabs ) > 1 ) {
            echo '<ul class="subsubsub">';

            $array_keys = array_keys( $tabs );
            $last = end( $array_keys );

            foreach ( $tabs as $tab => $label ) {
                echo '<li><a href="' . $this->page_url . '&tab=' . $tab . '" class="' . ( $this->current_tab === $tab ? 'current' : '' ) . '">' . $label . '</a> ' . ( $last === $tab ? '' : '|' ) . ' </li>';
            }

            echo '</ul><br class="clear" />';
        }

        if($this->current_tab != $this->tab){

            WC_Admin_Settings::show_messages();

            do_action( 'cr_import_export_display_' . $this->current_tab );

            echo "<div>";

            return ;
        }

        $download_template_url = add_query_arg( array(
            'action'   => 'ivole-download-import-template',
            '_wpnonce' => wp_create_nonce( 'download_csv_template' )
        ), $this->page_url );

        $max_upload_size = size_format(
            wp_max_upload_size()
        );

        $check_loopback = $this->can_perform_loopback();

        ?>
            <div class="ivole-import-container" data-nonce="<?php echo wp_create_nonce( 'cr_import_page' ); ?>">
                <h2><?php echo _e( 'Import Reviews from CSV', 'customer-reviews-woocommerce' ); ?></h2>
                <p><?php
                  _e( 'A utility to import reviews from a CSV file. Use it in three steps. ', 'customer-reviews-woocommerce' );
                  echo '<ol><li>';
                  _e( 'Start with downloading the template for entry of reviews.', 'customer-reviews-woocommerce' );
                  echo '</li><li>';
                  _e( 'Enter reviews to be imported in the template and save it (select CSV UTF-8 format if using MS Excel). Make sure to provide valid product IDs that exist on your WooCommerce site. To import general shop reviews (not related to any particular product), use -1 as a product ID. Please keep the column \'order_id\' blank unless you are importing a file created with the export utility of this plugin.', 'customer-reviews-woocommerce' );
                  echo '</li><li>';
                  _e( 'Finally, upload the template and run import.', 'customer-reviews-woocommerce' );
                  echo '</li></ol>';
                ?></p>
                <div id="ivole-import-upload-steps">
                    <div class="ivole-import-step">
                        <h3 class="ivole-step-title"><?php _e( 'Step 1: Download template', 'customer-reviews-woocommerce' ); ?></h3>
                        <a class="button button-secondary" href="<?php echo esc_url( $download_template_url ); ?>" target="_blank">
                            <?php _e( 'Download', 'customer-reviews-woocommerce' ); ?>
                        </a>
                    </div>

                    <div class="ivole-import-step">
                        <h3 class="ivole-step-title"><?php _e( 'Step 2: Enter reviews into the template', 'customer-reviews-woocommerce' ); ?></h3>
                    </div>

                    <div class="ivole-import-step">
                        <h3 class="ivole-step-title"><?php _e( 'Step 3: Upload template with your reviews', 'customer-reviews-woocommerce' ); ?></h3>
                        <p id="ivole-import-status"></p>
                        <?php
                        if ( 'good' !== $check_loopback->status ):
                        ?>
                          <div id="ivole-import-loopback" style="background-color:#FFA07A;padding:7px;"><?php echo $check_loopback->message; ?></div>
                        <?php
                        else:
                        ?>
                        <div id="ivole-import-filelist">
                            <?php _e( 'No file selected', 'customer-reviews-woocommerce' ); ?>
                        </div>
                        <div id="ivole-upload-container">
                            <table border="0" cellpadding="0" cellspacing="0">
                                <tbody>
                                    <tr>
                                        <td>
                                            <button type="button" id="ivole-select-button"><?php _e( 'Choose File', 'customer-reviews-woocommerce' ); ?></button><br/>
                                            <small>
                                            <?php
                                            printf(
                                                __( 'Maximum size: %s', 'customer-reviews-woocommerce' ),
                                                $max_upload_size
                                            );
                                            ?>
                                            </small>
                                        </td>
                                        <td>
                                            <button type="button" class="button button-primary" id="ivole-upload-button"><?php _e( 'Upload', 'customer-reviews-woocommerce' ); ?></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <?php
                        endif;
                        ?>
                    </div>
                </div>
                <div id="ivole-import-progress">
                    <h2 id="ivole-import-text"><?php _e( 'Import is in progress', 'customer-reviews-woocommerce' ); ?></h2>
                    <progress id="ivole-progress-bar" max="100" value="0"></progress>
                    <div>
                        <button id="ivole-import-cancel" class="button button-secondary"><?php _e( 'Cancel', 'customer-reviews-woocommerce' ); ?></button>
                    </div>
                </div>
                <div id="ivole-import-results">
                    <h3 id="ivole-import-result-status"><?php _e( 'Upload Completed', 'customer-reviews-woocommerce' ); ?></h3>
                    <p id="ivole-import-result-started"></p>
                    <p id="ivole-import-result-finished"></p>
                    <p id="ivole-import-result-imported"></p>
                    <p id="ivole-import-result-skipped"></p>
                    <p id="ivole-import-result-errors"></p>
                    <div id="ivole-import-result-details" style="display:none;">
                        <h4><?php _e( 'Details:', 'customer-reviews-woocommerce' ); ?></h4>
                    </div>
                    <br>
                    <a href="" class="button button-secondary"><?php _e( 'New Upload', 'customer-reviews-woocommerce' ); ?></a>
                </div>
            </div>
          </div>
        <?php
    }

    /**
     * Generates a CSV template file and sends it to the browser
     */
    public function handle_template_download() {
        if (
          isset( $_GET['action'] ) &&
          in_array( $_GET['action'], array( 'ivole-download-import-template', 'cr-download-import-qna-template' ) )
        ) {
            // Ensure a valid nonce has been provided
            if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'download_csv_template' ) ) {
                wp_die( sprintf( __( 'Failed to download template: invalid nonce. <a href="%s">Return to settings</a>', 'customer-reviews-woocommerce' ), $this->page_url ) );
            }

            if ( 'cr-download-import-qna-template' === $_GET['action'] ) {
              $template_data = array(
                  array(
                      'qna_id',
                      'qna_content',
                      'qna_parent',
                      'date',
                      'product_id',
                      'product_sku',
                      'display_name',
                      'email'
                  ),
                  array(
                      '1',
                      __( 'Does this t-shirt shrink after washing?', 'customer-reviews-woocommerce' ),
                      '',
                      '2025-04-01 15:30:05',
                      '22',
                      '',
                      __( 'Example Customer', 'customer-reviews-woocommerce' ),
                      'example.customer@mail.com'
                  ),
                  array(
                      '2',
                      __( 'The t-shirt is made from pre-shrunk cotton, so it holds its size well after washing.', 'customer-reviews-woocommerce' ),
                      '1',
                      '2025-04-02 10:22:07',
                      '22',
                      '',
                      __( 'Sample Store Manager', 'customer-reviews-woocommerce' ),
                      'sample.store.manager@mail.com'
                  ),
                  array(
                      '3',
                      __( 'To keep the best fit, we recommend washing in cold water and air drying, as this helps minimize any natural fabric shrinkage over time.', 'customer-reviews-woocommerce' ),
                      '1',
                      '2025-05-18 17:24:43',
                      '',
                      'sku-24',
                      __( 'Another Store Manager', 'customer-reviews-woocommerce' ),
                      'another.store.manager@mail.com'
                  )
              );
              $file_name = 'qna-import-template.csv';
            } else {
              $template_data = array(
                  array(
                      'review_content',
                      'review_score',
                      'date',
                      'product_id',
                      'product_sku',
                      'display_name',
                      'email',
                      'order_id',
                      'media'
                  ),
                  array(
                      __( 'This product is great!', 'customer-reviews-woocommerce' ),
                      '5',
                      '2018-07-01 15:30:05',
                      12,
                      'sku-123',
                      __( 'Example Customer', 'customer-reviews-woocommerce' ),
                      'example.customer@mail.com',
                      '',
                      'https://www.example.com/image-1.jpeg,https://www.example.com/image-2.jpeg,https://www.example.com/video-1.mp4'
                  ),
                  array(
                      __( 'This product is not so great.', 'customer-reviews-woocommerce' ),
                      '1',
                      '2017-04-15 09:54:32',
                      22,
                      'sku-456',
                      __( 'Sample Customer', 'customer-reviews-woocommerce' ),
                      'sample.customer@mail.com',
                      '',
                      ''
                  ),
                  array(
                      __( 'This is a shop review. Note that the product_id is -1 and product_sku is blank. Customer service is good!', 'customer-reviews-woocommerce' ),
                      '4',
                      '2017-04-18 10:24:43',
                      -1,
                      '',
                      __( 'Sample Customer', 'customer-reviews-woocommerce' ),
                      'sample.customer@mail.com',
                      '',
                      ''
                  )
              );
              $file_name = 'review-import-template.csv';
            }

            $stdout = fopen( 'php://output', 'w' );
            $length = 0;

            foreach ( $template_data as $row ) {
                $length += fputcsv( $stdout, $row );
            }

            header( 'Content-Description: File Transfer' );
            header( 'Content-Type: application/octet-stream' );
            header( 'Content-Disposition: attachment; filename="' . $file_name . '"' );
            header( 'Content-Transfer-Encoding: binary' );
            header( 'Connection: Keep-Alive' );
            header( 'Expires: 0' );
            header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
            header( 'Pragma: public' );
            header( 'Content-Length: ' . $length );
            fclose( $stdout );
            exit;
        }
    }

    public function include_scripts() {
        if ( $this->is_this_page() ) {
            wp_register_script( 'cr-admin-import', plugins_url( 'js/admin-import.js', dirname( dirname( __FILE__ ) ) ), [ 'wp-plupload', 'media', 'jquery' ], Ivole::CR_VERSION );
            wp_localize_script( 'cr-admin-import', 'ivoleImporterStrings', array(
                'uploading'          => __( 'Upload progress: %s%', 'customer-reviews-woocommerce' ),
                'importing'          => __( 'Import is in progress (%s/%s completed)', 'customer-reviews-woocommerce' ),
                'filelist_empty'     => __( 'No file selected', 'customer-reviews-woocommerce' ),
                'cancelling'         => __( 'Cancelling', 'customer-reviews-woocommerce' ),
                'cancel'             => __( 'Cancel', 'customer-reviews-woocommerce' ),
                'upload_cancelled'   => __( 'Upload Cancelled', 'customer-reviews-woocommerce' ),
                'upload_failed'      => __( 'Upload Failed', 'customer-reviews-woocommerce' ),
                'result_started'     => __( 'Started: %s', 'customer-reviews-woocommerce' ),
                'result_finished'    => __( 'Finished: %s', 'customer-reviews-woocommerce' ),
                'result_cancelled'   => __( 'Cancelled: %s', 'customer-reviews-woocommerce' ),
                'result_imported'    => __( '%d review(s) successfully uploaded', 'customer-reviews-woocommerce' ),
                'result_skipped'     => __( '%d duplicate review(s) skipped', 'customer-reviews-woocommerce' ),
                'result_errors'      => __( '%d error(s)', 'customer-reviews-woocommerce' ),
                'result_q_imported'  => __( '%d question(s) successfully uploaded', 'customer-reviews-woocommerce' ),
                'result_a_imported'  => __( '%d answer(s) successfully uploaded', 'customer-reviews-woocommerce' ),
                'result_q_skipped'   => __( '%d duplicate question(s) skipped', 'customer-reviews-woocommerce' ),
                'result_a_skipped'   => __( '%d duplicate answer(s) skipped', 'customer-reviews-woocommerce' )
            ) );
            wp_enqueue_media();
            wp_enqueue_script( 'cr-admin-import' );
            wp_enqueue_style( 'cr-import-export-css', plugins_url( 'css/import-export.css', dirname( dirname( __FILE__) ) ), array(), Ivole::CR_VERSION );
        }
    }

    public function is_this_page() {
        return ( isset( $_GET['page'] ) && $_GET['page'] === $this->menu_slug );
    }

    public function get_page_slug() {
        return $this->menu_slug;
    }

    private function can_perform_loopback() {
      $cookies = wp_unslash( $_COOKIE );
      if( isset( $cookies[session_name()] ) ) {
        unset( $cookies[session_name()] );
      }
  		$timeout = 10;
  		$headers = array(
  			'Cache-Control' => 'no-cache',
  		);

  		// Include Basic auth in loopback requests.
  		if ( isset( $_SERVER['PHP_AUTH_USER'] ) && isset( $_SERVER['PHP_AUTH_PW'] ) ) {
  			$headers['Authorization'] = 'Basic ' . base64_encode( wp_unslash( $_SERVER['PHP_AUTH_USER'] ) . ':' . wp_unslash( $_SERVER['PHP_AUTH_PW'] ) );
  		}

  		$url = admin_url();

  		$r = wp_remote_get( $url, compact( 'cookies', 'headers', 'timeout' ) );

  		if ( is_wp_error( $r ) ) {
  			return (object) array(
  				'status'  => 'critical',
  				'message' => sprintf(
  					'%s<br>%s',
  					__( 'The loopback request to your site failed. This means that import of reviews will not be working as expected. If you would like to use the import utility, please contact your hosting provider and request them to enable loopback requests for your site.', 'customer-reviews-woocommerce' ),
  					sprintf(
  						// translators: 1: The HTTP response code. 2: The error message returned.
  						__( 'Error: [%1$s] %2$s', 'customer-reviews-woocommerce' ),
  						wp_remote_retrieve_response_code( $r ),
  						$r->get_error_message()
  					)
  				),
  			);
  		}

  		if ( 200 !== wp_remote_retrieve_response_code( $r ) ) {
  			return (object) array(
  				'status'  => 'recommended',
  				'message' => sprintf(
  					// translators: %d: The HTTP response code returned.
  					__( 'The loopback request returned an unexpected http status code, %d. This means that import of reviews will not be working as expected. If you would like to use the import utility, please contact your hosting provider and request them to enable loopback requests for your site.', 'customer-reviews-woocommerce' ),
  					wp_remote_retrieve_response_code( $r )
  				),
  			);
  		}

  		return (object) array(
  			'status'  => 'good',
  			'message' => __( 'The loopback request to your site completed successfully.' ),
  		);
  	}
}

endif;
