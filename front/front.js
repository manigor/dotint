/**
 * Created by JetBrains PhpStorm.
 * User: igor
 * Date: 25.01.12
 * Time: 22:02
 * To change this template use File | Settings | File Templates.
 */
$j(document).ready(function(){
	$j(".top_list > li").click(function(){
		var centr = $j(this).attr("data-cval");
		if(centr){
			$j("#ctxt").empty().load(centr+".html");
		}
	});	
});