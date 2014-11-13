<?php
global $AppUI, $baseDir;
//require_once ($baseDir . '/modules/outputs/php_json.class.php');

function findRowItem($rt, &$glback, &$entries)
{
	$rsaved = $glback['bdata'];
	$rt = intval($rt);
	foreach ($rsaved as $rid => $rvals) {
		if (intval($rvals['t']) === $rt) {
			return $rvals;
		}
	}
	//return $glback['bdata'][$rt];
}

function utf8_urldecode($str)
{
	$str = preg_replace("/%u([0-9a-f]{3,4})/i", "&#x\\1;", urldecode($str));
	return html_entity_decode($str, null, 'UTF-8');
}


$textFormat = "%b %e, %Y"; //"j F Y";

$df = $AppUI->getPref('SHDATEFORMAT');

if ($_POST ['mode'] == 'save' || $_POST ['mode'] == 'update') {

	$allData = magic_json_decode($_POST['bps']);

	$fdata = $allData['entries'];
	$bdata['bdata'] = $allData['bdata'];
	$bdata['order'] = $allData['order'];
	$bdata['rows'] = $allData['rows'];
	$bdata['types'] = $allData['types'];
	$bdata['second'] = $allData['second'];
	$bdata['columns'] = $allData['columns'];
	$sdate = '';
	$edate = '';
	if (strlen($allData ['start']) > 8) {
		$tdate = new CDate ($allData ['start']);
		$sdate = $tdate->format(FMT_DATE_MYSQL);
	}
	if (strlen($allData ['end']) > 8) {
		$tdate = new CDate ($allData ['end']);
		$edate = $tdate->format(FMT_DATE_MYSQL);
	}
	$zmode = $_POST ['mode'];
	$zid = ( int )$_POST ['indb'];
	if ($zmode == 'save') {
		$stmpl = 'insert into reports (title,rep_desc,rep_dept,start_date,end_date,entries,backdoor) values ("%s","%s","%d","%s","%s","%s","%s")';
	} elseif ($zmode == 'update' && (isset ($_POST ['indb']) && $zid > 0)) {
		$stmpl = 'update reports set title="%s",rep_desc="%s",rep_dept="%d", start_date="%s", end_date="%s", entries="%s", backdoor="%s" where id="' . $zid . '"';
	}
	$rep_name = $fdata ['rep_name'];
	unset ($fdata ['rep_end'], $fdata ['rep_start'], $fdata ['rep_name']);
	$sql = sprintf($stmpl,
		my_real_escape_string($rep_name),
		my_real_escape_string($fdata['rep_desc']),
		intval($fdata['rep_dept']),
		$sdate,
		$edate,
		my_real_escape_string(gzencode(var_export($fdata, true), 9, FORCE_GZIP)),
		my_real_escape_string(gzencode(var_export($bdata, true), 9, FORCE_GZIP))
	);
	$res = my_query($sql);
	if ($res) {
		$rtext = my_insert_id();
	} else {
		$rtext = 'fail';
	}
	echo $zid > 0 ? $zid : $rtext;
	return;
} else
	switch ($_GET['mode']) {
		//if ($_GET ['mode'] == 'loadinfo') {
		case 'loadinfo':
			require_once $AppUI->getSystemClass('systemImport');
			$rid = ( int )($_GET ['dbrid']);
			$df = $AppUI->getPref('SHDATEFORMAT');
			$q = new DBQuery ();
			$q->addQuery('backdoor');
			$q->addTable('reports');
			$q->addWhere('id="' . $rid . '"');
			$datab = $q->loadResult();
			$sql = 'select title,end_date, start_date,entries from reports where id="' . $rid . '"';
			$res = my_query($sql);
			if ($res) {
				$datas = my_fetch_assoc($res);
				eval ('$bdd=' . gzdecode($datab) . ';');
				eval ('$bde=' . gzdecode($datas ["entries"]) . ';');
				$datas ["backdoor"] = $bdd;
				$datas ["entries"] = $bde;
				$sdate = new CDate ($datas ["start_date"]);
				$edate = new CDate ($datas ["end_date"]);
				$datas ['start_date'] = ($sdate->getYear() > 0 ? $sdate->format($df) : '');
				$datas ['end_date'] = ($edate->getYear() > 0 ? $edate->format($df) : '');
				echo json_encode($datas);
				return;
			}
			break;

		case '2pdf':
			$zkey = trim($_GET['zkey']);
			$savedName = $_SESSION['rnames'][$zkey];
			if (!$savedName) {
				$savedName = 'report';
			}
			$savedName = str_replace(" ", '_', $savedName);
			require_once($baseDir . '/lib/mpdf/mpdf.php');
			if ((int)$_GET['2cols'] === 1) {
				$dompdf = new mPDF('utf-8', '', 0, '', 3, 3, 4, 4, 2, 2, 'L'); // ('utf-8', 'A4-L'); //let 2 column layout will be in landscape
			} else {
				$dompdf = new mPDF();
			}
			$dompdf->WriteHTML(file_get_contents($baseDir . '/files/tmp/' . $zkey . '.rfs'));
			//echo $savedName;
			$pdfPath = $baseDir . '/files/tmp' . $savedName . ".pdf";
			//$dompdf->Output($pdfPath ,'D');
			$dompdf->Output($savedName . '.pdf', 'D');
			//printForSaveFromFile('application/pdf',$pdfPath,$savedName.'.pdf');
			return;
			break;
		case '2html':
			$zkey = trim($_GET['zkey']);
			$savedName = $_SESSION['rnames'][$zkey];
			if (!$savedName) {
				$savedName = 'report';
			}
            if(trim($_GET['rn']) != ''){
                $savedName = trim($_GET['rn']);
                $savedName = str_replace(" ","_",$savedName);
            }
			$savedName = str_replace(" ", '_', $savedName);
			printForSave(file_get_contents($baseDir . '/files/tmp/' . $zkey . '.rfs'), 'text/html', $savedName . '.html');
			return;
			break;
		case 'compile':
			if (( int )($_GET ['itid']) > 0) {
				buildTableDataDemand();
				$cid = intval($_GET['itid']);
				$currentKey = uniqid();
				$onceShown = ($_GET['kadze'] == 'kami');
				?>
			<div id="load_res">Loading&nbsp;<span style="font-weight: 800;" id="pcent">0</span><b>%</b>...</div>
			<script type="text/javaScript">
				window.onload = up;
				function up() {
					monitorPs("<?php echo $currentKey?>", "pcent");
					$j.post("/?m=outputs&a=reports&suppressHeaders=1&mode=wfrm&ds=<?php echo $_GET['ds']?>&de=<?php echo $_GET['de']?>&itid=<?php echo $cid?>&urkey=<?php echo $currentKey . ($onceShown === true ? "&akill=1" : "") ?>", function (dh) {
						$j("#load_res").replaceWith(dh);
					});
				}
			</script>
			<?php

				return;
			}
			break;
		case 'wfrm':
			if (( int )($_GET ['itid']) > 0) {
				$cid = intval($_GET['itid']);
				buildTableDataDemand();
				require_once('report.func.php');
				//if($_GET['akill'] == 1){ /// :))))
				if ($_GET['kadze'] == 'kami') {
					$sql = 'delete from reports where id ="' . $cid . '"';
					$killres = my_query($sql);
				}
			}
			break;
		case 'save_item':
			$new_id = 'fail';
			$idata = magic_json_decode($_POST['itemfo']);
			/************************************************/
			// Cleaning saved list of rows visible/hidden in order to reduce size of future json string.
			/************************************************/
			$sdata = magic_json_decode($_POST['sddata']);
			$sdata['list'] = array();
			$idata['tbsdata'] = $sdata;
			$sql = 'insert into report_items (title,itype,idata) values ("%s","%s","%s")';
			$res = my_query(sprintf($sql, $idata['n'], $idata['c'], my_real_escape_string(json_encode($idata))));
			if ($res) {
				$new_id = my_insert_id();
			}
			echo $new_id;
			break;
		case 'get_item_list':
			$sql = 'select * from report_items order by itype';
			$res = my_query($sql);
			$items = array();
			if (is_resource($res)) {
				while ($row = my_fetch_assoc($res)) {
					if (!is_array($items[$row['itype']])) {
						$items[$row['itype']] = array();
					}
					$items[$row['itype']][$row['id']] = $row['idata'];
				}
			}
			echo json_encode($items);
			break;
		case 'item_view':
			$itemID = intval($_GET['iid']);
			if ($itemID > 0) {
				$sql = 'select idata from report_items where id="%d"';
				$res = my_query(sprintf($sql, $itemID) );
				if ($res) {
					$irdata = my_fetch_row($res);
					$irdata = magic_json_decode($irdata[0], true);
					echo proceedReportItem($irdata);
				}
			}
			return;
			break;
		case 'item_kill':
			$pok = 'fail';
			if (intval($_GET['iid']) > 0) {
				$sql = 'delete from report_items where id="%d"';
				$res = my_query(sprintf($sql, intval($_GET['iid'])));
				if ($res) {
					$pok = 'ok';
				}
			}
			echo $pok;
			break;
		case 'item_import':
			break;
		default:
			break;
//}elseif($_GET['mode'] === 'item_import'){}
	}
?>