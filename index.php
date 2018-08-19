<?php
/*
Weather Widget

This script interprets a Yahoo Weather API data feed and renders the current
weather conditions as an informational graphic

*/

// ---> 0) Initialize Data

$weather = [
    'text' => 'Unknown',
    'temp' => 'Unknown',
    'humidity' => 'Unknown',
    'image' => 'images/unknown.png'
];

// ---> 1) Get the data

// Create a new cURL resource
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://weather.yahooapis.com/forecastrss?w=12786731');
curl_setopt($ch, CURLOPT_HEADER, FALSE);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
$data = curl_exec($ch);
curl_close($ch);

// Only process response if it is not empty
if ($data) {

    // Read in XML response to test for the last page
    $weather_xml = simplexml_load_string($data);

    // ---> 2) Parse the data

    /*
        The following section of code was taken from these web sites on 9/18/2011. They
        show how to capture custom namespace variables from XML.
        http://pkarl.com/articles/parse-yahoo-weather-rss-using-php-and-simplexml-al/
        http://snipt.net/pkarl/parse-yahoo-weather-feeds-with-simplexml-php
    */

    $channel_yweather = $weather_xml->channel->children("http://xml.weather.yahoo.com/ns/rss/1.0");
    foreach($channel_yweather as $x => $channel_item){
        foreach($channel_item->attributes() as $k => $attr) {
            $yw_channel[$x][$k] = $attr;
        }
    }

    $yw_forecast = array();
    $item_yweather = $weather_xml->channel->item->children("http://xml.weather.yahoo.com/ns/rss/1.0");
    foreach($item_yweather as $x => $yw_item) {
        foreach($yw_item->attributes() as $k => $attr) {
            if($k == 'day')
                $day = $attr;
            if($x == 'forecast'){
                $yw_forecast[$x][$day . ''][$k] = $attr;
            }else{
                $yw_forecast[$x][$k] = $attr;
            }
        }
    }

    if (isset($yw_forecast)) {
        $weather['text'] = $yw_forecast['condition']['text'];
    }
    if (isset($yw_forecast)) {
        $weather['text'] = $yw_forecast['condition']['text'];
    }

    $weather['humidity'] = (string)$weather_xml->channel->children("http://xml.weather.yahoo.com/ns/rss/1.0")->atmosphere->attributes()->humidity;

    // ---> 3) Translate the data

    // Parse weather codes, associating code to image
    if (($handle = fopen(realpath(__DIR__ . '/data' . '/yahoo-weather-codes.csv'), "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $weather_image[$data[0]] = $data[2];
        }
        fclose($handle);
    }
    $weather_image_index = trim($yw_forecast['condition']['code']);
    $weather['image'] = 'images/' . $weather_image[$weather_image_index];
}

?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset=utf-8>
    <title>Weather Widget</title>
    <style type="text/css">
        #weather_widget{
            width: 560px;
            margin: 10px auto;
            padding: 0 10px;
            border: 1px solid #CCC;
            background-color: #FFF;
            font-family: Helvetica, Arial, Sans-serif;
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.25);
        }
        #weather_widget h1{
            font-size: 18px;
        }
        #weather_widget p{
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div id="weather_widget">
        <h1>Current Weather Conditions in Lawrence, Kansas</h1>
        <?php printf('<img src="%s" width="512" height="200" alt="%s" />', $weather['image'], 'Weather Conditions: ' . $weather['text']); ?>
        <?php printf('<p><b>%s</b> &#8212; %s&deg;F</p>', $weather['text'], $weather['temp'], $weather['humidity']); ?>
    </div>
</body>
</html>