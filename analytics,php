<?php
$rootPath = $_SERVER["DOCUMENT_ROOT"];
$header = $rootPath."/header.php";
$footer = $rootPath."/footer.php";
include $header;
include $rootPath."/tool/toolList.php";
include "apiFunctions.php";
?>

<?php
error_reporting(E_ALL);  // Turn on all errors, warnings and notices for easier debugging



// Gather API call inputs
$filterarray =
array(
	array(
		'name' => 'SoldItemsOnly', // Historical prices data = sold items only
		'value' => 'true',
		'paramName' =>'',
		'paramValue' =>''),

	);
$query = $_POST["search_query"];  // From http POST, i.e. "iphone 6 128gb"
$operation = 'findCompletedItems';
$sortOrder = 'EndTimeSoonest';



// Load the call and capture the document returned by eBay API
$apicall = getApiURL($operation, $query, $filter, $sortOrder);
$resp = simplexml_load_file($apicall);





// Check to see if the request was successful, else print an error
if ($resp->ack == "Success") {
    $results = '';
    $results .= "<tr> <td><h2>No.<h2></td> <td><h2>Thumbnail</h2></td> <td><h2>Title</h2></td> <td><h2>Location</h2></td> <td><h2>Conditions</h2></td> <td><h2>Sold Price</h2></td> <td><h2>Listing Type</h2></td> <td><h2>Status</h2></td> <td><h2>Sold On</h2></td></tr>";
    $statTable ='';

    // Collect attrbutes data of each item into array 
    $itemDataArray = extractRespContent($resp);
    
    // Generate csv format and save
    $csvString = str_putcsv($itemDataArray);
    $fp = fopen('data.csv', 'w');
    fwrite($fp, $csvString);
    fclose($fp);

    // Python Processing
    $command = escapeshellcmd('python statistics.py');
    $output = shell_exec($command);
    $stats = json_decode($output,true); // Array ( [p60] => 626 [p70] => 643 [p90] => 749 [average] => 666 ...etc )


    // append stats
    displayStats($stats, $statTable);


    $n = 0;
    // If the response was loaded, parse it and build links
    foreach($resp->searchResult->item as $item) {
        $n++;
        displayCompletedHTML($item, $results, $n);
    }

}
// If the response does not indicate 'Success,' print an error
else {
	$results  = "<h3>Oops! The request was not successful. Make sure you are using a valid ";
	$results .= "AppID for the Production environment.</h3>";
}

/* End of script */
?>




<?php
/* PHP functions that help with outputing to Browser */


function displayCompletedHTML($item, &$results,$n){
    // Pull object content into variables
    $itemId       = $item->itemId;
    $title        = $item->title;
    $galleryURL   = $item->galleryURL;
    $viewItemURL  = $item->viewItemURL;
    $productId    = $item->productId;
    $postalCode   = $item->postalCode;
    $location     = $item->location;
    //sellingStatus
    $currentPrice    = $item->sellingStatus->currentPrice;
    $sellingState = $item->sellingStatus->sellingState;
    //listingInfo
    $listingType = $item->listingInfo->listingType;
    $startTime   = $item->listingInfo->startTime;
    $endTime     = $item->listingInfo->endTime;
    //condition
    $conditionDisplayName = $item->condition->conditionDisplayName;
    //shippingInfo
    $shippingCharge = $item->shippingInfo->shippingServiceCost;
    //calculated
    $totalPrice = $currentPrice+$shippingCharge;

    // For each SearchResultItem node, build a link and append it to $results
        
    $results .= "<tr>
    <td>$n. </td>
    <td><img src=\"$galleryURL\"></td>
    <td><a href=\"$viewItemURL\">$title</a></td>
    <td> $location</td>
    <td> $conditionDisplayName</td>
    <td>\$ $totalPrice</td>
    <td> $listingType </td>
    <td> $sellingState</td>
    <td> $endTime </td>
    </tr>";
}

function displayStats($stats, &$statTable){
    // Pull object content into variables
    $n = $stats["n"];
    $average = $stats["average"];
    $median = $stats["median"];
    $stdev = $stats["stdev"];
    $maxi = $stats["maxi"];
    $mini = $stats["mini"];
    $p10 = $stats["p10"];
    $p20 = $stats["p20"];
    $p30 = $stats["p30"];
    $p40 = $stats["p40"];
    $p50 = $stats["p50"];
    $p60 = $stats["p60"];
    $p70 = $stats["p70"];
    $p80 = $stats["p80"];
    $p90 = $stats["p90"];

    // For each SearchResultItem node, build a link and append it to $results

    $statTable .= "
    <tr>
     <td>
      <img src=\"plot.png\">
     </td>
     <td>
      <h3>Number of Sample Points: $n</h3>
      <h1>Average: \$$average</h1>
      <h3>Standard Deviation: \$$stdev</h3>
      <h3>30th Percentile:    \$$p30</h3>
      <h3>Maximum: \$$maxi</h3>
      <h3>Minimum: \$$mini</h3>
      <h3>Median:  \$$median</h3>
     </td>
    </tr>
    

    <tr>
     <td>
      <img src=\"histogram.png\">
     </td>
     <td>
      <img src=\"bootstrap.png\">
     </td>
    </tr>";

}
?>





<!-- Main Content -->
	
<h1>eBay Sold Items Search</h1>
<div id="search">
<form name="queryForm" action="analytics.php" method="post">
	<label>Search: <input class="text" type="text" name="search_query" style="height:2em; width:10em"/></label>
	<input type="submit" value="Go"/>
</form>
</div>

<h1>Search Results for: <?php echo $_POST["search_query"]; ?> </h1>
<div id="results"></div>

	<table width="70%" border="0" cellspacing="0" cellpadding="3"><tbody>
                <?php echo $statTable ?>
        <tbody></table>
		<!--<tr>
			<td>
				
			</td>
		</tr>
                -->
        <table width="100%" border="0" cellspacing="0" cellpadding="3"><tbody>

                <tr><td><?php echo $results ?></td></tr>
	<tbody></table>

		
<!-- End of main content -->

<?php include $footer ?>
