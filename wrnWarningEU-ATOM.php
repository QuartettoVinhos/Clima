<?php  $scrpt_vrsn_dt  = 'wrnWarningEU-ATOM.php|01|2023-04-19|';  # version 2  ATOM feed # release 2012_lts
# Display a list of warnings from  metealarm.eu 
# used in Advisory box top left                         https://edrop.zamg.ac.at/owncloud/index.php/s/j3axFZiMiKwmcBw#pdfviewer
# https://feeds.meteoalarm.org/feeds/meteoalarm-legacy-atom-slug
#-----------------------------------------------
#         PWS-Dashboard - Updates and support by 
#     Wim van der Kuil https://pwsdashboard.com/
#-----------------------------------------------
#       display source of script if requested so
#-----------------------------------------------
#
# DO NOT COPY THIS SCRIPT or reditribute
#
# Script snippets used from other developer and
# those are licensed for use in PWS_Dashboard 
#
# DO NOT COPY THIS SCRIPT or reditribute
#
#-----------------------------------------------
if (isset($_REQUEST['print']) && strtolower($_REQUEST['print']) == 'print' ) {
   $filenameReal = __FILE__;  
   $download_size = filesize($filenameReal);
   header('Pragma: public');
   header('Cache-Control: private');
   header('Cache-Control: no-cache, must-revalidate');
   header('Content-type: text/plain; charset=UTF-8');
   header('Accept-Ranges: bytes');
   header("Content-Length: $download_size");
   header('Connection: close');
   readfile($filenameReal);
   exit;}
# -------------------save list of loaded scrips;
if (!isset ($stck_lst) ) {$stck_lst = '';}
$stck_lst      .= basename(__FILE__).'('.__LINE__.') loaded  =>'.$scrpt_vrsn_dt.PHP_EOL;       // save list of loaded scrips;
#
# -------------load weatherdata and all settings 
$scrpt          = 'PWS_livedata.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
#
#  ------------------------------------ settings 
$cache_max_age  = 900;
$detail_page    = true;
$detail_page_url= './index.php?frame=weatheralarms';
$now            = time();                // valid warnings this period
$future         = $now + 36*3600;       // at least start before
$image_url      = './wrnImagesLarge/aw##.jpg';
#
#  --------------------------------- test values 
#$alarm_area      = 'DE015,DE029,DE052'; #'ES852,ES853'; #'HR803'; #
#$cache_max_age  = 900;
#$test_xml       =  './jsondata/meteoalarm-legacy-atom-germany';                         
#$test_xml_dtl   = '';
#$now            = $now - 3*24*3600;
#  --------------------------------- test values 
#
# -------------------------------------- styling
#   
$warncolors     = array();
$warncolors[0]  = '#fff';               
$warncolors[1]  = '#fff';  
$warncolors[2]  = '#FBEA55'; 
$warncolors[3]  = '#F19E39'; 
$warncolors[4]  = '#DD353D'; #'#BB2739'; 
#
$severities     = array ('Minor'=> 2, 'Moderate' => 2,'Severe' => 3, 'Extreme' => 4);
$colors         = array ('Yellow','Orange','Red', 'Amber', 'Moderate', 'Severe', 'Extreme');
#
$warntypes      = array('none_0',            
                        'wind',            'snow-ice',     'thunderstorm', 'fog',        'high-temperature',
                        'low-temperature', 'coastalevent', 'forest-fire]', 'avalanches', 'rain',
                        'none-11',         'flooding',     'rain-flood');
#
# --------------include warnings on every page ?
if ($weatheralarm <> 'europe') 
     {  echo 'error '.__LINE__.PHP_EOL;
        return false;}
