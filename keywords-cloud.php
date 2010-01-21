<?php
/* Plugin Name: Keywords Cloud for WordPress
Plugin URI: http://www.romeloft.com/info/keywords-cloud-plugin-for-wordpress/
Description: Discover which keywords your site visitors are googling to reach your site. Build automatically a tag cloud based on realtime usage data, not on the tags that you  THINK are appropriate. Fully customize the tag cloud links and keywords. Ban inapproriate keywords with one click. INSTRUCTIONS: Just activate the plugin, place one of the three available Keyword widgets in your sidebar, and wait some hours to see the first keywords
Plugin Version: 0.5
Plugin Author: RomeLoft
Author URI: http://www.romeloft.com/
 */ 
register_activation_hook(__FILE__,'keyw_table_install');
//register_deactivation_hook(__FILE__,'kill_keywords_table');
add_action("plugins_loaded", "KeywordsWidgetInit");
add_action('wp_head', 'Store_keywords');
add_action('wp_head','Admin_cloud');


add_option('KC_heading', 'Keywords:');
add_option('KC_limit', '25');
add_option('KC_offset', '0.5');
  
 
 
///parameters
//CUSTOMIZE HERE IF YOU WANT TO CHANGE BASIC KEYWORD CLOUD SETTINGS
update_option('KC_heading', 'Keywords:');
update_option('KC_limit', '25');
update_option('KC_offset', '0.5');
 



 function EditMessage1()
 {echo "<div style='font-size:0.9em; padding:3px;background: #fffed4 none repeat scroll 0% 0%;   '><b>Click  on a keyword</b> to edit it's properties. Use wisely!</div> ";}


   
function kill_keywords_table ()
{    global $wpdb;

$table_name = $wpdb->prefix . "kc_keywords";
$query_drop = "DROP TABLE   " . $table_name;

  $wpdb->query($query_drop); 
}


function keyw_table_install () {
   global $wpdb;
   $table_name = $wpdb->prefix . "kc_keywords";

   if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
      
      $sql = "CREATE TABLE " . $table_name . " (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	    occurrences mediumint(9),
	  
	  keyword tinytext NOT NULL,
	 ipaddress VARCHAR(255)   NULL,
	 follow int(1)   NULL,
	  url VARCHAR(255) NOT NULL,
	  UNIQUE KEY id (id)
	);";

      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sql);

    
   }
}








function KeywordsWidgetInit()
{
  register_sidebar_widget(__('Keywords widget Navigational'), 'Print_keywords_widget_1');
  register_sidebar_widget(__('Keywords widget This Pages\'s keywords'), 'Print_keywords_widget_2');  
    register_sidebar_widget(__('Keywords widget All Keywords'), 'Print_keywords_widget_3');          
}









function random_color(){
    mt_srand((double)microtime()*1000000);
    $c = '';
    while(strlen($c)<6){
        $c .= sprintf("%02X", mt_rand(0, 215));
    }
    return $c;
}





