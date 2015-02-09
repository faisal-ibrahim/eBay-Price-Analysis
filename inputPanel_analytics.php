<div>		
<fieldset>
<legend><h1>Price Analysis</h1></legend>
	<form name="queryForm" action="analytics.php" method="post">
	<table style="font-size:1em" cellspacing="10" align='center'>
	<tr>
		<th align="left" width="160">Listing Status </th>
		<th align="left" width="160">Price Range</th>
		<th align="left" width="160">Conditions</th>
		<th align="left" width="160">Format</th>
		<th align="left" width="160">Country </th>
	</tr>
	
	<tr>

	<!-- Listing Status -->
	<td>
		<a>
			<div><input type="radio" name="onlineStatus" value="active"  /> Active</div>
			<div><input type="radio" name="onlineStatus" value="sold" checked="checked" /> Sold</div>
		</a>
	</td>

	<!-- Price Range -->
	<td>
		<table>
		<tr>
			<td> Min </td>
			<td align="center">$<input type="text" name="minPrice" size="6" maxlength="13" value="0" /></td>
		</tr>
		<tr>
			<td> Max </td>
			<td align="center">$<input type="text" name="maxPrice" size="6" maxlength="13" /></td>
		</tr>

		</table>	
	</td>

	<!-- Condition -->
	<td>
		<a>
			<div><input type="radio" name="condition" value="new"  /> New</div>
			<div><input type="radio" name="condition" value="used"  /> Used</div>
			<div><input type="radio" name="condition" value="all" checked="checked" /> All</div>
		</a>
	</td>

	<!-- Format -->
	<td>
		<a>
			<div><input type="radio" name="format" value="all"  /> All </div>
			<div><input type="radio" name="format" value="auction"  /> Auction</div>
			<div><input type="radio" name="format" value="buyItNow" checked="checked" /> Fixed Price</div>
		</a>
	</td>

	<!-- Country -->
	<td>
		<a>
			<div><input type="radio" name="country" value="all"  /> All </div>
			<div><input type="radio" name="country" value="usa" checked="checked" /> US Only </div>
		</a>
	</td>
	
	<tr>
		<td colspan="5" style="height:100">
		    <input style="height:50; font-size:30" type="text" name="search_query" size="50" maxlength="50" placeholder="i.e. Iphone 6 plus 128GB -lot"/>
		</td>
	</tr>
	<tr>
		<td colspan="5">
		    <div align="center">
		    	<input style="height:50px; width:200px" type="submit" value="Search"/>
		    </div>
		    (Learn advance operators <a href="http://pages.ebay.com/help/search/advanced-search.html">here</a>.)
		</td>
	</tr>
	
	</table>
	</form>
</fieldset>
</div>
