<?php
/**
 * Created by IntelliJ IDEA.
 * User: stefan
 * Date: 18.06.15
 * Time: 10:58
 */

include '../lib/simplexlsx.class.php';
/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('Europe/London');

/** Include PHPExcel */
require_once dirname(__FILE__) . '/../Classes/PHPExcel/IOFactory.php';


function get_keys_for_duplicate_values($my_arr, $my_key, $get_key_list = false)
{
    /*
    if ($clean) {
        return array_unique($my_arr);
    }
    */
    $my_nodes = array();
    $dups = $new_arr = array();
    foreach($my_arr as $my_nodes) {
        //var_dump($my_nodes);
        foreach ($my_nodes as $key => $val) {
            if($key == $my_key){
                if (!isset($new_arr[$val])) {
                    $new_arr[$val] = $key;
                } else {
                    if (isset($dups[$val])) {
                        $dups[$val][] = $key;
                    } else {
                        $dups[$val] = array($key);
                        // here we build the new array
                    }
                }
            }

        }
    }
    // now we build the new array
    $output_arr = array();
    $new_output_arry = array();

    foreach($my_arr as $my_nodes) {
        $output_arr_node = array();
        if(!isset($new_output_arry[$my_nodes['name']])){
            //increase the size value of output_arr where name = $my_nodes['name']
            if(isset($dups[$my_nodes['name']])){
                // because $dups only has the duplicate entries
                $my_nodes['size'] = intval(count($dups[$my_nodes['name']]));
                $my_nodes['id'] = intval(count($output_arr)); //root ID ist der Geymüllerid
            } else {
                $my_nodes['size'] = 1;
                $my_nodes['id'] = intval(count($output_arr));
                if(intval(count($output_arr)) == 0){
                    $my_nodes['size'] = 200;    // size for the root node
                }
            }

            //echo(count($dups[$my_nodes['name']])."<br>");
            $output_arr_node =  $my_nodes;
            array_push( $output_arr, $output_arr_node);
            $new_output_arry[$my_nodes['name']] = 1;
        }  else {
            // do nothing, array exists
            $new_output_arry[$my_nodes['name']]++;
        }
    }
    // this are the values we have looked for
    //return $new_output_arry;
    // these are the duplicate entries
    //return $dups;
    // this is the new output array
    if($get_key_list){
        // this are the values we have looked for
        return $new_output_arry;
    } else {
        // this is the new output array
        return $output_arr;
    }

}

echo date('H:i:s') , " Load workbook from Excel2007 file" , "<br>";
$callStartTime = microtime(true);

$input_file_name = "Sichtungs-Fotografien_18062015_s";
$excelFile = "../data/pia/".$input_file_name.".xlsx";


$objReader = PHPExcel_IOFactory::createReader('Excel2007');
$objPHPExcel = $objReader->load($excelFile);

$objPHPExcel_Output = new PHPExcel();
$objPHPExcel_Output->getProperties()->setCreator("Pia Watzenboeck")
    ->setLastModifiedBy("Stefan Zedlacher")
    ->setTitle("Photoliste Geymueller bearbeitet")
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

echo 'Call time to load Workbook was ' , sprintf('%.4f',$callTime) , " seconds" , "<br>";
// Echo memory usage
echo date('H:i:s') , ' Current memory usage: ' , (memory_get_usage(true) / 1024 / 1024) , " MB" , "<br>";

//var_dump($arrayData);

echo '<h2>Aufstellung der bisherigen Daten</h2>';
echo '<table>';

$line_number = 0;
$jsonArray = array();
$jsonLinks = array();
$jsonArrayPLinkO = array();
$json = array();

// the source node
$jsonArrayNodeObjekt = array();
$jsonArrayNodeObjekt['id'] = 0;
$jsonArrayNodeObjekt['type'] = -1;
$jsonArrayNodeObjekt['name'] ='Geymueller';
$jsonArrayNodeObjekt['group'] = 0;
$jsonArrayNodeObjekt['size'] = 200;
$jsonArrayNodeObjekt['probe'] = 1;
$jsonArrayNodeObjekt['thema'] = '';  // the value is from $r[3]

array_push($jsonArray, $jsonArrayNodeObjekt);

