<?php
global $pathPrefix;
$pathPrefix = 'moa_output';
require_once("outputs.class.php");
$moduleScripts [] = '/modules/'.$pathPrefix.'/outputs.module.js';
global $AppUI,$m;
buildTableDataDemand();
if($_POST['mode']=='save'){
	require_once $AppUI->getFileInModule($m, 'patch.func');
	exportResultExcel();
	return ;
}elseif($_POST['faction'] == 'export'){
		$file=ExIm::makeFile((int)$_POST['qsid'],$_POST['stype']);
		if(count($file) == 2 && strlen($file[1]) > 1 ){
			printForSave($file[1],'application/octet-stream',$file[0].'.qbn');
			return ;
		}
}elseif ($_POST['mode'] == "importquery" && count($_FILES) == 1){
	$res='fail';
		if($_FILES['qfile']['size'] < 100000 && $_FILES['qfile']['error'] == 0){
			$res=ExIm::pickFile($_FILES['qfile']['tmp_name']);
			if($res !== false){
				$res= json_encode($res);
			}else{
				$res='fail';
			}
		}
		echo $res;
		return ;
}elseif($_POST ['mode'] == "query") {
	require_once $AppUI->getFileInModule($m, 'patch.func');
	proceedQueryStuff();
	return ;

}else if ($_POST ['mode'] == 'patch') {
	require_once $AppUI->getFileInModule($m, 'patch.func');
	proceedPatch();
	return ;
}elseif ($_GET['mode'] == 'rowkill'){
	$rid=(int)$_GET['row'];
	if($rid >=0 && is_numeric($_GET['row'])){
		//$fsaved=$_SESSION['table']['body'];
		$fsaved=getFileBody('body');
		if(count($fsaved) > 0){
			$ucase=unserialize($fsaved[$rid]);
			$sql='delete from '.$titles[$ucase['table']]['db'].' where '.$titles[$ucase['table']]['did'].' ="'.$ucase['id'].'" limit 1';
			$res=my_query($sql);
			if(my_affected_rows()){
				//unset($_SESSION['table']['body'][$rid]);
				$fsaved[$rid]=serialize('');
				saveFileBody($fsaved);
				echo "ok";
			}else{
				echo "fail";
			}
		}
	}
	return ;
}


global $titles;
?>
<link rel="stylesheet" type="text/css" href="/modules/outputs/jquery-ui.css" />
<link rel="stylesheet" type="text/css" href="/modules/<?php echo $pathPrefix?>/outputs.module.css" />
<?php
$l='""';
$f='""';
$h='""';
$u='""';
$s='""';
$r='""';
$p='""';
$preFils=array();
$y=0;
$staterd=0;
$html='';
$rhtml='';
$thtml='';
$rl='';
$bigtar=array();
$rqid=0;
$btlen=0;
$ftabsel=0;
$sels=array();
$mode='simple';
$clients=array();
$bigtar_keys=array();
$lcrows=0;
$uamode=false;
$thisCenter=FALSE;
$statusHistory=false;
$vis_mode= '';
$moduleScripts[]="./modules/outputs/stats.js";
$moduleScripts[]="./modules/outputs/reporter.js";
$moduleScripts[]="./modules/outputs/jquery-ui.min.js";
$moduleScripts[]="./modules/outputs/jquery.cleditor.min.js";
$js_comm='false';


