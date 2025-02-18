<?php  $scrpt_vrsn_dt  = 'PWS_load_files.php|01|2023-10-31|';  # check invalid debug status.arr + DS/VP/PW aurora purple-api + bot problem + PHP 8.1 + WF check loaded data + aurora + file status + cleaned logging  | release 2012_lts
#
#-----------------------------------------------
#         PWS-Dashboard - Updates and support by 
#     Wim van der Kuil https://pwsdashboard.com/
#-----------------------------------------------
#       display source of script if requested so
#-----------------------------------------------
if (isset($_REQUEST['sce']) && strtolower($_REQUEST['sce']) == 'view' ) {
   $filenameReal = __FILE__;    #               display source of script if requested so
   $download_size = filesize($filenameReal);
   header('Pragma: public');
   header('Cache-Control: private');
   header('Cache-Control: no-cache, must-revalidate');
   header("Content-type: text/plain");
   header("Accept-Ranges: bytes");
   header("Content-Length: $download_size");
   header('Connection: close');
   readfile($filenameReal);
   exit;}
# -------------------save list of loaded scrips;
if (!isset ($stck_lst) ) {$stck_lst = '';}
$stck_lst      .= basename(__FILE__).'('.__LINE__.') loaded  =>'.$scrpt_vrsn_dt.PHP_EOL;       // save list of loaded scrips;
#
# load settings when run stand-alone
$scrpt          = 'PWS_settings.php';
$stck_lst      .= basename(__FILE__).' ('.__LINE__.') include  =>'.$scrpt.PHP_EOL; 
include_once $scrpt; 
#
if (isset ($cron) && $cron == true) {$times  = 0.8;} else {$times  = 1;}
$now    = time();
$noload = $fl_to_load = '______________';
# 
unset   ($ch);
#
$fake_keys      = array();
$fl_to_load     = '';
$status_arr     = __DIR__.'/_my_settings/status.arr';
$status_arr_nw  = __DIR__.'/_my_settings/status.arr.tmp';
if (file_exists($status_arr))
     {  $statuses       = unserialize (file_get_contents($status_arr));
        if (!is_array ($statuses) ) {$statuses = array();}  
        }
else {  $statuses       = array();}
$warn_clr       = '<span class="PWS_round" style="background-color: transparent;">&nbsp;&nbsp&#x2713;&nbsp;&nbsp;</span>';
#
function calc_statuses()
     {  global $statuses, $status_arr, $warn_clr, $stck_lst, $times, $check_cron;      
        $errors = ''; $warn_clrR = ''; $warn_clrO = '';
        if ($check_cron === false) {return '';}                                          
        if ($times <> 1)   // cron job should save updated status array 
             {  $stck_lst      .= basename(__FILE__).'('.__LINE__.') updating status file'; 
                $status_arr_nw  = $status_arr.'.tmp';
                $return = file_put_contents ($status_arr_nw, serialize ($statuses));
                if ($return <>  false) 
                    {   rename ($status_arr_nw,$status_arr); 
                        $stck_lst      .= ' succesfull'.PHP_EOL; }
                if ($return ===  false)                                 # 2023-10-08
                    {   return $warn_clr = ' color: red; ';
                        $stck_lst      .= ' FAILED'.PHP_EOL; }
                } // # 2022-04-30
        if (!is_array ($statuses) ) {$statuses = array();}   
        if (array_key_exists('Aurora-kindex',$statuses) ) { unset ($statuses['Aurora-kindex']); }
        foreach ($statuses as $key=> $arr)
             {  $file_time      = filemtime($arr['fl_name']); # 2022-04-30
                $age    = time() - $file_time;  # $arr['last']; # 2022-04-30
                $allow  = $arr['fl_allw'];
                $errors.= PHP_EOL.'<!-- $key='.$key.' $age='.$age.' $allow='.$allow.' -->';
                if ($age <= $allow )
                     {  continue;}
                if ($age > 2 * $allow )
                     {  $warn_clrR      = '<span class="PWS_round" style="background-color: red; color: black; ">&nbsp;&nbsp;!&nbsp;&nbsp;</span>';
                        continue;}
                $warn_clrO      = '<span class="PWS_round" style="background-color: orange;  color: black;">&nbsp;&nbsp;!&nbsp;&nbsp;</span>';
                continue; }
        if ($warn_clrR <> '') { return $warn_clrR.$errors;}
        if ($warn_clrO <> '') { return $warn_clrO.$errors;}
        return $warn_clr.$errors;
        }
