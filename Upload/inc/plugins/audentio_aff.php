<?php
/***************************************************************************
 *
 *  Audentio Affiliate Design Affiliate Plugin
 *  Author: Audentio Design
 *  Copyright: © 2012 Audentio
 *  
 *  Website: http://www.audentio.com
 *  License: license.txt
 *
 *  Allows users to easily have an affiliate area on their forums
 *
 ***************************************************************************/


if(!defined("IN_MYBB"))
{
	die("This file cannot be accessed directly.");
}

function audentio_aff_info()
{
	return array(
		"name"			=> "Audentio Design Affiliate Plugin",
		"description"	=> "Allows users to easily have an affiliate area on their forums.",
		"website"		=> "http://www.audentio.com",
		"author"		=> "Audentio Design",
		"authorsite"	=> "http://www.audentio.com",
		"version"		=> "1.0",
		"guid" 			=> "da4e88fadace68c95e00bac787793ddd",
		"compatibility"	=> "16*"
	);
}

function audentio_aff_install()
{
	global $db;

	if($db->table_exists('audentio_products'))
	{
		$db->write_query("DROP TABLE ".TABLE_PREFIX."audentio_products");
	}
	
	$db->write_query("CREATE TABLE ".TABLE_PREFIX."audentio_products_cats (".
		"cid INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL DEFAULT NULL, ".
		"name VARCHAR(120), ".
		"items INT(5))
	");
	
	if($db->table_exists('audentio_products'))
	{
		$db->write_query("DROP TABLE ".TABLE_PREFIX."audentio_products_cats");
	}
	$db->write_query("CREATE TABLE ".TABLE_PREFIX."audentio_products (".
		"pid INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL DEFAULT NULL, ".
		"name VARCHAR(32), ".
		"image text, ".
		"title VARCHAR(255), ".
		"price int(10), ".
		"tagline VARCHAR(255) DEFAULT NULL, ".
		"link VARCHAR(120) NOT NULL DEFAULT 0, ".
		"cat VARCHAR(10) NOT NULL DEFAULT 1)
	");		
	
	//for removal <----------------------------------------------------------------------------------------------------
	install_sample();
	
	$query = $db->simple_select("settinggroups", "COUNT(*) as rows");
	$rows = $db->fetch_field($query, "rows");
	
	$insertarray = array(
		'name' => 'aff_system', 
		'title' => 'Audentio Design Affiliate Plugin', 
		'description' => "Settings for the Audentio Design Affiliate Plugin", 
		'disporder' => $rows+1, 
		'isdefault' => 0
	);
	$gid = $db->insert_query("settinggroups", $insertarray);

	
	$setting = array(
		"sid"			=> NULL,
		"name"			=> "aff_active",
		"title"			=> "Audentio Design Affiliate Plugin",
		"description"	=> "Is the affiliate system active?",
		"optionscode"	=> "yesno",
		"value"			=> 1,
		"disporder"		=> 1,
		"gid"			=> $gid
	);
	$db->insert_query("settings", $setting);
	
		$setting = array(
		"sid"			=> NULL,
		"name"			=> "aff_affid",
		"title"			=> "Affiliate ID",
		"description"	=> "What is your Audentio Design affiliate ID (same as your user ID, you can find it on your profile).<br />Learn more about our <a href=\"http://www.audentio.com/services/affiliate_program\" target=\"_blank\">affiliate program here</a>. Submit a ticket if you need assistance.",
		"optionscode"	=> "text",
		"value"			=> '',
		"disporder"		=> 2,
		"gid"			=> $gid
	);
	$db->insert_query("settings", $setting);
	
	$setting = array(
		"sid"			=> NULL,
		"name"			=> "aff_cats_shown",
		"title"			=> "Shown Products",
		"description"	=> "Selects which products you would like to show. Comma seperated.<br />0. Allow everything &middot;  1. MyBB &middot; 2. Xenforo",
		"optionscode"	=> "text",
		"value"			=> 0,
		"disporder"		=> 3,
		"gid"			=> $gid
	);
	$db->insert_query("settings", $setting);
	
	$setting = array(
		"sid"			=> NULL,
		"name"			=> "aff_page_name",
		"title"			=> "Affiliate Page File Name",
		"description"	=> "If you have changed the affiliates.php page <b><u>name</u></b> change it here as well. Include the .php extension.",
		"optionscode"	=> "text",
		"value"			=> "affiliates.php",
		"disporder"		=> 4,
		"gid"			=> $gid
	);
	$db->insert_query("settings", $setting);
	
	$setting = array(
		"sid"			=> NULL,
		"name"			=> "aff_page_title",
		"title"			=> "Page Title",
		"description"	=> "What do you want the page title to be. Will also be reflected in the breadcrum navigation.",
		"optionscode"	=> "text",
		"value"			=> "Premium Forum Themes",
		"disporder"		=> 5,
		"gid"			=> $gid
	);
	$db->insert_query("settings", $setting);
	
	$setting = array(
		"sid"			=> NULL,
		"name"			=> "aff_num_col",
		"title"			=> "Number of Columns",
		"description"	=> "Number of columns in the product table. We recommend 3.",
		"optionscode"	=> "text",
		"value"			=> 3,
		"disporder"		=> 6,
		"gid"			=> $gid
	);
	$db->insert_query("settings", $setting);
	
	$setting = array(
		"sid"			=> NULL,
		"name"			=> "aff_page_limit",
		"title"			=> "Items Per Page",
		"description"	=> "Number of products to show per page. We recommend it be evenly divisibly by the number of columns.",
		"optionscode"	=> "text",
		"value"			=> 12,
		"disporder"		=> 7,
		"gid"			=> $gid
	);
	$db->insert_query("settings", $setting);
	
	rebuild_settings();

	$template = array(
		"title" => "aud_affiliates_index",
		"template" => $db->escape_string('<html>
<head>
{$headerinclude}
<title>{$mybb->settings[\'bbname\']} - {$mybb->settings[\'aff_page_title\']}</title>
</head>
<body>
{$header}
<table border="0" width="100%">
	<tr>
		<td width="200" valign="top">
			{$cats}
		</td>
		<td>
			{$products}
		</td>
	</tr>
</table>
{$multipage}	
{$footer}
</body>
</html>'),
		"sid" => "-1",
	);
	$db->insert_query("templates", $template);
	
	$template = array(
		"title" => "aud_affiliates_cats",
		"template" => $db->escape_string('
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
	<td class="thead">
		Categories
	</td>
</tr>	
{$catbits}
<tr>
	<td class="tfoot">
		<a href="http://www.audentio.com/discount/{$mybb->settings[\'aff_affid\']}" target="_blank">Visit Audentio Design</a>
</tr>
</table>
		'),
		"sid" => "-1",
	);
	$db->insert_query("templates", $template);
	
	$template = array(
		"title" => "aud_affiliates_products",
		"template" => $db->escape_string('
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
	<td class="thead" colspan="{$mybb->settings[\'aff_num_col\']}">Products</td>
</tr>
<tr>
{$product_bits}
</tr>
</table>
		'),
		"sid" => "-1",
	);
	$db->insert_query("templates", $template);
	
	$template = array(
		"title" => "aud_affiliates_cat_bits",
		"template" => $db->escape_string('
<tr>
	<td class="{$altbg}">
		<a href="{$mybb->settings[\'bburl\']}/{$mybb->settings[\'aff_page_name\']}?category={$catname}">{$catname}</a>
	</td>
</tr>
		'),
		"sid" => "-1",
	);
	$db->insert_query("templates", $template);
	
	$template = array(
		"title" => "aud_affiliates_product_bits",
		"template" => $db->escape_string('
	<td class="{$altbg}">
		<a href="{$prods[\'link\']}/{$mybb->settings[\'aff_affid\']}" target="_blank"><img src="{$prods[\'image\']}" alt="" height="120px" width="200px" /></a><br />
		<div style="float: right;margin-right: 10px;"><strong>&#36;{$prods[\'price\']}</strong></div>
		<strong><a href="{$prods[\'link\']}/{$mybb->settings[\'aff_affid\']}" target="_blank">{$prods[\'name\']}</a></strong><br />
		{$prods[\'tagline\']}
	</td>
{$tr_e}
		'),
		"sid" => "-1",
	);
	$db->insert_query("templates", $template);
}

function audentio_aff_is_installed()
{
	global $db;
	
	if($db->table_exists('audentio_products'))
		return true;
	
	return false;
}

function audentio_aff_activate()
{
	global $db;
	
	$db->update_query('settings', array('value' => 1), 'name="aff_active"');
	
	rebuild_settings();
}

function audentio_aff_deactivate()
{
	global $db;
	
	$db->update_query('settings', array('value' => 0), 'name="aff_active"');
	
	rebuild_settings();
}

function audentio_aff_uninstall()
{
	global $db, $mybb;

	$db->delete_query("settinggroups", "name = 'aff_system'");
	$db->delete_query('settings', 'name IN ( \'aff_active\',\'aff_cats_shown\', \'aff_page_title\', \'aff_affid\')');
	$db->delete_query('templates', 'title IN (\'aud_affiliates_index\',\'aud_affiliates_cats\', \'aud_affiliates_products\', \'aud_affiliates_cat_bits\', \'aud_affiliates_product_bits\')');	
	
	rebuild_settings();
	
	$db->write_query("DROP TABLE ".TABLE_PREFIX."audentio_products");
	$db->write_query("DROP TABLE ".TABLE_PREFIX."audentio_products_cats");
	
}

function install_sample()
{
	global $db;
	
	$cat = array(
	'name'	=> 'MyBB',
	);
	$db->insert_query('audentio_products_cats', $cat);
	
	$cat = array(
	'name'	=> 'Xenforo',
	);
	$db->insert_query('audentio_products_cats', $cat);	
	
	$prod = array(
		'name'		=> 'Glowing',
		'image'		=> 'http://www.audentio.com/uploads/products/glowing-1_thumb.jpg',
		'title'		=> 'Glowing',
		'price'		=> '20',
		'tagline'	=> 'Illuminate your community',
		'link'		=> 'http://www.audentio.com/shop/view/MyBB/glowing-1',
		'cat'		=> 1
	);
	$db->insert_query('audentio_products', $prod);	
	
	$prod = array(
		'name'		=> 'Sky',
		'image'		=> 'http://www.audentio.com/uploads/products/sky-2_thumb.jpg',
		'title'		=> 'Sky',
		'price'		=> '20',
		'tagline'	=> 'Float on Cloud 9',
		'link'		=> 'http://www.audentio.com/shop/view/MyBB/sky-2',
		'cat'		=> 1
	);
	$db->insert_query('audentio_products', $prod);
	
	$prod = array(
		'name'		=> 'Gaming Jam',
		'image'		=> 'http://www.audentio.com/uploads/products/gaming_jam-3_thumb.jpg',
		'title'		=> 'Gaming Jam',
		'price'		=> '20',
		'tagline'	=> 'Gaming Paradise',
		'link'		=> 'http://www.audentio.com/shop/view/MyBB/gaming_jam-3',
		'cat'		=> 1
	);
	$db->insert_query('audentio_products', $prod);
	
	$prod = array(
		'name'		=> 'MiniColor',
		'image'		=> 'http://www.audentio.com/uploads/products/minicolor-4_thumb.jpg',
		'title'		=> 'MiniColor',
		'price'		=> '20',
		'tagline'	=> 'Choose a color, any color',
		'link'		=> 'http://www.audentio.com/shop/view/MyBB/minicolor-4',
		'cat'		=> 1
	);
	$db->insert_query('audentio_products', $prod);
	
	$prod = array(
		'name'		=> 'Urbana',
		'image'		=> 'http://www.audentio.com/uploads/products/urbana-5_thumb.jpg',
		'title'		=> 'Urbana',
		'price'		=> '10',
		'tagline'	=> 'Emphasizing your content',
		'link'		=> 'http://www.audentio.com/shop/view/MyBB/urbana-5',
		'cat'		=> 1
	);
	$db->insert_query('audentio_products', $prod);
	
	$prod = array(
		'name'		=> 'Orange Crush',
		'image'		=> 'http://www.audentio.com/uploads/products/orange_crush-6_thumb.jpg',
		'title'		=> 'Orange Crush',
		'price'		=> '20',
		'tagline'	=> 'Orange Creamsicle Delicious',
		'link'		=> 'http://www.audentio.com/shop/view/MyBB/orange_crush-6',
		'cat'		=> 1
	);
	$db->insert_query('audentio_products', $prod);

	$prod = array(
		'name'		=> 'Luxure',
		'image'		=> 'http://www.audentio.com/uploads/products/luxure-7_thumb.jpg',
		'title'		=> 'Luxure',
		'price'		=> '25',
		'tagline'	=> 'Classy, Creative, &amp; Comfortable',
		'link'		=> 'http://www.audentio.com/shop/view/MyBB/luxure-7',
		'cat'		=> 1
	);
	$db->insert_query('audentio_products', $prod);
	
	$prod = array(
		'name'		=> 'Dark Series',
		'image'		=> 'http://www.audentio.com/uploads/products/dark_series-8_thumb.jpg',
		'title'		=> 'Dark Series',
		'price'		=> '25',
		'tagline'	=> 'Ice or Flame',
		'link'		=> 'http://www.audentio.com/shop/view/MyBB/dark_series-8',
		'cat'		=> 1
	);
	$db->insert_query('audentio_products', $prod);
	
	$prod = array(
		'name'		=> 'Peachy Keen',
		'image'		=> 'http://www.audentio.com/uploads/products/peachy_keen-9_thumb.jpg',
		'title'		=> 'Peachy Keen',
		'price'		=> '35',
		'tagline'	=> 'Home grown and delicious',
		'link'		=> 'http://www.audentio.com/shop/view/XenForo/peachy_keen-9',
		'cat'		=> 2
	);
	$db->insert_query('audentio_products', $prod);
	
	$prod = array(
		'name'		=> 'XenSplash',
		'image'		=> 'http://www.audentio.com/uploads/products/xensplash-10_thumb.jpg',
		'title'		=> 'XenSplash',
		'price'		=> '35',
		'tagline'	=> 'Dangerously thirst quenching',
		'link'		=> 'http://www.audentio.com/shop/view/XenForo/xensplash-10',
		'cat'		=> 2
	);
	$db->insert_query('audentio_products', $prod);
	
	$prod = array(
		'name'		=> 'Eternelle',
		'image'		=> 'http://www.audentio.com/uploads/products/eternelle-11_thumb.jpg',
		'title'		=> 'Eternelle',
		'price'		=> '35',
		'tagline'	=> 'Back to a happier time',
		'link'		=> 'http://www.audentio.com/shop/view/XenForo/eternelle-11',
		'cat'		=> 2
	);
	$db->insert_query('audentio_products', $prod);
	
	$prod = array(
		'name'		=> 'Class',
		'image'		=> 'http://www.audentio.com/uploads/products/class-12_thumb.jpg',
		'title'		=> 'Class',
		'price'		=> '45',
		'tagline'	=> 'Pure class, full control',
		'link'		=> 'http://www.audentio.com/shop/view/XenForo/class-12',
		'cat'		=> 2
	);
	$db->insert_query('audentio_products', $prod);
	
	$prod = array(
		'name'		=> 'Drift',
		'image'		=> 'http://www.audentio.com/uploads/products/drift-13_thumb.jpg',
		'title'		=> 'Drift',
		'price'		=> '45',
		'tagline'	=> 'Control, on Nitrous',
		'link'		=> 'http://www.audentio.com/shop/view/XenForo/drift-13',
		'cat'		=> 2
	);
	$db->insert_query('audentio_products', $prod);

	$prod = array(
		'name'		=> 'Citrus',
		'image'		=> 'http://www.audentio.com/uploads/products/citrus-14_thumb.jpg',
		'title'		=> 'Citrus',
		'price'		=> '20',
		'tagline'	=> 'A Squeeze of Sour',
		'link'		=> 'http://www.audentio.com/shop/view/MyBB/citrus-14',
		'cat'		=> 1
	);
	$db->insert_query('audentio_products', $prod);	
	
	$prod = array(
		'name'		=> 'Unova',
		'image'		=> 'http://www.audentio.com/uploads/products/unova-15_thumb.jpg',
		'title'		=> 'Unova',
		'price'		=> '25',
		'tagline'	=> 'A journey through color',
		'link'		=> 'http://www.audentio.com/shop/view/MyBB/unova-15',
		'cat'		=> 1
	);
	$db->insert_query('audentio_products', $prod);
	
	$prod = array(
		'name'		=> 'Quark',
		'image'		=> 'http://www.audentio.com/uploads/products/quark-16_thumb.jpg',
		'title'		=> 'Quark',
		'price'		=> '30',
		'tagline'	=> 'From the ashes',
		'link'		=> 'http://www.audentio.com/shop/view/XenForo/quark-16',
		'cat'		=> 2
	);
	$db->insert_query('audentio_products', $prod);
	
	$prod = array(
		'name'		=> 'NeonGlow',
		'image'		=> 'http://www.audentio.com/uploads/products/neonglow-17_thumb.jpg',
		'title'		=> 'NeonGlow',
		'price'		=> '20',
		'tagline'	=> 'Shine on',
		'link'		=> 'http://www.audentio.com/shop/view/MyBB/neonglow-17',
		'cat'		=> 1
	);
	$db->insert_query('audentio_products', $prod);	
	
	$prod = array(
		'name'		=> 'Go Series',
		'image'		=> 'http://www.audentio.com/uploads/products/goseries-18_thumb.jpg',
		'title'		=> 'Go Series',
		'price'		=> '20',
		'tagline'	=> 'Fantastically Simple',
		'link'		=> 'http://www.audentio.com/shop/view/MyBB/go_series-18',
		'cat'		=> 1
	);
	$db->insert_query('audentio_products', $prod);	
	
	
}

?>