if ($_SERVER ['CONTENT_LENGTH'] > 0 && count ( $_POST ) > 0) {
	$lpost = array ();
	$starter=0;
	$ender=0;
	$show_start='';
	$show_end='';
	$final = array();
	require_once('result.func.php');
	$nfei= new evolver();

	$tab_src='';

	resultBuilder('out');
	$ftabsel=2;
	$mode='result';
	if($_POST['stype'] === 'Stats' || $_POST['stype'] === 'Chart'){
		$ftabsel=3;
		$js_comm='1';
		$qsid=(int)my_real_escape_string($_POST['qsid']);
		$q = new DBQuery();
		$q->addTable('stat_queries');
		$q->addWhere('id='.$qsid);
		$sdb=$q->loadList();
		$sdb=$sdb[0];
		$turns=unserialize($sdb['turns']);
		$svals=array(
			"rows" => unserialize(stripslashes($sdb['rows'])),
			'cols' => unserialize(stripslashes($sdb['cols'])),
			'range'=> unserialize(stripslashes($sdb['ranges'])),
			'sunqs'=> (int)$turns['sunqs'],
			'stots-rows'=> (int)$turns['stots_rows'],
			'stots-cols'=> (int)$turns['stots_cols'],
			'sperc-rows'=> (int)$turns['sperc_rows'],
			'sperc-cols'=> (int)$turns['sperc_cols'],
			'delta-count'=> (int)$turns['delta_count'],
			'records'	=>	(int)$turns['records'],
			'sblanks'=> (int)$turns['sblanks'],
			'list' => array(),
		);
		$do_show_result=(int)$sdb['show_result'];
		unset($turns);
		//$bar=getFileBody('stat');

		$trows=count($svals['rows']);
		$tcols=count($svals['cols']);

		if($do_show_result === 0){
			require_once('stater.class.php');
			$row_levels=array();
			$firstr=$svals['id'];
			$bar=getFileBody('stat');
			$turns=unserialize($sdb['turns']);
			if(count($bigtar_keys) > 0){
				$ulines=$bigtar_keys;//array_keys($bigtar);
			}else{
				/*for($i=0;$i < count($clients);$i++ ) 	$ulines[]=$i;*/
				$ulines=range(0,count($clients));
			}
			$svals['list']=$ulines;

			$thtml = makeStat($bar,$svals);

			DiskStatCache($thtml);
			$rhtml='';
			unset($bigtar,$clients,$bigtar_keys,$l,$r,$u,$sels,$f);
			$y=0;
		}
		if($_POST['stype'] === 'Chart'){
			$js_comm=2;
			$gdata=unserialize($sdb['chart_data']);
			$dset=json_decode($gdata['dset'],true);
			if(isset($dset['col_use'])){
				if($dset['col_use'] != 'xcall'){
					foreach ($dset['colb'] as $cv) {
						if($cv[0] == $dset['col_use']){
							$use_col=$cv[1];
						}
					}
				}else{
					$use_col='xcall';
				}
			}else{
				$use_col=false;
			}
			if(isset($dset['row_use'])){
				foreach ($dset['rowb'] as $rv) {
					if($rv[0] == $dset['row_use']){
						$use_row=$rv[1];
					}
				}
			}else{
				$use_row=false;
			}
			$chartDerectives=array(
				'mode'=>$gdata['cmode'],
				'pie_row'=>(isset($gdata['urow']['uvrow']) ? $gdata['urow']['uvrow'] : false),
				'col_use'=>$use_col,
				'row_use'=>$use_row
			);
		}
	}
}
$htmlpre = '<form method="POST" action="?m='.$pathPrefix.'" id="sendAll" name="xform" onsubmit="return false;">
	<input type="hidden" name="stype">
	<input type="hidden" name="pmode">
	<input type="hidden" name="faction">';
$mi = 0;
$block_count = 1;
$tchex=0;
$auto_open=array();
ksort ( $fielder );
$html=buildForms($fielder);
unset($fielder);
$lasttext='';
$alltext='';
$firsttext='';
$curcentext=($thisCenter !== FALSE ? 'checked' : '');
$stahistext=($statusHistory !== FALSE ? 'checked' : '');
if ($vis_mode == 'last') {
	$lasttext = 'checked';
} elseif($vis_mode == 'first') {
	$firsttext = 'checked';
}else{
	$alltext='checked';
}

$df = $AppUI->getPref('SHDATEFORMAT');
if ($starter != '' && !is_null($starter)) {
	$tdd = new CDate($starter);
	$show_start = $tdd->format($df);
	unset($tdd);
} else {
	$starter = date ( "Ymd" );
}
if ($ender != '' && !is_null($ender)) {
	$tdd= new CDate($ender);
	$show_end = $tdd->format($df);
	unset($tdd);
} else {
	$ender = date ( "Ymd" );
}

if($lvder != '' && !is_null($lvder)){
	$tdd= new CDate($lvder);
	$show_lvd = $tdd->format($df);
	unset($tdd);
}else{
	$show_lvd='';
}



$queriez=array();
$q= new DBQuery();
$q->addTable("queries");
$q->addWhere('visible="1"');
$q->addOrder("created desc");
$queriez['Table'] = $q->loadList();
$q->clearQuery();
$q->addTable('stat_queries','sqs');
$q->addOrder("created desc");
$q->addOrder('qmode asc');
$queriez['Stats']=$q->loadList();
$q->clearQuery();
$q->addQuery('id,title as qname, replace(start_date,"-","") as sdate, replace(end_date,"-","") as edate');
$q->addTable('reports');
$queriez['Report']=$q->loadList();
unset($q);
flush_buffers();
//<label><input type=checkbox name="extra[]" '.addChecks($lpost,'extra',"location").'>Location</label>
$html = $htmlpre.buildSelectOptions().$html.'
<br><br>
<div style="width: 1000px;">
	<input type="button" value="Go" onclick="getData()" class="button">&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="button" value="Clear Forms" onclick="clearData()" id="fcleaner" class="button" '. (($tchex > 0) ? '': 'disabled="disabled"').'>