#
# load current weather from WU with your private API 
if ($livedataFormat == 'wu') 
     {  if ( $wu_apikey <> 'ADD YOUR API KEY') 
              { $filename       = $livedata; 
                $cron_min_time  = 180;
                $allowed_age    = $cron_min_time*$times; 
                $url            = 'https://api.weather.com/v2/pws/observations/current?stationId='
                                .$wuID.'&format=json&units='
                                .$wu_unit.'&numericPrecision=decimal&apiKey='
                                .$wu_apikey;
                $fake_keys[0]   = $wu_apikey;
                fnctn_load_file ( 'WU-ccn');}
        if (isset ($read_net_data) ) 
             {  calc_statuses();
                return;}
} // eo WU
#
# load current weather from WeatherLink Cloud with your v1 API 
elseif ($livedataFormat == 'DWL')
     {  if ( $dwl_api <> 'ADD YOUR API KEY') 
             {  $filename       = $livedata; 
                $cron_min_time  = 300;
                $allowed_age    = $cron_min_time*$times;
                $url            = 'https://api.weatherlink.com/v1/NoaaExt.json?user='
                                .$dwl_did.'&pass='
                                .$dwl_pass.'&apiToken='
                                .$dwl_api;
                $fake_keys[0]   = $dwl_pass;
                $fake_keys[1]   = $dwl_api;
                fnctn_load_file ('WL-data');}
        if (isset ($read_net_data) ) 
             {  calc_statuses();
                return;}
} // eo Davis WL.com
#
# load current weather from WeatherLink Cloud with your v2 API 
elseif ($livedataFormat == 'DWL_v2api')
     {  if ( $dwl_api2 <> 'ADD YOUR API KEY'  && (int) $dwl_station <> 0)
             {  $filename       = $livedata; 
                $cron_min_time  = 300;
                $allowed_age    = $cron_min_time*$times;
                $now            = time();
                $data = 'api-key'.$dwl_api2.'station-id'.$dwl_station.'t'.$now; #die ($data .' secret= '.$dwl_secret);
                $apiSignature   = hash_hmac("sha256", $data, $dwl_secret);
                $url            = 'https://api.weatherlink.com/v2/current/'
                                .$dwl_station
                                .'?api-key='.$dwl_api2
                                .'&api-signature='.$apiSignature
                                .'&t='.$now;
                $fake_keys[0]   = $dwl_api2;
                $fake_keys[1]   = $dwl_api;
                fnctn_load_file ('WL-cloud-v2');}
        if (isset ($read_net_data) ) 
             {  calc_statuses();
                return;}
} // eo Davis WL.com
#
# load your current weather from weatherflow
elseif ( $livedataFormat == 'wf')
     {  $filename       = $livedata;
        $cron_min_time  = 300;
        $allowed_age    = $cron_min_time*$times;
        $url            = 'https://swd.weatherflow.com/swd/rest/observations/station/'
                        .$weatherflowID.'?api_key='
                        .$somethinggoeshere; 
        $fake_keys[0]   = $somethinggoeshere;
        fnctn_load_file ('WeatherFlow'); 
# check correct file
        $string = file_get_contents ($livedata);                #### 2021-08-17
        if (strpos ($string, '"SUCCESS"') == false)
             {  $stck_lst .= basename(__FILE__).' ('.__LINE__.') '.$fl_to_load.': invalid or incomplete data, will use old data'.PHP_EOL;
                if (is_file ($livedata.'old') )
                     {  rename ($filename.'old',$filename);
                        $string = file_get_contents ($livedata);}
                else {  die ('Problem '.__LINE__.' no old data to process found');}
                if (strpos ($string, '"SUCCESS"') == false)
                     {  $stck_lst .= basename(__FILE__).' ('.__LINE__.') '.$fl_to_load.': old data not OK'.PHP_EOL;
                        die ('Problem '.__LINE__.' no data to process found');}
                }                                               #### 2021-08-17
        if (isset ($read_net_data) ) 
             {  calc_statuses();
                return;}
}  // eo   weatherflow
#
# load your current weather from AmbientWeather.net
elseif ( $livedataFormat == 'AWapi') 
     {  if ($aw_key <> 'ADD YOUR API KEY')
             {  $filename       = $livedata;
                $cron_min_time  = 120;
                $allowed_age    = $cron_min_time*$times;
                $AW_app_key     = '45234c52a04040e8a8d0e240a1236200cfbbbc0aaf7b4136a5b313e6088e7889';
                $AW_app_key     = '2d94ac7e48e74359a823319e79db8675b596bceb50634fdcb171cff819b4e284';
                $url            = 'https://api.ambientweather.net/v1/devices?applicationKey='.$AW_app_key.'&apiKey='.$aw_key;
                $fake_keys[0]   =  $AW_app_key;
                $fake_keys[1]   =  $aw_key;    
                $fl_to_load     = 'AmbientWeather'; 
                fnctn_load_file (); }
        if (isset ($read_net_data) ) 
             {  calc_statuses();
                return;}
} 
#    
# load metar from checkwx.com with your private API 
if (isset ($metarapikey)  && $metarapikey <> '' && $metarapikey <> 'ADD YOUR API KEY')
     {  $filename       = $fl_folder.$mtr_fl;  
        $allowed_age    = $metarRefresh*$times;
        $url            = 'https://api.checkwx.com/metar/'
                        .$icao1.'/decoded';
        $header         = array( "X-API-KEY:".trim($metarapikey)."",);
        $fake_keys[0]    = $metarapikey;
        fnctn_load_file ('METAR-'.$icao1);
        $header         = ''; }  
