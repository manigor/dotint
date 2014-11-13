<?php

require_once($AppUI->getModuleClass("cbccheck"));
if($_SERVER['CONTENT_LENGTH'] > 0){
	$siis = json_decode(stripslashes($_POST['jfk']),true);

	$chmode = 'added';

	$entries = $_POST['fentry'];
	if(count($entries) > 0){
		$dbe = new CCBCCheck();

		$dbe->cbc_village = my_real_escape_string($_POST['cbc_village']);
		$dbe->cbc_name = my_real_escape_string($_POST['cbc_name']);
		$dbe->cbc_center_id = (int)$_POST['cbc_clinic_id'];
		$dbe->cbc_location = my_real_escape_string($_POST['cbc_location']);

		foreach ($entries as $key => $evals){
			$dbe1 = clone $dbe;
			$entry_date = new CDate ( $evals ["entry_date"] );
			$dbe1->cbc_entry_date = $entry_date->format ( FMT_DATE_MYSQL );
			$dbe1->cbc_adm_no = my_real_escape_string($evals['cbc_adm_no']);
			$dbe1->cbc_client_id = my_real_escape_string($evals['cbc_client_id']);
			$dbe1->cbc_old= $evals['cbc_old'];
			$dbe1->cbc_sex = $evals['cbc_sex'];
			$dbe1->cbc_age = $evals['cbc_age'];
			$dbe1->cbc_hbcare = @join(',',$siis[0]['care'][$key]);
			$dbe1->cbc_adh_support = $evals['cbc_adh_support'];
			$dbe1->cbc_remarks = my_real_escape_string($evals['cbc_remarks']);
			$dbe1->cbc_refers = @implode(',',$siis[0]['refs'][$key]);
			$dbe1->cbc_refers_note = $siis[1]['refs'][$key];
			if(isset($evals['cbc_id']) && (int)$evals['cbc_id'] > 0) {
				$dbe1->cbc_id = (int)$evals['cbc_id'];
				$chmode = ' changes saved';
			}
			$dbe1->store();

			unset($dbe1);
		}
		$AppUI->setMsg( 'CBC '.$chmode, UI_MSG_OK, false );
	}

	$AppUI->redirect("m=clients");
}
?>