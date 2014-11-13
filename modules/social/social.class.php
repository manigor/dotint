<?php /* SOCIAL WORK VISIT $Id: counselling.class.php,v 1.9 2004/01/29 06:30:43 ajdonnison Exp $ */
/**
 *	@package dotProject
 *	@subpackage modules
 *	@version $Revision: 1.9 $
 */

$caretypes = array ('pri' => 'Primary', 'sec' => 'Secondary' );

require_once ($AppUI->getSystemClass ( 'dp' ));

/**
 *	Social Visit Class
 *
 */
class CSocialVisit extends CDpObject {
	var $social_id = NULL;
	var $social_client_id = NULL;
	var $social_staff_id = NULL;
	var $social_clinic_id = NULL;
	var $social_entry_date = NULL;
	var $social_client_status = NULL;
	var $social_client_health = NULL;
	var $social_visit_type = NULL;
	var $social_change = NULL;
	var $social_death = NULL;
	var $social_death_notes = NULL;
	var $social_death_date = NULL;
	var $social_caregiver_pri_change = NULL;
	var $social_caregiver_sec_change = NULL;
	var $social_caregiver_pri_change_notes = NULL;
	var $social_caregiver_sec_change_notes = NULL;
/*	var $social_caregiver_change_notes = NULL;
	var $social_caregiver_fname = NULL;
	var $social_caregiver_lname = NULL;
	var $social_caregiver_age = NULL;
	var $social_caregiver_status = NULL;
	var $social_caregiver_relationship = NULL;
	var $social_caregiver_education = NULL;
	var $social_caregiver_employment = NULL;
	var $social_caregiver_income = NULL;
	var $social_caregiver_idno = NULL;
	var $social_caregiver_mobile = NULL;
	var $social_caregiver_health = NULL;
	var $social_caregiver_health_child_impact = NULL;

	var $social_caregiver_employment_change = NULL;*/
	var $social_caregiver_employment_change = null;
	var $social_caregiver_new_employment = null;
	var $social_caregiver_new_employment_desc = NULL;
	var $social_caregiver_income = NULL;
	/*var $social_caregiver_pri_health = NULL;
	var $social_caregiver_pri_health_child_impact = NULL;
	var $social_caregiver_pri_new_employment = NULL;
	var $social_caregiver_pri_new_employment_desc = NULL;
	//var $social_caregiver_pri_new_income = NULL;
	var $social_caregiver_sec_health = NULL;
	var $social_caregiver_sec_health_child_impact = NULL;
	var $social_caregiver_sec_new_employment = NULL;
	var $social_caregiver_sec_new_employment_desc = NULL;*/
	//var $social_caregiver_sec_new_income = NULL;

	var $social_class_form = null;
	var $social_reason_not_attending_notes = null;
	var $social_residence_mobile = NULL;
	var $social_residence = NULL;
	var $social_school_attendance = NULL;
	var $social_school = NULL;
	var $social_reason_not_attending = NULL;
	var $social_relocation = NULL;
	var $social_iga = NULL;
	var $social_placement = NULL;
	var $social_succession_planning = NULL;
	var $social_legal = NULL;
	var $social_nursing = NULL;
	var $social_transport = NULL;
	var $social_education = NULL;
	var $social_food = NULL;
	var $social_rent = NULL;
	var $social_solidarity = NULL;
	var $social_direct_support = NULL;
	var $social_medical_support = NULL;
	var $social_medical_support_desc = NULL;
	var $social_other_support = NULL;
	var $social_risk_level = NULL;
	var $social_othersupport_value = NULL;
	var $social_medicalsupport_value = NULL;
	var $social_directsupport_value = NULL;
	var $social_direct_support_desc = NULL;
	var $social_solidarity_value = NULL;
	var $social_rent_value = NULL;
	var $social_food_value = NULL;
	var $social_education_value = NULL;
	var $social_transport_value = NULL;
	var $social_nursing_value = NULL;
	var $social_legal_value = NULL;
	var $social_succession_value = NULL;
	var $social_permanency_value = NULL;
	var $social_custom = NULL;
	var $social_notes = NULL;
	var $social_training = NULL;
	var $social_training_desc = NULL;
	var $social_next_visit = NULL;
	var $social_referral = NULL;
	var $social_caregiver_pri = NULL;
	var $social_caregiver_sec = NULL;
	/*var $social_nhf = null;
	var $social_nhf_y = null;
	var $social_nhf_n = null;
	var $social_immun = null;
	var $social_immun_y = null;
	var $social_immun_n = null;*/
	var $social_caregiver_pri_type = null;
	var $social_caregiver_sec_type = null;
	var $social_any_needs = null;

	function CSocialVisit() {
		$this->CDpObject ( 'social_visit', 'social_id' );
	}

	// overload check
	function check() {
		/*if ($this->counselling_id === NULL) {
			return 'counselling id is NULL';
		}
		$this->counselling_id = intval( $this->counselling_id );*/

		if (empty ( $this->social_death_date )) {
			$this->social_death_date = NULL;
		}
		/*if (empty ( $this->social_caregiver_age )) {
			$this->social_caregiver_age = NULL;
		}*/

		return NULL; // object is ok
	}

