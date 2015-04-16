<?php
include('encyclapidi.class.php');
$encyclo = new encyclapidi();

//Gives JSON encoded response without html tags
echo json_encode($encyclo->getWord('test', true));

echo("<hr>");

echo json_encode($encyclo->suggest("test"));
?>