else {  $name   = substr('METAR-'.$icao1.$noload,0,14);
        $stck_lst .= basename(__FILE__).' ('.__LINE__.') '.$name.': not loaded API='.$metarapikey.PHP_EOL; }
#
# load WeatherUnderground forecast with your private API
if ( isset ($wu_apikey)   && $wu_apikey <> ''  && $wu_apikey <> 'ADD YOUR API KEY')
     {  $filename       = $fl_folder.'wufct_'.$locale_wu.'_' .$wu_fct_unit.'.txt';  
        $allowed_age    = $fcts_refresh*$times;
        $latlon= round($lat,2).','.round($lon,2);
        $url            = 'https://api.weather.com/v3/wx/forecast/daily/5day?geocode='
                        .$latlon.'&format=json&units='
                        .$wu_fct_unit.'&language='
                        .$locale_wu.'&apiKey='
                        .$wu_apikey;
        $fake_keys[0]    = $wu_apikey;
        fnctn_load_file ('WU_forecast'); } 
else {  $name           = substr('WU_forecast'.$noload,0,14);
        $stck_lst .= basename(__FILE__).' ('.__LINE__.') '.$name.': not loaded API='.$wu_apikey.PHP_EOL; }
# 2023-02-15
# load darksky || alternative forecast and current conditions with your private API
if (!isset ($dark_alt_vrs) ) { $dark_alt_vrs = 'ds';}
if ( isset ($dark_apikey)  && $dark_apikey <> ''   && $dark_apikey <> 'ADD YOUR API KEY'
     && $dark_alt_vrs <> 'nu')
     {  $filename       = $fl_folder.$drksk_fl; 
        $allowed_age    = $fcts_refresh*$times;      
        if     ($dark_alt_vrs == 'vc')
             {  $url    = $this_server.'PWS_Dark_Visual.php?lang='.$user_lang;  }
        elseif ($dark_alt_vrs == 'pw')
             {  $url    = $this_server.'PWS_Dark_Pirate.php?lang='.$user_lang; }
        elseif ($dark_alt_vrs == 'ow')
             {  $url    = $this_server.'PWS_Dark_Openweather.php?lang='.$user_lang; }      
        else {  $url    = 'https://api.forecast.io/forecast/'
                        .$dark_apikey.'/'
                        .$lat.','.$lon.'?lang='
                        .substr($locale_wu,0,2).'&units='
                        .$darkskyunit ;}               
        $fake_keys[0]   = $dark_apikey;  # echo $url; exit;
        fnctn_load_file ('Darksky'); } 
