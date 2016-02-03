<?php

    header('Access-Control-Allow-Origin: *');
    $geoUrl='';

    $address=$_GET["street"].",".$_GET["city"].",".$_GET["states"];
    //https://maps.googleapis.com/maps/api/geocode/xml?address=1600+Amphitheatre+Parkway,+Mountain+View,+CA&key=YOUR_API_KEY
    $geoUrl="https://maps.googleapis.com/maps/api/geocode/xml?address=".urlencode($address)."&key=AIzaSyD3gk16791NQFJ9pzKolCi5zo6MBUKncYU";

    $geocodexml=new SimpleXMLElement(file_get_contents($geoUrl));

    $status=$geocodexml->status;

    define("key", "5db69cc628dc3f8e5b2a294919df319e");

    $forecastUrl="";
    $lat="";
    $lng="";


    if($status=="OK"){
        $lat=$geocodexml->result->geometry->location->lat;
        $lng=$geocodexml->result->geometry->location->lng;
        $forecastUrl="https://api.forecast.io/forecast/".key."/".$lat.",".$lng."?units=".$_GET["temp"]."&exclude=flags";
        //echo $forecastUrl;

    }
    else {echo "Request from Google was Failed"; return;}


    $foreJson=file_get_contents($forecastUrl);
    $foreJson=utf8_encode($foreJson);
    $foreJson=json_decode($foreJson);


    $dataSource = array();
    $current = array();
    $days = array();
    $hours = array();

    if($foreJson) {

        global $current;
        global $days;
        global $hours;

        //Right Now
        $current["precipitation"]= $foreJson->currently->precipIntensity;
        $current["summary"] = $foreJson->currently->summary;
        $current["temperature"] = round($foreJson->currently->temperature,0);
        $current["icon"] = $foreJson->currently->icon;
        $current["chanceofrain"] = $foreJson->currently->precipProbability;
        $current["windspeed"] = $foreJson->currently->windSpeed;
        $current["dewpoint"] = $foreJson->currently->dewPoint;
        $current["humidity"] = $foreJson->currently->humidity;
        if(array_key_exists("visibility",$foreJson->currently)){
            $current["visibility"] = $foreJson->currently->visibility;
        }
        else{
            $current["visibility"] = "N/A";
        }

        $current["long"]=$foreJson->longitude;
        $current["latt"]=$foreJson->latitude;

        //sunrise sunset timezone
        $timezone=$foreJson->timezone;
        $sunrise=$foreJson->daily->data[0]->sunriseTime;
        $sunset=$foreJson->daily->data[0]->sunsetTime;
        date_default_timezone_set($timezone);
        $sunrise=date("h:i A",$sunrise);
        $sunset=date("h:i A",$sunset);

        $current["timezone"] = $foreJson->timezone;
        $current["sunrise"] = $sunrise;
        $current["sunset"] = $sunset;

        $current["lowtemp"] = round($foreJson->daily->data[0]->temperatureMin,0);
        $current["hightemp"] = round($foreJson->daily->data[0]->temperatureMax,0);


        //24 hours
        for($i=0;$i<48;$i++) {

            $hours[$i]["time"]=date("h:i A",$foreJson->hourly->data[$i]->time);
            $hours[$i]["summary"]=$foreJson->hourly->data[$i]->icon;
            $hours[$i]["cloudCover"]=$foreJson->hourly->data[$i]->cloudCover;
            $hours[$i]["temp"]=$foreJson->hourly->data[$i]->temperature;
            $hours[$i]["wind"]=$foreJson->hourly->data[$i]->windSpeed;

            if(array_key_exists("visibility",$foreJson->hourly->data[$i])){
                $hours[$i]["visibility"]=$foreJson->hourly->data[$i]->visibility;
            }
            else{
                $hours[$i]["visibility"]="N/A";
            }
            $hours[$i]["humidity"]=$foreJson->hourly->data[$i]->humidity;
            $hours[$i]["pressure"]=$foreJson->hourly->data[$i]->pressure;


        }

        //7 days
        for($j=1;$j<8;$j++){
            $days[$j]["week"]=date("l",$foreJson->daily->data[$j]->time);
            $days[$j]["day"]=date("M j",$foreJson->daily->data[$j]->time);
            $days[$j]["icon"]=$foreJson->daily->data[$j]->icon;
            $days[$j]["temperatureMin"]=$foreJson->daily->data[$j]->temperatureMin;
            $days[$j]["temperatureMax"]=$foreJson->daily->data[$j]->temperatureMax;
            $days[$j]["summary"]=$foreJson->daily->data[$j]->summary;
            $days[$j]["windspeed"]=$foreJson->daily->data[$j]->windSpeed;

            if(array_key_exists("visibility",$foreJson->daily->data[$j]))
            {
                $days[$j]["visibility"]=$foreJson->daily->data[$j]->visibility;
            }
            else{
                $days[$j]["visibility"]="N/A";
            }

            $days[$j]["pressure"]=$foreJson->daily->data[$j]->pressure;
            $days[$j]["humidity"]=$foreJson->daily->data[$j]->humidity;
            $days[$j]["sunrise"]=date("h:i A",$foreJson->daily->data[$j]->sunriseTime);
            $days[$j]["sunset"]=date("h:i A",$foreJson->daily->data[$j]->sunsetTime);

        }

    }

    $dataSource = array("current"=>$current,"days"=>$days,"hours"=>$hours);

    $dataSource=json_encode($dataSource);
    echo $dataSource;
    exit;
/*
    $currently=$foreJson->currently;
    $daily=$foreJson->daily;
    $hourly=array();
    for($i=0;$i<24;$i++) {
        $hourly[$i]=$foreJson->hourly->data[$i];
    }
*/
    //$result=array("currently"=>$currently);
    //$result=array("currently"=>$currently,"daily"=>$daily,"hourly"=>$hourly);
    //$result=json_encode($result);

    /*
    $result1=array("currently"=>$currently);
    $result2=array("daily"=>$daily);
    $result3=array("hourly"=>$hourly);
    $result1=json_encode($result1);
    $result2=json_encode($result2);
    $result3=json_encode($result3);

    echo $result1;
    //echo $result2;
    //echo $result3;
    exit;
    */



?>