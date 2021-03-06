<?php

require_once '/opt/fmc_repository/Process/Reference/Common/common.php';
require_once '/opt/fmc_repository/Process/Reference/OPENSTACK/Library/OBMF/openstack_common_obmf.php';

function list_args()
{
	create_var_def('openstack_device_id', 'Device');
	create_var_def('onm_network', 'OBMFRef');
	create_var_def('onm_network_ip', 'IpAddress');
	create_var_def('external_network', 'OBMFRef');
	create_var_def('private_network', 'OBMFRef');
	create_var_def('private_network_ip', 'IpAddress');
	create_var_def('server_name', 'String');
	create_var_def('availability_zone', 'OBMFRef');
	create_var_def('flavor', 'OBMFRef');
	create_var_def('image', 'OBMFRef');
}

check_mandatory_param('openstack_device_id');
check_mandatory_param('flavor');
check_mandatory_param('image');
check_mandatory_param('server_name');
#check_mandatory_param('availability_zone');

$PROCESSINSTANCEID = $context['PROCESSINSTANCEID'];
$EXECNUMBER = $context['EXECNUMBER'];
$TASKID = $context['TASKID'];
$process_params = array('PROCESSINSTANCEID' => $PROCESSINSTANCEID,
						'EXECNUMBER' => $EXECNUMBER,
						'TASKID' => $TASKID);
	
// Openstack Server creation parameters
$server_name = $context['server_name'];
$availability_zone = $context['availability_zone'];
$flavor = $context['flavor'];
$image = $context['image'];


$networks[0]['network'] = $context['onm_network'];
$networks[0]['fixed_ip'] = $context['onm_network_ip'];
$networks[1]['network'] = $context['private_network'];
$networks[1]['fixed_ip'] = $context['private_network_ip'];
$networks[2]['network'] = $context['external_network'];


//$openstack_device_id = substr($context['openstack_device_id'], 3);
$openstack_device_id = $context['openstack_device_id'];
$openstack_device_id = preg_replace('/\D/', '', $openstack_device_id);
$response = _nova_server_create($openstack_device_id, $server_name, $networks,
									$availability_zone, $flavor, $image);
$response = json_decode($response, true);
if ($response['wo_status'] !== ENDED) {
	$response = json_encode($response);
	echo $response;
	exit;
}

$response = get_server_id($openstack_device_id, $server_name);
$response = json_decode($response, true);
if ($response['wo_status'] !== ENDED) {
	$response = json_encode($response);
	echo $response;
	exit;
}
$server_id = $response['wo_newparams']['server_id'];
	
$response = wait_for_server_status($openstack_device_id, $server_id, ACTIVE, $process_params);
$response = json_decode($response, true);
if ($response['wo_status'] !== ENDED) {
	$response = json_encode($response);
	echo $response;
	exit;
}
	
$response = get_server_interface_details($openstack_device_id, $server_id, $networks);
$response = json_decode($response, true);
if ($response['wo_status'] !== ENDED) {
	$response = json_encode($response);
	echo $response;
	exit;
}
	
$wo_comment = "Openstack Server created successfully.\nServer Id : $server_id\nServer Status : " . ACTIVE . "\n";
$wo_comment .= $response['wo_comment'];
$context['server_interface_details'] = $response['wo_newparams']['server_interface_details'];
$context['server_id'] = $server_id;
if (array_key_exists('floating_ip_address', $context)) {
	$context['device_ip_address'] = $context['floating_ip_address'];
}
update_asynchronous_task_details($process_params, $wo_comment);
$response = prepare_json_response(ENDED, $wo_comment, $context, true);
echo $response;

?>