</div>
</form>';
cleanALoc($lpost);
//'.($ftabsel == 2 ? 'class="ui-tabs-selected"' : '').'
echo '
<DIV id="tabs" class="bigtab">
<UL class="topnav">
<LI><A href="#tabs-1"><span>Queries</span></A></LI>
<LI><A href="#tabs-2"><span>Forms</span></A></LI>
<LI><A href="#tabs-3"><span>Tables</span></A></LI>
<LI class="tabs-disabled"><A href="#tabs-4"><span>Stats</span></A></LI>
<LI><A href="#tabs-5"><span>Report</span></A></LI>
</ul>
<div id="tabs-1" class="mtab">
		<!-- <select onchange="rebootQTable(this);" data-items="">
			<option value="queries" selected>Queries</option>
			<option value="items">Report Items</option>
		</select>
		<br> -->
	<p>
		<span onclick="$j(\'#importbox\').toggle();" class="fhref flink">Import query</span><span class="offwall msgs" id="msg_place"></span>
		<div id="importbox" class="myimporter">
			<form name="upq" action="/?m=outputs&suppressHeaders=1" enctype="multipart/form-data" method="POST" onsubmit="return AIM.submit(this, {\'onStart\' : startCallback, \'onComplete\' : qurer.extractRow})">
				<input type="file" name="qfile" id="fultra" data-ext="qbn|rbn|ibn">
				<input type="submit" value="Import query/item" class="button" disabled="disabled" >
				<input type="hidden" name="mode" value="importquery">
			</form>
		</div>

		<table cellspacing="1" cellpadding="2" border="0" class="tbl tablesorter moretable" id="ittable" style="display: none;">
			<thead>
				<tr>
					<th class="phead">&nbsp;</th><th class="phead">Name</th><th class="phead">Type</th><th class="phead">&nbsp;</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>

		<table cellspacing="1" cellpadding="2" border="0" class="tbl tablesorter moretable" id="qtable">
			<thead>
			<tr>
				<th class="phead">&nbsp;</th>
				<th class="phead">Name</th>
				<th class="phead">Type</th>
				<th class="phead">Item Type</th>
				<th class="phead">Description</th>
				<th class="phead">Start Date</th>
				<th class="phead">End Date</th>
				<th class="phead">&nbsp;</th>
				<th class="phead">&nbsp;</th>
			</tr>
		</thead>';