foreach( $arrayData["Tabelle1"] as $r ) {
    //var_dump($r);
    echo '<tr>';
    $line_done  = 0;
    $line_number++;
    $cell_names = array("A","B","C","D","E","F","G","H");
    $jsonArrayNodePerson = array();
    $jsonArrayNodeObjekt = array();
    $jsonArrayNodePLinkO = array();
    $hjsonO = false;
    $hjsonP = false;

    for( $i=0; $i < 8; $i++ ){

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

            //$cities_found = searchCity(utf8_decode($r[$i]), $cities);
            // we do not need that
            //array_shift($cities_found);

            //if(searchCity(utf8_decode($r[$i]), $cities)[0] != "FALSE"){
            /*
            if($i == 3 || $i == 9 || $i == 11){

                if($cities_found[0] != "FALSE"){
                    echo '<td style="color: #EE0000; background-color: #EEE;">'.( (!empty($r[$i])) ? utf8_decode($r[$i]) : '&nbsp;' ).' <b>'.str_replace("TRUE",">",ary2string($cities_found)).'</b> </td>';

                    $objPHPExcel_Output->setActiveSheetIndex(0)
                        ->setCellValue($cell_names[$i].$line_number, $r[$i]);//.str_replace("TRUE",">",ary2string($cities_found))

                    $all_cities_found .= utf8_encode(str_replace("TRUE","",ary2string($cities_found)));

                } else {
                    echo '<td>'.( (!empty($r[$i])) ? utf8_decode($r[$i]) : '&nbsp;' ).'</td>';
                    $objPHPExcel_Output->setActiveSheetIndex(0)
                        ->setCellValue($cell_names[$i].$line_number, $r[$i]);
                }

            }*/
            // fields with no "Ort"
            if($i < 8){

            //if($i == 1 || $i == 2 || $i == 4 || $i == 5 || $i == 6 || $i == 7 || $i == 8 || $i == 10){
                echo '<td>'.( (!empty($r[$i])) ? utf8_decode($r[$i]) : '&nbsp;' ).'</td>';
                $objPHPExcel_Output->setActiveSheetIndex(0)
                    ->setCellValue($cell_names[$i].$line_number, $r[$i]);
                if($r[$i] && $i == 2){
                    //$jsonArrayNodeObjekt['id'] = intval($r[$i-1]);
                    $jsonArrayNodeObjekt['excel_id'] = intval($r[$i-1]);
                    $jsonArrayNodeObjekt['type'] = 1;
                    $jsonArrayNodeObjekt['name'] = $r[$i];
                    $jsonArrayNodeObjekt['group'] = 0;
                    $jsonArrayNodeObjekt['size'] = 0;
                    $jsonArrayNodeObjekt['probe'] = 0;
                    $jsonArrayNodeObjekt['thema'] = "";//$r[$i+1];  // the value is from $r[3]
                    $hjsonO = true;
                }
                if($r[$i] && $i == 5){
                    //$jsonArrayNodePerson['id'] = intval($r[$i-4])-6000;
                    $jsonArrayNodePerson['excel_id'] = intval($r[$i-4])-6000;
                    $jsonArrayNodePerson['type'] = 0;
                    $jsonArrayNodePerson['name'] = $r[$i];
                    $jsonArrayNodePerson['group'] = 0;
                    $jsonArrayNodePerson['size'] = 0;
                    $jsonArrayNodePerson['probe'] = 0;
                    $jsonArrayNodePerson['thema'] = ""; //$r[$i-2]; // the value is from $r[3]
                    $hjsonP = true;
                    // now we construct the link between persons and Objects
                    if($hjsonO){
                        $jsonArrayNodePLinkO['person'] = $jsonArrayNodePerson['name'];
                        $jsonArrayNodePLinkO['excel_id'] = $jsonArrayNodeObjekt['excel_id'];
                        $jsonArrayNodePLinkO['object'] = $jsonArrayNodeObjekt['name'];
                        $jsonArrayNodePLinkO['value'] = 1;
                        $jsonArrayNodePLinkO['type'] = 0;
                    }
                }
            }
            // we are at the END so rewrite the "Ort" field and mark the line as edited
            if ($i == 7) {
                $objPHPExcel_Output->setActiveSheetIndex(0)
                    //->setCellValue($cell_names[6].$line_number, $r[6].$all_cities_found)
                    ->setCellValue($cell_names[0].$line_number, "1");
            }

        } else {
            $objPHPExcel_Output->setActiveSheetIndex(0)
                ->setCellValue($cell_names[$i].$line_number, $r[$i]);

        }

    }


    // check size (duplicates)
    // write JSON

    if($hjsonP) {
        array_push($jsonArray, $jsonArrayNodePerson);
        array_push($jsonArrayPLinkO, $jsonArrayNodePLinkO);
    }
    ($hjsonO) ? array_push($jsonArray, $jsonArrayNodeObjekt) : null ;


    echo '</tr>';
}
echo '</table>';