#
$ownpagehtml ='<h3>'.lang('No active warnings').' ('.$alarm_area.')</h3>';
#
$countries      = array('AT'  => 'austria',     'BA'  => 'bosnia-herzegovina',                  'BE'  => 'belgium',     'BG' => 'bulgaria',    
                        'CH'  => 'switzerland', 'CY'  => 'cyprus',      'CZ'  => 'czechia',     'DE'  => 'germany',     'DK'  => 'denmark',
                        'EE'  => 'estonia',     'ES'  => 'spain',       'FI'  => 'finland',     'FR'  => 'france',      'GR'  => 'greece',
                        'HR'  => 'croatia',     'HU'  => 'hungary',     'IE'  => 'ireland',     'IL'  => 'israel',      'IS' => 'iceland',  
                        'IT'  => 'italy',       'LT'  => 'lithuania',   'LU' => 'luxembourg',   'LV'  => 'latvia',      'MD'  => 'moldova',
                        'ME'  => 'montenegro',  'MK'  => 'north-macedonia',                     'MT'  => 'malta',       'NL'  => 'netherlands', 
                        'NO'  => 'norway',      'PL'  => 'poland',      'PT'  => 'portugal',    'RO'  => 'romania',     'RS'  => 'serbia',      
                        'SE'  => 'sweden',      'SI'  => 'slovenia',    'SK'  => 'slovakia',    'UK' => 'united-kingdom');
#
$lang_warn      = array('en'    => 'english',   'es'    => 'español',   'fr'    => 'français', 
                        'no'    => 'norsk',     'sk'    => 'slovenčina','ne-NL' => 'nederlands','nl-BE' => 'nederlands', 
                        'de-DE' => 'deutsch',   'en-GB' => 'english',   'es-ES' => 'español',
                        'fi-FI' => 'suomi',     'fr-FR' => 'français',  'gr-GR' => 'Ελληνικά',
                        'hr-HR' => 'hrvatski',  'it-IT' => 'italiana',  'lv'    => 'latviešu',   
                        'po-PL' => 'polski',    'pt-PT' => 'português', 'sv-SE' => 'svenska'
                         );  #'' => '', 
#
$warns          = array();
$alarm_areas    = explode (',',$alarm_area); #echo __LINE__.print_r($alarm_areas,true); exit;
$cntrs          = array();
$text   = ' areas=';
foreach ($alarm_areas as $area)
     {  $cntr           = substr($area,0,2);
        $text   .= $area.', ';
        $cntrs[$cntr]   =$cntr;} # echo __LINE__.print_r($alarm_areas,true).print_r($cntrs,true); exit;
$text   = substr($text,0,-2).' countries=';
foreach ($cntrs as $cntr) 
     {  $text   .= $cntr.', ';}
$stck_lst .= basename(__FILE__).' ('.__LINE__.') '.substr($text,0,-2).PHP_EOL;  # echo $stck_lst; exit;
#
$warn_cache     = './jsondata/warningEU2.arr';
#
if (is_file($warn_cache) )
     {  $cache_age      = $now  - filemtime($warn_cache);}
else {  $cache_age      = $now;}
#
if (isset ($test_xml) )
     {  $cache_age      = 999999999999;}
