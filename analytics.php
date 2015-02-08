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
$onlineStatus = $_POST['onlineStatus'];  // "active" or "sold"
$minPrice     = $_POST["minPrice"];
$maxPrice     = $_POST["maxPrice"];
$format       = $_POST["format"];        // "all", "auction", "buyItNow"
$condition    = $_POST['condition'];     // "new", "used", "all"
$country      = $_POST['country'];       // "all" or "usa"
$query        = $_POST["search_query"];  // From http POST, i.e. "iphone 6 128gb"




// Build filter criterial
$filterarray  = filter_append($minPrice, $maxPrice, $condition, $format, $onlineStatus, $country);




// Identify operation whether to search for online items (sort by price) or historical itens (sort by time)
$operation = '';
$sortOrder = '';
if ($onlineStatus =="sold"){
    $operation = 'findCompletedItems';
    $sortOrder = 'EndTimeSoonest';
} else {
    $operation = 'findItemsByKeywords';
    $sortOrder = 'PricePlusShippingLowest';  
}




// Load the call and capture the document returned by eBay API
$apicall = getApiURL($operation, $query, $filterarray, $sortOrder);
$resp = simplexml_load_file($apicall);





// Check to see if the request was successful, else print an error
if ($resp->ack == "Success") {
    
    // HTML strings for statistics results and eBay listing results
    $listingContent  = '';
    $statisticsContent ='';




    // Collect attrbutes data of each item into array 
    $itemDataArray = extractRespContent($resp, $maxPrice);




    // Save all the data contents into a csv file
    $csvString = str_putcsv($itemDataArray);
    $fp = fopen('data.csv', 'w');
    fwrite($fp, $csvString);
    fclose($fp);




    // Python Processing: read csv files, perform statistical analysis, return statistis to PHP in JSON
    $command = escapeshellcmd('python statistics.py'); //. escapeshellarg(json_encode($itemDataArray)));
    $output = shell_exec($command);
    $stats = json_decode($output,true); // Array ( [p60] => 626 [p70] => 643 [p90] => 749 [average] => 666 ...etc )




    // Output statistical results to HTML/Browser
    displayStats($stats, $statisticsContent);



    // Output eBay listings to HTML/Browser
    displayListing($resp, $maxPrice, $listingContent);

}

// If the response does not indicate 'Success,' print an error
else {
	$listingContent  = "<h3>Oops! The request was not successful. Make sure you are using a valid ";
	$listingContent .= "AppID for the Production environment.</h3>";
}

/* End of script */
?>




<?php
/* PHP functions that help with outputing to Browser */

function displayListing($resp, $maxPrice, &$listingContent){
    $header='
    <table cellspacing="1" cellpadding="1" width="100%">
    <tr>
    <th align="center" bgcolor="#ccccc"><h2>No.<h2></th>
    <th align="center" bgcolor="#ccccc"><h2>Thumbnail</h2></th>
    <th align="center" bgcolor="#ccccc" width="80"><h2>Title</h2></th>
    <th align="center" bgcolor="#ccccc"><h2>Location</h2></th>
    <th align="center" bgcolor="#ccccc"><h2>Condition</h2></th>

    <th align="center" bgcolor="#ccccc"><h2>Price</h2></th>
    <th align="center" bgcolor="#ccccc"><h2>Shipping</h2></th>
    <th align="center" bgcolor="#ccccc"><h2>Total</h2></th>

    <th align="center" bgcolor="#ccccc"><h2>Format</h2></th>
    <th align="center" bgcolor="#ccccc"><h2>Status</h2></th>
    <th align="center" bgcolor="#ccccc"><h2>End Time</h2></th>
    </tr>
    ';

    $footer='</table>';
    

    // Append header to $listingContent string
    $listingContent .= $header;
    
     
    // Append all eBay listing in loop to $listingContent string
    $n=0;
    foreach($resp->searchResult->item as $item) {
        $n++;
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
        $buyItNowPrice = 0;
        //condition
        $conditionDisplayName = $item->condition->conditionDisplayName;
        //shippingInfo
        $shippingServiceCost = $item->shippingInfo->shippingServiceCost;


        //calculated quantity
        $totalPrice = 0;
        // If the item is an AuctionWithBIN, then it has an buyItNowPrice attribute to append
        if (isset($item->listingInfo->buyItNowPrice)){ //buyItNowPrice appears (generally an AuctionwWithBIN item)
             $buyItNowPrice = $item->listingInfo->buyItNowPrice;  // If auction: buy it now price
        }
        $price = max($currentPrice, $buyItNowPrice);
        $totalPrice = floatval($price)+floatval($shippingServiceCost);

        // For each SearchResultItem node, build a link and append it to $listingContent
        if ( ($maxPrice=="") || ($totalPrice < $maxPrice) ){
            $colorA='#F6CED8';
            $colorB='#E0F2F7';
            $listingContent .= "
            <tr>
            <td align='center' bgcolor=\"$colorA\">$n. </td>
            <td align='center' bgcolor=\"$colorB\"><img src=\"$galleryURL\"></td>
    
            <td align='left' bgcolor=\"$colorA\"><a href=\"$viewItemURL\">$title</a></td>
            <td align='left' bgcolor=\"$colorB\"> $location</td>
    
            <td align='center' bgcolor=\"$colorA\"> $conditionDisplayName</td>
            <td align='center' bgcolor=\"$colorB\">\$$price</td>

            <td align='center' bgcolor=\"$colorA\">\$$shippingServiceCost</td>
            <td align='center' bgcolor=\"$colorB\"><font color='black'><h3>\$$totalPrice</h3></font></td>

            <td align='center' bgcolor=\"$colorA\"> $listingType </td>
            <td align='center' bgcolor=\"$colorB\"> $sellingState</td>
    
            <td align='center' bgcolor=\"$colorA\"> $endTime </td>
            </tr>";
        }
    }
    
    // End of loop. Finish off with appending the footer
    $listingContent .= $footer;
}

