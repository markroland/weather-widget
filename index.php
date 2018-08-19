<?php
/*
  Weather Widget

  This script interprets a Yahoo Weather API data feed and renders the current
  weather conditions as an informational graphic

  Mark Roland <mark at markroland dot com>

  9/24/2011 - Completed

*/

// ---> 1) Get the data

// Create a new cURL resource
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'http://weather.yahooapis.com/forecastrss?w=12786731');
curl_setopt($ch, CURLOPT_HEADER, FALSE);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

// Grab URL and pass it to the browser
$data = curl_exec($ch);

// Close cURL resource, and free up system resources
curl_close($ch);

// View response XML
//echo "<pre>"; var_dump($data); echo "</pre>";

// Read in XML response to test for the last page
$weather = simplexml_load_string($data);



// ---> 2) Parse the data

/*
  The following section of code was taken from these web sites on 9/18/2011. They
  show how to capture custom namespace variables from XML.
  http://pkarl.com/articles/parse-yahoo-weather-rss-using-php-and-simplexml-al/
  http://snipt.net/pkarl/parse-yahoo-weather-feeds-with-simplexml-php
*/

$channel_yweather = $weather->channel->children("http://xml.weather.yahoo.com/ns/rss/1.0");
foreach($channel_yweather as $x => $channel_item){
	foreach($channel_item->attributes() as $k => $attr)
		$yw_channel[$x][$k] = $attr;
}


$item_yweather = $weather->channel->item->children("http://xml.weather.yahoo.com/ns/rss/1.0");
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


// ---> 3) Translate the data

// Parse weather codes, associating code to image
$row = 1;
if(($handle = fopen("yahoo-weather-codes.csv", "r")) !== FALSE){
  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    $weather_image[$data[0]] = $data[2];
  }
  fclose($handle);
}

// Save the weather code as a usable index for the $weather_image array
$weather_image_index = trim($yw_forecast['condition']['code']);


// ---> 4) Render the data



// Create image HTML
print('<!DOCTYPE HTML>
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
      -webkit-border-radius: 10px;
      -moz-border-radius: 10px;
      border-radius: 10px;
      box-shadow: 0 0 5px rgba(0, 0, 0, 0.25);
      -webkit-box-shadow: 0 0 5px rgba(0, 0, 0, 0.25);
      -moz-box-shadow: 0 0 5px rgba(0, 0, 0, 0.25);
	  }
	  #weather_widget h1{
	    font-size: 18px;
	  }
	  #weather_widget p{
	    font-size: 14px;
	  }
	</style>
</head>

<body>'."\n");
print("\t".'<div id="weather_widget">'."\n");
print("\t\t<h1>Current Weather Conditions in Lawrence, Kansas</h1>\n");
printf("\t\t".'<img src="images/%s" width="%d" height="%d" alt="%s" />'."\n",
        $weather_image[$weather_image_index],
        512, 200,
        'Weather Conditions: '.$yw_forecast['condition']['text'] );

printf("\t\t".'<p><b>%s</b> &#8212; %s&deg;F</p>'."\n",
  $yw_forecast['condition']['text'],
  $yw_forecast['condition']['temp'],
  (string)$weather->channel->children("http://xml.weather.yahoo.com/ns/rss/1.0")->atmosphere->attributes()->humidity
);
  

//printf("\t".'<p>Temperature: %s&deg; F</p>'."\n", $yw_forecast['condition']['temp']);
//printf("\t".'<p>Humidity: %s%%</p>'."\n", (string)$weather->channel->children("http://xml.weather.yahoo.com/ns/rss/1.0")->atmosphere->attributes()->humidity);
//printf("\t".'<p>Sunrise: %s</p>'."\n", (string)$weather->channel->children("http://xml.weather.yahoo.com/ns/rss/1.0")->astronomy->attributes()->sunrise);
//printf("\t".'<p>Sunrise: %s</p>'."\n", (string)$weather->channel->children("http://xml.weather.yahoo.com/ns/rss/1.0")->astronomy->attributes()->sunset);
print("\t".'</div>
</body>
</html>');

?>