#
#
if (array_key_exists('force',$_REQUEST) && trim($_REQUEST['force']) == 'alarm') { $cache_age = $now;}
#
if ($cache_age > $cache_max_age)
     {  $warns  = array();
        $items  = 0;
        $used   = -1;
        $others = 0;
        $invalid= 0;
        $to_old = 0;
        $to_young = 0;
        $multiple = 0;
        $updated= 0;      
        foreach ($cntrs as $country)     #----- / country
             {  $warn_url	= 'https://feeds.meteoalarm.org/feeds/meteoalarm-legacy-atom-'.$countries[$country]; # echo $warn_url; exit;
                $fl_to_load     = $country.'_warnings';  #echo __LINE__.' $warn_cache='.$warn_cache.'  $warn_url='.$warn_url; exit;
                $load   = warn_curl();  
                if ($load === false) 
                     {  $stck_lst      .= basename(__FILE__).'('.__LINE__.') invalid data load'.PHP_EOL;
                        return false;}
                $json   = json_encode($result);
                unset ($result);
                $array  = json_decode($json,TRUE); #echo $stck_lst.print_r ($array,true); exit;
                unset ($json);
                $valid_data     = true;
                if (!array_key_exists ('entry',$array) || count ($array['entry']) < 1)
                     {  continue;}  // next country
#
                if (!array_key_exists (0 ,$array['entry']))
                     {  $array['entry'][0]   = $array['entry'];} 
#                                               echo '<pre>'.__LINE__.PHP_EOL.$stck_lst.' count='.count($array['entry']).PHP_EOL.print_r($array['entry'],true); exit;
                foreach ($array['entry'] as $entry)
                     {  $items++; 
                        if (!array_key_exists ('message_type',$entry) || ( $entry['message_type'] <> 'Alert' && $entry['message_type'] <> 'Update') )                           # [message_type] => Alert
                              { $invalid++;
                                $stck_lst      .= basename(__FILE__).'('.__LINE__.') not an alert'.PHP_EOL; 
                                continue;}  //                        
                        if (!array_key_exists ('geocode',$entry) || !is_array($entry['geocode']) ) 
                             {  $invalid++;
                                $stck_lst      .= basename(__FILE__).'('.__LINE__.') invalid geocode'.PHP_EOL; 
                                continue;}  // missing region codes, invalid ? 
                        $test   = array();
                        if (!array_key_exists (0 , $entry['geocode'])  ) // check if multiple geaocodes or only 1
                             {  $test[0]= $entry['geocode'];}
                        else {  $test   = $entry['geocode'];}
                        $valid_area     = false;
                        foreach ($test as $area)  // check all geocodes if one is our code(s)
                             {  #echo __LINE__.print_r($test,true); exit;
                                if (array_key_exists ('value',$area) ) 
                                     {  $value  = $area['value'];              // what if they use an array ??????
                                        if (in_array($value,$alarm_areas) ) 
                                             {  $valid_area     = true;
                                                $geocode        = $value;}
                                        }  // eo check one code
                                continue;} // eo check all geocodes
                        if ($valid_area == false) 
                             {  #$stck_lst      .= basename(__FILE__).'('.__LINE__.') other geocode'.PHP_EOL; #.print_r($alarm_areas,true); #exit;
                                $others++;
                                continue;}  // not for our area, skip rest      
        #
                        if (!array_key_exists ('id', $entry) )
                             {  $stck_lst      .= basename(__FILE__).'('.__LINE__.') id not available '.PHP_EOL;
                                $invalid++; 
                                continue;}  // missing link to details, invalid ? 
                        $id     = $entry['id'];  #echo'<pre>'.__LINE__.print_r($entry,true); exit;
        # 
                        if (  !array_key_exists ('expires',  $entry) 
                           || !array_key_exists ('onset',$entry) ) 
                             {  $stck_lst      .= basename(__FILE__).'('.__LINE__.') expires or onset not avaiulable '.PHP_EOL; 
                                $invalid++; 
                                continue;}  // no time slot found
        #                        
                        $expires   = strtotime ($entry['expires']);
                        if ($expires < $now ) 
                             {  $stck_lst      .= basename(__FILE__).'('.__LINE__.') to_old => expires = '.$entry['expires'] .' now ='.gmdate('c',$now).PHP_EOL; #exit;
                                $to_old++; 
                                continue;}
        #
                        $onset   = strtotime ($entry['onset']);  
                        if ($future < $onset ) 
                             {  $stck_lst      .= basename(__FILE__).'('.__LINE__.') to_young = > effective = '.$entry['onset'] .' now ='.gmdate('c',$now).PHP_EOL; #exit;
                                $to_young++; 
                                continue;}
        #
                        if (!array_key_exists ('event',$entry) )
                             {  $event  = 'Unknown';}
                        else {  $event  = $entry['event'];}  
        #
                        $key_use=$geocode.'|'.$event;
                        if (array_key_exists ($key_use,  $warns) )   
                             {  $stck_lst      .= basename(__FILE__).'('.__LINE__.') multiple warnings:=>'.$key_use.' effective = '.$entry['onset'] .' now ='.gmdate('c',$now).PHP_EOL; #exit;
                                $multiple++; 
                                continue;}
                        
                        $stck_lst      .= basename(__FILE__).'('.__LINE__.') $key_use='.$key_use.' effective = '.$entry['onset'] .' expires = '.$entry['expires'] .'  now ='.gmdate('c',$now).PHP_EOL; #exit;
                        $used++;
                        $details= array ('language', 'urgency', 'severity'); # 'onset', ' expires'); #
                        foreach ($details as $key)
                             {  $$key = ''; } 
        #
                        if (array_key_exists ('areaDesc',$entry) )
                             {  $areaDesc = $entry['areaDesc'];}
                        else {  $areaDesc =  '';}
        #               
                        if (!array_key_exists ('severity',$entry) )
                             {  $severity = 'Moderate';}
                        else {  $severity = $entry['severity'];}
        #
                        if (!array_key_exists ('status',$entry) )
                             {  $status = 'Unknown';}
                        else {  $status = $entry['status'];}  
        #                 
                        $warn_url = $id ; #echo __LINE__.' '.$w_href; exit;
                        $test_xml = '';
                        $load   = warn_curl();
                        if ($load === false) 
                             {  $invalid++;
                                continue;   }
                        $json   = json_encode($result);
                        unset ($result);
                        $arr_info= json_decode($json,TRUE); 
                        if (!array_key_exists('info',$arr_info) ) 
                             {  $stck_lst .= basename(__FILE__).' ('.__LINE__.') invalid link-data '.$w_href.PHP_EOL;
                                $invalid++;      #echo __LINE__.' '.$stck_lst.print_r($arr_info,true).$stck_lst; 
                                continue;   }
        #    
                        if (array_key_exists(0,$arr_info['info']) )
                             {  $infos  = $arr_info['info'];}
                        else {  $infos  = array();
                                $infos[0]= $arr_info['info'];}
        #
                        $lng_txt= array ('headline', 'description' ,'instruction', 'event');
                        $lng_arr= array();
        #
                        $cnt_i  = -1;
                        $image   = '';
                        foreach ($infos as $info)  // first all identical fields
                             {  if (array_key_exists ('language',$info) )
                                     {  $d_lng  = $info['language'];}
                                else {  $d_lng  = 'unknown';}
        #                       
                                foreach ($lng_txt as $key)
                                     {  if (array_key_exists ($key,$info) )
                                             {  $lng_arr[$d_lng][$key] = $info[$key];}
                                        else {  $lng_arr[$d_lng][$key] = '';}
                                        }
                                if (array_key_exists ('parameter',$info) && is_array ($info['parameter']) && $image == '')
                                     {  foreach ($info['parameter'] as $param) 
                                             {  if (!is_array ($param) ) {continue;}
                                                if ($param['valueName'] == 'awareness_level' )
                                                     {  list ($pos2,$none,$none) = explode(';',$param['value'].';;;'); }
                                                if ($param['valueName'] == 'awareness_type' )
                                                     {  list ($pos1,$none,$none) = explode(';',$param['value'].';;;'); }
                                                }
                                        $type   = $pos1;
                                        $image  = $pos1.$pos2;}
                        }
                        if ($image == '') {$image = '000';} 
                        #$key_use=$geocode.'|'.$onset.'|'.$expires.'|'.$event;
                        $warns[$key_use]= array(
                                'geocode' => $geocode,  'onset'     => $onset,      'expires' => $expires,   
                                'areaDesc'=> $areaDesc, 'severity'  => $severity,   'event'   => $event,    'status' => $status, 
                                'language'=> $language, 'urgency'   => $urgency,    'image'   => $image,    'id' => $id,      
                                'texts'   => $lng_arr,    'type'    => $type);  
                } // eo all entries 1 country
                }  // eo all countries 
        $error = false;
        $stck_lst .= basename(__FILE__).' ('.__LINE__.') $items='.$items.' $used='.$used.' $to_old='.$to_old.' $others='.$others.' $to_young='.$to_young.' $multiple='.$multiple.PHP_EOL; 
        $error  = file_put_contents($warn_cache,serialize($warns) );
        $stck_lst .= basename(__FILE__).' ('.__LINE__.') file '.$warn_cache.' saving ';
        if ($error == false)
             {  $stck_lst .= ' failed - PROBLEM'.PHP_EOL; }
        else {  $stck_lst .= ' OK - with '.$error.' characters'.PHP_EOL; }   
        }	// eo all countries saved.
