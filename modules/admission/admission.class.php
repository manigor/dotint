<?php /* ADMISSION $Id: medical.class.php,v 1.9 2004/01/29 06:30:43 ajdonnison Exp $ */
/**
 *	@package dotProject
 *	@subpackage modules
 *	@version $Revision: 1.9 $
 */

require_once ($AppUI->getSystemClass ( 'dp' ));

/**
 *	Admission Record Class
 *
 */
class CAdmissionRecord extends CDpObject {

	var $admission_id = NULL;
	var $admission_client_id = NULL;
	var $admission_clinic_id = NULL;
	var $admission_staff_id = NULL;
	var $admission_entry_date = NULL;
	var $admission_dob = NULL;
	var $admission_age_yrs = NULL;
	var $admission_age_months = NULL;
	var $admission_age_status = NULL;
//	var $admission_gender = NULL;
	var $admission_school_level = NULL;
	var $admission_total_orphan = NULL;
	var $admission_reason_not_attending = NULL;
	var $admission_reason_not_attending_notes = NULL;
	//var $admission_total_orphan = NULL;
	var $admission_residence = NULL;
	var $admission_location = NULL;
	var $admission_province = NULL;
	var $admission_district = NULL;
	var $admission_village = NULL;
	var $admission_father_fname = NULL;
	var $admission_father_lname = NULL;
	var $admission_father_age = NULL;
	var $admission_father_status = NULL;
	var $admission_father_health_status = NULL;
	var $admission_father_raising_child = NULL;
	var $admission_father_marital_status = NULL;
	var $admission_father_educ_level = NULL;
	var $admission_father_employment = NULL;
	//var $admission_father_income = NULL;
	var $admission_father_idno = NULL;
	var $admission_father_mobile = NULL;
	var $admission_mother_fname = NULL;
	var $admission_mother_lname = NULL;
	var $admission_mother_age = NULL;
	var $admission_mother_status = NULL;
	var $admission_mother_health_status = NULL;
	var $admission_mother_raising_child = NULL;
	var $admission_mother_marital_status = NULL;
	var $admission_mother_educ_level = NULL;
	var $admission_mother_employment = NULL;
	//var $admission_mother_income = NULL;
	var $admission_mother_idno = NULL;
	var $admission_mother_mobile = NULL;
	var $admission_caregiver_pri = null;
	var $admission_caregiver_pri_fname = NULL;
	var $admission_caregiver_pri_lname = NULL;
	var $admission_caregiver_pri_age = NULL;
	var $admission_caregiver_pri_status = NULL;
	var $admission_caregiver_pri_health_status = NULL;
	var $admission_caregiver_pri_relationship = NULL;
	var $admission_caregiver_pri_marital_status = NULL;
	var $admission_caregiver_pri_educ_level = NULL;
	var $admission_caregiver_pri_employment = NULL;
	//var $admission_caregiver_pri_income = NULL;
	var $admission_caregiver_pri_idno = NULL;
	var $admission_caregiver_pri_mobile = NULL;
	var $admission_caregiver_sec = null;
	var $admission_caregiver_sec_fname = NULL;
	var $admission_caregiver_sec_lname = NULL;
	var $admission_caregiver_sec_age = NULL;
	var $admission_caregiver_sec_status = NULL;
	var $admission_caregiver_sec_health_status = NULL;
	var $admission_caregiver_sec_relationship = NULL;
	var $admission_caregiver_sec_marital_status = NULL;
	var $admission_caregiver_sec_educ_level = NULL;
	var $admission_caregiver_sec_employment = NULL;
	//var $admission_caregiver_sec_income = NULL;
	var $admission_caregiver_sec_idno = NULL;
	var $admission_caregiver_sec_mobile = NULL;
	var $admission_caregiver_pri_residence = NULL;
	var $admission_caregiver_sec_residence = NULL;
	var $admission_family_income = NULL;
	var $admission_risk_level = NULL;
	var $admission_risk_level_description = NULL;
	var $admission_notes = NULL;
	var $admission_custom = NULL;
	var $admission_father = null;
	var $admission_mother = null;
	var $admission_chw = null;
	var $admission_enclosures = null;
	var $admission_birth_cert = null;
	var $admission_id_no = null;
	var $admission_nhf = null;
	var $admission_med_recs = null;
	var $admission_immun = null;
	var $admission_death_cert= null;
	var $admission_enclosures_other = null;

