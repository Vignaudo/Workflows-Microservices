<?php

/**
 * This file is necessary to include to use all the in-built libraries of /opt/fmc_repository/Reference/Common
 */
require_once '/opt/fmc_repository/Process/Reference/Common/common.php';

/**
 * List all the parameters required by the task
 */
function list_args()
{
  /** 
   * You can use var_name convention for your variables
   * They will display automaticaly as "Var Name"
   * The allowed types are:
   *    'String', 'Boolean', 'Integer', 'Password', 'IpAddress',
   *    'IpMask', 'Ipv6Address', 'Composite', 'OBMFRef', 'Device'
   *
   * Add as many variables as needed
   */
  create_var_def('URL', 'String');
  create_var_def('token', 'String');
  create_var_def('subscription_id', 'String');
  create_var_def('res_gr_name', 'String');
  create_var_def('public_address', 'String');
  create_var_def('subnet_name', 'String');
  create_var_def('interface_name', 'String');
  create_var_def('nic', 'String');
  create_var_def('virtu_net', 'String');
  create_var_def('network_sec_gr_name', 'String');
  create_var_def('vm_name', 'String');
  
}

$context['nic']=''.$context['vm_name'].'_nic';
$context['interface_name']=''.$context['vm_name'].'_IpInt1';

sleep(20);
$authorization = 'Authorization: Bearer '.$context['token'].'';
$context['URL']='https://management.azure.com/subscriptions/'.$context['subscription_id'].'/resourceGroups/'.$context['res_gr_name'].'/providers/Microsoft.Network/networkInterfaces/'.$context['nic'].'?api-version=2018-06-01';
$doc='{"location":"'.$context['location'].'","properties": {"enableAcceleratedNetworking": true,"networkSecurityGroup":{"id": "/subscriptions/'.$context['subscription_id'].'/resourceGroups/'.$context['res_gr_name'].'/providers/Microsoft.Network/networkSecurityGroups/'.$context['network_sec_gr_name'].'"},"ipConfigurations":[{"name": "'.$context['interface_name'].'","properties": {"publicIPAddress": {"id": "/subscriptions/'.$context['subscription_id'].'/resourceGroups/'.$context['res_gr_name'].'/providers/Microsoft.Network/publicIPAddresses/'.$context['public_address'].'"},"subnet": {"id":"/subscriptions/'.$context['subscription_id'].'/resourceGroups/'.$context['res_gr_name'].'/providers/Microsoft.Network/virtualNetworks/'.$context['virtu_net'].'/subnets/'.$context['subnet_name'].'"}}}]}}';

$ci = curl_init();
    curl_setopt($ci, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
    curl_setopt($ci, CURLOPT_URL, $context['URL']);
    curl_setopt($ci, CURLOPT_TIMEOUT, 200);
    curl_setopt($ci, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ci, CURLOPT_FORBID_REUSE, 0);
    curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ci, CURLOPT_POSTFIELDS, $doc);
    $response = curl_exec($ci);


task_exit(ENDED, $response);
//$ret = prepare_json_response(ENDED, $response, true);
?>