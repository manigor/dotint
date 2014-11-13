<?php /* $Id: index.php,v 1.31.6.2 2006/03/04 07:39:49 gregorerhardt Exp $ */
$AppUI->savePlace();

if (isset( $_GET['orderby'] )) {
    $orderdir = $AppUI->getState( 'ContactIdxOrderDir' ) ? ($AppUI->getState( 'ContactIdxOrderDir' )== 'asc' ? 'desc' : 'asc' ) : 'desc';
	$AppUI->setState( 'ContactIdxOrderBy', $_GET['orderby'] );
    $AppUI->setState( 'ContactIdxOrderDir', $orderdir);
}
$orderby  = $AppUI->getState( 'ContactIdxOrderBy' ) ? $AppUI->getState( 'ContactIdxOrderBy' ) : 'contact_first_name';
$orderdir = $AppUI->getState( 'ContactIdxOrderDir' ) ? $AppUI->getState( 'ContactIdxOrderDir' ) : 'asc';
if (! $canAccess) {
	$AppUI->redirect('m=public&a=access_denied');
}

$perms =& $AppUI->acl();

// To configure an aditional filter to use in the search string
$additional_filter = "";
// retrieve any state parameters
if (isset( $_GET['where'] )) {
	$AppUI->setState( 'ContIdxWhere', $_GET['where'] );
}
if (isset( $_GET["search_string"] )){
	$AppUI->setState ('ContIdxWhere', "%".$_GET['search_string']);
				// Added the first % in order to find instrings also
	$additional_filter = "OR contact_first_name like '%{$_GET['search_string']}%'
	                      OR contact_last_name  like '%{$_GET['search_string']}%'
			      OR company_name       like '%{$_GET['search_string']}%'
			      OR contact_notes      like '%{$_GET['search_string']}%'
			      OR contact_email      like '%{$_GET['search_string']}%'";
}
$where = $AppUI->getState( 'ContIdxWhere' ) ? $AppUI->getState( 'ContIdxWhere' ) : '%';

//$orderby = 'contact_order_by';