function Print_keywords_widget(  )
{
global $_SERVER, $wpdb,$user_ID, $filter_results, $show_only_different_urls,$_GET;

if ($_GET[preview]=='true') return; //this way clouds are not shown when previewing an article

 $updated_kw=0;
$table_name = $wpdb->prefix . "kc_keywords";
$current_url="http://" .$_SERVER['HTTP_HOST']. $_SERVER['REQUEST_URI'];
$current_url=explode('?',$current_url);
$current_url=$current_url[0]; 
 
 
 ///admin links 
if( $user_ID     )
 	{ 
 	?><div align='right'>
 	
 	<a href='<?php echo $current_url.'?act=kwmode'; ?>' style='font-size:0.6em;text-decoration:underline;'  >edit keywords</a> &nbsp; 
 	<a href='<?php echo $current_url.'?act=killmode'; ?>' style='font-size:0.6em;text-decoration:underline;'  >ban</a>
 	
 	</div> <?php
 	
 	} 

 
 if( $user_ID )  
 	{
	if ($_GET[act]=='dofollow') echo "<div style='font-size:0.9em; padding:3px;background: #f8ffa9 none repeat scroll 0% 0%;   '><b>Click  to toggle nofollow status.</b> Red means Nofollow. If you love your blog, make the important keywords turn green, and minor keywords turn red! Green keywords live eternally.</div>";
 	
	if ($_GET[act]=='killmode') echo "<div style='font-size:0.9em; padding:3px;background: #ffa9a9 none repeat scroll 0% 0%;   '><b>Click  to eliminate keywords.</b> No confirmation is asked, so be careful. Shoot all the unrelevant keywords! They will be forever banned.</div> ";
 	
	if ($_GET[act]=='kwmode') {
 	 		
 	 			if (!isset($_GET[keyword_id])) EditMessage1();
 	 			
 	 			
 	 			else
 	 		  {
 	 			 	
 	 	EditMessage1();
 	 			 	
 	  ///if form is submitted, save changes
 	 			 
  if (isset($_POST[SaveKwValues])) 
 	 	 {  
 	 			 
 	 			   	$insert = "UPDATE  " . $table_name .
            " SET occurrences= " .$_POST[occurrences].
           ", url= '" .$_POST[url]."'".
                ", keyword= '" .$_POST[keyword]."'".
             ", follow= '" . $_POST[follow]."'".
           " WHERE id='".$_GET[keyword_id]  ."' limit 1";
 		$wpdb->query($insert);
 		$updated_kw=1;
 		  }
 	 			 
 	 			 
 	 			 //end submit handling	
 	 			 	
 	 			 	
 	 			 	
 	 			 	 ?>
 	 			 
 	 			 
<!-- begin form --> 	 			 
 	 			 <div style='font-size:0.9em; padding:3px;background: #fdfc93 none repeat scroll 0% 0%;'>
 	 			 
 	 			 <form action='#' method='POST'>
 	 			 
 Keyword:	<br /> 			 
<input type="text" name="keyword" value="<?php echo $wpdb->get_var("select keyword from $table_name where id=".$_GET[keyword_id]); ?>" />
<br />
Occurrences (size): <br />
<input type="number" name="occurrences" value="<?php echo $wpdb->get_var("select occurrences from $table_name where id=".$_GET[keyword_id]); ?>" size='5' />
<br />
Target URL: <br />
<input type="text" name="url" value="<?php echo $wpdb->get_var("select url from $table_name where id=".$_GET[keyword_id]); ?>" /><br /> 	
Follow link / Bless Keyword<br />
<select name="follow"  <?php $follow_value= $wpdb->get_var("select follow from $table_name where id=".$_GET[keyword_id]); ?>  />
  <option <?php if ($follow_value==1) echo " selected "; ?> value ="1">Yes</option>
  <option <?php if ($follow_value!=1) echo " selected "; ?> value ="0">No</option>
</select>
<br />
<input type="submit" name='SaveKwValues' value="Save" />
&nbsp; &nbsp; <?php if ($updated_kw==1) echo "Done!"; ?>
</form> 

<br />
<b>Hate that keyword? </b><br />
<a href='<?php echo $current_url.'?act=killmode&keyword_id='.$_GET[keyword_id]; ?>'>Ban forever</a> and switch to keyword ban mode 
 	 			 
 	 			 
 	 			 </div><br />

<!--end form -->

<?php }
 	 			 }
 	
 	 
 	

}
//end if user_id



if ($filter_results>0)
   {
	
	if ($show_only_different_urls>0)  $comp_symbol="!="; else $comp_symbol="=";
	$where_add=" where url!='' and url".$comp_symbol."'".$current_url."' ";
	} 
				else $where_add=" where  url!='' ";
		
		
//find top keyword's frequency in order to setup KW tag max size
$max_number_of_occurrences = $wpdb->get_var("select MAX(occurrences) from " . $table_name. $where_add   );

//keyword official sql query
$results = $wpdb->get_results("select * from " . $table_name.$where_add ."  and occurrences>=1 order by follow desc, occurrences desc, ID desc LIMIT 0,".get_option('KC_limit')  );
     
      

if   ($results)	 echo get_option('KC_heading')." ";
     		    
//optional keyword randomizer: uncomment the following line if desidered
 //if ($_GET[act]!='dofollow') ksort ($results);
      
      $count_kws=0;
foreach ($results as $result) 
      
      {  ///BEGIN KW LOOP
   $count_kws++;
      	 $color=random_color();
   
      	$tagsize= ($result->occurrences)/($max_number_of_occurrences);
 	
      	$tagsize=$tagsize+get_option('KC_offset');
      	 
      	
//follow/nofollow tags can have a different styling if you need to: just touch start/end variables 

	if ($result->follow==1)
      	 {
      		$nofollow_tag="";
      		 $tagsize=$tagsize+0.1; ///this makes FOLLOW tags a bit bigger
      		$start='';
      		 $end='';
      		if (($_GET[act]=='killmode') or ($_GET[act]=='kwmode'))
      						 $color='34dd00';
      		} 
      	
      			else {
      				$nofollow_tag="rel='nofollow'";
      				$start='';
      				$end='';
      			 if (($_GET[act]=='killmode') or ($_GET[act]=='kwmode')) 
      			 						$color='e50000';
      				}

 
if ( $_GET[keyword_id]==$result->id) 
		{ $start='<span style="background:yellow;">';$end='</span>';	}
		
		
$target_url=$result->url;

if ($_GET[act]=='dofollow') $target_url=  $current_page_url.'?act=dofollow&keyword_id='. ($result->id);

if ($_GET[act]=='killmode') $target_url=  $current_page_url.'?act=killmode&keyword_id='. ($result->id);

if ($_GET[act]=='kwmode') $target_url= $current_page_url.'?act=kwmode&keyword_id='. ($result->id);



/////begin KW link

if ("http://" .$_SERVER['HTTP_HOST']. $_SERVER['REQUEST_URI']!=$target_url) $link_it=TRUE; else $link_it=FALSE;//this is needed not to link the current page, comment if you need to.


if (isset($_GET[act]))	$link_it=TRUE;//this is needed for the admin functionality
 
 
  
 //$link_title="Read the post: ". $wpdb->get_var("select  post_title from ".$wpdb->wp_posts." where guid='".$target_url."'  "); 
 
 ///TAG STYLING
if ($link_it)
	 	  echo   "<a ".$nofollow_tag." style='text-decoration:underline; color:#".$color.";font-size: ".$tagsize."em;' href='".$target_url."' title='$link_title'>"; 
			else echo "<span style='text-decoration:none; color:#".$color.";font-size: ".$tagsize."em;'>";

 echo $start.htmlentities($result->keyword).$end;
 
if ($link_it)  echo "</a>"; else echo "</span>";
 echo "&nbsp;<span style='font-size:0.6em;line-height:90%;color:#$color'><sup>$result->occurrences</sup></span> ";

///////end kw link 	




 } //END KW LOOP


 	
 if ($count_kws>20) echo "<span style='font-size:9px; color:#ccc;'>Keyword Cloud by <a href='http://www.romeloft.com' style='color:#ccc;margin:0;'>Rome Loft</a></span>";     
      
 
 

 		
//if(  $results)	  echo " <br />";
 	 
 	 
}
///end function