#
else {  $warns  = unserialize (file_get_contents($warn_cache) );
        $stck_lst .= basename(__FILE__).' ('.__LINE__.') loading from cache: '.$warn_cache.' which is '.$cache_age.' seconds old'.PHP_EOL; 
        }  // eo 1 country cache
#
ksort ($warns);   #echo __LINE__.' '.print_r($warns,true).$stck_lst; $stck_lst = ''; #exit; 
$count  = count($warns);  #echo '<pre>'.__LINE__.' '.print_r($warns,true).$stck_lst ; exit;
if ($count == 0) {return false;}
$max_color      = 0;
$link           = '';
$table          = '';
$rows           = 3;
$cln_vnt        = '';
$nr = $wrnng  = 0;
foreach ($warns as $arr)
     {  $severity = $arr['severity']; # echo __LINE__.print_r($warns,true); exit;
        $event    = $arr['event']; 
        if (array_key_exists ($severity,$severities) )
             {  $level  = $severities[$severity];
                if ($level > $max_color) 
                     {  $max_color = $level;}
                }
        else { $level = '2';}
        $image_def = str_replace ('##',$arr['image'],$image_url);
        if (!file_exists($image_def) )
             {  $image_def = str_replace ('##','000',$image_url); }
        $table  .= '<tr style="background-color: '.$warncolors[$level].'">'.PHP_EOL;  
        $ymd_frm= date ($dateFormat.' ',$arr['onset']);
        $ymd_to = date ($dateFormat.' ',$arr['expires']);
        if ($ymd_frm == $ymd_to) {$ymd_to = '';}
        $type   = $arr['type'];
        $cln_vnt = lang( $warntypes[$type].' warning') ;
        $table  .= '<td colspan="2">'
                .'<span style="margin-left: 5px; float: left;"><b>'
                .$cln_vnt.'&nbsp;&nbsp;&nbsp;'
                .$arr['areaDesc'].'</b> <small>('.$arr['geocode'].')</small></span>'
                .'<span style="float: right; margin-right:5px;">'
                .'<b>'.lang('Valid').':&nbsp;</b>'
                .$ymd_frm. date ($timeFormatShort,$arr['onset'])
                .'&nbsp;&nbsp;-&nbsp;&nbsp;'
                .$ymd_to. date ($timeFormatShort,$arr['expires'])      
                .'</span></td>'.PHP_EOL.'</tr>'.PHP_EOL;
        $table  .= '<tr style="background-color: '.$warncolors[$level].'">'.PHP_EOL;  
        $table  .= '<td style="vertical-align: top;">';
        $table  .= '<img src="'.$image_def.'" style="margin: 4px; max-width: 128px; " alt="'.$arr['image'].'" title="'.$arr['image'].'"></td>'.PHP_EOL;
        $table  .= '<td style="text-align: left;">';
        $table  .= '
<span class="tab" style="">'.PHP_EOL;
        $other  = $start = '';
        $display= 'block';
        $active = 'active';
        $margin = 'margin-left: 20px;';
        $wrnng++;
        foreach ($arr['texts'] as $language =>  $text)
             {  $nr++;
                $lngtxt = $language;
                $lngshrt= substr ($language,0,2);
                if      (array_key_exists ($language,$lang_warn) ) {$lngtxt = $lang_warn[$language];}
                elseif  (array_key_exists ($lngshrt, $lang_warn) ) {$lngtxt = $lang_warn[$lngshrt];}
                $start  .= '<label class="t'.$wrnng.'tablinks tablinks '.$active.'"  style="'.$margin.'" onclick="openTab(event,\'t'.$wrnng.'\', \'t'.$wrnng.'-'.$nr.'\')" id="'.$wrnng.$language.'">&nbsp;'.$lngtxt.'&nbsp;</label> '.PHP_EOL;
                $margin = '';
                $other  .= '<span id="t'.$wrnng.'-'.$nr.'" class="t'.$wrnng.'tabcontent tabcontent" style="clear: left; display: '.$display.';">'.PHP_EOL;
                $display= 'none';
                $active = '';
                $hdln1  = trim($text['event']);
                $hdln2  = trim($text['headline']);
                if ($hdln2 == $hdln1) {$hdln2 = '';}
                if ($hdln1 <> '' && $hdln2 <> '') {$hdln1 .= '<br />';}
                if ($hdln1.$hdln2 <> '')
                     {  $other  .= '<b style="text-align: center; width: 100%; display: block;">'.$hdln1.$hdln2.'</b>'.PHP_EOL.'<br />'.PHP_EOL;}
                $txtbr  = str_replace(PHP_EOL,'<br />', $text['description']);
                $other  .= $txtbr.'<br />'.PHP_EOL;
                $instruction = '';
                if (is_array($text['instruction']) )
                     {  foreach ($text['instruction'] as $instruction) 
                             { break;}
                        } 
                else {  $instruction = $text['instruction']; }
                if ($instruction <> '' )
                     {  $other  .= '<br />'.$instruction.'<br />'.PHP_EOL;  }
                $other  .= '</span>'.PHP_EOL;    
                }  // eo e texts
#
        if (count ($arr['texts']) < 2)
             {  $start = '';}
        $table  .= $start.$other;  
        $table  .= '</span></td>'.PHP_EOL;
        $table  .= '</tr>'.PHP_EOL;  
#        $table  .= '<tr style="background-color: transparent; height: 10px;"><td colspan="2" style="font-size: 8px;">&nbsp;</td></tr>'.PHP_EOL;            
        $warncode= $arr['geocode'].' '.$arr['event'];
        $table  .= '<tr style="background-color: transparent; height: 6px;"><td colspan="2" style="font-size: 8px;" title="'.$warncode.'">'
                        .'<a href="PWS_frame_text.php?showtext='.$arr['id'].'" target="_blank"><hr style="border-color: white;" /></a>'
                        .'</td></tr>'.PHP_EOL; 
        } // eo for each warning;  