// Pull First Letters
$let = ":";
$q  = new DBQuery;
$q->addTable('contacts');
$q->addQuery("DISTINCT UPPER(SUBSTRING(contact_first_name,1,1)) as L");
$q->addWhere("contact_private=0 OR (contact_private=1 AND contact_owner=$AppUI->user_id)
		OR contact_owner IS NULL OR contact_owner = 0");
$q->addWhere('contact_id <> "13"');
$arr = $q->loadList();
foreach( $arr as $L ) {
    $let .= $L['L'];
}

// optional fields shown in the list (could be modified to allow breif and verbose, etc)
$showfields = array(
	// "test" => "concat(contact_first_name,' ',contact_last_name) as test",    why do we want the name repeated?
    "contact_client" => "contact_client",
	"contact_phone" => "contact_phone",
	"contact_email" => "contact_email"
);

require_once $AppUI->getModuleClass('clients');
$client =& new CClient;
$allowedClients = $client->getAllowedSQL($AppUI->user_id);

// assemble the sql statement
$q = new DBQuery;
$q->addQuery('contact_id, contact_order_by');
$q->addQuery($showfields);
$q->addQuery('contact_first_name, contact_last_name, contact_phone');
$q->addTable('contacts', 'a');
$q->leftJoin('clients', 'b', 'a.contact_client = b.client_id');
$q->addWhere("(contact_order_by LIKE '$where%' $additional_filter)");
$q->addWhere("
	(contact_private=0
		OR (contact_private=1 AND contact_owner=$AppUI->user_id)
		OR contact_owner IS NULL OR contact_owner = 0
	)");
if (count($allowedClients)) {
	$client_where = implode(' AND ', $allowedClients);
	$q->addWhere( '( (' . $client_where . ') OR contact_client = 0 )' );
}
$q->addOrder('contact_order_by');
$q->addWhere('contact_id <> "13"');



$sql = $q->prepare();
$q->clear();
$res = db_exec( $sql );
if ($res)
	$rn = db_num_rows( $res );
else {
	echo db_error();
	$rn = 0;
}

$carr[] = array();
$carrWidth = 4;
$carrHeight = 4;

$t = floor( $rn / $carrWidth );
$r = ($rn % $carrWidth);

if ($rn < ($carrWidth * $carrHeight)) {
	for ($y=0; $y < $carrWidth; $y++) {
		$x = 0;
		//if($y<$r)	$x = -1;
		while (($x<$carrHeight) && ($row = db_fetch_assoc( $res ))){
			$carr[$y][] = $row;
			$x++;
		}
	}
} else {
	for ($y=0; $y < $carrWidth; $y++) {
		$x = 0;
		if($y<$r)	$x = -1;
		while(($x<$t) && ($row = db_fetch_assoc( $res ))){
			$carr[$y][] = $row;
			$x++;
		}
	}
}

$tdw = floor( 100 / $carrWidth );

/**
* Contact search form
*/
 // Let's remove the first '%' that we previously added to ContIdxWhere
$default_search_string = dPformSafe(substr($AppUI->getState( 'ContIdxWhere' ), 1, strlen($AppUI->getState( 'ContIdxWhere' ))), true);

/*$form = "<form action='./index.php' method='get'>".$AppUI->_('Search for')."
           <input type='text' name='search_string' value='$default_search_string' />
		   <input type='hidden' name='m' value='contacts' />
		   <input type='submit' value='>' />
		   <a href='./index.php?m=contacts&amp;search_string='>".$AppUI->_('Reset search')."</a>
		 </form>";
		 
$form = "<form name='searchform' action='?m=search' method='post'>
                        <table>
                         <tr>
                           <td>						   
                              <input class = 'text' type='text' name ='search_string' value='$search_string' />
						   </td>
						   <td>
							  <input type='submit' value='" .$AppUI->_( 'search' )."' class='button' />
						   </td>
						  </tr>
                         </table>
                        </form>";		 
// En of contact search form
/*
$a2z = "\n<table cellpadding=\"2\" cellspacing=\"1\" border=\"0\">";
$a2z .= "\n<tr>";
$a2z .= "<td width='100%' align='right'>" . $AppUI->_('Show'). ": </td>";
$a2z .= '<td><a href="./index.php?m=contacts&where=0">' . $AppUI->_('All') . '</a></td>';
for ($c=65; $c < 91; $c++) {
	$cu = chr( $c );
	$cell = strpos($let, "$cu") > 0 ?
		"<a href=\"?m=contacts&where=$cu\">$cu</a>" :
		"<font color=\"#999999\">$cu</font>";
	$a2z .= "\n\t<td>$cell</td>";
}
$a2z .= "\n</tr>\n<tr><td colspan='28'>$form</td></tr></table>";

*/
// setup the title block

// what purpose is the next line for? Commented out by gregorerhardt, Bug #892912
// $contact_id = $carr[$z][$x]["contact_id"];
/*
$titleBlock = new CTitleBlock( 'Staff', NULL, $m, "$m.$a" );
$titleBlock->addCell( $a2z );
if ($canEdit) 
{
	$titleBlock->addCell(
		'<input type="submit" class="button" value="'.$AppUI->_('new staff').'">', '',
		'<form action="?m=contacts&a=addedit" method="post">', '</form>'
	);
	$titleBlock->addCrumbRight(
		'<a href="./index.php?m=contacts&a=csvexport&suppressHeaders=true">' . $AppUI->_('CSV Download'). "</a> | " .
		'<a href="./index.php?m=contacts&a=vcardimport&dialog=0">' . $AppUI->_('Import vCard') . '</a>'
	);
}
$titleBlock->show();*/

// TODO: Check to see that the Edit function is separated.
//pager settings
$page = dPgetParam($_GET, 'page', 1);
$limit = intval($dPconfig['max_limit']);

$tabBox = new CTabBox ("?m=contacts", dPgetConfig('root_dir')."/modules/contacts/");
$tabBox->add('vw_contacts_new', 'Staff' );
$tabBox->show();


?>
