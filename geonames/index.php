<html>
	<head>
		<title>Geymueller GeoNames</title>
	</head>
	<body>
		<?php $date = new Datetime();
			echo 'Abfrage gestartet. '.$date->	format('Y-m-d H:i:s').'<br/>'
		?>
<?php
include '../lib/simplexlsx.class.php';

// search string in search
function searchString($string, $search) {
	/*
	if($string){
		// sucht teile des String
		$pos = strpos($search, $string);
		
		//echo($string);
		//$exp = '/\b'.$string.'\b/';
		//$exp = "/".$string."/";
		//echo($exp.'('.$search.')');
		//preg_match_all("/Brief/", $input_lines, $output_array);
		//preg_match_all($exp, $search, $pos, PREG_OFFSET_CAPTURE);
		//var_dump($pos);
	} else {
		$pos = false;
	}
	return $pos;
	*/
	
	// vom String . und () weg nehmen
	
	if($string){
		//echo($string);
		//$exp = '/\b'.$string.'\b/';
		if ( preg_match('/\s/', $string) ){
			// for word groups 
			$exp = "/".preg_quote($string, "/")."/";
		} else {
			// for single words
			$exp = "/".preg_quote($string, "/")."\b/";	
		}
		//echo($exp.'('.$search.')');
		preg_match_all($exp, $search, $pos);
		//preg_match($exp, $search, $pos);
		//var_dump($pos);
	}
	// check if Array is empty
	$pos = array_filter($pos);
	if (!empty($pos)) {
		return true;
	} else {
		return false;
	}
	
	
}

// search string in cities. 
function searchCity($string, $citiesAry) {
	$is_city[0] = "FALSE";
	foreach($citiesAry as $city){	
		if(searchString($city, $string)){
			$is_city[0] = "TRUE";
			array_push($is_city, $city);
		}
	}
	return $is_city;
}

function ary2string($array){
	$returnString = "";
	foreach($array as $value){
		$returnString .= $value." ";
		
	}
	return $returnString;
}

// get the cities 

//$file = fopen('../data/cities1000_n.csv', 'r');
$file = fopen('../data/cities15000_n.csv', 'r');
//$file = fopen('../data/cities5000_n.csv', 'r');
$cities = array();
$is_city = false;

while (($csv_val = fgetcsv($file, 2000, ";")) !== FALSE) {
  //$line is an array of the csv elements
  //print_r($line);
  /*
  foreach($line as $value){
	  //echo(utf8_decode($value));
  }
  */
  //print_r($line);
  $europe = strstr($csv_val[17], '/', true);
  // country
  //$country = strstr($csv_val[8], '/', false);
  $country = $csv_val[8];
  //echo $country;
  if($europe == "Europe"){
	  array_push($cities, utf8_decode($csv_val[1]));
	  
	  // auf Italien und D/A/CH/F einschränken	  
	  // this is for all known cites	  

      /*
	  //if($country == "IT" || $country == "AT" || $country == "DE" || $country == "FR" || $country == "CH"){
		  $city_names_alternatives = array();	  
		  $city_names_alternatives = explode(',', $csv_val[3]);
		  foreach($city_names_alternatives as $city){
			  //echo utf8_decode($city)." ";
			  if($city){
			  	array_push($cities, utf8_decode($city));	  
			  }
		  }
	  //} */
  } 
  //echo('<br>');
}
fclose($file);

// OUTPUT the XLsX file

/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('Europe/London');

define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
date_default_timezone_set('Europe/London');

/** Include PHPExcel */
require_once dirname(__FILE__) . '/../Classes/PHPExcel/IOFactory.php';

echo date('H:i:s') , " Load workbook from Excel2007 file" , EOL;
$callStartTime = microtime(true);

$input_file_name = "Intvent_final_18062015";
$excelFile = "../data/christoph/alt/".$input_file_name.".xlsx";


$objReader = PHPExcel_IOFactory::createReader('Excel2007');
$objPHPExcel = $objReader->load($excelFile);

