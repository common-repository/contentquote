<?php

include('../../../wp-config.php');
$cq_kinvey_options = get_option('cq_kinvey_options');

echo json_encode($cq_kinvey_options);

?>
