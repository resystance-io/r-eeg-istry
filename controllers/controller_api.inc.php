<?php

class CONTROLLER_API
{

    // ## instance variables
    private $object_broker;                 // object broker
    private $db;                            // database object
    private $config;                        // config object

    public function __construct($object_broker)
    {
        $this->object_broker = $object_broker;
        $this->db = $this->object_broker->instance['db'];
        $this->config = $this->object_broker->instance['config'];
    }

    public function __destruct()
    {

    }

    public function get_public_statistics()
    {
        header('Content-Type: application/json; charset=utf-8');

        // public statistics API
        $clean_tenant = preg_replace("/[^a-zA-Z]/", '', $_REQUEST['tenant']);
        $tenant_info = $this->db->get_rows_by_column_value($this->config->user['DBTABLE_TENANTS'], 'referrer', $clean_tenant, 1);

        if(isset($tenant_info[0]))
        {
            $total_supplier_kwp = 0;
            $total_storage_kwh = 0;
            $total_supplying_meters = 0;
            $total_consuming_meters = 0;
            $result_array = array();

            if($tenant_info[0]['enabled'] == 'y')
            {
                $active_registration_count = $this->db->get_rowcount_by_field_value_extended($this->config->user['DBTABLE_REGISTRATIONS'],'tenant',$tenant_info[0]['id'], $this->config->user['DBTABLE_REGISTRATIONS'] . '.state = "active"');
                $result_array['active_registration_count'] = $active_registration_count;

                $meters = $this->db->get_rows_by_column_value_extended($this->config->user['DBTABLE_METERS'],
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    $this->config->user['DBTABLE_REGISTRATIONS'] . '.tenant = "' . $tenant_info[0]['id'] . '" AND ' . $this->config->user['DBTABLE_REGISTRATIONS'] . '.state = "active"',
                    'INNER JOIN ' . $this->config->user['DBTABLE_REGISTRATIONS'] . ' ON (' . $this->config->user['DBTABLE_METERS'] . '.registration_id = ' . $this->config->user['DBTABLE_REGISTRATIONS'] . '.id) WHERE');

                foreach($meters as $meter)
                {
                    if($meter['meter_type'] == 'supplier')
                    {
                        //print "DBG: Supplier found: LIMIT(" . $meter['meter_feedlimit'] . ") POWER(" . $meter['meter_power'] . ") PARTICIPATION(" . $meter['meter_participation'] . ")\n";
                        $total_supplying_meters++;
                        if($meter['meter_feedlimit'])   $meter_power = $meter['meter_feedlimit'];   else    $meter_power = $meter['meter_power'];
                        $total_supplier_kwp += $meter_power / 100 * $meter['meter_participation'];
                    }
                    else
                    {
                        $total_consuming_meters++;
                    }
                }

                $storages = $this->db->get_rows_by_column_value_extended($this->config->user['DBTABLE_STORAGES'],
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    NULL,
                    $this->config->user['DBTABLE_REGISTRATIONS'] . '.tenant = "' . $tenant_info[0]['id'] . '" AND ' . $this->config->user['DBTABLE_REGISTRATIONS'] . '.state = "active"',
                    'INNER JOIN ' . $this->config->user['DBTABLE_REGISTRATIONS'] . ' ON (' . $this->config->user['DBTABLE_STORAGES'] . '.registration_id = ' . $this->config->user['DBTABLE_REGISTRATIONS'] . '.id) WHERE');

                foreach($storages as $storage)
                {
                    $total_storage_kwh += $storage['storage_capacity'];
                }

                $result_array['total_kwp_generated'] = $total_supplier_kwp;
                $result_array['total_kwh_capacity'] = $total_storage_kwh;
                $result_array['supplying_meters_count'] = $total_supplying_meters;
                $result_array['consuming_meters_count'] = $total_consuming_meters;
                $result_json = json_encode($result_array);
                print $result_json;
                exit();
            }
        }
    }

}