$objPHPExcel_Output = new PHPExcel();
$objPHPExcel_Output->getProperties()->setCreator("Christoph Breser")
							 ->setLastModifiedBy("Stefan Zedlacher")
							 ->setTitle("Inventarliste Geymueller bearbeitet")
							 ->setSubject("PHPExcel Document")
							 ->setDescription("Dieses Dokument wurde digital bearbeitet")
							 ->setKeywords("office geymueller php")
							 ->setCategory("geymueller_digital");


//Itrating through all the sheets in the excel workbook and storing the array data
foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
    $arrayData[$worksheet->getTitle()] = $worksheet->toArray();
}

$callEndTime = microtime(true);
$callTime = $callEndTime - $callStartTime;

echo 'Call time to load Workbook was ' , sprintf('%.4f',$callTime) , " seconds" , EOL;
// Echo memory usage
echo date('H:i:s') , ' Current memory usage: ' , (memory_get_usage(true) / 1024 / 1024) , " MB" , EOL;
echo ('we have ' .count($cities).' cities<br>');
//var_dump($arrayData["Tabelle1"]);

echo '<h2>Aufstellung der bisherigen Daten</h2>';
echo '<table>';

$line_number = 0;

foreach( $arrayData["Tabelle1"] as $r ) {
	//var_dump($r);
	echo '<tr>';
	$line_done  = 0;
	$line_number++;
	$cell_names = array("A","B","C","D","E","F","G","H","I","J","K","L","M");
	$all_cities_found = "";
	
	for( $i=0; $i < 13; $i++ ){
		
		// set the basic informations
		if($i == 0){
			if ($r[0] == 1){
				// the line is done, just rewrite it.
				$line_done = 1;
			} else {
				// we need to work on this line.
				$line_done  = 0;
			}
		}
		if($line_done == 0){
			//echo(	searchCity(utf8_decode($r[$i]), $cities));
			//$cities_found = array();
			//$cities_found[0] = "FALSE";
			$cities_found = searchCity(utf8_decode($r[$i]), $cities);
			// we do not need that
			//array_shift($cities_found);
			
			//if(searchCity(utf8_decode($r[$i]), $cities)[0] != "FALSE"){
			if($i == 3 || $i == 9 || $i == 11){
				
				if($cities_found[0] != "FALSE"){
					echo '<td style="color: #EE0000; background-color: #EEE;">'.( (!empty($r[$i])) ? utf8_decode($r[$i]) : '&nbsp;' ).' <b>'.str_replace("TRUE",">",ary2string($cities_found)).'</b> </td>';
					
					$objPHPExcel_Output->setActiveSheetIndex(0)
		            	->setCellValue($cell_names[$i].$line_number, $r[$i]);/*.str_replace("TRUE",">",ary2string($cities_found))*/
		            	
		            $all_cities_found .= utf8_encode(str_replace("TRUE","",ary2string($cities_found)));
				
				} else {
					echo '<td>'.( (!empty($r[$i])) ? utf8_decode($r[$i]) : '&nbsp;' ).'</td>';
					$objPHPExcel_Output->setActiveSheetIndex(0)
		            	->setCellValue($cell_names[$i].$line_number, $r[$i]);
				}
				
			}
			// fields with no "Ort"
			if($i == 1 || $i == 2 || $i == 4 || $i == 5 || $i == 6 || $i == 7 || $i == 8 || $i == 10){
				echo '<td>'.( (!empty($r[$i])) ? utf8_decode($r[$i]) : '&nbsp;' ).'</td>';
				$objPHPExcel_Output->setActiveSheetIndex(0)
		            ->setCellValue($cell_names[$i].$line_number, $r[$i]);
			}
			// we are at the END so rewrite the "Ort" field and mark the line as edited
			if ($i == 12) {
				$objPHPExcel_Output->setActiveSheetIndex(0)
		            ->setCellValue($cell_names[6].$line_number, $r[6].$all_cities_found)
		            ->setCellValue($cell_names[0].$line_number, "1");
			}
					
		} else {
			$objPHPExcel_Output->setActiveSheetIndex(0)
		        ->setCellValue($cell_names[$i].$line_number, $r[$i]);

		}
	}
	echo '</tr>';
}
echo '</table>';

