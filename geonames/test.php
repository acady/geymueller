<?php

include '../lib/simplexlsx.class.php';

$xlsx = new SimpleXLSX('../data/Intvent_final_21052015.xlsx');


// output worsheet 1

list($num_cols, $num_rows) = $xlsx->dimension();

echo '<h1>Sheet 1</h1>';
echo '<table>';
foreach( $xlsx->rows() as $r ) {
	echo '<tr>';
	for( $i=0; $i < $num_cols; $i++ )
		echo '<td>'.( (!empty($r[$i])) ? $r[$i] : '&nbsp;' ).'</td>';
	echo '</tr>';
}
echo '</table>';


?>