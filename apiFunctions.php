<?php
function getApiURL($operation, $query, $filterarray, $sortOrder){
	
	// API request variables
	$endpoint = 'http://svcs.ebay.com/services/search/FindingService/v1';  // URL to call
	$version  = '1.13.0';                                                  // API version supported by your application
	$appid    = '';                                                        // Replace with your own AppID
	$globalid = 'EBAY-US';                                                  // Global ID of the eBay site you want to search (e.g., EBAY-DE)

	// prepare query
	$safequery = urlencode($query);

	// Build the indexed item filter URL snippet
	$urlfilter = buildURLArray($filterarray);

	// Construct the URL call
	$apicall  = "$endpoint?";
	$apicall .= "OPERATION-NAME=$operation";
	$apicall .= "&SERVICE-VERSION=$version";
	$apicall .= "&SECURITY-APPNAME=$appid";
	$apicall .= "&GLOBAL-ID=$globalid";
	$apicall .= "&keywords=$safequery";
	$apicall .= "&paginationInput.entriesPerPage=100";
	$apicall .= "$urlfilter";
	$apicall .= "&sortOrder=$sortOrder";
        // Used to get shipping cost. Should be parameterized buyer's postal code.
        $apicall .= "&buyerPostalCode=11432";
	
	return $apicall;
}
// Generates an indexed URL snippet from the array of item filters
function buildURLArray($filterarray) {
	$urlfilter ='';
	$i='0';
	// Iterate through each filter in the array
	foreach ($filterarray as $itemfilter) {
		// Iterate through each key in the filter
		foreach ($itemfilter as $key => $value) {
			if (is_array($value)) {
				foreach ($value as $j => $content) {// Index the key for each value
					$urlfilter .= "&itemFilter($i).$key($j)=$content";
				}
			} else {
				if ($value != "") {
					$urlfilter .= "&itemFilter($i).$key=$value";
				}
			}
		}
		$i++;
	}
	return "$urlfilter";
}


function extractRespContent($resp, $maxPrice){
    $arr = array();
    $n = 0;
    foreach($resp->searchResult->item as $item){
        $temp = array(
                'itemId'       => $item->itemId,
                'title'        => $item->title,
                'galleryURL'   => $item->galleryURL,
                'viewItemURL'  => $item->viewItemURL,
                'postalCode'   => $item->postalCode,
                'location'     => $item->location,
                //sellingStatus
                'currentPrice'  => $item->sellingStatus->currentPrice, // Also used by auction as: current bidded price
                'sellingState' => $item->sellingStatus->sellingState,//
                //listingInfo
                'listingType' => $item->listingInfo->listingType,
                'startTime'   => $item->listingInfo->startTime,
                'endTime'     => $item->listingInfo->endTime,
                'buyItNowPrice'=> 0, // to be modifed below
                //condition
                'conditionDisplayName' => $item->condition->conditionDisplayName,
                //shippingInfo
                'shippingServiceCost' => $item->shippingInfo->shippingServiceCost,
                //calculated
                'totalPrice' => ($item->sellingStatus->currentPrice)  + ($item->shippingInfo->shippingServiceCost)
            );

        // If the item is an AuctionWithBIN, then it has an buyItNowPrice attribute to append
        if (isset($item->listingInfo->buyItNowPrice)){
            $temp['buyItNowPrice'] = $item->listingInfo->buyItNowPrice;  // If auction: buy it now price
            $temp['totalPrice'] = max(($item->sellingStatus->currentPrice),($item->listingInfo->buyItNowPrice) )  + ($item->shippingInfo->shippingServiceCost);
        }
        
        if ( ($maxPrice=="") || ($temp['totalPrice'] < $maxPrice) ){
            array_push($arr,$temp);
        }else{
    
        }


        $n++;
    }
    //var_dump($arr);
    return $arr;
}


function filter_append($minPrice, $maxPrice, $condition, $format, $onlineStatus, $country){
    // build filter: 1.minPrice 2.maxPrice 3.condition 4.format 5.onlineStatus 6.country
    $filterarray = array();

    // 1. filter: min price
    if ($minPrice != ""){
        $filter_minPrice = array(
        'name' => 'MinPrice', // Historical prices data = sold items only
        'value' => $minPrice,
        'paramName' =>'',
        'paramValue' =>'');
        array_push($filterarray, $filter_minPrice);    
    }

    // 2. filter: max price
    if ($maxPrice != ""){
        $filter_maxPrice = array(
        'name' => 'MaxPrice', // Historical prices data = sold items only
        'value' => $maxPrice,
        'paramName' =>'',
        'paramValue' =>'');
        array_push($filterarray, $filter_maxPrice);    
    }

    // 3. filter: condition
    if ($condition == "new" ){
        $conditionType = array('New');
        $filter_conditionType = array(
        'name' => 'Condition',
        'value' => $conditionType,
        'paramName' =>'',
        'paramValue' =>'');
        array_push($filterarray, $filter_conditionType);
    } elseif ($condition == "used"){
        $conditionType = array('Used');
        $filter_conditionType = array(
        'name' => 'Condition',
        'value' => $conditionType,
        'paramName' =>'',
        'paramValue' =>'');
        array_push($filterarray, $filter_conditionType);
    } else { /*for any condition, just don't pass in a condition filter*/ }

    // 4. filter: listing format
    if ($format == "all" ){
        $listingType = array('All');
    } elseif ($format == "auction"){
        $listingType = array('Auction');
    } else { // Fixed Price
        $listingType = array('AuctionWithBIN', 'FixedPrice');
    }
    $filter_listingType = array(
    'name' => 'ListingType',
    'value' => $listingType,
    'paramName' =>'',
    'paramValue' =>'');
    array_push($filterarray, $filter_listingType);

    // 5. filter: onlineStatus i.e. active or sold 
    if ($onlineStatus == "sold"){
        $filter_format = array(
        'name' => 'SoldItemsOnly', // Historical prices data = sold items only
        'value' => 'true',
        'paramName' =>'',
        'paramValue' =>'');
        
        array_push($filterarray, $filter_format);
    }

    // 6. filter: country
    if ($country == "usa"){
        $filter_country = array(
        'name' => 'LocatedIn', // Historical prices data = sold items only
        'value' => 'US',
        'paramName' =>'',
        'paramValue' =>'');
        array_push($filterarray, $filter_country);    
    }

    return $filterarray;
}




/**
 * Convert a multi-dimensional, associative array to CSV data
 * @param  array $data the array of data
 * @return string       CSV text
 */
function str_putcsv($data) {
        # Generate CSV data from array
        $fh = fopen('php://temp', 'rw'); # don't create a file, attempt
                                         # to use memory instead

        # write out the headers
        fputcsv($fh, array_keys(current($data)));

        # write out the data
        foreach ( $data as $row ) {
                fputcsv($fh, $row);
        }
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);

        return $csv;
}


?>
