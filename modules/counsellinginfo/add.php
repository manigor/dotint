<?php
require_once ($AppUI->getModuleClass('clients'));

$client_id = intval( dPgetParam( $_GET, "client_id", 0 ));
$client_type = intval( dPgetParam( $_GET, "client_type", 3 ));


if 	($contact_unique_update == 0)
  $contact_unique_update = uniqid("");
	
$perms = & $AppUI->acl();

if ($client_id)
  $canEdit = $perms->checkModuleItem( $m, "edit", $client_id );
else
  $canEdit = $perms->checkModule($m, "add");

if (!$canEdit)
{
  $AppUI->redirect("m=public&a=access_denied");
}



$q  = new DBQuery;
$q->addTable('clients');
$q->addQuery('clients.*');
$q->addWhere('clients.client_id = '.$client_id);
$sql = $q->prepare();
$q->clear();

$obj = new CClient();

if (!db_loadObject($sql, $obj) && ($client_id > 0))
{
  	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}
//load all sales reps
$q  = new DBQuery;
$q->addTable('contacts', 'c');
$q->addQuery('c.contact_id');
$q->addQuery('CONCAT_WS(", ",c.contact_last_name,c.contact_first_name)');
$q->innerJoin('client_contacts', 'b', 'b.client_contacts_contact_id = c.contact_id');
$q->addWhere('b.client_contacts_contact_type = 13');
$q->addOrder('c.contact_first_name');

//load contacts
$chw_contacts = arrayMerge(array(0=> '-Select CHW -'),$q->loadHashList());
$q->clear();
$q->addTable('contacts', 'c');
$q->addQuery('c.contact_id');
$q->addQuery('CONCAT_WS(", ",c.contact_last_name,c.contact_first_name)');
$q->innerJoin('client_contacts', 'b', 'b.client_contacts_contact_id = c.contact_id');
$q->addWhere('b.client_contacts_contact_type = 14');
$q->addOrder('c.contact_first_name');


$shw_contacts = arrayMerge(array(0=> '-Select SHW -'),$q->loadHashList());

//load status stuff
$status = arrayMerge(array(-1=>'-Select Current Client Status-'), dPgetSysVal('ClientStatus'));
//load priority stuff
$priority = arrayMerge(array(-1=>'-Select Current Client Priority-'), dPgetSysVal('ClientPriority'));

//load cities
$citiesArray = arrayMerge(array(-1=>'-Select City-'), dPgetSysVal('ClientCities'));
//load clinics

$q->clear();
$q->addTable('clinics', 'c');
$q->addQuery('c.clinic_id, c.clinic_name');
$q->addOrder('c.clinic_name');

$clinics = arrayMerge(array(0=> '-Select Center -'),$q->loadHashList());
/*$q->clear();
$q->addTable('client_status');
$q->addQuery('client_status_id');
$q->addQuery('client_status_desc');
$q->addOrder('client_status_desc');


//load priorities
$q->clear();
$q->addTable('client_priority');
$q->addQuery('client_priority_id');
$q->addQuery('client_priority_desc');
$q->addOrder('client_priority_id');
$priority = arrayMerge(array(0=>'-Select Client Class-'), $q->loadHashList());
//load company types
$types = dPgetSysVal('ClientStatus');
$type = $types[$client_type];

//load cities
$citiesArray = arrayMerge(array(0=>'-Select City-'), dPgetSysVal('ClientCities'));
*/
//$ttl = "$type :: ";
$ttl .= $client_id > 0 ? "Edit Intake & PCR" : "New Intake & PCR";

$titleBlock = new CTitleBlock( $ttl, '', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=clients", "Clients" );
if ($client_id != 0)
  $titleBlock->addCrumb( "?m=clients&a=view&client_id=$client_id", "View" );
$titleBlock->show();

?>
<script language="javascript">
var selected_contacts_id = "<?php echo $obj->main_contacts; ?>";
var contact_unique_update = "<?php echo $contact_unique_update; ?>";
var client_id = '<?php echo $obj->client_id;?>';
var client_name_msg = "<?php echo $AppUI->_('Please enter a name for the client');?>";


var calendarField = '';
var calWin = null;


function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.editFrm.log_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=251, height=220, scollbars=false, resizable' );
}

