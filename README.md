# Historical-Price-Analysis
Show sold eBay listings, graphs, and analysis result.
Play with my implementation here:
http://jumbojimbo.ddns.net/tool/analytics/index_analytics.php

# What it does?
The analytics.php script will cal eBay to retrieve a list of sold listings corresponding to your search query.
The data are then saved to a csv file called data.csv.
During this time, PHP calls the python script, "statistics.py", which does the follow:
 1. Load data.csv
 2. Compute statistical results (historgram, average, means, standard deviation, percentile)
 3. 1000 Bootstrap re-sampling on the data set to get a measure of what's called "Variance of the means"
 4. Generate plots: bootstrap.png, histogram.png, plot.png
All the statistical results are passed back to PHP through JSON format.
The script finishes by outputing to HTML.

# Requirements
-eBay developer token. Place it in apiFunctions.php line 7 (or variable $appid).
-Python and Matplotlib

# Usage
1. Serve the analytics.php file with your web server.
2. Browse to the page, and enter a product of interest into the search bar.

