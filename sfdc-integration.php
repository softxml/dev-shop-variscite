<?php

class contactSFDCIntegration {

    private $sfdc_username;
    private $sfdc_password;
    private $sfdc_se_token;

    private $wsdl;

    function __construct() {

        # SFDC Auth Information
        $this->sfdc_username = 'hadas.s@variscite.com';
        $this->sfdc_password = 'Sh102030';
        $this->sfdc_se_token = 'FZH6Hm8zOGtOVYa2UADxLF73t';

        $this->wsdl = __DIR__ . '/soapclient/partner.wsdl.xml';

        # WP information
        $this->admin_email = array(get_option('admin_email'), 'lena.g@variscite.com', 'eden.d@variscite.com', 'roi@designercoded.com');
    }

    function SFDC_auth() {

        // Init salesforce connection
        $mySforceConnection = new SforcePartnerClient();
        $mySforceConnection->createConnection($this->wsdl);
        $mySforceConnection->login($this->sfdc_username, $this->sfdc_password . $this->sfdc_se_token);

        return $mySforceConnection;
    }

    public function pass_lead_to_sfdc($data) {
        $SFDC = $this->SFDC_auth();

        $contact_data = new SObject();

        $contact_data->type = 'Lead';
        $contact_data->fields = $data;
        $contact_data->fields['LeadSource'] = 'Web - contact via store';

        try {
            $response = $SFDC->create(array($contact_data));

            if ($response[0]->success == true) {

                return array(
                    'success' => $response[0]->success,
                    'message' => print_r($response, true)
                );

            } else {
                wp_mail($this->admin_email, "Variscite Store: Contact Form Salesforce Integration Failure", json_encode($response) . "\n" . json_encode($contact_data));

                return array(
                    'success' => false,
                    'message' => print_r($response, true)
                );
            }

        } catch (SoapFault $e) {

            # Catch and send out email to support if there is an error
            $errmessage =  "Exception ".$e->faultstring."<br/><br/>\n";
            $errmessage .= "Last Request:<br/><br/>\n";
            $errmessage .= $SFDC->getLastRequestHeaders();
            $errmessage .= "<br/><br/>\n";
            $errmessage .= $SFDC->getLastRequest();
            $errmessage .= "<br/><br/>\n";
            $errmessage .= "Last Response:<br/><br/>\n";
            $errmessage .= $SFDC->getLastResponseHeaders();
            $errmessage .= "<br/><br/>\n";
            $errmessage .= $SFDC->getLastResponse();

            wp_mail($this->admin_email, "Variscite Store: Contact Form Salesforce Integration Failure", json_encode($errmessage));

            return array(
                'success' => false,
                'message' => print_r($response, true)
            );

        } catch (Exception $e) {

            # Catch and send out email to support if there is an error
            $errmessage =  "Exception ".$e->faultstring."<br/><br/>\n";
            $errmessage .= "Last Request:<br/><br/>\n";
            $errmessage .= $SFDC->getLastRequestHeaders();
            $errmessage .= "<br/><br/>\n";
            $errmessage .= $SFDC->getLastRequest();
            $errmessage .= "<br/><br/>\n";
            $errmessage .= "Last Response:<br/><br/>\n";
            $errmessage .= $SFDC->getLastResponseHeaders();
            $errmessage .= "<br/><br/>\n";
            $errmessage .= $SFDC->getLastResponse();

            wp_mail($this->admin_email, "Variscite Store: Contact Form Salesforce Integration Failure", json_encode($errmessage));

            return array(
                'success' => false,
                'message' => print_r($response, true)
            );
        }

        return array(
            'success' => false,
            'message' => 'Failed to init SFDC integration'
        );

    }
}