function displayStats($stats, &$statisticsContent){
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

    // For each SearchResultItem node, build a link and append it to $listingContent
    // Explanation of resizing: max-width allows resizing up to certain percentage (relative to current container);
    //   -width (in percent) always lock objects at that relative size
    //   -Hence the table is locked with "width=80%" while the inner, td, gets max-width resizes up to 100% relative to container   
    $statisticsContent .= "
    <table align='left' width='80%'>

    <tr>

     <td valign='top'>
      <img src=\"plot.png\" style='max-width:100%'>
     </td>

     <td valign='' align='left'>
      <table style='font-size:30' width='70%'>
       <tr>
        <td bgcolor='#E0F2F7'>Sample Points Count</td>
        <td bgcolor='#E0F2F7'>$n</td>
       </tr>
       <tr>
        <td bgcolor='#F6CED8'>Average</td>
        <td bgcolor='#F6CED8'>\$$average</td>
       </tr>
       <tr>
        <td bgcolor='#E0F2F7'>Standard Deviation</td>
        <td bgcolor='#E0F2F7'>\$$stdev</td>
       </tr>

       <tr>
        <td bgcolor='#F6CED8'>Max</td>
        <td bgcolor='#F6CED8'>\$$maxi</td>
       </tr>
       <tr>
        <td bgcolor='#E0F2F7'>Min</td>
        <td bgcolor='#E0F2F7'>\$$mini</td>
       </tr>
       <tr>
        <td bgcolor='#F6CED8'>Median</td>
        <td bgcolor='#F6CED8'>\$$median</td>
       </tr>
       <tr>
        <td bgcolor='#E0F2F7'>30th Percentile</td>
        <td bgcolor='#E0F2F7'>\$$p30</td>
       </tr>
      </table>
     </td>
    </tr>
    

    <tr>
     <td valign='top'>
      <img src=\"histogram.png\" style='max-width:100%'>
     </td>
     <td valign='top'>
      <img src=\"bootstrap.png\" style='max-width:100%'>
     </td>
    </tr>


    </table>
 
    ";

}
?>





<!-- Main Content -->
	
<?php include $rootPath."/tool/analytics/inputPanel_analytics.php";?>

<!-- Echo user's search query and filter input -->
<div>
<table align='left' style="font-size:20" cellspacing="1" cellpadding="5" width="51%">
    <tr><th colspan="2" align="center" bgcolor="#ccccc"> Search Query Attributes </th></tr>
    <tr>
        <th width=100 align="left" bgcolor="#ccccc">Filter</th>
        <th width=100 align="left" bgcolor="#ccccc">Input</th>
    </tr>
    <tr>
        <td align="left" bgcolor="#E0F2F7"> Query </td>
        <td align="left" bgcolor="#E0F2F7"> <font color="blue"> <b> <?php echo $_POST["search_query"] ?> </b> </font></td>
    </tr>
    <tr>
        <td align="left" bgcolor="#F6CED8"> Listing Status </td>
        <td align="left" bgcolor="#F6CED8"> <?php echo $_POST["onlineStatus"] ?> </td>
    </tr>
    <tr>
        <td align="left" bgcolor="#E0F2F7"> Max </td>
        <td align="left" bgcolor="#E0F2F7"> <?php echo "\$".$_POST["maxPrice"] ?> </td>
    </tr>
    <tr>
        <td align="left" bgcolor="#F6CED8"> Min </td>
        <td align="left" bgcolor="#F6CED8"> <?php echo "\$".$_POST["minPrice"] ?> </td>
    </tr>
    <tr>
        <td align="left" bgcolor="#E0F2F7"> Format </td>
        <td align="left" bgcolor="#E0F2F7"> <?php echo $_POST["format"] ?> </td>
    </tr>
    <tr>
        <td align="left" bgcolor="#F6CED8"> Country </td>
        <td align="left" bgcolor="#F6CED8"> <?php echo $_POST["country"] ?> </td>
    </tr>
</table>
</div>


<!-- Display graphs and statistics analysis -->
<div>
    <?php echo $statisticsContent ?>
</div>

<!-- Display eBay listings -->
<div>
     <?php echo $listingContent ?>
</div>  

		
<!-- End of main content -->

<?php include $footer ?>
