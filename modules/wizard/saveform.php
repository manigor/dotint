<?php
/**
 * Created by JetBrains PhpStorm.
 * User: stig
 * Date: 10.04.11
 * Time: 15:04
 */

global $AppUI,$fileSels,$baseDir,$newd;

function dataEntry(&$fld,$name,$uid){
	global $fileSels;
	$code='"'.$name.'" => ';
	$vname=$uid.'.'.$fld['name'];
	if($fld['type'] === 'date' || $fld['type'] === 'entry_date'){
		$code.='array("title"=>"'.$vname.'","xtype"=>"date")';
	}elseif($fld['sysv'] != '' &&  !strstr($fld['sysv'],'Sys')){
		$code.='array("title"=>"'.$vname.'","value"=>"sysval","query"=>"'.$fld['sysv'].'"'.(($fld['type'] === 'select_multi' || $fld['type'] === "checkbox") ? ',"mode"=>"multi"' : '' ).')';
	}elseif($fld['type'] === 'select' && strstr($fld['sysv'],'Sys')){
		$list=array(
			'SysStaff'=>array('n'=>'staffName','i'=>'staffId','s'=>'select contact_id as id, CONCAT_WS(" ",contact_first_name,contact_last_name) as name from contacts  where contact_id<>"13" and contact_active="1" order by name asc'),
			'SysClients'=>array('n'=>'clientName','i'=>'clientId','s'=> 'select client_id as id, CONCAT_WS(" ",client_first_name,client_last_name) as name from clients  order by name asc'),
			'SysCenters'=>array('n'=>'clinicName','i'=>'clinicId','s'=>'select clinic_id as id,clinic_name as name from clinics order by name asc'),
			'SysLocations'=>array('n'=>'locationName','i'=>'locationId','s'=>'select clinic_location_id as id, clinic_location as name from clinic_location order by name asc '),
			'SysPositions'=>array('n'=>'positionName','i'=>'positionId','s'=>'select id , title as name from positions order by name asc '),
		);
		$lcrit=$fld['sysv'];
		$fileSels[]=$code."'".$list[$lcrit]['s']."'";
		$code.="array('title'=>'".$vname."','value'=>'preSQL','query'=>'".$list[$lcrit]['n']."','rquery'=>'".$list[$lcrit]['i']."'
				".(($fld['smult'] === true ) ? ',"mode"=>"multi"' : '' ).")";
	}else{
		$code.='"'.$vname.'"';
	}
	return $code;
}