function Print_keywords_widget_1($args)
{ global $filter_results, $show_only_different_urls,$_GET;
extract($args); // extracts before_widget,before_title,after_title,after_widget
echo $before_widget;

$filter_results=1; $show_only_different_urls=1;

Print_keywords_widget();
  
echo $after_widget;  
  
} 
function Print_keywords_widget_2($args)
{  global $filter_results, $show_only_different_urls,$_GET;
extract($args); // extracts before_widget,before_title,after_title,after_widget
echo $before_widget;
$filter_results=1; $show_only_different_urls=0;
  Print_keywords_widget();
  echo $after_widget; 
}

function Print_keywords_widget_3($args)
{  global $filter_results, $show_only_different_urls,$_GET;
extract($args); // extracts before_widget,before_title,after_title,after_widget
echo $before_widget;
$filter_results=0; $show_only_different_urls=0;
  Print_keywords_widget();
  echo $after_widget;
}





function Store_keywords()
{ global $_SERVER;
    global $wpdb;

$parse = parse_url($_SERVER['HTTP_REFERER']);
$se = $parse["host"];
$raw_var = explode("&", $parse["query"] );
foreach ($raw_var as $one_var) {
    $raw = explode("=", $one_var);
    $var[$raw[0]] = urldecode ($raw[1]);
}
$se = explode (".", $se);
switch ($se[1]) {
    case 'yahoo':
        $keywords = $var['p'];
        break;
    case 'aol':
        $keywords = $var['query'];
        break;
    default:
        $keywords = $var['q'];
}
unset($parse, $se, $raw_var, $one_var, $var);


 $table_name = $wpdb->prefix . "kc_keywords";

$keywords=mysql_real_escape_string($keywords);
//echo $keywords;

if (strlen($keywords)>3)

{ 
 
$keyword_already_present = $wpdb->get_var("select COUNT(*)   from " . $table_name." WHERE keyword='".$keywords ."'");

$keyword_from_same_ip = $wpdb->get_var("select COUNT(*)   from " . $table_name." WHERE keyword='".$keywords ."' and ipaddress='".$_SERVER['REMOTE_ADDR']  ."'");

    
if ($keyword_from_same_ip<1)     
 if( $keyword_already_present>0) 
      
       { $number_of_occurrences = $wpdb->get_var("select occurrences   from " . $table_name." WHERE keyword='".$keywords ."'");
       	
       	$insert = "UPDATE  " . $table_name .
            " SET occurrences= " .($number_of_occurrences+1).
           ", ipaddress= '" .$_SERVER['REMOTE_ADDR']."'".
           " WHERE keyword='".$keywords ."'";
 		$wpdb->query($insert);
 		 }

      
      else
      { $insert = "INSERT INTO " . $table_name .     " (keyword, url,occurrences,ipaddress,follow)  VALUES ('" . $keywords . "','http://" .$_SERVER['HTTP_HOST']. $_SERVER['REQUEST_URI'] .   "',1,'".$_SERVER['REMOTE_ADDR']."',1)";
 		$wpdb->query($insert); 
      }
      
} //fine if strlen


 
} ///fine funzione






function Admin_cloud()
{ global $_SERVER;
    global $wpdb,$_GET;
 


 $table_name = $wpdb->prefix . "keywords";

$keyword_id=$_GET[keyword_id];



 global $user_ID; if( $user_ID )  if ($_GET[act]=='dofollow')

	if (isset($keyword_id)  and ($keyword_id>0)  )
{	   $current_value = $wpdb->get_var("select follow   from " . $table_name." WHERE id=".$keyword_id);
 
if ($current_value==1) $new_value=0; else $new_value=1;
 	$insert = "UPDATE  " . $table_name .
            " SET follow= " .($new_value) ." WHERE id=".$keyword_id;
 		$wpdb->query($insert);

	
}

 
 global $user_ID; if( $user_ID )  if ($_GET[act]=='killmode')

	if (isset($keyword_id)  and ($keyword_id>0)  )
{ $insert = "UPDATE  " . $table_name . " SET occurrences=-999999 WHERE id=".$keyword_id;
 		$wpdb->query($insert); }
 

} //end function



?>