# 2023-02-15
else {  $name           = substr('Darksky'.$noload,0,14);
        $stck_lst .= basename(__FILE__).' ('.__LINE__.') '.$name.': not loaded API='.$dark_apikey.PHP_EOL; }
#
# load yr.no  forecasts
if ( $fct_default == 'fct_yrno_block.php') #$fct_yrno_block_used
     {  $filename       = $fl_folder.'metno2complete.json';
        $lat4   = round ($lat,4);
        $lon4   = round ($lon,4);
        $allowed_age    = 3600*$times;                                ## 2020-08-02
        $url            = 'https://api.met.no/weatherapi/locationforecast/2.0/complete?lat='.$lat4.'&lon='.$lon4;
        fnctn_load_file ('yrno_metno_fct');}
else  { $name           = substr('yr.no'.$noload,0,14);
        $stck_lst .= basename(__FILE__).' ('.__LINE__.') '.$name.': Not used / not yet correctly set'.PHP_EOL; }

# load AERIS weatherdata 
if ( isset ($aeris_access_id)   && $aeris_access_id <> ''  && $aeris_access_id <> 'ADD YOUR API KEY'
  && isset ($aeris_secret_key)  && $aeris_secret_key <> '' && $aeris_secret_key <> 'ADD YOUR API KEY')
     {  $lat_plus       = 0.0;          ##### 2023-02-15
        $lon_plus       = 0.0;          ##### 2023-02-15
        $use_airport    = false;        ##### 2023-02-15
        $url_a  = 'https://api.aerisapi.com/';
        $lat_p  = (float) $lat_plus + $lat;
        $lon_p  = (float) $lon_plus + $lon;
        $url_b  = '/'.$lat_p.','.$lon_p.'?format=json&client_id='.$aeris_access_id.'&client_secret='.$aeris_secret_key;
        $url_mtr= '/'.$icao1.'?format=json&client_id='.$aeris_access_id.'&client_secret='.$aeris_secret_key;
#
        $fake_keys[0]   = $aeris_secret_key;
        $fake_keys[1]   = $aeris_access_id;
        $filename       = $fl_folder.'aeris_fct_hrs.json';
        $allowed_age    = $metarRefresh*$times;
        $url            = $url_a.'forecasts'   .$url_b.'&filter=1hr&limit=26'; 
        fnctn_load_file ('Aeris_hourly'); 
#        
        $filename       = $fl_folder.'aeris_fct_dp.json';
        $allowed_age    = $fcts_refresh*$times;
        $url            = $url_a.'forecasts'   .$url_b.'&filter=daynight&limit=14';
        fnctn_load_file ('Aeris_dayparts');   
#         
        $filename       = $fl_folder.'aeris_ccn.json';
        $allowed_age    = $metarRefresh*$times;
        $url            = $url_a.'observations'.$url_b.'&filter=metar&limit=1';  
        if (isset ($use_airport) && $use_airport == true)                            #### 2023-02-15
             {  $url    = $url_a.'observations'.$url_mtr.'&filter=metar&limit=1'; }  #### 2023-02-15 
        fnctn_load_file ('Aeris_ccn'); }
