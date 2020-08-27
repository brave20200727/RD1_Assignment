<?php
    $method = $_SERVER['REQUEST_METHOD'];
    $dbLink = mysqli_connect("localhost", "root", "root", "RD1_Assignment", 8889) or die(mysqli_connect_error());
    mysqli_query($dbLink, "set names utf8");

    // getAllCities();
    // getWeatherInfoFromInternet();
    // insertCitis();
    if($method == "GET") {
        if(isset($_GET["cityId"])) {
            $cityId = $_GET["cityId"];
            $sqlCommand = "SELECT * FROM weathers WHERE cityId = '$cityId'";
            $result = mysqli_query($dbLink, $sqlCommand);
            while($oneRow = mysqli_fetch_assoc($result)) {
                $allData[] = $oneRow;
            }
            echo json_encode($allData);
        }
        else {
            getAllCities();
        }
    }

    class City
    {
        public $locationName;
        public $geoCode;
    }
    class CweatherOfCity
    {
        public $geoCode;
        public $startTime;
        public $endTime;
        public $PoP12H;
        public $RH;
        public $WS1;
        public $WS2;
        public $WD;
        public $Wx;
        public $MaxAT;
        public $MinAT;
        public $MaxT;
        public $MinT;
    }

    function insertCitis() {
        global $dbLink;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://opendata.cwb.gov.tw/api/v1/rest/datastore/F-D0047-091?Authorization=CWB-237F0446-FCE5-4CD0-A343-4C4B52E42D65&format=JSON&sort=time");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        $data = json_decode($result);
        curl_close($ch);
        for($i = 0; $i < count($data->records->locations[0]->location); $i++) {
            $oneCity = new City();
            $oneCity->locationName = $data->records->locations[0]->location[$i]->locationName;
            $oneCity->geoCode = $data->records->locations[0]->location[$i]->geocode;
            var_dump($oneCity);
            $sqlCommand = <<<multiLine
                INSERT INTO cities(cityId, cityName) VALUE ($oneCity->geoCode, '$oneCity->locationName')
            multiLine;
            mysqli_query($dbLink, $sqlCommand);
        }
        mysqli_close($dbLink);
    }

    function getAllCities() {
        global $dbLink;
        $result = mysqli_query($dbLink, "SELECT * FROM cities");
        while($row = mysqli_fetch_assoc($result)) {
            $allData[] = $row;
        }
        echo json_encode($allData);
        mysqli_close($dbLink);
    }

    function getWeatherInfoFromInternet() {
        global $dbLink;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://opendata.cwb.gov.tw/api/v1/rest/datastore/F-D0047-091?Authorization=CWB-237F0446-FCE5-4CD0-A343-4C4B52E42D65&format=JSON&sort=time");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        $data = json_decode($result);
        curl_close($ch);
        for($i = 0; $i < count($data->records->locations[0]->location); $i++) {
            $oneCityWeather = new CweatherOfCity();
            $oneCityWeather->geoCode = $data->records->locations[0]->location[$i]->geocode;
            for($j = 0; $j < 14; $j++){
                $oneCityWeather->startTime[] = $data->records->locations[0]->location[$i]->weatherElement[0]->time[$j]->startTime;
                $oneCityWeather->endTime[] = $data->records->locations[0]->location[$i]->weatherElement[0]->time[$j]->endTime;
                if($data->records->locations[0]->location[$i]->weatherElement[0]->time[$j]->elementValue[0]->value == " ") {
                    $oneCityWeather->PoP12H[] = "0";
                }
                else {
                    $oneCityWeather->PoP12H[] = $data->records->locations[0]->location[$i]->weatherElement[0]->time[$j]->elementValue[0]->value;
                }
                $oneCityWeather->RH[] = $data->records->locations[0]->location[$i]->weatherElement[2]->time[$j]->elementValue[0]->value;
                $oneCityWeather->WS1[] = $data->records->locations[0]->location[$i]->weatherElement[4]->time[$j]->elementValue[0]->value;
                $oneCityWeather->WS2[] = $data->records->locations[0]->location[$i]->weatherElement[4]->time[$j]->elementValue[1]->value;
                $oneCityWeather->MaxAT[] = $data->records->locations[0]->location[$i]->weatherElement[5]->time[$j]->elementValue[0]->value;
                $oneCityWeather->Wx[] = $data->records->locations[0]->location[$i]->weatherElement[6]->time[$j]->elementValue[0]->value;
                $oneCityWeather->MinT[] = $data->records->locations[0]->location[$i]->weatherElement[8]->time[$j]->elementValue[0]->value;
                $oneCityWeather->MinAT[] = $data->records->locations[0]->location[$i]->weatherElement[11]->time[$j]->elementValue[0]->value;
                $oneCityWeather->MaxT[] = $data->records->locations[0]->location[$i]->weatherElement[12]->time[$j]->elementValue[0]->value;
                $oneCityWeather->WD[] = $data->records->locations[0]->location[$i]->weatherElement[13]->time[$j]->elementValue[0]->value;
            }
            // var_dump($oneCityWeather);
            $cityId = $oneCityWeather->geoCode;
            $sqlCommand = "REPLACE INTO weathers(cityId, maxAT, maxT, minAT, minT, pop12h, rh, wd, ws1, ws2, wx, startTime, endTime) VALUES";
            for($j = 0; $j < 14; $j++) {
                $startTime = $oneCityWeather->startTime[$j];
                $endTime = $oneCityWeather->endTime[$j];
                $PoP12H = $oneCityWeather->PoP12H[$j];
                $RH = $oneCityWeather->RH[$j];
                $WS1 = $oneCityWeather->WS1[$j];
                $WS2 = $oneCityWeather->WS2[$j];
                $MaxAT = $oneCityWeather->MaxAT[$j];
                $MinAT = $oneCityWeather->MinAT[$j];
                $Wx = $oneCityWeather->Wx[$j];
                $MaxT = $oneCityWeather->MaxT[$j];
                $MinT = $oneCityWeather->MinT[$j];
                $WD = $oneCityWeather->WD[$j];
                if($j == 0) {
                    $sqlCommand = $sqlCommand . " ('$cityId', $MaxAT, $MaxT, $MinAT, $MinT, $PoP12H, $RH, '$WD', '$WS1', '$WS2', '$Wx', '$startTime', '$endTime')";
                }
                else{
                    $sqlCommand = $sqlCommand . ", ('$cityId', $MaxAT, $MaxT, $MinAT, $MinT, $PoP12H, $RH, '$WD', '$WS1', '$WS2', '$Wx', '$startTime', '$endTime')";
                }
            }
            echo $sqlCommand . "<br>";
            mysqli_query($dbLink, $sqlCommand);
        }
        mysqli_close($dbLink);
    }
?>