echo date('H:i:s') , " Write to CSV format" , EOL;
$callStartTime = microtime(true);

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel_Output, 'CSV')->setDelimiter(',')
                                                                  ->setEnclosure('"')
                                                                  ->setSheetIndex(0)
                                                                  ->save(str_replace('.php', '.xlsx', '../data/output/'.$input_file_name.'_cleared.xlsx'));
$callEndTime = microtime(true);
$callTime = $callEndTime - $callStartTime;

echo date('H:i:s') , " File written to " , str_replace('.php', '.csv', pathinfo(__FILE__, PATHINFO_BASENAME)) , EOL;
echo 'Call time to write Workbook was ' , sprintf('%.4f',$callTime) , " seconds" , EOL;
// Echo memory usage
echo date('H:i:s') , ' Current memory usage: ' , (memory_get_usage(true) / 1024 / 1024) , " MB" , EOL;

echo date('H:i:s') , " Write to Excel2007 format" , EOL;
$callStartTime = microtime(true);
$objPHPExcel_Output->getActiveSheet()->setTitle('Tabelle1');
$objWriter2007 = PHPExcel_IOFactory::createWriter($objPHPExcel_Output, 'Excel2007');
$objWriter2007->save(str_replace('.php', '.xlsx', '../data/output/'.$input_file_name.'_cleared.xlsx'));
$callEndTime = microtime(true);
$callTime = $callEndTime - $callStartTime;
echo date('H:i:s') , " File written to " , str_replace('.php', '.xlsx', pathinfo(__FILE__, PATHINFO_BASENAME)) , EOL;
echo 'Call time to write Workbook was ' , sprintf('%.4f',$callTime) , " seconds" , EOL;
// Echo memory usage
echo date('H:i:s') , ' Current memory usage: ' , (memory_get_usage(true) / 1024 / 1024) , " MB" , EOL;

// Echo memory peak usage
echo date('H:i:s') , " Peak memory usage: " , (memory_get_peak_usage(true) / 1024 / 1024) , " MB" , EOL;

// Echo done
echo date('H:i:s') , " Done writing files" , EOL;
echo 'Files have been created in ' , getcwd() , EOL;

/*

$xlsx = new SimpleXLSX('../data/Intvent_final_21052015_s.xlsx');

// output worsheet 1

list($num_cols, $num_rows) = $xlsx->dimension();

//array_shift($cities_found);

echo '<h2>Aufstellung der bisherigen Daten</h2>';
echo '<table>';
foreach( $xlsx->rows() as $r ) {
	echo '<tr>';
	for( $i=0; $i < $num_cols; $i++ )

		
		//echo(	searchCity(utf8_decode($r[$i]), $cities));
		//$cities_found = array();
		$cities_found = searchCity(utf8_decode($r[$i]), $cities);
		//array_shift($cities_found);
		
		//if(searchCity(utf8_decode($r[$i]), $cities)[0] != "FALSE"){
		if($cities_found[0] != "FALSE"){
			echo '<td style="color: #EE0000; background-color: #EEE;">'.( (!empty($r[$i])) ? utf8_decode($r[$i]) : '&nbsp;' ).' <b>'.str_replace("TRUE",">",$cities_found).'</b> </td>';
		
		} else {
			echo '<td>'.( (!empty($r[$i])) ? utf8_decode($r[$i]) : '&nbsp;' ).'</td>';
		}
		
	echo '</tr>';
}
echo '</table>';
*/




/*
$file = fopen('../data/Intvent_final_21052015.csv', 'r');
while (($line = fgetcsv($file)) !== FALSE) {
  //$line is an array of the csv elements
  print_r($line);
  echo($line);
}
fclose($file);
*/
?>

		
	</body>
</html>