else {  $name           = substr('Aeris'.$noload,0,14);
        $stck_lst .= basename(__FILE__).' ('.__LINE__.') '.$name.': not loaded keys='.$aeris_access_id.' '.$aeris_secret_key.PHP_EOL; }
#
# $weatheralarm == 'canada' ||  currentconditions  from ccn_ec_block.php  
$load_ec = false; 
if ($weatheralarm == 'canada')
     {  $load_ec        = true;
        $allowed_age    = 600;}
elseif ($position13  == 'ccn_ec_block.php') 
     {  $load_ec        = true;
        $allowed_age    = 3000;}
if ($load_ec == true)
     {  $EClang = 'e';
        if (substr($used_lang,0,2)  == 'fr') { $EClang = 'f';}
        $filename       = $fl_folder.'ec_'.$province.'_'.$alarm_area.'_'.$EClang.'.xml';
        $url            = 'https://dd.meteo.gc.ca/citypage_weather/xml/'
                        .$province.'/'
                        .$alarm_area.'_'
                        .$EClang.'.xml';
        fnctn_load_file ('EC-weather');}
else {  $name           = substr('EC-weather'.$noload,0,14);
        $stck_lst .= basename(__FILE__).' ('.__LINE__.') '.$name.': Environement Canada: not used '.PHP_EOL; }
#
# load earthquakes file, can always be loaded, just check age 
$filename       = $fl_folder.$qks_fl;
$allowed_age    = $quakesRefresh*$times;
$url            = $this_server.'PWS_quakes_load.php?json'; ##### 2020-12-31 'https://earthquake-report.com/feeds/recent-eq?json'; 
fnctn_load_file ('Earthquakes'); 
#
#  load k-index, can always be loaded, just check age
$filename       = $fl_folder.$kndx_fl;
$allowed_age    = $kindexRefresh*$times;
#$url            = 'https://services.swpc.noaa.gov/products/geospace/planetary-k-index-dst.json'; #### 2021-06-12
$url            = 'https://services.swpc.noaa.gov/json/planetary_k_index_1m.json'; #### 2021-06-12
fnctn_load_file ('Aurora-kindex'); 
#
#### 20200916 Davis AQ  
# load Davis air quality      
if(isset ($dwl_AQ)  &&  (   $dwl_AQ <>  '0000') &&  (   $dwl_AQ <>  $dwl_station)  )
     {  $filename       = $fl_folder.'wlcomv2API'.$dwl_AQ.'.json';
        $cron_min_time  = 900;
        $allowed_age    = $cron_min_time*$times;
        $now            = time();
        $data = 'api-key'.$dwl_api2.'station-id'.$dwl_AQ.'t'.$now; #die ($data .' secret= '.$dwl_secret);
        $apiSignature   = hash_hmac("sha256", $data, $dwl_secret);
        $url            = 'https://api.weatherlink.com/v2/current/'
                        .$dwl_AQ
                        .'?api-key='.$dwl_api2
                        .'&api-signature='.$apiSignature
                        .'&t='.$now;
        $fake_keys[0]   = $dwl_api2;
        $fake_keys[1]   = $dwl_api;
        fnctn_load_file ('AQ-Davis');}
elseif (isset ($dwl_AQ)  &&  (   $dwl_AQ <>  '0000') &&  (   $dwl_AQ ===  $dwl_station)  )
     {  $name           = substr('AQ-Davis'.$noload,0,14);
        $stck_lst .= basename(__FILE__).' ('.__LINE__.') '.$name.': Davis AQ loaded from main station ? '.PHP_EOL; }
else {  $name           = substr('AQ-Davis'.$noload,0,14);
        $stck_lst .= basename(__FILE__).' ('.__LINE__.') '.$name.': Davis AQ sensor not used '.PHP_EOL; }
