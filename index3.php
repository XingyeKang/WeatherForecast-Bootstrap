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
            $hours[$i]["temp"]=$foreJson->hourly->data[$i]->temperature;

        }

        //7 days
        for($j=0;$j<7;$j++){
            $temp=$j+1;
            $days[$j]["week"]=date("l",$foreJson->daily->data[$temp]->time);
            $days[$j]["day"]=date("M j",$foreJson->daily->data[$temp]->time);
            $days[$j]["icon"]=$foreJson->daily->data[$temp]->icon;
            $days[$j]["temperatureMin"]=$foreJson->daily->data[$temp]->temperatureMin;
            $days[$j]["temperatureMax"]=$foreJson->daily->data[$temp]->temperatureMax;
        }

    }

    $dataSource = array("current"=>$current,"days"=>$days,"hours"=>$hours);

    $dataSource=json_encode($dataSource);
    echo $dataSource;
    exit;



?>