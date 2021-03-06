<?php /* COUNSELLING WORK $Id: do_counsellingwork_aed.php,v 1.9 2005/04/26 06:55:42 ajdonnison Exp $ */
$del = dPgetParam( $_POST, 'del', 0 );
$obj = new CCounsellingWork();
$msg = '';

if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}

require_once("./classes/CustomFields.class.php");


// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'Counselling Log Entry' );
if ($del) {
	if (!$obj->canDelete( $msg )) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	}
	if (($msg = $obj->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( 'deleted', UI_MSG_ALERT, true );
		$AppUI->redirect( 'm=counsellingwork' );
	}
} 
else 
{
	if (($msg = $obj->store())) 
	{
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} 
	else 
	{
 		$custom_fields = New CustomFields( $m, 'addedit', $obj->counselling_id, "edit" );
 		$custom_fields->bind( $_POST );
 		$sql = $custom_fields->store( $obj->counselling_id ); // Store Custom Fields
		$AppUI->setMsg( @$_POST['social_id'] ? 'updated' : 'added', UI_MSG_OK, true );
	}
	$AppUI->redirect();
}
?>