#### 20200916 Davis AQ
#  
# load luftdaten air quality      
if(isset ($luftdatenhardware)  &&  (   $luftdatenhardware === true) )
     {  $filename       = $fl_folder.$lfdtn_fl;
        $allowed_age    = $luftRefresh*$times;
        $url            = 'https://data.sensor.community/airrohr/v1/sensor/' # replaced old url 'http://api.luftdaten.info/v1/sensor/'
                        .$luftdatenID.'/'; 
        fnctn_load_file ('AQ-Luftdaten'); }
else {  $name           = substr('AQ-Luftdaten'.$noload,0,14);
        $stck_lst .= basename(__FILE__).' ('.__LINE__.') '.$name.': luftdaten sensor not used '.PHP_EOL; }
# 
# load purple air quality                
if( isset ($purpleairhardware) && (   $purpleairhardware === true) )
     {  $filename       = $fl_folder.$prpl_fl;
        $allowed_age    = $purpleRefresh*$times;
        $url            = 'https://api.purpleair.com/v1/sensors/'
                        .$purpleairID
                        .'?api_key='.$purpleairAPI
                        .'&fields='     # 2023-09-04 
                        .'latitude,longitude,last_seen,pm2.5,sensor_index,name,temperature,humidity,'
                        .'pm10.0_atm_a,pm10.0_atm_b,'
                        .'pm2.5_10minute,pm2.5_30minute,pm2.5_60minute,pm2.5_6hour,pm2.5_24hour,pm2.5_1week'
                        ;               # 2023-09-04 
        $fake_keys[0]   = $purpleairAPI; 
# 2022-05-29
        fnctn_load_file ('AQ-Purpleair'); }
else {  $name           = substr('AQ-Purpleair'.$noload,0,14);
        $stck_lst .= basename(__FILE__).' ('.__LINE__.') '.$name.': purplair sensor not used '.PHP_EOL; }
#
# load WAQI air quality
if(isset ($gov_aqi)  &&  (   $gov_aqi === true)  &&  $waqitoken <> 'ADD YOUR API KEY')
     {  $filename       = $fl_folder.$gvaqi_fl;
        $allowed_age    = 3600*$times;
        $url            = 'https://api.waqi.info/feed/geo:'
                        .$lat.';'.$lon.'/?token='
                        .$waqitoken;
        $fake_keys[0]   = $waqitoken;
        fnctn_load_file ('AQ-official'); }
else {  $name           = substr('AQ-official'.$noload,0,14);
        $stck_lst .= basename(__FILE__).' ('.__LINE__.') '.$name.': goverment aqi not used '.PHP_EOL; }
#
if ( $weatherflowoption == true && $livedataFormat <> 'wf') # load weatherflow 
     {  $filename       = $fl_folder.'weatherflow.txt';
        $allowed_age    = 660*$times;
        $url            = 'https://swd.weatherflow.com/swd/rest/observations/station/'
                        .$weatherflowID.'?api_key='
                        .$somethinggoeshere; 
        $fake_keys[0]   = $somethinggoeshere;
        fnctn_load_file ('Weatherflow'); } 
else {  $name           = substr('Weatherflow'.$noload,0,14);
        if ($livedataFormat == 'wf') {$text = ': WeatherFlow data already loaded';} else {$text = ': WeatherFlow device not used';}
        $stck_lst .= basename(__FILE__).' ('.__LINE__.') '.$name.$text.PHP_EOL; }
#
# check if we have to load WU files to generate charts
if ($charts_from <> 'WU') 
     {  $name           = substr('WU_graphs'.$noload,0,14);
        $stck_lst .= basename(__FILE__).' ('.__LINE__.') '.$name.': WeatherUnderground graphs-data not used '.PHP_EOL;
        $return =  calc_statuses();
        if (isset ($_REQUEST['test'])) 
             {  echo '<pre>'.$stck_lst.'</pre>';
                $stck_lst = '';
                return;}
        if ($return <> '') {echo $return;}
        return;}      