$trid=0;
$sr='';
$qsr='';
foreach ($queriez as $pname => $part) {
	foreach ($part as $row) {
		$edClass='qeditor';
		if($pname == 'Stats'){
			$row['show_result'] == 1 ? $sr ='true' : $sr='false';
			if($row['qmode'] === 'graph'){
				//$edClass='qreditor';
				$pnameOut='Chart';
			}else{
				$pnameOut='Stats';
			}
		}elseif($pname === 'Report'){
			$edClass='qreditor';
			$pnameOut='Report';
		}else{
			$pnameOut='Table';
		}
		$qsr.='<tr id="qsr_'.$trid.'" data-showr="'.$sr.'">
		<td title="Edit" align="center"><div class="'.$edClass.'" data-id="'.$row['id'].'"></td>';
		$st=trimView($row['qname']);
		$qsr.='<td data-text="'.$st['orig'].'" '.($st['show'] === true ? ' class="moreview"' : '').'><span class="fhref flink" onclick="qurer.run(\''.$trid.'\',\'run\');">'.$st['str'].'</span></td>
		<td align="center">'.$pnameOut.'</td>
		<td>&nbsp;</td>';
		$st=trimView($row['qdesc']);
		$qsr.='<td data-text="'.$st['orig'].'"'.($st['show'] === true ? ' class="moreview"' : '').' >'.$st['str'].'</td>';
		$sdateClean=viewDate($row['sdate']);
		$edateClean=viewDate($row['edate']);
		//if($pname == "Table"){
		//onclick = "popTCalendar(\'start_'.$trid.'\')"
		//onclick = "popTCalendar(\'end_'.$trid.'\')"
			$qsr.='
			<td >
				<div class="tdw">
				<div class="stdw" fsort="'.$sdateClean[1] .'">'.$sdateClean[0].'</div>
				<img width="16" height="16" border="0" alt="Calendar" src="/images/calendar.png" class="calpic" onclick = "popTCalendar(\'start_' . $trid . '\')">
				</div>
				<input type="hidden" id="start_'.$trid.'" value="'.$row['sdate'].'" >
			</td>
			<td >
				<div class="tdw">
				<div class="stdw" fsort="'.$edateClean[1] .'">'.$edateClean[0].'</div>
				<img width="16" height="16" border="0" alt="Calendar" src="/images/calendar.png" class="calpic" onclick = "popTCalendar(\'end_' . $trid . '\')">
				</div>
				<input type="hidden" id="end_'.$trid.'" value="'.$row['edate'].'" >
			</td>';
		/*}else{
			$qsr.='<td >&nbsp;</td><td >&nbsp;</td>';
		}*/
		$qsr.='
		<!-- <td ><span title="Run" class="fhref"  ><img src="/images/run1.png" weight=22 height=22 border=0 alt="Run"></span></td> -->
		<td align="center"><span title="Delete" class="fhref" onclick="qurer.delq(\''.$trid.'\');" ><img src="/images/delete1.png" weight=16 height=16 border=0 alt="Delete"></span></td>
		<td align="center"><div title="Export" class="exportq" onclick="qurer.run(\''.$trid.'\',\'export\');" ></div></td>
		</tr>';
		$trid++;
		echo $qsr;
		unset($qsr);
	}
}
unset($queriez);
flush_buffers();
//if(count($bigtar) == 0 &&  count ( $clients ) == 0){
$lpo=false;
if($y ==0 ){
	$rhtml='<span class="note">No data to display</span>';
	$lpo=true;
}
flush_buffers();
echo '</table></p></div>';
echo '<div id="tabs-2" class="mtab"><p>',$html,'</p></div>';
unset($html);
flush_buffers();
ob_end_clean();
//echo '<div id="tabs-3" class="mtab"><p>',$rhtml,'</p></div>';
echo '</span></span><div id="tabs-3" class="mtab"><p>';
//,$rhtml,
if($lpo === true){
	echo $rhtml ;
}else{
	diskFile::printOut();
}
echo '</p></div>';
unset($rhtml);
//flush_buffers();
//<!-- <div class="fbutton sec_type sec_table" title="Custom section"></div> -->
/*Report to be here*/

$tpl = new Templater($baseDir.'/modules/moa_output/report.tpl');
$tpl->cal_start=drawDateCalendar('rep_start','',false,'id="rep_start"',false,10);
$tpl->cal_end=drawDateCalendar('rep_end','',false,'id="rep_end"',false,10);
$tpl->thtml = $thtml;
$tpl->dept_selector = arraySelect(dPgetSysVal("ClinicalDepartments"),'rep_dept',"id='rep_dept' class='text'",1);
$tpl->output(true);

if($thtml !=''){
	$grinit=true;
}else{
	$grinit=false;
}
unset($html,$thtml,$rhtml);
flush_buffers();

$tpl->reboot($baseDir.'/modules/moa_output/outputs.bottom.tpl');
$tpl->chex = ($mi - 1);
$tpl->rrr = $y;
$tpl->today =  date("Ymd");
$tpl->fakes = json_encode($f);
$tpl->btr = json_encode($l);
$tpl->heads = json_encode($h);
$tpl->lets = json_encode($u);
$tpl->selects = json_encode($sels);
$tpl->tgt = $ftabsel;
$tpl->aopen = json_encode($auto_open);
$tpl->st_do =  $staterd;
$tpl->rqid = $rqid;
$tpl->refs =  json_encode($r);
$tpl->plus = json_encode($p);
$tpl->rels = json_encode($rl);
$tpl->pf = json_encode($preFils);
$tpl->mstart = $js_comm;
$tpl->extraCode = '';
if(strlen($thtml) > 0){
		$tpl->append('extraCode','$j("#tthome").show();');
	}
	if($_POST['stype'] ===  'Stats' || $_POST['stype'] ===  'Chart'){
		unset($svals['list']);
		$svals['rbox']=$svals['rows'];
		unset($svals['rows']);
		$svals['cbox']=$svals['cols'];
		unset($svals['cols']);
		$tpl->append('extraCode','fstatp='.json_encode($svals).';');
	}
	if(is_array($chartDerectives) && count($chartDerectives) > 0){
		$tpl->append('extraCode','chartMode='.json_encode($chartDerectives).';');
	}

$tpl->output(true);
?>
