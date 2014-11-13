<?php
##
##	Ticketsmith sql handler
##

$name = dPgetParam($_POST, 'name', '');
$email = dPgetParam($_POST, 'email', '');
$subject = dPgetParam($_POST, 'subject', '');
$priority = dPgetParam($_POST, 'priority', '');
$description = dPgetParam($_POST, 'description', '');
//$description = db_escape($description);

$author = $name . " <" . $email . ">";
$tsql =
"INSERT INTO tickets (author,subject,priority,body,timestamp,type) ".
"VALUES('$author','$subject','$priority','$description',UNIX_TIMESTAMP(),'Open')";

$rc = my_query($tsql);

if (!my_errno()) {
	$AppUI->setMsg( my_error() );
	// add code to mail to ticket master
} else {
	$AppUI->setMsg( "Ticket added" );
}
$AppUI->redirect( "m=ticketsmith" );
?>