#echo $table; exit;
$table  = str_replace('Â°','°',$table);
$table  = '<table style="width: 100%; border-collapse: collapse; margin-top: 8px; " >
'.$table.'
</table>'.$legal.'<hr style="border-color: white;" />';
$icon   =  '<svg style="vertical-align: bottom;" id="i-info" viewBox="0 0 32 32" width="20" height="20" fill="none" stroke="black" stroke-linecap="round" stroke-linejoin="round" stroke-width="6.25%"><path d="M16 14 L16 23 M16 8 L16 10"></path><circle cx="16" cy="16" r="14"></circle></svg>';      
if ($count > 1) 
     {  $text =  lang('Multiple warnings') ;}
else {  $text =  $cln_vnt;}
$wrnStrings    = '<div style="text-align: center; position: absolute;top: 18px;  width: 100%; height: 60px;  font-size: 12px; background-color: '.$warncolors[$max_color].';">
<div style="color: black;   margin-top: 4px;"><b>MeteoAlarm</b><br />'.$text.'<br />';
#$wrnHref 	= '<a href="'.$detail_page_url.'">';
$wrnHref 	= '<a href="./wrnPopupWarnings.php" data-featherlight="iframe">';
$wrnStrings    .= $wrnHref.$icon.'
</a>
</div>
</div>'; 
#
$ownpagehtml = '<script>
if(typeof document.createStyleSheet === "undefined") {
    document.createStyleSheet = (function() {
        function createStyleSheet(href) {
            if(typeof href !== "undefined") {
                var element = document.createElement("link");
                element.type = "text/css";
                element.rel = "stylesheet";
                element.href = href;}
            else {
                var element = document.createElement("style");
                element.type = "text/css";}
            document.getElementsByTagName("head")[0].appendChild(element);
            var sheet = document.styleSheets[document.styleSheets.length - 1];
            if(typeof sheet.addRule === "undefined")
               { sheet.addRule = addRule;}
            if(typeof sheet.removeRule === "undefined")
               { sheet.removeRule = sheet.deleteRule;}
            return sheet;
        }
        function addRule(selectorText, cssText, index) {
            if(typeof index === "undefined") { index = this.cssRules.length;}
            this.insertRule(selectorText + " {" + cssText + "}", index);
        }
        return createStyleSheet;
    })();
}
var sheet = document.createStyleSheet();
sheet.addRule(".tab","overflow: hidden; display: block; border: 0px solid #ccc; background-color: white; text-align: left; margin:  4px; margin-left: 0px;");
sheet.addRule(".tab span","text-align: left; margin:  4px;");
sheet.addRule(".tab label","float: left; border-radius: 4px; background-color: #ccc; border: 1px solid #ddd; cursor: pointer; margin: 3px; margin-bottom: 0px; padding: 3px; ");
sheet.addRule(".tab label:hover","background-color: white;");
sheet.addRule(".tab label.active","border-bottom-right-radius: 0px; border-bottom-left-radius: 0px; background-color: transparent; border: 1px solid black; border-bottom: 1px solid white;");
sheet.addRule(".tabcontent","display: none; border-top: none;");

