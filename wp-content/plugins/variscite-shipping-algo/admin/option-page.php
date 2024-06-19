<?php

//require_once __DIR__ . '/classes/class.csv-processor.php';

class variShippingAlgoOptionPage {

    function __construct() {
        add_action('admin_menu', array($this, 'algo_add_option_page'), -99);
		add_action('admin_menu', array($this, 'algo_add_option_sub_page'), 99);
    }

    public function algo_add_option_page() {

        acf_add_options_page(array(
            'page_title' => 'Shipping Algo',
            'menu_title' => 'Shipping Algo',
            'menu_slug' => 'variscite-shipping-algo',
            'capability' => 'edit_posts',
            'redirect' => false,
            'icon_url' => 'dashicons-location',
            'position' => 58
        ));

//        $csv_processor = new CsvProcessor();

    }

	public function algo_add_option_sub_page() {

		add_submenu_page(
			'variscite-shipping-algo',
			'Upload CSV',
			'Upload CSV',
			'edit_posts',
			'variscite-shipping-algo-import',
			array( $this, 'import_page_callback' )
		);

	}

	public function import_page_callback() {

		?>
        <div class="upload_container">
            <form method="post" enctype="multipart/form-data">
                <input type="file" name="zones_csv_file" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" />
                <button type="submit">Upload</button>
            </form>
        </div>
		<?php

        if ( isset($_FILES['zones_csv_file']) ) {

	        $zones = get_field('vari__algo_zones-config', 'option');

	        $data = array();

            // Get file content and put it into `data` variable
	        $file_name = $_FILES['zones_csv_file']['tmp_name'];
	        $file = fopen($file_name, 'r');
	        while (($line = fgetcsv($file)) !== FALSE) {
		        //$line is an array of the csv elements
                $data[] = $line;
	        }
	        fclose($file);

	        $data[0][0] = "Weight";

            $processed_data = array();
	        $rest_of_the_world = array();
//            $rest_of_the_world = new stdClass();
            $titles = $data[0];

            for ( $i = 1; $i < count($titles); $i++ ) {
                $obj = array();
//	            $obj = new stdClass();
	            $obj['zone_name'] = $titles[$i];
                $obj['freight_costs'] = array();
                for ( $j = 1; $j < count($data); $j++ ) {
	                $freight_costs = array();
//                    $freight_costs = new stdClass();
                    $freight_costs['the_weight'] = str_replace(" kg", "", $data[$j][0]);
                    $freight_costs['the_cost'] = $data[$j][$i];
                    $obj['freight_costs'][] = $freight_costs;
                }
                foreach ($zones as $z) {
                    if ($z['zone_name'] == $titles[$i]) {
                        $obj['the_discount'] = $z['the_discount'];
                    }
                }
                if (strtolower($titles[$i]) == "rest of the world") {
                    unset($obj['zone_name']);
                    $obj['the_discount'] = get_field('vari__algo_zones-config--other', 'option')['the_discount'];
                    $rest_of_the_world = $obj;
                } else {
	                $processed_data[] = $obj;
                }
            }

            update_field('vari__algo_zones-config', $processed_data, 'option');
            update_field('vari__algo_zones-config--other', $rest_of_the_world, 'option');

            echo "Upload succeeded :)";

        }
	}

}

// Init the class
new variShippingAlgoOptionPage();