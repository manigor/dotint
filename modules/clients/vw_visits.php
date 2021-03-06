<?php /* CALENDAR $Id: vw_day_tasks.php,v 1.6 2004/10/21 22:47:07 jcgonz Exp $ */
require_once( $AppUI->getModuleClass( 'tasks' ) );
global $this_day, $first_time, $last_time, $company_id, $m, $a;

$links = array();
// assemble the links for the tasks
require_once( dPgetConfig('root_dir' )."/modules/calendar/links_tasks.php" );
getTaskLinks( $first_time, $last_time, $links, 100, $company_id );

$s = '';
$dayStamp = $this_day->format( FMT_TIMESTAMP_DATE );

echo '<table cellspacing="1" cellpadding="2" border="0" width="100%" class="tbl">';

if (isset( $links[$dayStamp] )) {
	foreach ($links[$dayStamp] as $e) {
		$href = isset($e['href']) ? $e['href'] : null;
		$alt = isset($e['alt']) ? $e['alt'] : null;

		$s .= "<tr><td>";
		$s .= $href ? "<a href=\"$href\" class=\"event\" title=\"$alt\">" : '';
		$s .= "{$e['text']}";
		$s .= $href ? '</a>' : '';
		$s .= '</td></tr>';
	}
}
echo $s;

echo '</table>';

$min_view = 1;
include dPgetConfig( 'root_dir' ).'/modules/tasks/todo.php';
?>