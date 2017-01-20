#!/usr/bin/env drush
<?php

// READ CLI Options
$menu_name 		= drush_get_option('menu');
$destination 	= drush_get_option('destination');
$show_hidden 	= drush_get_option('hidden');
$indent_size 	= drush_get_option('indent');
$help					= drush_get_option('help');

if(!$show_hidden) {
	$show_hidden = 0;
}

if(!$indent_size) {
	$indent_size = 4;
}

drush_print("Show Disabled: " . $show_hidden);
drush_print("Child Indent Size: " . $indent_size);

if($menu_name && $destination && !$help) {
	$menu_tree = menu_tree_all_data($menu_name);
	$menu_struct = build_menu_struct($menu_tree, $show_hidden, $indent_size);
	make_csv($menu_struct, $destination);
	drush_print("File Written to: " . $destination);
	drush_print("Complete!");
	drush_print("");
}else{
	usage();
}


function build_menu_struct($menu, $hidden=0, $child_indent=2) {
	//print_r(array_keys($menu));
	$data = array();
	foreach($menu as $key => $item){
		if($hidden == 1 || $item['link']['hidden'] == 0){
			$depth = $item['link']['depth'];
			$title = str_repeat(" ", ($depth-1) * $child_indent) . $item['link']['title'];
			$path  = drupal_get_path_alias($item['link']['link_path']);

			if($item['link']['external'] == 0){
				$path = 'http://www.lib.rowan.edu/' . $path;
			}

			$row = array('Title' => $title, 'URL' => $path);

			if($hidden == 1) {
				$row['Status'] = ($item['link']['hidden'] == 0) ? "Enabled" : "Disabled";
			}

			$data[] = $row;

			if($item['link']['has_children'] == 1 ) {
				$tree = $item['below'];
				$children = build_menu_struct($tree, $hidden, $child_indent);
				$data = array_merge($data, $children);
			}
		}
	}

	return $data;
}

function make_csv($data, $destination){
	$output = "";
	if(count($data) > 0){
		$keys = array_keys($data[0]);
		
		$output .= getHeaders($keys);

		foreach($data as $line){
			$output .= getLine($keys, $line);
		}
	}

	file_put_contents($destination, $output);
}

function getHeaders($keys) {
	$output = '';
	foreach($keys as $key){
		$output .= (empty($output)) ? "\"$key\"" : "|\"$key\"";
	}

	$output .= "\n";

	return $output;
}

function getLine($keys, $data){
	$output = "";

	foreach($keys as $key){
		$output .= (empty($output)) ? "\"{$data[$key]}\"" : "|\"{$data[$key]}\"";
	}

	$output .= "\n";

	return $output;
}


function usage() {
	drush_print("GetMenuStruct.drush @site-alias --menu=[menu-name] --destination=[path to outputfile]");
	drush_print("");
	drush_print("Optional Arguments:");
	drush_print("--hidden\t\tShow disabled menu items");
	drush_print("--indent=[number]\t\tSet Indentation Size for child menu items, default=4");
}
?>