	function CAdmissionRecord() {
		$this->CDpObject ( 'admission_info', 'admission_id' );
	}

	// overload check
	function check() {
		/*if ($this->medical_id === NULL) {
		return 'counselling id is NULL';
		}
		$this->medical_id = intval( $this->medical_id );*/

		/*if (empty($this->medical_dob))
		{
		$this->medical_dob = NULL;
		}
		if (empty($this->medical_child_nvp_date ))
		{
		$this->medical_child_nvp_date = NULL ;
		}
		if (empty($this->medical_mother_date_art))
		{
		$this->medical_mother_date_art = NULL;
		}
		if (empty($this->medical_child_azt_date))
		{
		$this->medical_child_azt_date = NULL;
		}


		if (empty($this->medical_mother_date_cd4))
		{
		$this->medical_mother_date_cd4 = NULL;
		}
		if (empty($this->medical_determine_date))
		{
		$this->medical_determine_date = NULL;
		}
		if (empty($this->medical_unigold_date))
		{
		$this->medical_unigold_date = NULL;
		}
		if (empty($this->medical_elisa_date))
		{
		$this->medical_elisa_date = NULL;
		}
		if (empty($this->medical_pcr1_date))
		{
		$this->medical_pcr1_date = NULL;
		}
		if (empty($this->medical_pcr2_date))
		{
		$this->medical_pcr2_date = NULL;
		}

		if (empty($this->medical_rapid12_date))
		{
		$this->medical_rapid12_date = NULL;
		}
		if (empty($this->medical_rapid18_date))
		{
		$this->medical_rapid18_date = NULL;
		}

		if (empty($this->medical_other_date))
		{
		$this->medical_other_date = NULL;
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
		$url_array = array ("view" => "./index.php?m=counsellinginfo&a=view&company_id=$this->company_id", "add" => "./index.php?m=counsellinginfo&a=addedit&company_type=$companyType", "edit" => "./index.php?m=counsellinginfo&a=addedit&company_id=$this->company_id" );
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

		$pureTable = array ();
		$caregivers = array ('father' => array (), 'mother' => array (), 'caregiver_pri' => array (), 'caregiver_sec' => array () );
		$offs=array('raising_child','residence');//'status',

		foreach ( get_object_vars ( $this ) as $key => $val ) {
			if (preg_match ( '/^admission/', $key )) {
				$found = false;
				foreach ( $caregivers as $kname => $kv ) {
					if (strstr ( $key, $kname )) {
						$found = true;
						$field = str_replace ( 'admission_' . $kname . '_', '', $key );
						if(strstr($kname,'caregiver')){
							$caregivers[$kname]['role']=str_replace('caregiver_','',$kname);
						}else{
							$caregivers[$kname]['role']=$kname;
						}
						$caregivers[$kname]['client_id']=$this->admission_client_id;
						if ($field == '' || $field == 'admission_' . $kname) {
							$field = 'id';
						}
						if (!in_array($field,$offs)){ /* != 'raising_child' && //$field != 'relationship' && /*( ($field == 'status' && !strstr($kname,'care')) ||/						$field != 'status'*/ // )
							$caregivers [$kname] [$field] = $val;
						} else {
							$found = false;
						}
					}
				}
				if (! $found) {
					$pureTable [$key] = $val;
				}
			}
		}

		if (($this->admission_id) && ($this->admission_id > 0)) {

			addHistory ( 'admissionrecord', $this->admission_id, 'update', $this->admission_id );
			$this->_action = 'updated';

			//$ret = db_updateObject( 'admission_info', $this, 'admission_id', true );


			/*if($this->admission_father >= 0){
			db_updateArray('admission_caregivers',$caregivers['father'],'id');
			}elseif ($this->admission_father === null && $this->admission_father_fname != ''){
			$res=db_insertArray('admission_caregivers',$caregivers['father']);
			}*/
			foreach ( $caregivers as $careName => $careArr ) {
				$gvar = 'admission_' . $careName;
				$gname = $gvar . '_fname';
				if ($this->$gvar !== null && $this->$gvar >= 0) {
					db_updateArray ( 'admission_caregivers', $careArr, 'id' );
				} elseif ($this->$gvar === null && $this->$gname != '' && $this->{$gvar . '_lname'}) {
					$go = true;
					if (strstr ( $careName, 'caregiver' )) {
						$item = str_replace ( 'admission_caregiver', '', $careName );
						if (( int ) $_POST [$item . '_mode'] > 1) {
							$go = false;
						}
					}
					if ($go) {
						unset ( $careArr ['id'] );
						$res = db_insertArray ( 'admission_caregivers', $careArr );
						$this->$gvar = $careArr ['id'];
						$pureTable [$gvar] = $careArr ['id'];
					}
				}
			}
			$this->correctLink ( $pureTable );
			$ret = db_updateArray ( 'admission_info', $pureTable, 'admission_id' );
			/*$sql = 'update clients set client_gender="' . $this->admission_gender . '",
							client_dob="' . $this->admission_dob . '"
					where client_id="' . $this->admission_client_id . '"';
			$cc = db_exec ( $sql );*/

		} else {
			foreach ( $caregivers as $careName => $careArr ) {
				$gvar = 'admission_' . $careName;
				$gname = $gvar . '_fname';
				if ($this->$gname != '') {
					$res = db_insertArray ( 'admission_caregivers', $careArr );
					$this->$gvar = $careArr['id'];
					$pureTable [$gvar] = $careArr['id'];
				}
			}

			$this->_action = 'added';
			//$ret = db_insertObject( 'admission_info', $this, 'admission_id' );
			$this->correctLink ( $pureTable );
			$ret = db_insertArray ( 'admission_info', $pureTable );
			if ($ret) {
				$this->admission_id = $pureTable ['id'];
				//client_gender="' . $this->admission_gender . '"
				/*$sql = 'update clients set
					client_dob="' . $this->admission_dob . '"
					where client_id="' . $this->admission_client_id . '"';
				db_exec ( $sql );*/
			}
			addHistory ( 'admission_info', $this->medical_id, 'add', $this->admission_id );

		}

		if (! $ret) {
			return get_class ( $this ) . "::store failed <br />" . db_error ();
		} else {
			return NULL;
		}
	}

	function correctLink(&$pureTable) {
		$pres = array ('pri_mode', 'sec_mode' );
		$links = array ('2' => 'mother', '3' => 'father' );
		foreach ( $pres as $cars ) {
			if (isset ( $_POST [$cars] ) && ( int ) $_POST [$cars] > 1) {
				$exact = str_replace ( '_mode', '', $cars );
				$oldvalue = $pureTable ['admission_caregiver_' . $exact];
				$pureTable ['admission_caregiver_' . $exact] = $pureTable ['admission_' . $links [$_POST [$cars]]];
				if (! is_null ( $oldvalue ) && ( int ) $oldvalue > 0) {
					$sql = 'delete from admission_caregivers where id="' . $oldvalue . '" limit 1';
					my_query ( $sql );
				}
			}
		}
	}

	function getCare($inject = false) {
		global $caregivers, $tablePre, $careofs;
		$caregivers = array ('father' => array ('title' => 'Father' ), 'mother' => array ('title' => 'Mother' ), 'caregiver_pri' => array ('title' => 'Primary Caregiver' ), 'caregiver_sec' => array ('title' => 'Secondary Caregiver' ) );
		$tablePre = 'admission_';
		$careofs = 0;
		foreach ( $caregivers as $careName => $carearr ) {
			$gname = $tablePre . $careName;
			if ($this->$gname !== null && $this->$gname > 0) {
				$sql = 'select * from admission_caregivers where id="' . $this->$gname . '"';
				$res = my_query ( $sql );
				if ($res) {
					$caregivers [$careName] ['data'] = my_fetch_assoc ( $res );
					if ($inject === true) {
						foreach ( $caregivers [$careName] ['data'] as $vname => $vval ) {
							$this->{$tablePre . $careName . '_' . $vname} = $vval;
						}
					}
					$careofs ++;
				}
			}
		}
	}

	function guessPerson($careItem) {
		$field = 'admission_caregiver_' . $careItem;
		$res = 1;
		if ($this->$field !== null) {
			if ($this->$field == $this->admission_father) {
				$res = 3;
			} elseif ($this->$field == $this->admission_mother) {
				$res = 2;
			}
		}
		return $res;
	}
}
?>