function setCalendar( idate, fdate ) 
{
	fld_date = eval( 'document.editFrm.log_' + calendarField );
	fld_fdate = eval( 'document.editFrm.client_' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;
}

function checkDate()
{
           if (document.frmDate.log_start_date.value == "" || document.frmDate.log_end_date.value== ""){
                alert("<?php echo $AppUI->_('You must fill fields', UI_OUTPUT_JS) ?>");
                return false;
           } 
           return true;
}

function popClinic() {
//        window.open('./index.php?m=public&a=selector&dialog=1&callback=setCompany&table=companies', 'company','left=50,top=50,height=250,width=400,resizable');
	window.open("./index.php?m=clients&a=select_client_clinic&dialog=1&table_name=clinics&clinic_id=<?php echo $clinic_detail['clinic_id'];?>", "clinic", "left=50,top=50,height=250,width=400,resizable");
}

function setClinic( key, val ){
	var f = document.editFrm;
 	if (val != '') {
    	f.contact_company.value = key;
			f.client_clinic_name.value = val;
    	if ( window.clinic_id != key )
		{
    		f.contact_department.value = "";
				f.contact_department_name.value = "";
    	}
    	window.clinic_id = key;
    	window.clinic_value = val;
    }
}
</script>
<?php
$date_reg = date("Y-m-d");
$entry_date = intval( $date_reg) ? new CDate( dPgetParam($_REQUEST, "client_entry_date", date("Y-m-d") ) ) : null;
$df = $AppUI->getPref('SHDATEFORMAT');

?>
 <form name="editFrm" action="?m=clients&client_id=<?php echo $client_id; ?>" method="post">
   <input type="hidden" name="dosql" value="do_newclient_aed" />
   <input type="hidden" name="insert_id" value="<?php echo uniqid(""); ?>" />
   <input type="hidden" name="client[client_id]" value="<?php echo $client_id; ?>" />
   <input type="hidden" name="client[client_type]" value="<?php echo $client_type; ?>" />
   <input type="hidden" name="client[client_date_entered]" value="<?php echo date('Y-m-d'); ?>" />
   <table cellspacing="1" cellpadding="1" border="0" width="100%" class="std">
   <td align="left">
	<tr>
	  <td>
		<table>
			<tr>
			 <td align="right" nowrap><?php echo $AppUI->_('Admission No');?>: </td>
			  <td>
					<input type="text" class="text" name="client[client_adm_no]" id="client_adm_no" value="<?php echo dPformSafe(@$obj->client_adm_no);?>"
					size="20" maxlength="255" /> (<?php echo $AppUI->_('required');?>)
			</td>
		   </tr>
		   <tr>
			 <td align="right" nowrap><?php echo $AppUI->_('Date of Admission');?>: </td>
			<td>
				<input type="hidden" name="log_entry_date" value="<?php echo $entry_date ? $entry_date->format( FMT_TIMESTAMP_DATE ) : "" ;?>" />
				<input type="text" name="client_entry_date" value="<?php echo $entry_date ? $entry_date->format( $df ) : "" ;?>" class="text" readonly disabled="disabled" />
				<a href="#" onClick="popCalendar('entry_date')">
				<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" ></a>
			</td>
		   </tr>
		   <tr>
			 <td align="right"><?php echo $AppUI->_('First Name');?>: </td>
			  <td>
					<input type="text" class="text" name="client[client_first_name]" id="client_first_name" value="<?php echo dPformSafe(@$obj->client_first_name);?>"
					size="50" maxlength="255" /> 
			</td>
		   </tr>
		   <tr>
			 <td align="right"><?php echo $AppUI->_('Other Name(s)');?>: </td>
			  <td>
					<input type="text" class="text" name="client[client_other_name]" id="client_other_name" value="<?php echo dPformSafe(@$obj->client_other_name);?>"
					size="50" maxlength="255" /> 
			</td>
		   </tr>
		   <tr>
			 <td align="right"><?php echo $AppUI->_('Last Name');?>: </td>
			  <td>
					<input type="text" class="text" name="client[client_last_name]" id="client_last_name" value="<?php echo dPformSafe(@$obj->client_last_name);?>"
					size="50" maxlength="255" /> 
			</td>
		   </tr>
        <tr>
			<td align="right"><?php echo $AppUI->_('Center');?>: </td>
			<td>
				<?php echo arraySelect( $clinics, 'client[client_clinic]', 'size="1" class="text"', @$obj->client_clinic ? $obj->client_clinic:0); ?>        
			</td>
		</tr>		
	
        <tr>
			<td align="right"><?php echo $AppUI->_('Current Status');?>: </td>
			<td>
				<?php echo arraySelect( $status, 'client[client_status]', 'size="1" class="text"', @$obj->client_status ? $obj->client_status:0); ?>        
			</td>
		</tr>
	
   </table>
   </td>
	  <td align="right" valign=top><?php echo $AppUI->_('Notes on client');?>:</td>
	 <td align="left" valign="top">
		<textarea cols="30" rows="5" class="textarea" name="client[client_description]"><?php echo @$obj->client_description;?></textarea>
	</td>
   </tr>
  </table>
   <tr>
   		<td><input type="button" value="<?php echo $AppUI->_('back');?>" class="button" onClick="javascript:history.back(-1);" /></td>
		<td colspan="5" align="right"><input type="button" name="btnFuseAction" value="<?php echo $AppUI->_('submit');?>" class="button" onClick="javascript:submitIt(document.editFrm)" /></td>
   </tr>

<?php
	if (isset($_GET['tab']))
		$AppUI->setState('ClientAeTabIdx', dPgetParam($_GET, 'tab', 0));

		$moddir = $dPconfig['root_dir'] . '/modules/clients/';	
	
	$tab = $AppUI->getState('ClientAeTabIdx', 0);
	$tabBox =& new CTabBox("?m=clients&a=addedit&client_id=$client_id", "", $tab, "");
	$tabBox->add($moddir . "ae_counselling", "Intake & PCR");
	$tabBox->add($moddir . "ae_contacts", "Contacts");
	$tabBox->loadExtras('clients', 'addedit');
	$tabBox->show('', true);
?>
</td>
</table>
</form>