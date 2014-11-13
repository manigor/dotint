<?php
/**
 * Created by JetBrains PhpStorm.
 * User: stig
 * Date: 03.06.13
 * Time: 14:15
 * To change this template use File | Settings | File Templates.
 */
global $dPconfig;
$mysqliLink = new mysqli($dPconfig['dbhost'],$dPconfig['dbuser'],$dPconfig['dbpass'],$dPconfig['dbname']);
mysqli_set_charset($mysqliLink, "utf8");

function my_query ($sql){
	global $mysqliLink;
	$res = mysqli_query($mysqliLink,$sql);
	return $res;
}

function my_num_rows($res){
	$resc = mysqli_num_rows($res);
	return $resc;
}

function my_free_result($res){
	mysqli_free_result($res);
}

function my_fetch_assoc($res){
	$final = mysqli_fetch_assoc($res);
	return $final;
}

function my_fetch_result($res){
	$final = mysqli_fetch_array($res);
	return $final[0];
}

function my_fetch_all(&$res,$rType = MYSQLI_BOTH){
	$final = array();
	if ($res && my_num_rows($res) > 0){
		while($row = my_fetch_assoc($res,$rType)){
			$final[] = $row;
		}
	}
	mysqli_free_result($res);
	return $final;
}

function my_fetch_object($res){
	$final = mysqli_fetch_object($res);
	return $final;
}

function my_fetch_array($res, $artype = MYSQLI_BOTH){
	$final = mysqli_fetch_array($res, $artype);
	return $final;
}

function my_fetch_row($res){
	$final = mysqli_fetch_row($res);
	return $final;
}

function my_real_escape_string($res){
	global $mysqliLink;
	$final = mysqli_real_escape_string($mysqliLink, $res);
	return $final;
}

function my_affected_rows($res){
	global $mysqliLink;
	$final = mysqli_affected_rows($mysqliLink);
	return $final;
}

function my_insert_id(){
	global $mysqliLink;
	$final = mysqli_insert_id($mysqliLink);
	return $final;
}

function my_list_tables($dbname){
	global $mysqliLink;
	$res = my_query('show tables from '.$dbname);
	return $res;
}

function my_error(){
	global $mysqliLink;
	$res = mysqli_error($mysqliLink);
	return $res;
}