// ----- TO DO -----------------

// build the links
// reduce $jsonArrayPLinkO to source - target - size
var_dump(get_keys_for_duplicate_values($jsonArrayPLinkO, 'person object');
// build the links from that.

foreach(get_keys_for_duplicate_values($jsonArray, 'name') as $node) {
    $jsonArrayLinkObjekt = array();
    $sourceId = 0;
    $isBaselink = false;
    while (list($key, $value) = each($node)) {
        //echo "Schlüssel: $key; Wert: $value<br />\n";

        // this are the Object links
        if ($key == "type" && $value == "1") {
            //echo "Schlüssel: $key; Wert: $value<br />\n";
            $isBaselink = true;
            $jsonArrayLinkObjekt['target'] = 0;
            $jsonArrayLinkObjekt['value'] = 1;
            $jsonArrayLinkObjekt['type'] = 1;

        }
        if ($key == 'id' && $isBaselink == true) {
            $sourceId = $value;
            $jsonArrayLinkObjekt['source'] = intval($sourceId);
            array_push($jsonLinks, $jsonArrayLinkObjekt);
            $isBaselink = false;
        }
        // this are the Persons links
        if ($key == "type" && $value == "0") {

        }
    }
}
/*
foreach ($arr as $key => $value) {
    echo "Schlüssel: $key; Wert: $value<br />\n";
}
*/
// build the final array
$json['nodes'] = get_keys_for_duplicate_values($jsonArray, 'name');
$json['links'] = $jsonLinks;

/*
//echo(get_keys_for_duplicate_values($jsonArray));
echo("<hr>JSON<hr><br>");
echo(json_encode($json));
echo("<hr><br>");
*/
$fp = fopen('json/personen.json', 'w');
fwrite($fp, json_encode($json));
fclose($fp);
//var_dump(get_keys_for_duplicate_values($jsonArray, 'name'));

echo date('H:i:s') , " Write to CSV format" , PHP_EOL;
$callStartTime = microtime(true);

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel_Output, 'CSV')->setDelimiter(',')
    ->setEnclosure('"')
    ->setSheetIndex(0)
    ->save(str_replace('.php', '.xlsx', '../data/output/'.$input_file_name.'_cleared.xlsx'));
$callEndTime = microtime(true);
$callTime = $callEndTime - $callStartTime;

echo date('H:i:s') , " File written to " , str_replace('.php', '.csv', pathinfo(__FILE__, PATHINFO_BASENAME)) , "<br>";
echo 'Call time to write Workbook was ' , sprintf('%.4f',$callTime) , " seconds" , "<br>";
// Echo memory usage
echo date('H:i:s') , ' Current memory usage: ' , (memory_get_usage(true) / 1024 / 1024) , " MB" , "<br>";

echo date('H:i:s') , " Write to Excel2007 format" , "<br>";;
$callStartTime = microtime(true);
$objPHPExcel_Output->getActiveSheet()->setTitle('Tabelle1');
$objWriter2007 = PHPExcel_IOFactory::createWriter($objPHPExcel_Output, 'Excel2007');
$objWriter2007->save(str_replace('.php', '.xlsx', '../data/output/'.$input_file_name.'_cleared.xlsx'));
$callEndTime = microtime(true);
$callTime = $callEndTime - $callStartTime;
echo date('H:i:s') , " File written to " , str_replace('.php', '.xlsx', pathinfo(__FILE__, PATHINFO_BASENAME)) , "<br>";;
echo 'Call time to write Workbook was ' , sprintf('%.4f',$callTime) , " seconds" , "<br>";
// Echo memory usage
echo date('H:i:s') , ' Current memory usage: ' , (memory_get_usage(true) / 1024 / 1024) , " MB" , "<br>";

// Echo memory peak usage
echo date('H:i:s') , " Peak memory usage: " , (memory_get_peak_usage(true) / 1024 / 1024) , " MB" , "<br>";

// Echo done
echo date('H:i:s') , " Done writing files" , "<br>";
echo 'Files have been created in ' , getcwd() , "<br>";
