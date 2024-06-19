<?php	
/***************************************************************************************
 **	GLOBAL AJAX ACTIONS
 ***************************************************************************************/

function general_convert_symbols_str( $val ){
	$val = htmlspecialchars_decode( $val );
	$val = str_replace( '\r', '', $val );
	$val = str_replace( '\"', "'", $val );
	$val = str_replace( "\'", "'", $val );
	$val = str_replace( array( "&quot;", "\&quot;" ), '"', $val );
	$val = str_replace( array( "\&forall;", "\&part;", "\&exist;", "\&empty;", "\&nabla;", "\&isin;", "\&notin;", "\&ni;", "\&prod;", "\&sum;", "\&Alpha;", "\&Beta;", "\&Gamma;", "\&Delta;", "\&Epsilon;", "\&Zeta;", "\&copy;", "\&reg;", "\&euro;", "\&trade;", "\&larr;", "\&uarr;", "\&rarr;", "\&darr;", "\&spades;", "\&clubs;", "\&hearts;", "\&diams;", "\&lt;br&gt;", "\&lt;", "\&gt;", "&lt;", "&gt;", "&amp;" ), array( "∀", "∂", "∃", "∅", "∇", "∈", "∉", "∋", "∏", "∑", "Α", "Β", "Γ", "Δ", "Ε", "Ζ", "©", "®", "€", "™", "←", "↑", "→", "↓", "♠", "♣", "♥", "♦", " ", "<", ">", "<", ">", "&" ), $val );
	
	return $val;
}