#        
if (trim($wuID) == '' || trim($wuID) == 'no key') 
     {  $name           = substr('WU_graphs'.$noload,0,14);
        $stck_lst .= basename(__FILE__).' ('.__LINE__.') '.$name.': No valid WU station name found '.trim($wuID).PHP_EOL; 
        $return =  calc_statuses();
        if (isset ($_REQUEST['test'])) 
             {  echo '<pre>'.$stck_lst.'</pre>';
                $stck_lst = '';
                return;}
        if ($times == 1 &&  $return <> '') {echo $return;}
        return;}      
#
# LAST part of loads , loading WU .CSV can take considarable time
$chartdata      = 'chartswudata';
$wu_server      = $this_server;
$now            = time();
$from_year      = $now - 12*30*3600*24;
$year_month     = date ('m',$from_year);
$year_year      = date ('Y',$from_year);
$timeout_lf     = 20;
#
#
$nm_prt1        = realpath(dirname(__FILE__)).'/'.$chartdata.'/'.$wuID;    // chartswudata/IVLAAMSG47
$filename       = $nm_prt1.'YMD.txt';           // todays data as known by WU
$allowed_age    = 1800*$times;                  // 1800 seconds = 0.5 hour
$url            = $wu_server.'PWS_DailyHistory.php?ID='.$wuID.'&graphspan=day&day&month&year&format=1';   #localhost/pwsWD/   https://www.wunderground.com/weatherstation/
fnctn_load_file ('WU-today-CSV');
#
$now            = time();
$month          = date ('m',$now);
$year           = date ('Y',$now);
$day            = date ('d',$now);
$start          = $now - 31*3600*24;
$from_month     = date ('m',$start);
$from_year      = date ('Y',$start);
$from_day       = date ('d',$start);
#
$filename       = $nm_prt1.'YM.txt';            // this "month" (30 days) data as known by WU
$allowed_age    = 4*3600*$times;
$url            = $wu_server.'PWS_DailyHistory.php?ID='.$wuID.'&graphspan=custom'
                        .  '&year='.$from_year.    '&month='.$from_month.     '&day='.$from_day.
                        '&yearend='.$year.       '&monthend='.$month.       '&dayend='.$day.'&format=1';   #echo $url.PHP_EOL;
fnctn_load_file ('WU-month-CSV');
#
$start          = (int) $now - 365*3600*24;
$from_month     = date ('m',$start);
$from_year      = date ('Y',$start);
$filename       = $nm_prt1.'Y.txt';             // this "year" (360 days) data as known by WU
$allowed_age    = 12*3600*$times;
$url            = $wu_server.'PWS_DailyHistory.php?ID='.$wuID.'&graphspan=custom'
                        .  '&year='.$from_year.    '&month='.$from_month.     '&day='.$day.
                        '&yearend='.$year.       '&monthend='.$month.       '&dayend='.$day.'&format=1';  #echo $url; exit;                       
fnctn_load_file ('WU-year-CSV');
#
$return =  calc_statuses();
if (isset ($_REQUEST['test'])) 
     {  echo '<pre>'.$stck_lst.'</pre>'.PHP_EOL.$return; $stck_lst=''; 
        return;}
