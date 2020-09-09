<?php
    $method = $_SERVER['REQUEST_METHOD'];
    $dbLink = mysqli_connect("localhost", "root", "root", "RD1_Assignment", 8889) or die(mysqli_connect_error());
    mysqli_query($dbLink, "set names utf8");

    // var_dump($_GET);

    if($method == "GET") {
        getAllCities();
    }
    else if($method == "POST") {
        if(isset($_POST["getWeatherData"])) {
            if($_POST["getWeatherData"] == 1) {
                getWeatherInfoFromInternet();
                $sqlCommand = "INSERT INTO weatherCatch(catchTime) VALUE (CURRENT_TIMESTAMP())";
                mysqli_query($dbLink, $sqlCommand);
            }
        }
        if(isset($_POST["getRainData"])) {
            if($_POST["getRainData"] == 1) {
                getRainInfoFromInternet();
                $sqlCommand = "INSERT INTO rainCatch(catchTime) VALUE (CURRENT_TIMESTAMP())";
                mysqli_query($dbLink, $sqlCommand);
            }
        }

        if(isset($_POST["cityId"])) {
            $cityId = $_POST["cityId"];
            $sqlCommand = "SELECT * FROM weathers WHERE cityId = '$cityId' AND (startTime >= CURRENT_TIMESTAMP() or endTime >= CURRENT_TIMESTAMP())";
            $result = mysqli_query($dbLink, $sqlCommand);
            while($oneRow = mysqli_fetch_assoc($result)) {
                $weatherData[] = $oneRow;
            }
            $returnData["weatherData"] = $weatherData;
            $sqlCommand = "SELECT * FROM rain WHERE cityId = '$cityId' AND obsTime = (SELECT obsTime FROM rain ORDER BY obsTime LIMIT 0,1)";
            $result = mysqli_query($dbLink, $sqlCommand);
            while($oneRow = mysqli_fetch_assoc($result)) {
                $rainData[] = $oneRow;
            }
            $returnData["rainData"] = $rainData;
            $sqlCommand = <<< multi
                SELECT cityPic FROM cities WHERE cityId = $cityId
            multi;
            $result = mysqli_query($dbLink, $sqlCommand);
            $row = mysqli_fetch_assoc($result);
            $returnData["cityPic"] = $row["cityPic"];            
            echo json_encode($returnData);
        }
        else if(isset($_POST["getWeatherCatchTime"])){
            $sqlCommand = "SELECT * FROM weatherCatch ORDER BY catchTime DESC Limit 0,1";
            $result = mysqli_query($dbLink, $sqlCommand);
            $weatherRowNum = mysqli_num_rows($result);
            $catchTimeArray[] = mysqli_fetch_assoc($result);
            $sqlCommand = "SELECT * FROM rainCatch ORDER BY catchTime DESC Limit 0,1";
            $result = mysqli_query($dbLink, $sqlCommand);
            $rainRowNum = mysqli_num_rows($result);
            $catchTimeArray[] = mysqli_fetch_assoc($result);
            if($weatherRowNum == 0 && $rainRowNum != 0) {
                echo '{"errorCode": 1}';
                return;
            }            
            else if($rainRowNum == 0 && $weatherRowNum != 0) {
                echo '{"errorCode": 2}';
                return;
            }
            else if( $rainRowNum == 0 && $weatherRowNum == 0 ){
                echo '{"errorCode": 3}';
                return;
            }
            else {
                $returnData["errorCode"] = 666;
                $returnData["catchTimeArray"] = $catchTimeArray;
                echo json_encode($returnData);                
            }
        }
    }

    class City
    {
        public $locationName;
        public $geoCode;
    }
    class WeatherOfCity
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
                INSERT INTO cities(cityId, cityName) VALUE ('$oneCity->geoCode', '$oneCity->locationName')
            multiLine;
            mysqli_query($dbLink, $sqlCommand);
        }
    }

    function getAllCities() {
        global $dbLink;
        $result = mysqli_query($dbLink, "SELECT * FROM cities");
        while($row = mysqli_fetch_assoc($result)) {
            $allData[] = $row;
        }
        echo json_encode($allData);
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
            $oneCityWeather = new WeatherOfCity();
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

            $cityId = $oneCityWeather->geoCode;
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
                $sqlCommand = <<< multi
                    INSERT INTO weathers(cityId, maxAT, maxT, minAT, minT, pop12h, rh, wd, ws1, ws2, wx, startTime, endTime)
                    VALUES('$cityId', $MaxAT, $MaxT, $MinAT, $MinT, $PoP12H, $RH, '$WD', '$WS1', '$WS2', '$Wx', '$startTime', '$endTime')
                    ON DUPLICATE KEY UPDATE maxAT = $MaxAT, maxT = $MaxT, minAT = $MinAT, minT = $MinT, pop12h = $PoP12H, rh = $RH, wd = '$WD', ws1 = '$WS1', ws2 = '$WS2', wx = '$Wx';
                multi;
                mysqli_multi_query($dbLink, $sqlCommand);
            } 
        }
    }

    function getRainInfoFromInternet() {
        global $dbLink;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://opendata.cwb.gov.tw/api/v1/rest/datastore/O-A0002-001?Authorization=CWB-237F0446-FCE5-4CD0-A343-4C4B52E42D65&format=JSON");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        $rainData = json_decode($result);
        curl_close($ch);
        $allRainData = $rainData->records->location;
        foreach($allRainData AS $oneRainData) {
            $cityName = $oneRainData->parameter[0]->parameterValue;
            $locationName = $oneRainData->locationName;
            $rain = $oneRainData->weatherElement[1]->elementValue;
            $hour_3 = $oneRainData->weatherElement[3]->elementValue;
            $hour_6 = $oneRainData->weatherElement[4]->elementValue;
            $hour_12 = $oneRainData->weatherElement[5]->elementValue;
            $hour_24 = $oneRainData->weatherElement[6]->elementValue;
            $now = $oneRainData->weatherElement[7]->elementValue;
            $obsTime = $oneRainData->time->obsTime;
            $sqlCommand = <<< multi
                INSERT INTO rain(cityId, locationName, rain, hour_3, hour_6, hour_12, hour_24, now, obsTime)
                VALUES ((SELECT cityId FROM cities WHERE cityName = '$cityName'), '$locationName', '$rain', '$hour_3', '$hour_6', '$hour_12', '$hour_24', '$now', '$obsTime')
                ON DUPLICATE KEY UPDATE rain = '$rain', hour_3 = '$hour_3', hour_6 = '$hour_6', hour_12 = '$hour_12', hour_24 = '$hour_24', now = '$now';
            multi;
            mysqli_query($dbLink, $sqlCommand);
        }
    }
    mysqli_close($dbLink);
?>