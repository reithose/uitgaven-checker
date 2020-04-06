<?php
error_reporting(E_ALL ^ E_NOTICE);
include("parsecsv.lib.php");

if($_GET["config"])
  $configFile = $_GET["config"];
else
  $configFile = "config.json"; //default config file

$posten = json_decode(file_get_contents("configs/".$configFile));
$postenUniek = array();
$postenTotalen = array();

$configDir = opendir("configs");
while($file = readdir($configDir)) {
  if ($file != "." && $file != ".." && $file != ".DS_Store"){
    $configFiles[] = $file;
  }
}


$dir = opendir("csv");
while($file = readdir($dir)) {
  if ($file != "." && $file != ".." && $file != ".DS_Store" && substr($file, -3) == "csv"){
    $csvFile = $file;
  }
}
$csv = new parseCSV();
$csv->parse("csv/".$csvFile);



//CSV data met posten uit config vergelijken en array opbouwen
foreach ($csv->data as $cKey => $cValue) {  
  foreach ($posten as $pKey => $pValue) {
    if(strstr($csv->data[$cKey][$posten[$pKey]->veld], $posten[$pKey]->waarde)){
      $result["maand"] = substr($csv->data[$cKey]["Datum"], 0, 4)."-".substr($csv->data[$cKey]["Datum"], 4, -2);
      $result["post"] = $posten[$pKey]->post;
      $result["bedrag"] = (double)str_replace(",", ".", $csv->data[$cKey]["Bedrag (EUR)"]);
      $result["bijaf"] = $csv->data[$cKey]["Af Bij"];
      $postData[] = $result;
    }
  }
}

  
//Totalen per maand en per post bepalen
$currMaand = $postData[0]["maand"]; //Beginnen bij het begin

foreach ($postData as $key => $value) {

  $currJaar = substr($postData[$key]["maand"], 0, 4);

    if($postData[$key]["maand"] != $currMaand){

    $tableData[] = array("maand" => $currMaand, "posten" => $currPost);
    $currMaand = $postData[$key]["maand"]; //Alles een maand opschuiven
    unset($currPost);
  }

  if($postData[$key]["bijaf"] == "Bij"){
    $currPost[$postData[$key]["post"]] = $currPost[$postData[$key]["post"]] + $postData[$key]["bedrag"];
    $postenTotalen[$postData[$key]["post"]] = $postenTotalen[$postData[$key]["post"]] + $postData[$key]["bedrag"];
    $jaarData[$currJaar] = $jaarData[$currJaar] + $postData[$key]["bedrag"];
  }
  else{
    $currPost[$postData[$key]["post"]] = $currPost[$postData[$key]["post"]] - $postData[$key]["bedrag"];  
    $postenTotalen[$postData[$key]["post"]] = $postenTotalen[$postData[$key]["post"]] - $postData[$key]["bedrag"];  
    $jaarData[$currJaar] = $jaarData[$currJaar] - $postData[$key]["bedrag"];
  }

  //Array met unieke posten maken
  if(!in_array($postData[$key]["post"], $postenUniek))
    $postenUniek[] = $postData[$key]["post"];
  
}

$tableData[] = array("maand" => $currMaand, "posten" => $currPost);

?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">

    <title>Rekeningoverzicht</title>
  </head>
  <body>
  	<div class="container">
    <div class="row">
      <div class="col-lg-4">
        <table class='table table-hover'>
          <tr>
            <th>jaar</th>
            <th>bedrag</th> 
          </tr>
  <?php
          foreach ($jaarData as $key => $value) {
            echo"<tr>
                  <td>".$key."</td>
                  <td>".$value."</td>
                </tr>";
          }
  ?>
    </table>
    </div>
    <div class="col-lg-4">
        <table class='table table-hover'>
          <tr>
            <th>Config files</th>
          </tr>
  <?php
          foreach ($configFiles as $key => $value) {
            echo"<tr>
                    <td><a href='index.php?config=".$value."'>".substr($value, 0, -5)."</a> <a href='configs/".$value."'>[view json]</a></td>
                </tr>";
          }
  ?>

        </table>
      </div>
      <div class="col-lg-4">
        <table class='table table-hover'>
          <tr>
            <th>Bron</th>
          </tr>
          <tr>
            <td><a href='csv/ <?php echo $csvFile ?>'><?php echo $csvFile ?></a></td>
          </tr>
        </table>
      </div>
  </div>
  <div class='row'>
    <div class='col'>
      <table class='table table-hover'>
    		<tr>
    			<th>maand</th>
    <?php
    			foreach ($postenUniek as $puKey => $puValue) {
    				echo"<th>".$postenUniek[$puKey]."</th>";
    			}
    ?>
    		  <th>maand totaal</th>	
    	</tr>
    	<tr>
    		<th>totaal</th>
    <?php    
    			foreach ($postenTotalen as $ptKey => $ptValue) {
    				echo"<th>".str_replace(".", ",", $postenTotalen[$ptKey])."</th>";
    				$totaal = $totaal + $postenTotalen[$ptKey];
    			}
    ?>

    		<th><?php str_replace(".", ",", $totaal) ?></th>
    	</tr>

    <?php
      foreach ($tableData as $key => $value) {
        echo"<tr>
        	     <td>".$tableData[$key]["maand"]."</td>";
        	unset($rowTotal);
        	foreach ($postenUniek as $puKey => $puValue) {
        		$rowTotal = $rowTotal + $tableData[$key]["posten"][$postenUniek[$puKey]];
        		echo"<td>".str_replace(".", ",", $tableData[$key]["posten"][$postenUniek[$puKey]])."</td>";
        	}

        	echo"<td>".str_replace(".", ",", $rowTotal)."</td>
          </tr>";
    }
    ?>

    </table>
   </div>
   </div>
   </div>
  </body>
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js" integrity="sha384-cs/chFZiN24E4KMATLdqdvsezGxaGsi4hLGOzlXwp5UZB1LY//20VyM2taTB4QvJ" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js" integrity="sha384-uefMccjFJAIv6A+rW+L4AHf99KvxDjWSu1z9VI8SKNVmz4sk7buKt/6v9KI65qnm" crossorigin="anonymous"></script>
</html>