if ($times == 1 &&  $return <> '') {echo $return;}
return;      
#
function fnctn_load_file ($string='')
     {  global $now, $filename, $allowed_age, $url, $stck_lst, $header, $fake_keys, $fl_to_load, $timeout_lf, $statuses, $times, $new_loaded ;
        $new_loaded = false;
        if ($string <> '') {$fl_to_load = substr($string.'______________',0,14);}
        if (!isset ($timeout_lf)) {$timeout_lf = 10;}
        $start_time =  microtime(true);
        $fake_url = str_replace ($fake_keys, '_API_SETTING_',$url);
        if ( file_exists($filename))
             {  $lst_ldd= filemtime($filename); #### 2022-12-07
                $age = $now - $lst_ldd;         #### 2022-12-07
                if ($age < $allowed_age)
                     {  $stck_lst .= basename(__FILE__).' ('.__LINE__.') '.$fl_to_load.': File not old enough ('.floor ($age).'/'.$allowed_age.' seconds) '.$fake_url.PHP_EOL; 
                        if (is_array ($statuses) && array_key_exists($string,$statuses ) )      #### 2022-12-07
                             {  $statuses[$string]['last'] = $lst_ldd; }                        #### 2022-12-07
                        return false; } 
                        }
        $fp     = fopen($filename.'rnm', 'wb');
        if ($fp === false) 
             {  $stck_lst .= basename(__FILE__).' ('.__LINE__.') '.$fl_to_load.': NO DATA will be loaded - unable to open file to save data into: '.$filename.'rnm, check permissions of file and folder.'.PHP_EOL;
                return false;}
        $ch     = curl_init(); 
        if (isset ($header) && is_array($header) )
             {  curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
                unset ($header); }
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,$timeout_lf); // connection timeout
        curl_setopt($ch, CURLOPT_TIMEOUT,$timeout_lf);        // data timeout 
        curl_setopt($ch, CURLOPT_FILE, $fp );
curl_setopt ($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36'); # 2023-02-15
#        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');    #### 2020-09-30
        $result = curl_exec ($ch);
        $info	= curl_getinfo($ch);
        $error  = curl_error($ch);
        curl_close ($ch);
       # $now    = microtime(true);  # 2022-08-30
        $passed = microtime(true) -  $start_time;   # $now - $start_time;  # 2022-08-30
        if ($passed < 0.0001) {$string1 = '< 0.0001';} else {$string1 = round($passed,4);}
        $CHECK_HTTP_CODES = array ('404', '429','502', '500');
        if (in_array ($info['http_code'],$CHECK_HTTP_CODES) ) 
             {  $stck_lst .= basename(__FILE__).' ('.__LINE__.') '.$fl_to_load.': time spent: '.$string1.' - PROBLEM => http_code: '.$info['http_code'].', no valid data '.$fake_url.PHP_EOL;
                return false;} 
        if ($error <> '')
             {  $stck_lst .= basename(__FILE__).' ('.__LINE__.') '.$fl_to_load.': time spent: '.$string1.' -  invalid CURL '.$error.' '.$fake_url.PHP_EOL; 
                return false;}  #### 2021-05-29
        else {  $stck_lst .= basename(__FILE__).' ('.__LINE__.') '.$fl_to_load.': time spent: '.$string1.' -  CURL OK for '.$fake_url.PHP_EOL; }
        fclose ($fp);
        if (file_exists($filename.'rnm') && filesize ($filename.'rnm') > 10) 
             {  rename ($filename,$filename.'old');
                $rename = rename ($filename.'rnm',$filename);
                if ($rename === false) 
                     {  $stck_lst .= basename(__FILE__).' ('.__LINE__.') '.$fl_to_load.': File '.$filename .'could not be created, check permissions of file and folder'.PHP_EOL;
                        rename ($filename.'old', $filename);
                        return false;}
                }
        else {  $stck_lst .= basename(__FILE__).' ('.__LINE__.') '.$fl_to_load.': time spent: '.$string1.' -  empty data or wrong file permissions for '.$fake_url.' Old data will be used '.$filename.'rnm'.PHP_EOL;}
        unset  ($ch, $fp);
        $new_loaded = true;
        $key    = trim ($string);
        if (!is_array ($statuses) || $key == '')
             {  return true;}
        if (!array_key_exists($key,$statuses ) )
             {  $arr            = array();
                $arr['fl_name'] = $filename;
                $arr['fl_url']  = $fake_url;
                $arr['fl_allw'] = round ($allowed_age/$times);}     
        else {  $arr    = $statuses[$key];}
        $arr['last']    = time(); 
        $statuses[$key] = $arr;    
} // eof fnctn_load_file