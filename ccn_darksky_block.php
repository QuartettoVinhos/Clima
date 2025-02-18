<?php $scrpt_vrsn_dt  = 'ccn_darksky_block.php|01|2021-05-25|';  # check file | release 2012_lts
#-----------------------------------------------
# currentconditionsDS_block
# |-> PWS_livedata.php
# |-> fct_darksky_shared.php
# |-> if the icon and condition text are not 
#     available from another script those fields are used from ccn array
# |-> The hourly forecasts are read until the next-hour forecast
#     |-> the correct hourly fct is used
# |-> ccn_shared.php    to print all info
#-----------------------------------------------
#         PWS-Dashboard - Updates and support by 
#     Wim van der Kuil https://pwsdashboard.com/
#-----------------------------------------------
#       display source of script if requested so
#-----------------------------------------------
if (isset($_REQUEST['sce']) && strtolower($_REQUEST['sce']) == 'view' ) {
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
elseif (!isset ($_REQUEST['test'])) 
     {  ini_set('display_errors', 0);   error_reporting(0);}
# -------------------save list of loaded scrips;
if (!isset ($stck_lst) ) {$stck_lst = '';}  
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') version =>'.$scrpt_vrsn_dt.PHP_EOL;       
#
# ------------check if script is already running
$string = str_replace('.php','',basename(__FILE__));
if (isset ($$string) ) {echo 'This info is already displayed'; return;}
$$string = $string;
#
# -------------load weatherdata and all settings 
$scrpt          = 'PWS_livedata.php'; 
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once =>'.$scrpt.PHP_EOL;
include_once $scrpt; 
#
# -----------------   load general DarkSky code
$scrpt          = 'fct_darksky_shared.php';
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once  =>'.$scrpt.PHP_EOL; 
$return = include_once $scrpt; 
if ($return === false) { return;}  #### 2021-05-25
#-----------------------------------------------
#  save condition icon and texts for current conditions block
$fl_nm          = $fl_folder.$drksk_fl;
if (!isset ($timeXX) )  // if not already generated by another script
     {  $timeXX         = filemtime($fl_nm);  // time of forecast file 
        $textXX         = $darkskycurSummary;
        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') conditions from provider $darkskycurSummary='.$darkskycurSummary.PHP_EOL;
#        
        if ($itsday == false)
             {  $iconXX =  DSicon_trns ('nt_'.$darkskycurIcon);}
        else {  $iconXX =  DSicon_trns ($darkskycurIcon);}
        $stck_lst      .= basename(__FILE__).' ('.__LINE__.') icon from provider data $darkskycurIcon='.$darkskycurIcon.PHP_EOL;
        #
        if (isset ($ccn_small) ) { return;}        
     }  // eo if not already generated by another script
#
# --------------   find current hour or use last
$now    = time();
foreach ($darkskyhourlyCond as $cond)  // 
     {  if ($cond['time'] > $now)  { break;} }  
#
$hourlySummary          = $cond['summary'];
$hourlyIcon             = $cond['icon'];
$num                    = $cond['temperature'];
$hourlyTemp             = convert_temp ($num,$darksky_used_temp,$tempunit,0); 
$tempC                  = convert_temp ($num,$darksky_used_temp,'c',0); 
#
$num2                   = $cond['apparentTemperature'];  
if ($num2 > $num)
     {  $chillC         = '';
        $hourlychill    = '';
        $hourlyhudx     = convert_temp ($num2,$darksky_used_temp,$tempunit,0);  
        $hudxC          = convert_temp ($num2,$darksky_used_temp,'c',0);}
else {  $hudxC          = '';
        $hourlyhudx     = '';
        $hourlychill    = convert_temp ($num2,$darksky_used_temp,$tempunit,0);  
        $chillC         = convert_temp ($num2,$darksky_used_temp,'c',0); }
# save as debug info  
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') $hourlyTemp='.$hourlyTemp.' $hourlychill='.$hourlychill.' $hourlyhudx='.$hourlyhudx.PHP_EOL;
#
$hourlyWinddir          = $cond['windBearing'];
$hourlyPrecipProb       = $cond['precipProbability']*100;
$hourlyPrecipType       = '';
if (isset($cond['precipType']))
     {  $hourlyPrecipType= $cond['precipType'];}
else {  $haystack       = strtolower($hourlySummary);
        if ( DSfound($haystack, 'snow') || DSfound($haystack, lang('snow')) )
             {  $hourlyPrecipType = 'snow'; }}
$hourlyprecipIntensity  = 0;
if (isset($cond['precipIntensityMax']))
     {  $num    = $cond['precipIntensityMax'];
        $hourlyprecipIntensity  = convert_precip($num,$darksky_used_rain,$rainunit,2) ; }      
$hourlyWindSpeed        = convert_speed ($cond['windSpeed'],$darksky_used_wind,$windunit,0) ;
$hourlyWindGust         = convert_speed ($cond['windGust'], $darksky_used_wind,$windunit,0) ;
$hourlyuv               = $cond['uvIndex'];
#
#-----------------------------------------------
# After all current condition fields are ready
# we use another general script to print them   
$scrpt          = 'ccn_shared.php';
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include_once  =>'.$scrpt.PHP_EOL; 
include_once $scrpt;
#
if (isset ($_REQUEST['test']) ) { echo '<!-- '.PHP_EOL.$stck_lst.'-->'.PHP_EOL; $stck_lst='';}