if($_POST['formName'] != ''){
	$eform=(int)$_POST['form_id'];
	$presql=array();
	//need to create/save new form
	$fname=my_real_escape_string(trim($_POST['formName']));
	$plainFields=json_decode(stripslashes($_POST['formsum']),true);
	$registry=(int)$_POST['regForm'];

	$fileData=array();

	$fileSels = array();

	$subTables=array();

	$typeSQL=array(
	/*'clients' => ' varchar(100)  DEFAULT NULL',
	'centers'=> ' varchar(100)  DEFAULT NULL',
	'staff'=> ' varchar(100)  DEFAULT NULL',*/
	'date' => 'date DEFAULT NULL',
	'entry_date'=>'date DEFAULT NULL',
	'time' => 'varchar(10) DEFAULT NULL',
	'datetime'=>'datetime DEFAULT NULL',
	'plain'=>' varchar(100)  DEFAULT NULL',
	'bigText'=>' text default NULL',
	'select'=>'varchar(100)  DEFAULT NULL',
	'select-multi'=>'varchar(100)  DEFAULT NULL',
	'radio'=>'int(11) unsigned DEFAULT NULL',
	'checkbox'=>'varchar(100)  DEFAULT NULL',
	'note'=>' text default NULL',
	'numeric' => 'varchar(100)  DEFAULT NULL',
	'positive' => 'varchar(100)  DEFAULT NULL',
	'range'  => 'varchar(100)  DEFAULT NULL'
	);
	$bcsql='CREATE TABLE IF NOT EXISTS `wform_#@ID@#` ';
	$sqlappix=',
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=latin1';

	if($eform > 0){
		$presql[]='DROP TABLE IF EXISTS `wform_'.$eform.'`';
		$sql='select subs from form_master where id ="'.$eform.'"';
		$res=my_query($sql);
		if($res && my_num_rows($res) > 0){
			$rsb = my_fetch_array($res);
			$pts=explode(',',$rsb[0]);
			foreach($pts as $ps){
				$presql[]='DROP TABLE IF EXISTS `'.$ps.'`';
			}
		}
	}
	$sfids=array(
		'id int(11) unsigned NOT NULL AUTO_INCREMENT',
		/*TODO uncomment for non-MOA usage */
		//'client_id int(11) unsigned NOT NULL',
		'entry_date DATE NOT NULL'
	);
	$rcn=0;
	$digests=array();
	$subCnt=0;
	foreach($plainFields as $pid => &$pfdata){
		if($pfdata ){
			if($pfdata['type'] !== 'entry_date') {

				if(isset($pfdata['subs']) && is_array($pfdata['subs']) && count($pfdata['subs']) > 0){
					$subrcn=0;
					$lpos=false;
					foreach ($pfdata['subs'] as $sbid => &$spfdata) {
						if($pfdata['otm'] === false || $registry === 1){
							$sfids[]='`fld_'.$rcn.'` '.$typeSQL[$spfdata['type']];
							if($spfdata['dgst'] === true){
								$digests[]='fld_'.$rcn;
							}
							$spfdata['dbfld']='fld_'.$rcn;
							$fileData[]=dataEntry($spfdata,'fld_'.$rcn,++$rcn);
						}elseif($pfdata['otm'] === true && $registry === 0){

							if($lpos === false){
								$lpos=count($fileData);
								$pfdata['dbfld']='fld_'.$rcn.'_subs';
								$pfdata['dbsub']=$subCnt;
								++$subCnt;
							}
							if(!is_array($subTables[$pid])){
								$subTables[$pid]=array('sql'=>array(
								'id int(11) unsigned NOT NULL AUTO_INCREMENT',
								//'client_id int(11) unsigned NOT NULL',
								'wf_id int(11) unsigned NOT NULL'
								),
								'name'=>'wf_#@ID@#_sub_'.$pid,
								'title'=>$pfdata['name'],
								'fdid'=>$lpos,
								'list'=>array(),
								'dates'=>array()
								);
							}
							$subTables[$pid]['sql'][]='`fld_'.$subrcn.'` '.$typeSQL[$spfdata['type']];
							if($spfdata['type'] == 'date'){
								$subTables[$pid]['dates'][]='fld_'.$subrcn;
							}
							$subTables[$pid]['list'][]='fld_'.$subrcn;
							if(!is_array($fileData[$lpos])){
								$fileData[$lpos]=array('name'=>'wf_#@ID@#_sub_'.$pid,'fields'=>array(),'id'=>$pid);
							}
							$spfdata['dbfld']='fld_'.$subrcn;
							$fileData[$lpos]['fields'][]=dataEntry($spfdata,'fld_'.$subrcn,++$subrcn);

						}
					}
				}else{
					$sfids[]='`fld_'.$rcn.'` '.$typeSQL[$pfdata['type']];
					if($pfdata['dgst'] === true){
						$digests[]='fld_'.$rcn;
					}
					$pfdata['dbfld']='fld_'.$rcn;
					$fileData[]=dataEntry($pfdata,'fld_'.$rcn,++$rcn);
				}
			}else{
				$pfdata['dbfld']='entry_date';
			}
		}
	}
	$fields=my_real_escape_string(gzcompress(serialize($plainFields),9));
	if($eform === 0){
		$sql='insert into form_master (title,fields,registry,touch) values ("'.$fname.'","'.$fields.'","'.$registry.'",now())';
		$res=my_query($sql);
		$newd=my_insert_id();
	}else{
		$sql='update form_master set title="'.$fname.'", fields="'.$fields.'",touch = now()  where id="'.$eform.'"';
		$res=my_query($sql);
		$newd=$eform;
	}
	$bcsql = str_replace('#@ID@#',$newd,$bcsql);

	$plurals=array();

	if(count($sfids) > 0){
		$bcsql.='( '.join(",\n",$sfids).$sqlappix;
		foreach($presql as $psql){
			$psql =str_replace('#@ID@#',$newd,$psql);
			my_query($psql);
		}
		$wres=my_query($bcsql);
		if($wres ){
			$updates=array();
			if(count($digests) > 0){
				$updates['digest']=join(',',$digests);
			}
			if(count($subTables) > 0){
				$tar=array();
				$parseDAtes=array();
				foreach($subTables as $sid => &$sval){
					$tabname=str_replace('#@ID@#',$newd,$sval['name']);
					$fdata=&$fileData[$sval['fdid']];
					if(is_array($fdata) && $fdata['id'] === $sid){
						foreach($sval['dates'] as $sfd){
							$parseDates[]='"'.$sfd.'"=>"$resex=turnDateSQL(\"#XYZ#\");"';
						}
						$fdata['name']=str_replace('#@ID@#',$newd,$fdata['name']);
						$tmp='"wform_sub_'.$sid.'" => array("title"=>"'.$sval['title'].'","value"=>"plural",
							"query"=>array(
									"set"=>"select * from '.$tabname.' where wf_id=\'%d\'",
									"fields"=>array(
										'.join(",\n",$fdata['fields']).'
									)
								)
							)';
						//'client'=>'client_id',
						$plurals[]="'wform_sub_".$sid."'=>array(
			'table'=>'".$tabname."',
			'index'=>'wf_id',
			'clients'=> false,
			'fields'=>array(\"".join('","',$sval['list'])."\"),
			'eparser'=>".(count($parseDates) > 0 ? 'array('.join(",",$parseDates).')' : 'false')."
				)";
					}

					$tar[]=$tabname;
					$fsql='CREATE TABLE IF NOT EXISTS '.$tabname.' ('.join("\n,",$sval['sql']).$sqlappix;
					$tres=my_query($fsql);
					$fdata=$tmp;
				}

				$updates['subs']=join(",",$tar);
				unset($tar);
			}
			if(count($updates) > 0){
				$q = new DBQuery();
				$q->addWhere('id="'.$newd.'"');
				$q->addTable('form_master');
				foreach($updates as $ukey => $uval){
					$q->addUpdate($ukey,$uval);
				}
				$sql=$q->prepare();
				$dres=my_query($sql);
			}
			$fh=fopen($baseDir.'/modules/outputs/data/wform_'.$newd.'.fields.php',"w+");
			fputs($fh,'<?php
$partShow=true;
$selects = array('.join(",\n",$fileSels).');'."\n");
			fputs($fh,'$fields=array('.join(",\n",$fileData).");\n?>");
			fclose($fh);
			$fh=fopen($baseDir.'/modules/outputs/titles/wform_'.$newd.'.title.php',"w+");
			fputs($fh,'<?php
$titles["wform_'.$newd.'"]=
		array(
			"title" => "'.$fname.'",
			"db"=>"wform_'.$newd.'",
			"client"=>"client_id",
			"uid"=>"tbw'.$newd.'",
			"date"=>"entry_date",
			"client_name"=> "concat(client_first_name,\' \',client_last_name) as client_name",
			"did"=>"id",
			"defered"=>array(),
			"abbr"=>"WF'.$newd.'",
			"link"=>array("href"=>"?m=wizard&a=form_use&'.($registry === 1 ? '' : 'client_id=#client_id#&').'itemid=#did#&fid='.$newd.'&todo=addedit","vals"=>array("'.($registry === 1 ? '' : 'client_id').'","did")),
			"plurals"=>array('.join(',',$plurals).'),
			"referral"=>"",
			"next_visit"=>"",
			"form_type"=>"'.($registry === 1 ? 'registry' : 'contus').'"
		);
?>');
			fclose($fh);
		}
	}

	if(!$_POST['fakereturn']){
		if($wres){
			$AppUI->setMsg("Form ".$fname.' saved',UI_MSG_OK);
			$AppUI->redirect("m=wizard");
		}elseif(my_error()!= ''){
			echo my_error();
		}
	}else{
		if($wres){
			return 'ok';
		}
	}

}
?>