<?php
/***************************************************************************
 *
 *  Audentio Design Affiliated Themes Plugin
 *  Authors: Audentio Design & updated by Vintagedaddyo
 *  Copyright: © 2012 Audentio
 *  
 *  Website: http://www.audentio.com
 *  License: license.txt
 *
 *  Allows users to easily have an affiliated themes area on their forums
 * ***************************************************************************/
 
define('IN_MYBB', 1);

define('THIS_SCRIPT', 'affiliates.php');
 
$templatelist = "aud_affiliates_index, aud_affiliates_cats, aud_affiliates_products, aud_affiliates_cat_bits, aud_affiliates_product_bits";

require_once "./global.php";

require_once MYBB_ROOT."inc/functions.php";

if($mybb->settings['aff_active'] != 1)
{
	error_no_permission();
}
 
add_breadcrumb($mybb->settings['aff_page_title'], $mybb->settings['aff_page_name']);

$i = 0;	//this magic $i shall be used for colours everywhere...booyah
$j = 0; //another counter

// multi stuff
$page = intval($mybb->input['page']) > 0 ? intval($mybb->input['page']) : 1;

$pplimit = $mybb->settings['aff_page_limit'];

$start = ($page-1)*$pplimit;

// num of columns
$num_col = $mybb->settings['aff_num_col'];

//let's load the sidebar shall we
if($mybb->settings['aff_cats_shown'] != 0)
	$allowed = explode(',', $mybb->settings['aff_cats_shown']);
	
$query = $db->simple_select("audentio_products_cats", "*");

while($bits = $db->fetch_array($query))
{
	if($mybb->settings['aff_cats_shown'] == 0 || in_array($bits['cid'], $allowed))
	{
		
		$catname = $bits['name'];
		
		$count = $bits['items'];
		
		if(++$i % 2) {
			$altbg = 'trow1';
			}
			
		else {
			$altbg = 'trow2';
			}
			
		eval("\$catbits .= \"".$templates->get("aud_affiliates_cat_bits")."\";");
		
	}
}

//only load the table if we have items
if($catbits)
	eval("\$cats = \"".$templates->get("aud_affiliates_cats")."\";");

//Sidebar Loaded

if($mybb->input['category'] != '')
{
	//lets load a category
	$query = $db->simple_select('audentio_products_cats', '*' , 'name="'.$db->escape_string(strtolower($mybb->input['category'])).'"');
	
	if($db->num_rows($query) > 0)
	{
		//found cat
		$cid = $db->fetch_array($query);
		add_breadcrumb($cid['name']);
		if($mybb->settings['aff_cats_shown'] == 0 || in_array($cid['cid'], $allowed))
		{
			$options = array(
				"limit_start" => $start,
				"limit" => $pplimit,
			);
			
			$query = $db->simple_select("audentio_products", "COUNT(*) as rows", 'cat="'.$cid['cid'].'"');
			
			$num = $db->fetch_field($query, "rows");
			
			$query = $db->simple_select('audentio_products', '*', 'cat="'.$cid['cid'].'"', $options);
			
			while($prods = $db->fetch_array($query))
			{
				
				if(++$i % 2) {
					$altbg = 'trow1';
					}
					
				else {
					$altbg = 'trow2';
					}
					
				if ($j % $num_col == $num_col - 1) { 
				 // start a new row
					$tr_e = "</tr>\n<tr>";
				 } 
				 
				 else {
					$tr_e = '';
				 }
				 
				$j++;
					
				eval("\$product_bits .= \"".$templates->get("aud_affiliates_product_bits")."\";");
				
			}
			
			$multipage = multipage($num, $pplimit, $page, $mybb->settings['bburl'].'/'.$mybb->settings['aff_page_name'].'?category='.$cid['name']);
			
		}
	}
}

else {
	
        // meh perhaps look at this more when I actually have the time to do so? Note to self: sleep and coffee may be a solution, lol
        //if(count($allowed > 0) && $mybb->settings['aff_cats_shown'] != 0)
        if(count(array($allowed > 0)) && $mybb->settings['aff_cats_shown'] != 0)
        {
		
		$where = "cat IN ({$mybb->settings['aff_cats_shown']})";
		
	}
	
	//load the default page
	$options = array(
		"limit_start" => $start,
		"limit" => $pplimit,
	);
	
	$query = $db->simple_select("audentio_products", "COUNT(*) as rows", $where);
	$num = $db->fetch_field($query, "rows");
	
	$query = $db->simple_select('audentio_products', '*' ,$where, $options);
	
	while($prods = $db->fetch_array($query)) {
		
		if(++$i % 2) {
			$altbg = 'trow1';
			}
			
		else {
			$altbg = 'trow2';
			}
			
		if ($j % $num_col == $num_col - 1) { 
		 // start a new row
			$tr_e = "</tr>\n<tr>";
		 } 
		 
		 else {
			$tr_e = '';
		 }
		 
		$j++;
			
		eval("\$product_bits .= \"".$templates->get("aud_affiliates_product_bits")."\";");
		
	}
	
	$multipage = multipage($num, $pplimit, $page, $mybb->settings['bburl'].'/'.$mybb->settings['aff_page_name']);
	
}
//only load the table if we have items
if($product_bits) {
	eval("\$products = \"".$templates->get("aud_affiliates_products")."\";");

eval("\$aff_index = \"".$templates->get("aud_affiliates_index")."\";");

}

output_page($aff_index);

?>
