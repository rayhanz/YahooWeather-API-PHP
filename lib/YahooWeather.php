<?php

/*
*****************************************************************
*                                                               *
* @ Class Name : Yahoo Weather API                              *
*                                                               *
* @ link : https://github.com/rayhanz/YahooWeather-API-PHP      *
*                                                               *
* @ Date : 27th MArch,2018                                      *
*                                                               *
* @ Author's Name : Rayhan Sardar                               *
*                                                               *
* @ Author's Email : rayhan.host@gmail.com
*
* @ Version : 1.0
*                                                               *
* @ Licence : Apache License 2.0                                *
*                                                               *
*****************************************************************
*/

class YahooWeather {

	const BASE_URL = 'http://query.yahooapis.com/v1/public/yql';

	const CONDITIONS = [
        '0' => 'tornado',
        '1' => 'tropical storm',
        '2' => 'hurricane',
        '3' => 'severe thunderstorms',
        '4' => 'thunderstorms',
        '5' => 'mixed rain and snow',
        '6' => 'mixed rain and sleet',
        '7' => 'mixed snow and sleet',
        '8' => 'freezing drizzle',
        '9' => 'drizzle',
        '10' => 'freezing rain',
        '11' => 'showers',
        '12' => 'showers',
        '13' => 'snow flurries',
        '14' => 'light snow showers',
        '15' => 'blowing snow',
        '16' => 'snow',
        '17' => 'hail',
        '18' => 'sleet',
        '19' => 'dust',
        '20' => 'foggy',
        '21' => 'haze',
        '22' => 'smoky',
        '23' => 'blustery',
        '24' => 'windy',
        '25' => 'cold',
        '26' => 'cloudy',
        '27' => 'mostly cloudy (night)',
        '28' => 'mostly cloudy (day)',
        '29' => 'partly cloudy (night)',
        '30' => 'partly cloudy (day)',
        '31' => 'clear (night)',
        '32' => 'sunny',
        '33' => 'fair (night)',
        '34' => 'fair (day)',
        '35' => 'mixed rain and hail',
        '36' => 'hot',
        '37' => 'isolated thunderstorms',
        '38' => 'scattered thunderstorms',
        '39' => 'scattered thunderstorms',
        '40' => 'scattered showers',
        '41' => 'heavy snow',
        '42' => 'scattered snow showers',
        '43' => 'heavy snow',
        '44' => 'partly cloudy',
        '45' => 'thundershowers',
        '46' => 'snow showers',
        '47' => 'isolated thundershowers',
        '3200' => 'not available',
    ];

	private $WOEID = null;

	private $city = null;
	
	private $unit = null;

	private $units = array();
	
	private $wind = array();
	
	private $atmosphere = array();
	
	private $astronomy = array();
	
	private $location = array();

	private $condition = array();
	
	private $forecast = array();

	private $query = null;

    private $data = null;


	public function __construct()
	{
		$this->WOEID = '';
		$this->city = 'london,uk';
		$this->unit = 'c';
	}


	public function setWOEID($value)
	{
		    $this->WOEID = $value;
	}


	public function setCity($value)
	{
		$this->city = $value;
	}

	public function setUnit($value)
	{
		$this->unit = $value;
	}


	public function doQuery()
	{
		if(!empty($this->WOEID) OR !empty($this->city)) {

        if (!empty($this->WOEID)) {
			$this->query = 'select * from weather.forecast where woeid = "'.$this->WOEID.'" and u = "'.$this->unit.'"';
		} else {
            $this->query = 'select * from weather.forecast where woeid in (select woeid from geo.places(1) where text = "'.$this->city.'") and u = "'.$this->unit.'"';
        }

        $qURL = self::BASE_URL.'?q='.urlencode($this->query).'&format=json';
        $this->data = self::getAPI($qURL);
        $qRESULT = json_decode($this->data)->query->results->channel;

        $this->units['distance'] = $qRESULT->units->distance;
        $this->units['pressure'] = $qRESULT->units->pressure;
        $this->units['speed'] = $qRESULT->units->speed;
        $this->units['temperature'] = $qRESULT->units->temperature;

        $this->location['city'] = $qRESULT->location->city;
        $this->location['region'] = $qRESULT->location->region;
        $this->location['country'] = $qRESULT->location->country;

        $this->wind['chill'] = $qRESULT->wind->chill;
        $this->wind['direction'] = $qRESULT->wind->direction;
        $this->wind['speed'] = $qRESULT->wind->speed;

        $this->atmosphere['humidity'] = $qRESULT->atmosphere->humidity;
        $this->atmosphere['pressure'] = $qRESULT->atmosphere->pressure;
        $this->atmosphere['rising'] = $qRESULT->atmosphere->rising;
        $this->atmosphere['visibility'] = $qRESULT->atmosphere->visibility;

        $this->astronomy['sunrise'] = $qRESULT->astronomy->sunrise;
        $this->astronomy['sunset'] = $qRESULT->astronomy->sunset;

        $this->condition['code'] = $qRESULT->item->condition->code;
        $this->condition['date'] = $qRESULT->item->condition->date;
        $this->condition['temp'] = $qRESULT->item->condition->temp;
        $this->condition['text'] = $qRESULT->item->condition->text;

        foreach ($qRESULT->item->forecast as $value) {
            $this->forecast[] = array('code' => $value->code,'date' => $value->date,'day' => $value->day,'high' => $value->high,'low' => $value->low,'text' => $value->text);
        }

        return $qRESULT;

     } else {
        die('No City or WOEID provided!');
     }

	}

    
    public function isSuccess()
    {
        if (json_decode($this->data)->query->count == '1') {
            return true;
        }
    }
    public function windData()
    {
        return $this->wind;
    }

    public function locationData()
    {
        return $this->location;
    }

    public function atmosphereData()
    {
        return $this->atmosphere;
    }

    public function astronomyData()
    {
        return $this->astronomy;
    }

    public function conditionData()
    {
        return $this->condition;
    }

    public function forecastData()
    {
        return $this->forecast;
    }

    public function getTempreture()
    {
        return $this->condition['temp'].'°'.$this->units['temperature'];
    }

    public function getCondition()
    {
        return $this->condition['text'];
    }

    public function getWindChill()
    {
        return $this->wind['chill'].'°';
    }

    public function getWindDirection()
    {
        return $this->wind['direction'].'°';
    }

    public function getWindSpeed()
    {
        return $this->wind['speed'].$this->units['speed'];
    }

    public function getHumidity()
    {
        return $this->atmosphere['humidity'].'%';
    }

    public function getAirPresure()
    {
        return $this->atmosphere['pressure'].$this->units['pressure'];
    }

    public function getSunRise()
    {
        return $this->astronomy['sunrise'];
    }

    public function getSunSet()
    {
        return $this->astronomy['sunset'];
    }

    private function getAPI($value)
    {
        if(function_exists('curl_version')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        curl_setopt($ch, CURLOPT_TIMEOUT, '10');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, '10');
        curl_setopt($ch, CURLOPT_URL,$value);
        $result=curl_exec($ch);
        curl_close($ch);
        } else {
            $result = file_get_contents($value);
        }

        return $result;
    }
}
