<?php
$strings = array('string1' => "\n\r\t", 'string2' => "\narf12", 'string3' => '\n\r\t', 'string4' =>'das ist');
foreach ($strings as $name => $testcase) {
    if ( preg_match('/\s/',$testcase) ){
    	echo "Der String '$name' enthält  Leerzeichen.\n";
    } else {
        echo "Der String '$name' enthält KEINE Leerzeichen.\n";
    }
    echo '<br>';
    if (ctype_space($testcase)) {
        echo "Der String '$name' besteht aus Leerzeichen.\n";
    } else {
        echo "Der String '$name' enthält nicht nur Leerzeichen.\n";
    }
     echo '<br>';
}
?>