	// overload canDelete
	function canDelete(&$msg, $oid = null) {

	}
	function getContacts($type = NULL) {
		$contacts = NULL;
		$q = new DBQuery ( );

		if (isset ( $this->company_id )) {
			$q->addTable ( 'company_contacts' );
			$q->addQuery ( 'company_contacts_contact_id' );
			$q->addWhere ( "company_contacts_company_id = $this->company_id" );
			if ($type)
				$q->addWhere ( "company_contacts_contact_type = $type" );

			$contacts = $q->loadColumn ();
		}
		//if (count($contacts)==1)
		//$contacts = $contacts[0];


		return $contacts;

	}
	function getUrl($urlType = 'view', $companyType = NULL) {
		if ($companyType == NULL)
			$companyType = $this->company_type;

		$modules = dPgetSysVal ( 'CompanyModules' );
		$unit = $modules [$companyType];
		$url_array = array ("view" => "./index.php?m=counselling&a=view&company_id=$this->company_id", "add" => "./index.php?m=counselling&a=addedit&company_type=$companyType", "edit" => "./index.php?m=counselling&a=addedit&company_id=$this->company_id" );
		return $url_array [$urlType];
	}
	function getDescription() {
		static $types;
		if (! isset ( $types )) {
			$types = dPgetSysVal ( 'CompanyType' );
		}
		$desc = $types [$this->company_type];
		return $desc;
	}
	function getCount($type = NULL) {
		if (! empty ( $type )) {
			$sql = "SELECT COUNT(*) FROM companies WHERE company_type IS NOT NULL AND company_type = $type";
		} else {
			$sql = "SELECT COUNT(*)  FROM companies WHERE company_type IS NOT NULL";
		}
		$count = db_loadResult ( $sql );
		return $count;
	}

	function store() {
		global $AppUI;

		//$importing_tasks = false;
		$msg = $this->check ();
		if ($msg) {
			$return_msg = array (get_class ( $this ) . '::store-check', 'failed', '-' );
			if (is_array ( $msg ))
				return array_merge ( $return_msg, $msg );
			else {
				array_push ( $return_msg, $msg );
				return $return_msg;
			}
		}

		if (($this->social_id) && ($this->social_id > 0)) {

			addHistory ( 'socialvisit', $this->social_id, 'update', $this->social_id );
			$this->_action = 'updated';

			$ret = db_updateObject ( 'social_visit', $this, 'social_id', true );

		} else {

			$this->_action = 'added';
			$ret = db_insertObject ( 'social_visit', $this, 'social_id' );
			addHistory ( 'socialvisit', $this->social_id, 'add', $this->social_id );

		}

		if (! $ret) {
			return get_class ( $this ) . "::store failed <br />" . db_error ();
		} else {
			return NULL;
		}
	}

	function changeRole($id,$newRole){
		$sql='select * from admission_caregivers where id="'.$id.'" limit 1';
		$res=my_query($sql);
		$sql = 'update admission_caregivers set reason="5",datesoff=now() where id="' . $id . '"';
		my_query( $sql );
		if($res){
			$old=my_fetch_assoc($res);
			$old['role']=$newRole;
			$sqli='insert into admission_caregivers (fname,lname, age,idno,mobile,health_status,relationship,employment,educ_level,client_id,role)
				values ("'.$old['fname'].'","'.$old['lname'].'","'.$old['age'].'","'.$old['idno'].'","'.$old['mobile'].'","'.$old['health_status'].'",
							"'.$old['relationship'].'","'.$old['employment'].'","'.$old['educ_level'].'","'.$old['client_id'].'","'.$old['role'].'")';
			$ires=my_query($sqli);
			if($ires){
				$newId=my_insert_id();
				return $newId;
			}
		}
	}
}

function getCareInfo($pref,$set,$arr){
	$clean = array();
	foreach ($set as $var) {
		$clean[$var]=my_real_escape_string($arr[$pref.'_'.$var]);
	}
	return $clean;
}

function insertCaregiver($pref,$role,$newMode = FALSE){
	global $_POST,$careFields,$obj;
	$newrs = array ();
	$newMode === true ? $urole='new' : $urole = $role;
	$newrs = getCareInfo ( 'social_caregiver_'.$urole, $careFields, $_POST );
	$sql = 'insert into admission_caregivers (client_id,fname,lname,age,idno,mobile,employment,educ_level,role,health_status,relationship)
			values("%d","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s")';
	$sql = sprintf ( $sql, $obj->social_client_id, $newrs ['fname'], $newrs ['lname'], $newrs ['age'], $newrs ['idno'], $newrs ['mobile'], $newrs ['employment'], $newrs ['educ_level'], $role, $newrs ['health_status'], $newrs ['relationship']);
	$res = my_query ( $sql );
	if($res){
		return my_insert_id();
	}
}
?>
