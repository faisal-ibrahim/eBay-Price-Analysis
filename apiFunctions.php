<?php
function getApiURL($operation, $query, $filterarray, $sortOrder){
	
	// API request variables
	$endpoint = 'http://svcs.ebay.com/services/search/FindingService/v1';  // URL to call
	$version  = '1.13.0';  // API version supported by your application
	$appid    = '';  // Replace with your own AppID
	$globalid = 'EBAY-US';  // Global ID of the eBay site you want to search (e.g., EBAY-DE)

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


function extractRespContent($resp){
    $arr = array();
    $n = 0;
    foreach($resp->searchResult->item as $item){
        array_push(
            $arr,
            array(
                'itemId'       => $item->itemId,
                'title'        => $item->title,
                'galleryURL'   => $item->galleryURL,
                'viewItemURL'  => $item->viewItemURL,
                'postalCode'   => $item->postalCode,
                'location'     => $item->location,
                //sellingStatus
                'currentPrice'    => $item->sellingStatus->currentPrice,
                'sellingState' => $item->sellingStatus->sellingState,//
                //listingInfo
                'listingType' => $item->listingInfo->listingType,
                'startTime'   => $item->listingInfo->startTime,
                'endTime'     => $item->listingInfo->endTime,
                //condition
                'conditionDisplayName' => $item->condition->conditionDisplayName,
                //shippingInfo
                'shippingServiceCost' => $item->shippingInfo->shippingServiceCost,
                //calculated
                'totalPrice' => ($item->sellingStatus->currentPrice) + ($item->shippingInfo->shippingServiceCost)
            )
        );

        $n++;
    }
    return $arr;
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
