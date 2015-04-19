<?php
include('encyclapidi.class.php');
$encyclo = new encyclapidi();

//Gives JSON encoded response without html tags.
echo json_encode($encyclo->getWord('test', true));

echo("<hr>");

//Gives JSON encoded suggestions for a word, without the word itself.
echo json_encode($encyclo->suggest("test", true));
?>