function openTab(evt, block,  spanName) {
  var i, tabcontent, tablinks;
  var clssnm    = block+"tabcontent";
  tabcontent = document.getElementsByClassName(clssnm);
  
  for (i = 0; i < tabcontent.length; i++) 
   {tabcontent[i].style.display = "none";}
   
  
  tablinks = document.getElementsByClassName(block+"tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  document.getElementById(spanName).style.display = "block";
  evt.currentTarget.className += " active";
}
</script>
'.$table; 
return true; 

function warn_curl()
     {  global $warn_url, $stck_lst, $result, $test_xml, $fl_to_load;
        $result = '';
        if ( isset ($test_xml) && strlen($test_xml) > 2 )
             {  $stck_lst .= basename(__FILE__).' ('.__LINE__.') test_file '.$test_xml.' is used'.PHP_EOL; 
                $result = file_get_contents ($test_xml);}
        else {  $start_time     =  microtime(true);
                $ch             = curl_init(); 
                curl_setopt($ch, CURLOPT_URL,$warn_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,4); // connection timeout
                curl_setopt($ch, CURLOPT_TIMEOUT,10);        // data timeout 
                curl_setopt ($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; rv:12.0) Gecko/20120424 Firefox/12.0');
                $result         = curl_exec ($ch);
                $info	        = curl_getinfo($ch);
                $error          = curl_error($ch);
                curl_close ($ch);
                $end            = microtime(true);
                $passed         = $end - $start_time;
                if ($passed < 0.0001) {$string1 = '< 0.0001';} else {$string1 = round($passed,4);}
                $CHECK_HTTP_CODES = array ('404', '429','502', '500');
                if (in_array ($info['http_code'],$CHECK_HTTP_CODES) ) 
                     {  $stck_lst .= basename(__FILE__).' ('.__LINE__.') '.$fl_to_load.': time spent: '.$string1.' - PROBLEM => http_code: '.$info['http_code'].', no valid data '.$warn_url.PHP_EOL;
                        $stck_lst .= basename(__FILE__).' ('.__LINE__.') url used'.$warn_url.PHP_EOL;
                        return false;} 
                if ($error <> '')
                     {  $stck_lst .= basename(__FILE__).' ('.__LINE__.') '.$fl_to_load.': time spent: '.$string1.' -  invalid CURL '.$error.' '.$warn_url.PHP_EOL; 
                        return false;}
                else {  $stck_lst .= basename(__FILE__).' ('.__LINE__.') '.$fl_to_load.': time spent: '.$string1.' -  CURL OK for '.$warn_url.PHP_EOL; }
                $result   = trim($result);}
 #echo $stck_lst.$result;		exit;
	libxml_use_internal_errors(true);
	libxml_clear_errors();
	$doc    = new DOMDocument('1.0', 'utf-8');
	$doc->loadXML($result);
	$errors = libxml_get_errors();  #echo var_dump($doc,true); exit;
	unset ($doc);
	if(!empty($errors))
	     {  foreach(libxml_get_errors() as $error) 
	             { $stck_lst .= basename(__FILE__).' ('.__LINE__.') rawData error '.trim($error->message).PHP_EOL;}
		return false;}
#
        libxml_clear_errors();
        $result = str_replace ('cap:','',$result);
        $result = new SimpleXMLElement(trim($result) ); #echo '<pre>'.__LINE__.' '.print_r($result,true); exit;
        if ($result === false) 
             {  $stck_lst .= basename(__FILE__).' ('.__LINE__.') errors processing xml ';
                foreach(libxml_get_errors() as $error) 
                     {  $stck_lst .= basename(__FILE__).' ('.__LINE__.') error '.$error->message.PHP_EOL;}
                libxml_clear_errors();
                return false;}
        else {  $stck_lst .= basename(__FILE__).' ('.__LINE__.') no errors processing xml'.PHP_EOL;}      
        return true;        
} // eof warn_curl
