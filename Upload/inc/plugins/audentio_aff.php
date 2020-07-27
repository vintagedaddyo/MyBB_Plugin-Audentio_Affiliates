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

if(!defined("IN_MYBB"))
{
	die("This file cannot be accessed directly.");
}

function audentio_aff_info()
{
	return array(
		"name"			=> "Audentio Design Affiliated Themes Plugin",
		"description"	=> "Allows users to easily have an affiliated themes area on their forums.",
		"website"		=> "https://www.themehouse.com",
		"author"		=> "Audentio Design & updated by Vintagedaddyo",
		"authorsite"	=> "https://www.themehouse.com",
		"version"		=> "1.1",
		"guid" 			=> "da4e88fadace68c95e00bac787793ddd",
		"compatibility"	=> "18*"
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
		"cid INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL, ".
		"name VARCHAR(120), ".
		"items INT(5))
	");
		if($db->table_exists('audentio_products'))
	{
		$db->write_query("DROP TABLE ".TABLE_PREFIX."audentio_products_cats");
	}

	$db->write_query("CREATE TABLE ".TABLE_PREFIX."audentio_products (".
		"pid INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL, ".
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
	
	$query = $db->simple_select("settinggroups", "COUNT(*) as `rows`");
	
	$affrows = $db->fetch_field($query, "rows");
	
	$insertarray = array(
		'name' => 'aff_system', 
		'title' => 'Audentio Design Affiliate Plugin', 
		'description' => "Settings for the Audentio Design Affiliate Plugin", 
		'disporder' => $affrows+1, 
		'isdefault' => '0'
	);
	
	$gid = $db->insert_query("settinggroups", $insertarray);

	$setting = array(
		"sid"			=> '0',
		"name"			=> "aff_active",
		"title"			=> "Audentio Design Affiliate Plugin",
		"description"	=> "Is the affiliate system active?",
		"optionscode"	=> "yesno",
		"value"			=> '1',
		"disporder"		=> '1',
		"gid"			=> $gid
	);
	
	$db->insert_query("settings", $setting);
	
//		$setting = array(
//		"sid"			=> '0',
//		"name"			=> "aff_affid",
//		"title"			=> "Affiliate ID",
//		"description"	=> "What is your Audentio Design affiliate ID (same as your user ID, you can find it on your profile).<br />Learn more about our <a href=\"http://www.audentio.com/services/affiliate_program\" target=\"_blank\">affiliate program here</a>. Submit a ticket if you need assistance.",
//		"optionscode"	=> "text",
//		"value"			=> '',
//		"disporder"		=> '2',
//		"gid"			=> $gid
//	);
//	$db->insert_query("settings", $setting);
	
	$setting = array(
		"sid"			=> '0',
		"name"			=> "aff_cats_shown",
		"title"			=> "Shown Products",
		"description"	=> "Selects which products you would like to show. Comma seperated.<br />
		<br />
		0. Allow everything  <br />
		1. MyBB  <br />
		2. Xenforo-1  <br />
		3. Xenforo-2",
		"optionscode"	=> "text",
		"value"			=> '0',
		"disporder"		=> '3',
		"gid"			=> $gid
	);
	
	$db->insert_query("settings", $setting);
	
	$setting = array(
		"sid"			=> '0',
		"name"			=> "aff_page_name",
		"title"			=> "Affiliate Page File Name",
		"description"	=> "If you have changed the affiliates.php page <b><u>name</u></b> change it here as well. Include the .php extension.",
		"optionscode"	=> "text",
		"value"			=> "affiliates.php",
		"disporder"		=> '4',
		"gid"			=> $gid
	);
	
	$db->insert_query("settings", $setting);
	
	$setting = array(
		"sid"			=> '0',
		"name"			=> "aff_page_title",
		"title"			=> "Page Title",
		"description"	=> "What do you want the page title to be. Will also be reflected in the breadcrum navigation.",
		"optionscode"	=> "text",
		"value"			=> "Premium Forum Themes",
		"disporder"		=> '5',
		"gid"			=> $gid
	);
	
	$db->insert_query("settings", $setting);
	
	$setting = array(
		"sid"			=> '0',
		"name"			=> "aff_num_col",
		"title"			=> "Number of Columns",
		"description"	=> "Number of columns in the product table. We recommend 3.",
		"optionscode"	=> "text",
		"value"			=> '3',
		"disporder"		=> '6',
		"gid"			=> $gid
	);
	
	$db->insert_query("settings", $setting);
	
	$setting = array(
		"sid"			=> '0',
		"name"			=> "aff_page_limit",
		"title"			=> "Items Per Page",
		"description"	=> "Number of products to show per page. We recommend it be evenly divisibly by the number of columns.",
		"optionscode"	=> "text",
		"value"			=> '12',
		"disporder"		=> '7',
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
<!--		<a href="http://www.themehouse.com/discount/{$mybb->settings[\'aff_affid\']}" target="_blank">Visit ThemeHouse</a> -->
    <a href="http://www.themehouse.com" target="_blank">Visit ThemeHouse</a>
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
		if($db->table_exists('audentio_products')) {
		return true;
	    }
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
	
 // cat 1	
	
	$cat = array(
	'name'	=> 'MyBB',
	);

	$db->insert_query('audentio_products_cats', $cat);

 // cat 2
 	
	$cat = array(
	'name'	=> 'Xenforo-1',
	);

	$db->insert_query('audentio_products_cats', $cat);	
	
	// cat 3
	
	$cat = array(
	'name'	=> 'Xenforo-2',
	);

	$db->insert_query('audentio_products_cats', $cat);		
		
// cat 1
	
	$prod = array(
		'name'		=> 'Glowing',
		'image'		=> 'https://media.audent.io/product/hero_image/1/glowing-1_display.png',
		'title'		=> 'Glowing',
		'price'		=> '0',
		'tagline'	=> '<img src="images/nav_bit.png"/> MyBB Theme',
		'link'		=> 'https://www.themehouse.com/mybb/themes/glowing',
		'cat'		=>  '1'
	);

	$db->insert_query('audentio_products', $prod);	
	
	$prod = array(
		'name'		=> 'Sky',
		'image'		=> 'https://media.audent.io/product/hero_image/2/sky-2_display.jpg',
		'title'		=> 'Sky',
		'price'		=> '0',
		'tagline'	=> '<img src="images/nav_bit.png"/> MyBB Theme',
		'link'		=> 'https://www.themehouse.com/mybb/themes/sky',
		'cat'		=>  '1'
	);

	$db->insert_query('audentio_products', $prod);
	
	$prod = array(
		'name'		=> 'Gaming Jam',
		'image'		=> 'https://media.audent.io/product/hero_image/3/gaming_jam-3_display.png',
		'title'		=> 'Gaming Jam',
		'price'		=> '0',
		'tagline'	=> '<img src="images/nav_bit.png"/> MyBB Theme',
		'link'		=> 'https://www.themehouse.com/mybb/themes/gaming-jam',
		'cat'		=>  '1'
	);

	$db->insert_query('audentio_products', $prod);
	
	$prod = array(
		'name'		=> 'Necro',
		'image'		=> 'https://media.audent.io/product/hero_image/33/necro-33_display.png',
		'title'		=> 'Necro',
		'price'		=> '0',
		'tagline'	=> '<img src="images/nav_bit.png"/> MyBB Theme',
		'link'		=> 'https://www.themehouse.com/mybb/themes/necro',
		'cat'		=>  '1'
	);

	$db->insert_query('audentio_products', $prod);
	
	$prod = array(
		'name'		=> 'Dazzle',
		'image'		=> 'https://media.audent.io/product/hero_image/22/dazzle-22_display.jpg',
		'title'		=> 'Dazzle',
		'price'		=> '0',
		'tagline'	=> '<img src="images/nav_bit.png"/> MyBB Theme',
		'link'		=> 'https://www.themehouse.com/mybb/themes/dazzle',
		'cat'		=>  '1'
	);

	$db->insert_query('audentio_products', $prod);
	
	$prod = array(
		'name'		=> 'Citrus',
		'image'		=> 'https://media.audent.io/product/hero_image/14/citrus-14_display.jpg',
		'title'		=> 'Citrus',
		'price'		=> '0',
		'tagline'	=> '<img src="images/nav_bit.png"/> MyBB Theme',
		'link'		=> 'https://www.themehouse.com/mybb/themes/citrus',
		'cat'		=>  '1'
	);

	$db->insert_query('audentio_products', $prod);

	$prod = array(
		'name'		=> 'Luxure',
		'image'		=> 'https://media.audent.io/product/hero_image/7/luxure-7_display.jpg',
		'title'		=> 'Luxure',
		'price'		=> '0',
		'tagline'	=> '<img src="images/nav_bit.png"/> MyBB Theme',
		'link'		=> 'https://www.themehouse.com/mybb/themes/luxure',
		'cat'		=>  '1'
	);

	$db->insert_query('audentio_products', $prod);
	
	$prod = array(
		'name'		=> 'Dark Series',
		'image'		=> 'https://media.audent.io/product/hero_image/8/dark_series-8_display.png',
		'title'		=> 'Dark Series',
		'price'		=> '0',
		'tagline'	=> '<img src="images/nav_bit.png"/> MyBB Theme',
		'link'		=> 'https://www.themehouse.com/mybb/themes/dark-series',
		'cat'		=>  '1'
	);

	$db->insert_query('audentio_products', $prod);
	
	$prod = array(
		'name'		=> 'Orange Crush',
		'image'		=> 'https://media.audent.io/product/hero_image/6/orange_crush-6_display.jpg',
		'title'		=> 'Orange Crush',
		'price'		=> '0',
		'tagline'	=> '<img src="images/nav_bit.png"/> MyBB Theme',
		'link'		=> 'https://www.themehouse.com/mybb/themes/orange-crush',
		'cat'		=>  '1'
	);

	$db->insert_query('audentio_products', $prod);	
	
	$prod = array(
		'name'		=> 'Unova',
		'image'		=> 'https://media.audent.io/product/hero_image/15/unova-15_display.jpg',
		'title'		=> 'Unova',
		'price'		=> '0',
		'tagline'	=> '<img src="images/nav_bit.png"/> MyBB Theme',
		'link'		=> 'https://www.themehouse.com/mybb/themes/unova',
		'cat'		=>  '1'
	);

	$db->insert_query('audentio_products', $prod);
	
	$prod = array(
		'name'		=> 'NeonGlow',
		'image'		=> 'https://media.audent.io/product/hero_image/17/neonglow-17_display.png',
		'title'		=> 'NeonGlow',
		'price'		=> '0',
		'tagline'	=> '<img src="images/nav_bit.png"/> MyBB Theme',
		'link'		=> 'https://www.themehouse.com/mybb/themes/neonglow',
		'cat'		=>  '1'
	);

	$db->insert_query('audentio_products', $prod);	
	
	$prod = array(
		'name'		=> 'Go Series',
		'image'		=> 'https://media.audent.io/product/hero_image/18/goseries-18_display.jpg',
		'title'		=> 'Go Series',
		'price'		=> '0',
		'tagline'	=> '<img src="images/nav_bit.png"/> MyBB Theme',
		'link'		=> 'https://www.themehouse.com/mybb/themes/go-series',
		'cat'		=>  '1'
	);

	$db->insert_query('audentio_products', $prod);		
	
	
// cat 2
	
	$prod = array(
		'name'		=> 'Peachy Keen',
		'image'		=> 'https://media.audent.io/product/hero_image/9/Peachy_Keen_Device_Mock_Up.png',
		'title'		=> 'Peachy Keen',
		'price'		=> '99',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 1 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/1/themes/peachy-keen',
		'cat'		=>  '2'
	);

	$db->insert_query('audentio_products', $prod);
	
	$prod = array(
		'name'		=> 'XenSplash',
		'image'		=> 'https://media.audent.io/product/hero_image/10/xenSplash_Device_Mock_Up.png',
		'title'		=> 'XenSplash',
		'price'		=> '99',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 1 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/1/themes/xensplash',
		'cat'		=>  '2'
	);

	$db->insert_query('audentio_products', $prod);
	
	$prod = array(
		'name'		=> 'UI.X',
		'image'		=> 'https://media.audent.io/product/hero_image/23/UI.X_Device_Mock_Up.png',
		'title'		=> 'UI.X',
		'price'		=> '99',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 1 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/1/themes/ui-x',
		'cat'		=>  '2'
	);

	$db->insert_query('audentio_products', $prod);
	
	$prod = array(
		'name'		=> 'UI.X Dark',
		'image'		=> 'https://media.audent.io/product/hero_image/28/UI.X_Dark_Device_Mock_Up.png',
		'title'		=> 'UI.X Dark',
		'price'		=> '99',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 1 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/1/themes/ui-x-dark',
		'cat'		=>  '2'
	);

	$db->insert_query('audentio_products', $prod);	
	
	$prod = array(
		'name'		=> 'Class',
		'image'		=> 'https://media.audent.io/product/hero_image/12/Class_Device_Mock_Up.png',
		'title'		=> 'Class',
		'price'		=> '99',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 1 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/1/themes/class',
		'cat'		=>  '2'
	);

	$db->insert_query('audentio_products', $prod);
	
	$prod = array(
		'name'		=> 'Drift',
		'image'		=> 'https://media.audent.io/product/hero_image/13/Drift_Device_Mock_Up.png',
		'title'		=> 'Drift',
		'price'		=> '99',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 1 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/1/themes/drift',
		'cat'		=>  '2'
	);

	$db->insert_query('audentio_products', $prod);
	
	$prod = array(
		'name'		=> 'Drift',
		'image'		=> 'https://media.audent.io/product/hero_image/26/Drift_Dark_Device_Mock_Up.png',
		'title'		=> 'Drift',
		'price'		=> '99',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 1 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/1/themes/drift-dark',
		'cat'		=>  '2'
	);

	$db->insert_query('audentio_products', $prod);	
	
	$prod = array(
		'name'		=> 'Quark',
		'image'		=> 'https://media.audent.io/product/hero_image/16/Quark_Device_Mock_Up.png',
		'title'		=> 'Quark',
		'price'		=> '99',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 1 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/1/themes/quark',
		'cat'		=>  '2'
	);

	$db->insert_query('audentio_products', $prod);	

	$prod = array(
		'name'		=> 'Antiquark',
		'image'		=> 'https://media.audent.io/product/hero_image/27/Antiquark_Device_Mock_Up.png',
		'title'		=> 'Antiquark',
		'price'		=> '99',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 1 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/1/themes/antiquark',
		'cat'		=>  '2'
	);

	$db->insert_query('audentio_products', $prod);	
	
	$prod = array(
		'name'		=> 'Rekt',
		'image'		=> 'https://media.audent.io/product/hero_image/41/Rekt_Device_Mock_Up.png',
		'title'		=> 'Rekt',
		'price'		=> '99',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 1 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/1/themes/rekt',
		'cat'		=>  '2'
	);

	$db->insert_query('audentio_products', $prod);	
		
	$prod = array(
		'name'		=> 'Tactical',
		'image'		=> 'https://media.audent.io/product/hero_image/39/Tactical_Device_Mock_Up.png',
		'title'		=> 'Tactical',
		'price'		=> '99',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 1 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/1/themes/tactical',
		'cat'		=>  '2'
	);

	$db->insert_query('audentio_products', $prod);	
	
	$prod = array(
		'name'		=> 'Rogue',
		'image'		=> 'https://media.audent.io/product/hero_image/36/Rogue_Device_Mock_Up.png',
		'title'		=> 'Rogue',
		'price'		=> '99',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 1 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/1/themes/rogue',
		'cat'		=>  '2'
	);

	$db->insert_query('audentio_products', $prod);	

	$prod = array(
		'name'		=> 'Reneue',
		'image'		=> 'https://media.audent.io/product/hero_image/34/Reneue_Device_Mock_Up.png',
		'title'		=> 'Reneue',
		'price'		=> '99',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 1 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/1/themes/reneue',
		'cat'		=>  '2'
	);

	$db->insert_query('audentio_products', $prod);	
	
	$prod = array(
		'name'		=> 'xenBlock',
		'image'		=> 'https://media.audent.io/product/hero_image/20/xenblock_Device_Mock_Up.png',
		'title'		=> 'xenBlock',
		'price'		=> '99',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 1 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/1/themes/xenblock',
		'cat'		=>  '2'
	);

	$db->insert_query('audentio_products', $prod);		
	
	$prod = array(
		'name'		=> 'Material',
		'image'		=> 'https://media.audent.io/product/hero_image/152/material_heroimage-min.png',
		'title'		=> 'Material',
		'price'		=> '99',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 1 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/1/themes/material',
		'cat'		=>  '2'
	);

	$db->insert_query('audentio_products', $prod);			
	
	$prod = array(
		'name'		=> 'Abyss',
		'image'		=> 'https://media.audent.io/product/hero_image/57/abyss_device_2.png',
		'title'		=> 'Abyss',
		'price'		=> '99',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 1 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/1/themes/abyss',
		'cat'		=>  '2'
	);

	$db->insert_query('audentio_products', $prod);				

	$prod = array(
		'name'		=> 'Xenith',
		'image'		=> 'https://media.audent.io/product/hero_image/52/Xenith_Device_Mock_Up.png',
		'title'		=> 'Xenith',
		'price'		=> '99',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 1 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/1/themes/xenith',
		'cat'		=>  '2'
	);

	$db->insert_query('audentio_products', $prod);				
	
	$prod = array(
		'name'		=> 'Scratch',
		'image'		=> 'https://media.audent.io/product/hero_image/35/Scratch_Device_Mock_Up.png',
		'title'		=> 'Scratch',
		'price'		=> '99',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 1 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/1/themes/scratch',
		'cat'		=>  '2'
	);

	$db->insert_query('audentio_products', $prod);				

	$prod = array(
		'name'		=> 'Intrepid',
		'image'		=> 'https://media.audent.io/product/hero_image/32/Intrepid_Device_Mock_Up.png',
		'title'		=> 'Intrepid',
		'price'		=> '99',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 1 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/1/themes/intrepid',
		'cat'		=>  '2'
	);

	$db->insert_query('audentio_products', $prod);				

	$prod = array(
		'name'		=> 'Tacctical Light',
		'image'		=> 'https://media.audent.io/product/hero_image/40/Tactical_Light_Device_Mock_Up.png',
		'title'		=> 'Tactical Light',
		'price'		=> '99',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 1 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/1/themes/tactical-light',
		'cat'		=>  '2'
	);

	$db->insert_query('audentio_products', $prod);				
	
	$prod = array(
		'name'		=> 'UI.Flex',
		'image'		=> 'https://media.audent.io/product/hero_image/37/UI.Flex_Device_Mock_Up.png',
		'title'		=> 'UI.Flex',
		'price'		=> '99',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 1 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/1/themes/ui-flex',
		'cat'		=>  '2'
	);

	$db->insert_query('audentio_products', $prod);					

	$prod = array(
		'name'		=> 'Intrinsic',
		'image'		=> 'https://media.audent.io/product/hero_image/31/Intrinsic_Device_Mock_Up.png',
		'title'		=> 'Intrinsic',
		'price'		=> '99',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 1 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/1/themes/intrinsic',
		'cat'		=>  '2'
	);

	$db->insert_query('audentio_products', $prod);					
	
	$prod = array(
		'name'		=> 'Corp',
		'image'		=> 'https://media.audent.io/product/hero_image/61/Corp_Device_Mock_Up.png',
		'title'		=> 'Corp',
		'price'		=> '99',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 1 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/1/themes/corp',
		'cat'		=>  '2'
	);

	$db->insert_query('audentio_products', $prod);							

	$prod = array(
		'name'		=> 'UI.X Halloween',
		'image'		=> 'https://media.audent.io/product/hero_image/24/UI.X_Halloween_Device_Mock_Up.png',
		'title'		=> 'UI.X Halloween',
		'price'		=> '99',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 1 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/1/themes/ui-x-halloween',
		'cat'		=>  '2'
	);

	$db->insert_query('audentio_products', $prod);		
	
	$prod = array(
		'name'		=> 'UI.Flex Dark',
		'image'		=> 'https://media.audent.io/product/hero_image/38/UI.Flex_Dark_Device_Mock_Up.png',
		'title'		=> 'UI.Flex Dark',
		'price'		=> '99',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 1 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/1/themes/ui-flex-dark',
		'cat'		=>  '2'
	);

	$db->insert_query('audentio_products', $prod);	
	
	$prod = array(
		'name'		=> 'Bliss',
		'image'		=> 'https://media.audent.io/product/hero_image/62/Bliss_Device_Mock_Up.png',
		'title'		=> 'Bliss',
		'price'		=> '99',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 1 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/1/themes/bliss',
		'cat'		=>  '2'
	);

	$db->insert_query('audentio_products', $prod);			

	$prod = array(
		'name'		=> 'Proximity',
		'image'		=> 'https://media.audent.io/product/hero_image/42/Proximity_Device_Mock_Up.png',
		'title'		=> 'Proximity',
		'price'		=> '99',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 1 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/1/themes/proximity',
		'cat'		=>  '2'
	);

	$db->insert_query('audentio_products', $prod);	
	
	$prod = array(
		'name'		=> 'Crumble',
		'image'		=> 'https://media.audent.io/product/hero_image/60/Crumble_Device_Mock_Up.png',
		'title'		=> 'Crumble',
		'price'		=> '99',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 1 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/1/themes/crumble',
		'cat'		=>  '2'
	);

	$db->insert_query('audentio_products', $prod);		
							
// cat 3	

	$prod = array(
		'name'		=> 'UI.X 2',
		'image'		=> 'https://media.audent.io/product/hero_image/210/hero_image.png',
		'title'		=> 'UI.X 2',
		'price'		=> '35',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 2 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/2/themes/ui-x',
		'cat'		=>  '3'
	);
		
        $db->insert_query('audentio_products', $prod);
			
	$prod = array(
		'name'		=> 'UI.X 2 Dark',
		'image'		=> 'https://media.audent.io/product/hero_image/218/uix-dark-hero-image2.png',
		'title'		=> 'UI.X 2 Dark',
		'price'		=> '35',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 2 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/2/themes/ui-x-dark',
		'cat'		=>  '3'
	);
		
        $db->insert_query('audentio_products', $prod);

	$prod = array(
		'name'		=> 'Intrepid',
		'image'		=> 'https://media.audent.io/product/hero_image/222/intrepid-hero-image.png',
		'title'		=> 'Intrepid',
		'price'		=> '35',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 2 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/2/themes/intrepid',
		'cat'		=>  '3'
	);
		
        $db->insert_query('audentio_products', $prod);			

	$prod = array(
		'name'		=> 'Xenith',
		'image'		=> 'https://media.audent.io/product/hero_image/225/xenith-hero-image.png',
		'title'		=> 'Xenith',
		'price'		=> '35',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 2 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/2/themes/xenith',
		'cat'		=>  '3'
	);
		
        $db->insert_query('audentio_products', $prod);

	$prod = array(
		'name'		=> 'Abyss',
		'image'		=> 'https://media.audent.io/product/hero_image/226/abyss-hero-image.png',
		'title'		=> 'Abyss',
		'price'		=> '35',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 2 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/2/themes/abyss',
		'cat'		=>  '3'
	);
		
        $db->insert_query('audentio_products', $prod);

	$prod = array(
		'name'		=> 'Drift',
		'image'		=> 'https://media.audent.io/product/hero_image/229/drift-hero-image.png',
		'title'		=> 'Drift',
		'price'		=> '35',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 2 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/2/themes/drift',
		'cat'		=>  '3'
	);
		
        $db->insert_query('audentio_products', $prod);

	$prod = array(
		'name'		=> 'Drift Dark',
		'image'		=> 'https://media.audent.io/product/hero_image/230/drift-dark-hero-image.png',
		'title'		=> 'Drift Dark',
		'price'		=> '35',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 2 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/2/themes/drift-dark',
		'cat'		=>  '3'
	);
		
        $db->insert_query('audentio_products', $prod);

	$prod = array(
		'name'		=> 'iO',
		'image'		=> 'https://media.audent.io/product/hero_image/237/io-hero-image.png',
		'title'		=> 'iO',
		'price'		=> '35',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 2 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/2/themes/io',
		'cat'		=>  '3'
	);
		
        $db->insert_query('audentio_products', $prod);

	$prod = array(
		'name'		=> 'iO Dark Mode',
		'image'		=> 'https://media.audent.io/product/hero_image/238/io-dark-hero-image.png',
		'title'		=> 'iO Dark Mode',
		'price'		=> '35',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 2 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/2/themes/io-dark-mode',
		'cat'		=>  '3'
	);
		
        $db->insert_query('audentio_products', $prod);

	$prod = array(
		'name'		=> 'Class',
		'image'		=> 'https://media.audent.io/product/hero_image/239/class-hero-image.png',
		'title'		=> 'Class',
		'price'		=> '35',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 2 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/2/themes/class',
		'cat'		=>  '3'
	);
		
         $db->insert_query('audentio_products', $prod);				

	 $prod = array(
		'name'		=> 'UI.X Classic',
		'image'		=> 'https://media.audent.io/product/hero_image/240/classic-hero-image.png',
		'title'		=> 'UI.X Classic',
		'price'		=> '35',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 2 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/2/themes/uix-classic',
		'cat'		=>  '3'
	);
		
         $db->insert_query('audentio_products', $prod);	

	 $prod = array(
		'name'		=> 'UI.X Classic Dark',
		'image'		=> 'https://media.audent.io/product/hero_image/242/classic-dark-hero-image.png',
		'title'		=> 'UI.X Classic Dark',
		'price'		=> '35',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 2 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/2/themes/uix-classic-dark',
		'cat'		=>  '3'
	);
		
         $db->insert_query('audentio_products', $prod);	

	 $prod = array(
		'name'		=> '#Rekt',
		'image'		=> 'https://media.audent.io/product/hero_image/263/rekt-hero-image.png',
		'title'		=> '#Rekt',
		'price'		=> '35',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 2 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/2/themes/rekt',
		'cat'		=>  '3'
	);
		
         $db->insert_query('audentio_products', $prod);		

       	 $prod = array(
		'name'		=> 'Westlake',
		'image'		=> 'https://media.audent.io/product/hero_image/264/westlake-hero-image.png',
		'title'		=> 'Westlake',
		'price'		=> '35',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 2 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/2/themes/westlake',
		'cat'		=>  '3'
	);
		
          $db->insert_query('audentio_products', $prod);	

	  $prod = array(
		'name'		=> 'Prisma',
		'image'		=> 'https://media.audent.io/product/hero_image/265/prisma-hero-image.png',
		'title'		=> 'Prisma',
		'price'		=> '35',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 2 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/2/themes/prisma',
		'cat'		=>  '3'
	);
		
          $db->insert_query('audentio_products', $prod);	

	 $prod = array(
		'name'		=> 'BLOK',
		'image'		=> 'https://media.audent.io/product/hero_image/273/BLOK_light_hero_TH.png',
		'title'		=> 'BLOK',
		'price'		=> '35',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 2 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/2/themes/blok',
		'cat'		=>  '3'
	);
		
         $db->insert_query('audentio_products', $prod);	

	 $prod = array(
		'name'		=> 'BLOK Dark',
		'image'		=> 'https://media.audent.io/product/hero_image/274/BLOK_dark_hero_TH_2x.png',
		'title'		=> 'BLOK Dark',
		'price'		=> '35',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 2 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/2/themes/blok-dark',
		'cat'		=>  '3'
	);
		
         $db->insert_query('audentio_products', $prod);	

	 $prod = array(
		'name'		=> 'Boo!',
		'image'		=> 'https://media.audent.io/product/hero_image/275/boo-th-hero.png',
		'title'		=> 'Boo!',
		'price'		=> '35',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 2 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/2/themes/boo',
		'cat'		=>  '3'
	);
		
         $db->insert_query('audentio_products', $prod);	

	 $prod = array(
		'name'		=> 'Thankful',
		'image'		=> 'https://media.audent.io/product/hero_image/276/thankful_hero_2x.png',
		'title'		=> 'Thankful',
		'price'		=> '35',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 2 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/2/themes/thankful',
		'cat'		=>  '3'
	);
		
        $db->insert_query('audentio_products', $prod);	

 	$prod = array(
		'name'		=> 'Legend',
		'image'		=> 'https://media.audent.io/product/hero_image/277/legend-th-hero_2x.png',
		'title'		=> 'Legend',
		'price'		=> '35',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 2 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/2/themes/legend',
		'cat'		=>  '3'
	);
		
         $db->insert_query('audentio_products', $prod);	

	 $prod = array(
		'name'		=> 'Gift',
		'image'		=> 'https://media.audent.io/product/hero_image/278/gift-th-hero_2x.png',
		'title'		=> 'Gift',
		'price'		=> '35',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 2 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/2/themes/gift',
		'cat'		=>  '3'
	);
		
         $db->insert_query('audentio_products', $prod);	

	 $prod = array(
		'name'		=> 'Tactical',
		'image'		=> 'https://media.audent.io/product/hero_image/221/tactical-hero-image-updated.png',
		'title'		=> 'Tactical',
		'price'		=> '35',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 2 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/2/themes/tactical',
		'cat'		=>  '3'
	);
		
        $db->insert_query('audentio_products', $prod);	

	$prod = array(
		'name'		=> 'Flare',
		'image'		=> 'https://media.audent.io/product/hero_image/233/flare-hero-image.png',
		'title'		=> 'Flare',
		'price'		=> '35',
		'tagline'	=> '<img src="images/nav_bit.png"/> Xenforo 2 Theme',
		'link'		=> 'https://www.themehouse.com/xenforo/2/themes/flare',
		'cat'		=>  '3'
	);
		
         $db->insert_query('audentio_products', $prod);	

}

?>
