<?php

    class newsletterSFDCIntegration {

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
        }

        function SFDC_auth() {

            // Init salesforce connection
            $mySforceConnection = new SforcePartnerClient();
            $mySforceConnection->createConnection($this->wsdl);
            $mySforceConnection->login($this->sfdc_username, $this->sfdc_password . $this->sfdc_se_token);

            return $mySforceConnection;
        }

        public function subscribe_to_newsletter($data) {
            $SFDC = $this->SFDC_auth();

            // Get both leads and contacts from SFDC with the privacy field empty,
            // that use the specified email.
            $objects = $this->sfdc_get_contacts_and_leads(
                false, $data['email'], $SFDC
            );

            // (2) If there are existing records, update their privacy policy field value
            if($objects && ! empty($objects)) {

                // (2.1) Check if there are records without the Privacy Policy field filled and fill it for them,
                // if there are none - don't do anything.
                $empty_objects = $this->sfdc_get_contacts_and_leads(
                    true, $data['email'], $SFDC
                );

                if($empty_objects && ! empty($empty_objects)) {

                    foreach($empty_objects['contacts'] as $id) {
                        $contact_data = new SObject();

                        $contact_data->Id = $id;
                        $contact_data->type = 'Contact';
                        $contact_data->fields = array(
                            'Privacy_Policy__c' => $data['privacy']
                        );

                        $SFDC->update(array($contact_data));
                    }

                    foreach($empty_objects['leads'] as $id) {
                        $contact_data = new SObject();

                        $contact_data->Id = $id;
                        $contact_data->type = 'Lead';
                        $contact_data->fields = array(
                            'Privacy_Policy__c' => $data['privacy']
                        );

                        $SFDC->update(array($contact_data));
                    }
                }

                return true;
            }

            else { // (3) If there are none, create a new contact under the Newsletter account
                $contact_data = new SObject();

                $contact_data->type = 'Contact';
                $contact_data->fields = array(
                    'FirstName' => $data['firstname'],
                    'LastName' => $data['lastname'],
                    'Email' => $data['email'],
                    'MailingCountry' => $data['country'],
                    'Privacy_Policy__c' => $data['privacy'],
                    'AccountId' => '0011p00002gBOqMAAW',
                    'LeadSource' => 'Newsletter'
                );

                $response = $SFDC->create(array($contact_data));
                return $response[0]->success;
            }
        }

        private function sfdc_get_contacts_and_leads($with_privacy_policy, $email, $SFDC) {
            $objects = array();

            $query = "SELECT Id, Privacy_Policy__c FROM Contact WHERE email='" . $email . "'" . ($with_privacy_policy ? " AND Privacy_Policy__c=null" : "");

            // First, get the Contacts
            $results = $SFDC->query(
                $query
            );

            foreach($results->records as $contact) {
                $objects['contacts'][] = $contact->Id[0];
            }

            // Then, get the Leads
            $results = $SFDC->query(
                "SELECT Id, Privacy_Policy__c FROM Lead WHERE email='" . $email . "'" . ($with_privacy_policy ? " AND Privacy_Policy__c=null" : "")
            );

            foreach($results->records as $lead) {
                $objects['leads'][] = $lead->Id[0];
            }

            return $objects;
        }
    }