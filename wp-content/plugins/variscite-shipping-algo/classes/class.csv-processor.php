<?php

class CsvProcessor {

    function __construct() {
        echo "ASDF";
        add_action( 'acf/upload_prefilter', array($this, 'process_csv'));
    }

    public function process_csv( $errors, $file, $field ) {
        die($file);
    }

}

// Init the class
new CsvProcessor();