add_action('wp_ajax_global_ajaxfunc', 'global_ajaxfunc');
add_action('wp_ajax_nopriv_global_ajaxfunc', 'global_ajaxfunc');
function global_ajaxfunc(){
	$sfdcIntegration = new newsletterSFDCIntegration();
	
    $lang = ICL_LANGUAGE_CODE;
    $actionType = sanitize_text_field($_POST['action_type']);

    /*********************************************************
     **	SEND QUOTE FROM SPECS PAGE
     *********************************************************/
    if($actionType == 'send_quote') {

        $data 		= $_POST['form_data'];
        $settings	= get_field('quote_settings', 'option');


        foreach( $data as $key => $item ){
            if( $key == 'note' ){
                $email[$key] = $item;
            }else if( $key == 'agreement' ){
				$item = str_replace( ";", "", $item );
				$email[$key] = $item;
			}else{
                $email[$key] = sanitize_text_field( $item );
            }
        }

//
//		$email['som'] 		= sanitize_text_field( implode(', ', $data['som']) );
//		$email['sys']		= sanitize_text_field( implode(', ', $data['sys']) );
//		$email['amount'] 	= sanitize_text_field( implode(', ', $data['amount']) );

        if( strlen($email['first_name']) < 2 ) { wp_send_json_error( array('msg' => __('Please enter a valid first name.') ) ); }
        elseif( !is_valid_email($email['email']) ) { wp_send_json_error( array('msg' => __('Enter a valid email.') ) ); }
        elseif( empty($email['phone']) ) { wp_send_json_error( array('msg' => __('Enter a valid phone.') ) );}
        else {

            // Validate max length of first and last names
            if(strlen($email['first_name']) > 50) {
                wp_send_json_error( array('msg' => __('Please enter a valid first name.') ));
            }

            if(strlen($email['last_name']) > 50) {
                wp_send_json_error( array('msg' => __('Please enter a valid first name.') ));
            }

            /*************************
             ** SAVE EMAIL TO BACKEND
             **************************/
            $post = array(
                'post_title'    => __('New Lead From', THEME_NAME).': '.$email['first_name'].' '.$email['last_name'],
                'post_status'   => 'publish',
                'post_type'		=> 'leads'
            );
            $lid = wp_insert_post($post, 10, 1);
//			do_action('wp_insert_post', 'wp_insert_post', 10, 1);

            // user info
            update_field('first_name', $email['first_name'], $lid);
            update_field('last_name', $email['last_name'], $lid);
            update_field('email', $email['email'], $lid);
            update_field('company', $email['company'], $lid);
            update_field('country', $email['country_code'], $lid);
            update_field('phone', $email['phone'], $lid);
            update_field('note', $email['note'], $lid);

            // product info
            update_field('som', $email['quote-product'], $lid);
            update_field('quan', $email['quote-quantity'], $lid);
            update_field('product_page', $email['Product_page__c'], $lid);

            // custom info
            update_field('curl', $email['curl'], $lid);
//			update_field('cdevice', $email['device'], $lid);
            update_field('lead_source', $email['leadsource'], $lid);
            update_field('lead_record_created', 'on', $lid);

            // campagin info
            update_field('campagin_medium', $email['Campaign_medium__c'], $lid);
            update_field('campagin_source', $email['Campaign_source__c'], $lid);
            update_field('campagin_content', $email['Campaign_content__c'], $lid);
            update_field('campagin_term', $email['Campaign_term__c'], $lid);
            update_field('page_url__c', $email['curl'], $lid);
            update_field('paid_campaign_name__c', $email['Paid_Campaign_Name__c'], $lid);
            update_field('ga_id', $email['GA_id__c'], $lid);

            if( $email['agreement'] ){
				$agreement = str_replace( ";", "", $email['agreement'] );
                update_field( 'privacy_policy', $agreement, $lid );
            }

            /*************************
             ** SEND EMAIL TO OWNERS
             **************************/
            $message = '
				<h4>'.__('Sender Information', THEME_NAME).'</h4>
				<ul>
					<li> <strong>From:</strong> '.$email['first_name'].' '.$email['last_name'].'</li>
					<li> <strong>Phone:</strong> '.$email['phone'].'</li>
					<li> <strong>Email:</strong> '.$email['email'].'</li>
					<li> <strong>Company:</strong> '.$email['company'].'</li>
					<li> <strong>Country:</strong> '.$email['country_code'].'</li>
					<li> <strong>Note:</strong><br> '.$email['note'].'</li>
				</ul>
				<br>

				<h4>'.__('Product Information', THEME_NAME).'</h4>
				<ul>
					<li> <strong>System on Module:</strong> '.$email['quote-product'].'</li>
					<li> <strong>Estimated Quantities:</strong> '.$email['quote-quantity'].'</li>
					<li> <strong>Product Page URL:</strong> '.$email['Product_page__c'].'</li>
				</ul>
				<br>

				<h4>'.__('Additional Information', THEME_NAME).'</h4>
				<ul>
					<li> <strong>Origin Product:</strong> '.$email['curl'].'</li>
					<li> <strong>User Device:</strong> '.$email['device'].'</li>
					<li> <strong>Lead Source:</strong> '.$email['leadsource'].'</li>
				</ul>


				<h4>'.__('Campagin Information (optional)', THEME_NAME).'</h4>
				<ul>
					<li> <strong>Campagin Medium:</strong> '.$email['Campaign_medium__c'].'</li>
					<li> <strong>Campagin Source:</strong> '.$email['Campaign_source__c'].'</li>
					<li> <strong>Campagin Content:</strong> '.$email['Campaign_content__c'].'</li>
					<li> <strong>Campagin Term:</strong> '.$email['Campaign_term__c'].'</li>
					<li> <strong>Page_url__c:</strong> '.$email['curl'].'</li>
					<li> <strong>Paid_Campaign_Name__c:</strong> '.$email['Paid_Campaign_Name__c'].'</li>
					<li> <strong>GA_id__c:</strong> '.$email['GA_id__c'].'</li>
				</ul>
            ';


            /*************************
             ** SEND EMAIL TO SALESFORCE
             **************************/

            $email['quote-product'] = str_replace(' / ', ' ', $email['quote-product']);
            rtrim($email['quote-product'], ';');


            // BUILD FIELD FOR CURL URL
//				$fieldURL 	= array();

            // CONNECTION STUFF
            $sfdc_data = array(
                'FirstName' => htmlspecialchars($email['first_name']),
                'LastName' => htmlspecialchars($email['last_name']),
                'Company' => htmlspecialchars($email['company']),
                'Email' => htmlspecialchars($email['email']),
                'Country' => ucwords( str_replace('-', ' ', htmlspecialchars($email['country_code']) )),
                'Phone' => htmlspecialchars($email['phone']),
                'Note__c' => htmlspecialchars(str_replace("\n", "\\n", $email['note'])),
                'Processor__c' => htmlspecialchars($email['quote-product']),
                'Projected_Quantities__c' => htmlspecialchars($email['quote-quantity']),
                'Product_page__c' => htmlspecialchars($email['Product_page__c']),
                'LeadSource' => htmlspecialchars($email['leadsource']),
                'Campaign_medium__c' => htmlspecialchars($email['Campaign_medium__c']),
                'Campaign_source__c' => htmlspecialchars($email['Campaign_source__c']),
                'Campaign_content__c' => htmlspecialchars($email['Campaign_content__c']),
                'Campaign_term__c' => htmlspecialchars($email['Campaign_term__c']),
                'Page_url__c' => htmlspecialchars($email['curl']),
                'Paid_Campaign_Name__c' => htmlspecialchars($email['Paid_Campaign_Name__c']),
                'GA_id__c' => htmlspecialchars($email['GA_id__c'])
            );

            if($email['agreement']) {
                $sfdc_data['Privacy_Policy__c'] = htmlspecialchars($email['agreement']);
            }
			
			$sfdc_data_n = array();
			foreach( $sfdc_data as $sfkey => $sfvalue ){
				$sfdc_data_n[$sfkey] = general_convert_symbols_str( $sfvalue );
			}
			
            // Save the SFDC data as a JSON in the lead CPT
            $json_encoded = json_encode($sfdc_data_n, JSON_UNESCAPED_UNICODE);
            $json_encoded = str_replace("\&quot;", "&quot;", $json_encoded);
            $json_encoded = str_replace("\'", "'", $json_encoded);
            update_field('sfdc_object_to_be_sent', $json_encoded, $lid);

            // Save the email message in the lead CPT
            update_field('email_message_to_be_sent', $message, $lid);
            update_field('email_subject_to_be_sent', '[New Lead from product page] '. $email['company'] . ' [' . strtoupper($lang).']', $lid);

            $resData = array(
                'thanks' => $email['thanks'],
            );

            wp_send_json_success($resData);
        }
    }



    /*********************************************************
     ** XXX
     *********************************************************/
    if($actionType == 'send_widget_quote') {

        $data 		= $_POST['form_data'];
        $settings	= get_field('quote_settings', 'option');


        foreach( $data as $key => $item ){
           if( $key == 'note' ){
                $email[$key] = $item;
            }else if( $key == 'agreement' ){
				$item = str_replace( ";", "", $item );
				$email[$key] = $item;
			}else{
                $email[$key] = sanitize_text_field($item);
            }
        }



        /*************************
         ** SAVE EMAIL TO BACKEND
         **************************/
        $post = array(
            'post_title'    => __('New Lead From', THEME_NAME).': '.$email['first_name'].' '.$email['last_name'],
            'post_status'   => 'publish',
            'post_type'		=> 'leads'
        );
        $lid = wp_insert_post( $post, 10, 1 );
//		do_action('wp_insert_post', 'wp_insert_post', 10, 1);

        // user info
        update_field('first_name', $email['first_name'], $lid);
        update_field('last_name', $email['last_name'], $lid);
        update_field('email', $email['email'], $lid);
        update_field('company', $email['company'], $lid);
        update_field('country', $email['country'], $lid);
        update_field('phone', $email['phone'], $lid);
        update_field('note', $email['note'], $lid);

        // product info
        update_field('som', $email['System__c'], $lid);
        update_field('quan', $email['Projected_Quantities__c'], $lid);
        update_field('product_page', $email['Product_page__c'], $lid);

        // custom info
        update_field('curl', $email['curl'], $lid);
//		update_field('cdevice', $email['cdevice'], $lid);
        update_field('lead_record_created', 'on', $lid);

        // campagin info
        update_field('campagin_medium', $email['Campaign_medium__c'], $lid);
        update_field('campagin_source', $email['Campaign_source__c'], $lid);
        update_field('campagin_content', $email['Campaign_content__c'], $lid);
        update_field('campagin_term', $email['Campaign_term__c'], $lid);
        update_field('page_url__c', $email['curl'], $lid);
        update_field('paid_campaign_name__c', $email['Paid_Campaign_Name__c'], $lid);
        update_field('ga_id', $email['GA_id__c'], $lid);

        if($email['agreement']) {
            $agreement = str_replace( ";", "", $email['agreement'] );
            update_field( 'privacy_policy', $agreement, $lid );
        }

        /*************************
         ** SEND EMAIL TO OWNERS
         **************************/
        $message = '
		<h4>'.__('Sender Information', THEME_NAME).'</h4>
		<ul>
			<li> <strong>From:</strong> '.$email['first_name'].' '.$email['last_name'].'</li>
			<li> <strong>Phone:</strong> '.$email['phone'].'</li>
			<li> <strong>Email:</strong> '.$email['email'].'</li>
			<li> <strong>Company:</strong> '.$email['company'].'</li>
			<li> <strong>Country:</strong> '.$email['country'].'</li>
			<li> <strong>Note:</strong><br> '.$email['note'].'</li>
		</ul>
		<br>

		<h4>'.__('Product Information', THEME_NAME).'</h4>
		<ul>
			<li> <strong>System on Module:</strong> '.$email['System__c'].'</li>
			<li> <strong>Estimated Quantities:</strong> '.$email['Projected_Quantities__c'].'</li>
			<li> <strong>Product Page URL:</strong> '.$email['Product_page__c'].'</li>
		</ul>
		<br>

		<h4>'.__('Additional Information', THEME_NAME).'</h4>
		<ul>
			<li> <strong>Origin Product:</strong> '.$email['curl'].'</li>
			<li> <strong>User Device:</strong> '.$email['device'].'</li>
		</ul>

		<h4>'.__('Campagin Information (optional)', THEME_NAME).'</h4>
		<ul>
			<li> <strong>Campagin Medium:</strong> '.$email['Campaign_medium__c'].'</li>
			<li> <strong>Campagin Source:</strong> '.$email['Campaign_source__c'].'</li>
			<li> <strong>Campagin Content:</strong> '.$email['Campaign_content__c'].'</li>
			<li> <strong>Campagin Term:</strong> '.$email['Campaign_term__c'].'</li>
            <li> <strong>Page_url__c:</strong> '.$email['curl'].'</li>
            <li> <strong>Paid_Campaign_Name__c:</strong> '.$email['Paid_Campaign_Name__c'].'</li>
            <li> <strong>GA ID:</strong> '.$email['GA_id__c'].'</li>
		</ul>
		';

        if(strpos($email['email_subject'], 'landing') != false){
            $subjectString = '[New Lead from landing page] ';
        } else if(strpos($email['email_subject'], 'Web-exit') != false){
            $subjectString = '[New lead from Web-exit popup] ';
        } else {
            $subjectString = '[New Lead from contact us page] ';
        }

        /*************************
         ** SEND EMAIL TO SALESFORCE
         **************************/

        // REMOVE TEXT AFTER DOTS ":" IN PRODUCT NAME
//		$tmpProductNames 	= explode(',', $email['som']);
//		$email['som'] = '';
//
//		foreach($tmpProductNames as $tmpProdNm) {
//			$tmp			= explode(':', $tmpProdNm);
//			$email['som'] 	.= $tmp[0].',';
//		}
//		rtrim($email['som'], ',');


        // BUILD FIELD FOR CURL URL
//		$fieldURL 	= array();

        // CONNECTION STUFF
        $sfdc_data = array(
            'FirstName' => htmlspecialchars($email['first_name']),
            'LastName' => htmlspecialchars($email['last_name']),
            'Company' => htmlspecialchars($email['company']),
            'Email' => htmlspecialchars($email['email']),
            'Country' => ucwords( str_replace('-', ' ', htmlspecialchars($email['country']) )),
            'Phone' => htmlspecialchars($email['phone']),
            'Note__c' => htmlspecialchars(str_replace("\n", "\\n", $email['note'])),
            'System__c' => htmlspecialchars($email['System__c']),
            'Projected_Quantities__c' => htmlspecialchars($email['Projected_Quantities__c']),
            'Product_page__c' => htmlspecialchars($email['Product_page__c']),
            'LeadSource' => htmlspecialchars($email['lead_source']),
            'Campaign_medium__c' => htmlspecialchars($email['Campaign_medium__c']),
            'Campaign_source__c' => htmlspecialchars($email['Campaign_source__c']),
            'Campaign_content__c' => htmlspecialchars($email['Campaign_content__c']),
            'Campaign_term__c' => htmlspecialchars($email['Campaign_term__c']),
            'Page_url__c' => htmlspecialchars($email['curl']),
            'Paid_Campaign_Name__c' => htmlspecialchars($email['Paid_Campaign_Name__c']),
            'GA_id__c' => htmlspecialchars($email['GA_id__c'])
        );

        if($email['agreement']) {
            $sfdc_data['Privacy_Policy__c'] = $email['agreement'];
        }
		
		$sfdc_data_n = array();
		foreach( $sfdc_data as $sfkey => $sfvalue ){
			$sfdc_data_n[$sfkey] = general_convert_symbols_str( $sfvalue );
		}
			
        // Save the SFDC data as a JSON in the lead CPT
        $json_encoded = json_encode($sfdc_data_n, JSON_UNESCAPED_UNICODE);
        $json_encoded = str_replace("\&quot;", "&quot;", $json_encoded);
        $json_encoded = str_replace("\'", "'", $json_encoded);
        update_field('sfdc_object_to_be_sent', $json_encoded, $lid);

        // Save the email message in the lead CPT
        update_field('email_message_to_be_sent', $message, $lid);
        update_field('email_subject_to_be_sent', $subjectString . $email['company'] . ' [' . strtoupper($lang).']', $lid);

        $resData = array(
            'thanks' => $email['thanks'],
        );

        wp_send_json_success($resData);
    }

    /*********************************************************
     ** Send Exit Popup Form 2022
     *********************************************************/
    if($actionType == 'send_exitPopup_form') {

        $data 		= $_POST['form_data'];
        $settings	= get_field('quote_settings', 'option');

		foreach( $data as $key => $item ){
            if($key == 'note') {
                $email[$key] = $item;
            }else if( $key == 'agreement' ){
				$item = str_replace( ";", "", $item );
				$email[$key] = $item;
			}else{
                $email[$key] = sanitize_text_field($item);
            }
        }

//
//		$email['som'] 		= sanitize_text_field( implode(', ', $data['som']) );
//		$email['sys']		= sanitize_text_field( implode(', ', $data['sys']) );
//		$email['amount'] 	= sanitize_text_field( implode(', ', $data['amount']) );

        if( strlen($email['first_name']) < 2 ) { wp_send_json_error( array('msg' => __('Please enter a valid first name.') ) ); }
        elseif( !is_valid_email($email['email']) ) { wp_send_json_error( array('msg' => __('Enter a valid email.') ) ); }
        elseif( empty($email['phone']) ) { wp_send_json_error( array('msg' => __('Enter a valid phone.') ) );}
        else {

            // Validate max length of first and last names
            if(strlen($email['first_name']) > 50) {
                wp_send_json_error( array('msg' => __('Please enter a valid first name.') ));
            }

            if(strlen($email['last_name']) > 50) {
                wp_send_json_error( array('msg' => __('Please enter a valid first name.') ));
            }

            /*************************
             ** SAVE EMAIL TO BACKEND
             **************************/
            $post = array(
                'post_title'    => __('New Lead From', THEME_NAME).': '.$email['first_name'].' '.$email['last_name'],
                'post_status'   => 'publish',
                'post_type'		=> 'leads'
            );
            $lid = wp_insert_post($post, 10, 1);
//			do_action('wp_insert_post', 'wp_insert_post', 10, 1);

            // user info
            update_field('first_name', $email['first_name'], $lid);
            update_field('last_name', $email['last_name'], $lid);
            update_field('email', $email['email'], $lid);
            update_field('company', $email['company'], $lid);
            update_field('country', $email['country_exit'], $lid);
            update_field('phone', $email['phone'], $lid);
            update_field('note', $email['note'], $lid);

            // product info
            update_field('som', $email['quote-product'], $lid);
            update_field('quan', $email['quote-quantity'], $lid);
            update_field('product_page', $email['Product_page__c'], $lid);

            // custom info
            update_field('curl', $email['curl'], $lid);
//			update_field('cdevice', $email['device'], $lid);
            update_field('lead_source', $email['lead_source'], $lid);
            update_field('lead_record_created', 'on', $lid);

            // campagin info
            update_field('campagin_medium', $email['Campaign_medium__c'], $lid);
            update_field('campagin_source', $email['Campaign_source__c'], $lid);
            update_field('campagin_content', $email['Campaign_content__c'], $lid);
            update_field('campagin_term', $email['Campaign_term__c'], $lid);
            update_field('page_url__c', $email['curl'], $lid);
            update_field('paid_campaign_name__c', $email['Paid_Campaign_Name__c'], $lid);
            update_field('ga_id', $email['GA_id__c'], $lid);

            if($email['agreement']) {
                $agreement = str_replace( ";", "", $email['agreement'] );
                update_field( 'privacy_policy', $agreement, $lid );
            }

            /*************************
             ** SEND EMAIL TO OWNERS
             **************************/
            $message = '
				<h4>'.__('Sender Information', THEME_NAME).'</h4>
				<ul>
					<li> <strong>From:</strong> '.$email['first_name'].' '.$email['last_name'].'</li>
					<li> <strong>Phone:</strong> '.$email['phone'].'</li>
					<li> <strong>Email:</strong> '.$email['email'].'</li>
					<li> <strong>Company:</strong> '.$email['company'].'</li>
					<li> <strong>Country:</strong> '.$email['country_exit'].'</li>
					<li> <strong>Note:</strong><br> '.$email['note'].'</li>
				</ul>
				<br>

				<h4>'.__('Product Information', THEME_NAME).'</h4>
				<ul>
					<li> <strong>System on Module:</strong> '.$email['quote-product'].'</li>
					<li> <strong>Estimated Quantities:</strong> '.$email['quote-quantity'].'</li>
					<li> <strong>Product Page URL:</strong> '.$email['Product_page__c'].'</li>
				</ul>
				<br>

				<h4>'.__('Additional Information', THEME_NAME).'</h4>
				<ul>
					<li> <strong>Origin Product:</strong> '.$email['curl'].'</li>
					<li> <strong>User Device:</strong> '.$email['device'].'</li>
					<li> <strong>Lead Source:</strong> '.$email['lead_source'].'</li>
				</ul>


				<h4>'.__('Campagin Information (optional)', THEME_NAME).'</h4>
				<ul>
					<li> <strong>Campagin Medium:</strong> '.$email['Campaign_medium__c'].'</li>
					<li> <strong>Campagin Source:</strong> '.$email['Campaign_source__c'].'</li>
					<li> <strong>Campagin Content:</strong> '.$email['Campaign_content__c'].'</li>
					<li> <strong>Campagin Term:</strong> '.$email['Campaign_term__c'].'</li>
					<li> <strong>Page_url__c:</strong> '.$email['curl'].'</li>
					<li> <strong>Paid_Campaign_Name__c:</strong> '.$email['Paid_Campaign_Name__c'].'</li>
					<li> <strong>GA_id__c:</strong> '.$email['GA_id__c'].'</li>
				</ul>
            ';


            /*************************
             ** SEND EMAIL TO SALESFORCE
             **************************/

            $email['quote-product'] = str_replace(' / ', ' ', $email['quote-product']);
            rtrim($email['quote-product'], ';');


            // BUILD FIELD FOR CURL URL
//				$fieldURL 	= array();

            // CONNECTION STUFF
            $sfdc_data = array(
                'FirstName' => htmlspecialchars($email['first_name']),
                'LastName' => htmlspecialchars($email['last_name']),
                'Company' => htmlspecialchars($email['company']),
                'Email' => htmlspecialchars($email['email']),
                'Country' => ucwords( str_replace('-', ' ', htmlspecialchars($email['country_exit']) )),
                'Phone' => htmlspecialchars($email['phone']),
                'Note__c' => htmlspecialchars(str_replace("\n", "\\n", $email['note'])),
                'Processor__c' => htmlspecialchars($email['quote-product']),
                'Projected_Quantities__c' => htmlspecialchars($email['quote-quantity']),
                'Product_page__c' => htmlspecialchars($email['Product_page__c']),
                'LeadSource' => htmlspecialchars($email['lead_source']),
                'Campaign_medium__c' => htmlspecialchars($email['Campaign_medium__c']),
                'Campaign_source__c' => htmlspecialchars($email['Campaign_source__c']),
                'Campaign_content__c' => htmlspecialchars($email['Campaign_content__c']),
                'Campaign_term__c' => htmlspecialchars($email['Campaign_term__c']),
                'Page_url__c' => htmlspecialchars($email['curl']),
                'Paid_Campaign_Name__c' => htmlspecialchars($email['Paid_Campaign_Name__c']),
                'GA_id__c' => htmlspecialchars($email['GA_id__c'])
            );

            if($email['agreement']) {
                $sfdc_data['Privacy_Policy__c'] = $email['agreement'];
            }
			
			$sfdc_data_n = array();
			foreach( $sfdc_data as $sfkey => $sfvalue ){
				$sfdc_data_n[$sfkey] = general_convert_symbols_str( $sfvalue );
			}
			
            // Save the SFDC data as a JSON in the lead CPT
            $json_encoded = json_encode($sfdc_data_n, JSON_UNESCAPED_UNICODE);
            $json_encoded = str_replace("\&quot;", "&quot;", $json_encoded);
            $json_encoded = str_replace("\'", "'", $json_encoded);
            update_field('sfdc_object_to_be_sent', $json_encoded, $lid);

            // Save the email message in the lead CPT
            update_field('email_message_to_be_sent', $message, $lid);
            update_field('email_subject_to_be_sent', '[New Lead from product page] '. $email['company'] . ' [' . strtoupper($lang).']', $lid);

            $resData = array(
                'thanks' => $email['thanks'],
            );

            wp_send_json_success($resData);
        }

    }


    /*********************************************************
     ** Contact Popup form request
     *********************************************************/
    if($actionType == 'send_popup_request') {

        $data 		= $_POST['form_data'];

        $popup_form_email_to = get_field('popup_form_email_to','option');
        $popup_form_subject = get_field('popup_form_subject','option');
		
        foreach( $data as $key => $item ){
            $key = str_replace('popup_','',$key);
			if( $key == 'agreement' ){
				$item = str_replace( ";", "", $item );
				$email[$key] = $item;
			}
            if($key == 'note') { $email[$key] = sanitize_textarea_field($item); }
            else { $email[$key] = sanitize_text_field($item); }
        }


        /*************************
         ** SAVE EMAIL TO BACKEND
         **************************/
        $post = array(
            'post_title'    => __('New Lead From', THEME_NAME).': '.$email['first_name'].' '.$email['last_name'],
            'post_status'   => 'publish',
            'post_type'		=> 'leads'
        );
        $lid = wp_insert_post($post, 10, 1);
//		do_action('wp_insert_post', 'wp_insert_post', 10, 1);

        // user info
        update_field('first_name', $email['first_name'], $lid);
        update_field('last_name', $email['last_name'], $lid);
        update_field('email', $email['email'], $lid);
        update_field('company', $email['company'], $lid);
        update_field('country', $email['country'], $lid);
        update_field('phone', $email['telephone'], $lid);
        update_field('note', $email['note'], $lid);

        // product info
        update_field('som', $email['System__c'], $lid);
        update_field('quan', $email['Projected_Quantities__c'], $lid);
        update_field('product_page', $email['Product_page__c'], $lid);

        // custom info
        update_field('curl', $email['curl'], $lid);
//		update_field('cdevice', $email['cdevice'], $lid);
        update_field('lead_source', $email['lead_source'], $lid);
        update_field('lead_record_created', 'on', $lid);

        // campagin info
        update_field('campagin_medium', $email['Campaign_medium__c'], $lid);
        update_field('campagin_source', $email['Campaign_source__c'], $lid);
        update_field('campagin_content', $email['Campaign_content__c'], $lid);
        update_field('campagin_term', $email['Campaign_term__c'], $lid);
        update_field('page_url__c', $email['curl'], $lid);
        update_field('paid_campaign_name__c', $email['Paid_Campaign_Name__c'], $lid);
        update_field('ga_id', $email['GA_id__c'], $lid);

        if($email['agreement']) {
            $agreement = str_replace( ";", "", $email['agreement'] );
            update_field( 'privacy_policy', $agreement, $lid );
        }

        /*************************
         ** SEND EMAIL TO OWNERS
         **************************/
        $message = '
		<h4>'.__('Sender Information', THEME_NAME).'</h4>
		<ul>
			<li> <strong>From:</strong> '.$email['first_name'].' '.$email['last_name'].'</li>
			<li> <strong>Phone:</strong> '.$email['telephone'].'</li>
			<li> <strong>Email:</strong> '.$email['email'].'</li>
			<li> <strong>Company:</strong> '.$email['company'].'</li>
			<li> <strong>Country:</strong> '.$email['country'].'</li>
			<li> <strong>Note:</strong><br> '.$email['note'].'</li>
		</ul>
		<br>

		<h4>'.__('Product Information', THEME_NAME).'</h4>
		<ul>
			<li> <strong>System on Module:</strong> '.$email['System__c'].'</li>
			<li> <strong>Estimated Quantities:</strong> '.$email['Projected_Quantities__c'].'</li>
			<li> <strong>Product Page URL:</strong> '.$email['Product_page__c'].'</li>
		</ul>
		<br>

		<h4>'.__('Additional Information', THEME_NAME).'</h4>
		<ul>
			<li> <strong>Origin Product:</strong> '.$email['curl'].'</li>
			<li> <strong>User Device:</strong> '.$email['device'].'</li>
		</ul>

		<h4>'.__('Campagin Information (optional)', THEME_NAME).'</h4>
		<ul>
			<li> <strong>Campagin Medium:</strong> '.$email['Campaign_medium__c'].'</li>
			<li> <strong>Campagin Source:</strong> '.$email['Campaign_source__c'].'</li>
			<li> <strong>Campagin Content:</strong> '.$email['Campaign_content__c'].'</li>
			<li> <strong>Campagin Term:</strong> '.$email['Campaign_term__c'].'</li>
            <li> <strong>Page_url__c:</strong> '.$email['curl'].'</li>
            <li> <strong>Paid_Campaign_Name__c:</strong> '.$email['Paid_Campaign_Name__c'].'</li>
            <li> <strong>GA ID:</strong> '.$email['GA_id__c'].'</li>
		</ul>
		';

        $subjectString = '[New Lead from Web - pop up] ';

        $sendResult	= wp_mail($email['email_to'], $subjectString . $email['company'] . ' [' . strtoupper($lang).']', $message);
        if($sendResult) { update_field('lead_record_email', 'on', $lid);  }

        /*************************
         ** SEND EMAIL TO SALESFORCE
         **************************/

        // REMOVE TEXT AFTER DOTS ":" IN PRODUCT NAME
//		$tmpProductNames 	= explode(',', $email['som']);
//		$email['som'] = '';
//
//		foreach($tmpProductNames as $tmpProdNm) {
//			$tmp			= explode(':', $tmpProdNm);
//			$email['som'] 	.= $tmp[0].',';
//		}
//		rtrim($email['som'], ',');


        // BUILD FIELD FOR CURL URL
//		$fieldURL 	= array();

        // CONNECTION STUFF
        $sfdc_data = array(
            'FirstName' => htmlspecialchars($email['first_name']),
            'LastName' => htmlspecialchars($email['last_name']),
            'Company' => htmlspecialchars($email['company']),
            'Email' => htmlspecialchars($email['email']),
            'Country' => ucwords( str_replace('-', ' ', htmlspecialchars($email['country']) )),
            'Phone' => htmlspecialchars($email['telephone']),
            'Note__c' => htmlspecialchars(str_replace("\n", "\\n", $email['note'])),
            'System__c' => htmlspecialchars($email['System__c']),
            'Projected_Quantities__c' => htmlspecialchars($email['Projected_Quantities__c']),
            'Product_page__c' => htmlspecialchars($email['Product_page__c']),
            'LeadSource' => htmlspecialchars($email['lead_source']),
            'Campaign_medium__c' => htmlspecialchars($email['Campaign_medium__c']),
            'Campaign_source__c' => htmlspecialchars($email['Campaign_source__c']),
            'Campaign_content__c' => htmlspecialchars($email['Campaign_content__c']),
            'Campaign_term__c' => htmlspecialchars($email['Campaign_term__c']),
            'Page_url__c' => htmlspecialchars($email['curl']),
            'Paid_Campaign_Name__c' => htmlspecialchars($email['Paid_Campaign_Name__c']),
            'GA_id__c' => htmlspecialchars($email['GA_id__c'])
        );

        if( $email['agreement'] ){
            $sfdc_data['Privacy_Policy__c'] = $email['agreement'];
        }
		
		$sfdc_data_n = array();
		foreach( $sfdc_data as $sfkey => $sfvalue ){
			$sfdc_data_n[$sfkey] = general_convert_symbols_str( $sfvalue );
		}
		
        update_field( 'lead_initiator', 'send_popup_request', $lid );
        // Init the connection to the API
        try {
            $url = $sfdcIntegration->baseUrl."services/data/v56.0/sobjects/Lead/";
			$response = $sfdcIntegration->call_sfdc_api( $url, $sfdc_data_n, 'POST' );
			
            if( isset( $response->success ) && $response->success == true ){
                update_field('lead_record_sf', 'on', $lid);
            } else {
                update_field('curl_errors_documentation', 'Request failed: HTTP status code: ' . json_encode($response), $lid);
            }

        } catch (SoapFault $e) {

            # Catch and send out email to support if there is an error
            $errmessage =  "Exception ".$e->faultstring."<br/><br/>\n";
            
            update_field('curl_errors_documentation', json_encode($errmessage), $lid);
            sfalert_email($lid);

        } catch (Exception $e) {

            # Catch and send out email to support if there is an error
            $errmessage =  "Exception ".$e->faultstring."<br/><br/>\n";
            
            update_field('curl_errors_documentation', json_encode($errmessage), $lid);
            sfalert_email($lid);
        }

        // BUILD RESPONSE
        $resData = array(
            'thanks' => $email['thanks'],
        );

        wp_send_json_success($resData);

        curl_close($ch);
    }

    /*********************************************************
     **	SEND QUOTE WITH MULTI STEP FORM
     *********************************************************/
    if( $actionType == 'send_multi_step_quote' ){

        $data 		= $_POST['form_data'];
        $settings	= get_field('quote_settings', 'option');

        foreach($data as $key => $item) {
            if( $key == 'note' ) {
                $email[$key] = sanitize_textarea_field($item);
            }else if( $key == 'agreement' ){
				$item = str_replace( ";", "", $item );
				$email[$key] = $item;
			}else if( $key == 'quote-product') {
                $email[$key] = implode(';', $item);
            }else{
                $email[$key] = sanitize_text_field($item);
            }
        }

        if( strlen($email['first_name']) < 2 ) {
            wp_send_json_error( array('msg' => __('Please enter a valid first name.') ) );
        } elseif( !is_valid_email($email['email']) ) {
            wp_send_json_error( array('msg' => __('Enter a valid email.') ) );
        } elseif( empty($email['phone']) ) {
            wp_send_json_error( array('msg' => __('Enter a valid phone.') ) );
        } else {

            // Validate max length of first
            if(strlen($email['first_name']) > 50) {
                wp_send_json_error( array('msg' => __('Please enter a valid first name.') ));
            }

            /*************************
             ** SAVE EMAIL TO BACKEND
             **************************/
            $post = array(
                'post_title'    => __('New Lead From', THEME_NAME).': '.$email['first_name'].' '.$email['last_name'],
                'post_status'   => 'publish',
                'post_type'		=> 'leads'
            );
            $lid = wp_insert_post($post, 10, 1);
//			do_action('wp_insert_post', 'wp_insert_post', 10, 1);

            // user info
            update_field('first_name', $email['first_name'], $lid);
            update_field('last_name', $email['last_name'], $lid);
            update_field('email', $email['email'], $lid);
            update_field('phone', $email['phone'], $lid);
            update_field('country', $email['country'], $lid);
            update_field('company', $email['company'], $lid);
            update_field('note', $email['note'], $lid);

            // product info
            update_field('som', $email['quote-product'], $lid);
            update_field('quan', $email['quote-quantity'], $lid);
            update_field('product_page', $email['Product_page__c'], $lid);

            // custom info
            update_field('curl', $email['curl'], $lid);
//			update_field('cdevice', $email['device'], $lid);
            update_field('lead_source', $email['lead_source'], $lid);
            update_field('lead_record_created', 'on', $lid);

            // campagin info
            update_field('campagin_medium', $email['Campaign_medium__c'], $lid);
            update_field('campagin_source', $email['Campaign_source__c'], $lid);
            update_field('campagin_content', $email['Campaign_content__c'], $lid);
            update_field('campagin_term', $email['Campaign_term__c'], $lid);
            update_field('page_url__c', $email['curl'], $lid);
            update_field('paid_campaign_name__c', $email['Paid_Campaign_Name__c'], $lid);
            update_field('ga_id', $email['GA_id__c'], $lid);

            if( $email['agreement'] ){
				$agreement = str_replace( ";", "", $email['agreement'] );
                update_field( 'privacy_policy', $agreement, $lid );
            }

            /*************************
             ** SEND EMAIL TO OWNERS
             **************************/
            $message = '
				<h4>'.__('Sender Information', THEME_NAME).'</h4>
				<ul>
					<li> <strong>From:</strong> '.$email['first_name'].' '.$email['last_name'].'</li>
					<li> <strong>Phone:</strong> '.$email['phone'].'</li>
					<li> <strong>Email:</strong> '.$email['email'].'</li>
					<li> <strong>Country:</strong> '.$email['country'].'</li>
					<li> <strong>Note:</strong><br> '.$email['note'].'</li>
				</ul>
				<br>

				<h4>'.__('Product Information', THEME_NAME).'</h4>
				<ul>
					<li> <strong>System on Module:</strong> '.$email['quote-product'].'</li>
					<li> <strong>Estimated Quantities:</strong> '.$email['quote-quantity'].'</li>
					<li> <strong>Product Page URL:</strong> '.$email['Product_page__c'].'</li>
				</ul>
				<br>

				<h4>'.__('Additional Information', THEME_NAME).'</h4>
				<ul>
					<li> <strong>Origin Product:</strong> '.$email['curl'].'</li>
					<li> <strong>User Device:</strong> '.$email['device'].'</li>
					<li> <strong>Lead Source:</strong> '.$email['lead_source'].'</li>
				</ul>


				<h4>'.__('Campagin Information (optional)', THEME_NAME).'</h4>
				<ul>
					<li> <strong>Campagin Medium:</strong> '.$email['Campaign_medium__c'].'</li>
					<li> <strong>Campagin Source:</strong> '.$email['Campaign_source__c'].'</li>
					<li> <strong>Campagin Content:</strong> '.$email['Campaign_content__c'].'</li>
					<li> <strong>Campagin Term:</strong> '.$email['Campaign_term__c'].'</li>
					<li> <strong>Page_url__c:</strong> '.$email['curl'].'</li>
					<li> <strong>Paid_Campaign_Name__c:</strong> '.$email['Paid_Campaign_Name__c'].'</li>
					<li> <strong>GA_id__c:</strong> '.$email['GA_id__c'].'</li>
				</ul>
				';

//			$sendResult	= wp_mail($settings['email_to'], '[New Lead from product page] '. $email['company'] . ' [' . strtoupper($lang).']', $message);
//			if($sendResult) { update_field('lead_record_email', 'on', $lid);}



            /*************************
             ** SEND EMAIL TO SALESFORCE
             **************************/

            $email['quote-product'] = str_replace(' / ', ' ', $email['quote-product']);
            rtrim($email['quote-product'], ';');


            // BUILD FIELD FOR CURL URL
//				$fieldURL 	= array();

            // CONNECTION STUFF
            $sfdc_data = array(
                'FirstName' => htmlspecialchars($email['first_name']),
                'LastName' => htmlspecialchars($email['last_name']),
                'Company' => htmlspecialchars($email['company']),
                'Email' => htmlspecialchars($email['email']),
                'Country' => ucwords( str_replace('-', ' ', htmlspecialchars($email['country']) )),
                'Phone' => htmlspecialchars($email['phone']),
                'Note__c' => htmlspecialchars(str_replace("\n", "\\n", $email['note'])),
                'Processor__c' => htmlspecialchars($email['quote-product']),
                'Projected_Quantities__c' => htmlspecialchars($email['quote-quantity']),
                'Product_page__c' => htmlspecialchars($email['Product_page__c']),
                'LeadSource' => htmlspecialchars($email['lead_source']),
                'Campaign_medium__c' => htmlspecialchars($email['Campaign_medium__c']),
                'Campaign_source__c' => htmlspecialchars($email['Campaign_source__c']),
                'Campaign_content__c' => htmlspecialchars($email['Campaign_content__c']),
                'Campaign_term__c' => htmlspecialchars($email['Campaign_term__c']),
                'Page_url__c' => htmlspecialchars($email['curl']),
                'Paid_Campaign_Name__c' => htmlspecialchars($email['Paid_Campaign_Name__c']),
                'GA_id__c' => htmlspecialchars($email['GA_id__c'])
            );

            if($email['agreement']) {
                $sfdc_data['Privacy_Policy__c'] = htmlspecialchars($email['agreement']);
            }
			
			$sfdc_data_n = array();
			foreach( $sfdc_data as $sfkey => $sfvalue ){
				$sfdc_data_n[$sfkey] = general_convert_symbols_str( $sfvalue );
			}
			
            // Save the SFDC data as a JSON in the lead CPT
            $json_encoded = json_encode($sfdc_data_n, JSON_UNESCAPED_UNICODE);
            $json_encoded = str_replace("\&quot;", "&quot;", $json_encoded);
            $json_encoded = str_replace("\'", "'", $json_encoded);
            update_field('sfdc_object_to_be_sent', $json_encoded, $lid);

            // Save the email message in the lead CPT
            update_field('email_message_to_be_sent', $message, $lid);
            update_field('email_subject_to_be_sent', '[New Lead from Product page]' . $email['company'] . ' [' . strtoupper($lang).']', $lid);

            // BUILD RESPONSE
            $thank_you_text = get_field('step_form_thank_you_text', 'option');
            $text_above_title = get_field('text_above_title', 'option');
            $discount_text = get_field('step_form_discount_text', 'option');

            if(isset($email['product_id']) && !empty($email['product_id'])) {

                $product_id = $email['product_id'];
                $kit_postid = get_field('vrs_specs_relevant_mid_product', $product_id);
                $custom_image = get_field('vrs_specs_evaluation_kit_cimg',$product_id);
                $kit_cimg_webp = get_field('vrs_specs_evaluation_kit_cimg_webp',$product_id);
                $prodalturl = get_field('vrs_specs_relevant_mid_prodalturl',$product_id);
                $kit_desc = get_field('vrs_specs_relevant_mid_prodalturl',$product_id);

                $desc		= get_field('vrs_specs_product_middesc', $kit_postid);
                $price		= get_field('vrs_specs_price', $kit_postid);

                if(!$custom_image) {$thumb = smart_thumbnail($kit_postid, NULL, NULL, NULL, get_the_title($kit_postid));}
                else {

                    $thumb = '
					<picture class="img-responsive">
						'.( !empty($webp_image) ? '<source srcset="'.$webp_image['url'].'" type="image/webp"  alt="'.$custom_image['alt'].'">' : '' ).'
						<img src="'.$custom_image['url'].'" alt="'.$custom_image['alt'].'">
					</picture>
					';

                }

                $product_title = get_the_title($kit_postid);
                $button_html = '<a href="'.$prodalturl.'" class="btn btn-warning btn-lg orderkit-btn"><span class="txtlbl">'.__('order a kit', THEME_NAME).'</span></a>';
            }

            $global_data = get_field('use_global_thank_you_data',$email['product_id']);

            if($global_data) {
                $product_image = get_field('step_form_product_image', 'option');
                $button_label = get_field('step_form_button_label', 'option');
                $button_link = get_field('step_form_button_link', 'option');
                $product_title = get_field('product_title', 'option');
                $desc = get_field('step_form_product_description', 'option');

                $thumb = '
					<picture class="img-responsive img-option">
						'.( !empty($product_image) ? '<source srcset="'.$product_image['url'].'" type="image/webp"  alt="'.$product_image['alt'].'">' : '' ).'
						<img src="'.$product_image['url'].'" alt="'.$product_image['alt'].'">
					</picture>
					';

                if(!empty($button_label) && !empty($button_link)) {
                    $button_html = '<a href="'.$button_link.'" class="btn btn-warning btn-lg orderkit-btn"><span class="txtlbl">'.$button_label.'</span></a>';
                }
            }

            $thank_you_html .= '<div class="step-response">';
            $thank_you_html .= '<div class="response-msg">';
            $thank_you_html .= $thank_you_text;
            $thank_you_html .= '</div>';

            $thank_you_html .= '<div class="response-product"><div class="row">';
            $thank_you_html .= '<div class="col-md-6 res-global-description">';
            $thank_you_html .= '<div class="res-global-desc">'.$text_above_title.'</div>';
            $thank_you_html .= '</div>';
            $thank_you_html .= '<div class="col-md-6">';
            $thank_you_html .= '<strong class="res-title">'.$product_title.'</strong>';
            $thank_you_html .= '<div class="res-product-desc">'.$desc.'</div>';

            // $thank_you_html .='<a href="'.get_permalink($kit_postid).'" class="btn btn-default btn-lg"><span class="txtlbl">'.__('Kit Specs', THEME_NAME).'</span></a>';
            $thank_you_html .= '</div>';
            $thank_you_html .= '<div class="col-md-6">';

            $thank_you_html .=  $thumb;

            $thank_you_html .= '</div>';
            $thank_you_html .= '</div></div>';
            $thank_you_html .= '<div class="discount">'.$discount_text.'</div>';
            $thank_you_html .= '<div class="thank-you-btn">'.$button_html.'</div>';
            $thank_you_html .= '</div>';

            $resData = array(
                'response' => $thank_you_html,
            );
            wp_send_json_success($resData);


//			curl_close($ch);
        }
    }

    wp_die();
}