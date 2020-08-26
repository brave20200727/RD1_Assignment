<?php
    $method = $_SERVER['REQUEST_METHOD'];
    $dbLink = mysqli_connect("localhost", "root", "root", "RD1_Assignment", 8889) or die(mysqli_connect_error());
    mysqli_query($dbLink, "set names utf8");

    if($method == "GET") {
        if(isset($_GET["cityId"])) {
            getOneCity();
        }
        else if(isset($_GET["getWeather"])) {
            getWeatherInfoForInternet();
        }
        else {
            getAllCities();
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

    function getOneCity() {
        global $dbLink;
        $cityId = $_GET["cityId"];
        $selectCommand = "SELECT cityName FROM cities WHERE cityId = $cityId";
        $result = mysqli_query($dbLink, $selectCommand);
        $row = mysqli_fetch_assoc($result);
        echo json_encode($row);
    }

    function getWeatherInfoForInternet() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://opendata.cwb.gov.tw/api/v1/rest/datastore/F-D0047-091?Authorization=CWB-237F0446-FCE5-4CD0-A343-4C4B52E42D65&format=JSON&sort=time");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        $answer = json_decode($result);
        $cityWeather = $answer->records->locations[0]->location[0];
        // var_dump($cityWeather);
        $cityName = $cityWeather->locationName;
        $weatherElementPoP12H = $cityWeather->weatherElement[0]; // 12小時降雨機率
        $weatherElementT = $cityWeather->weatherElement[1]; // 平均溫度
        $weatherElementRH = $cityWeather->weatherElement[2]; // 平均相對濕度
        $weatherElementWS = $cityWeather->weatherElement[4]; // 風速
        $weatherElementED = $cityWeather->weatherElement[13]; // 風向
        $weatherElementMaxAT = $cityWeather->weatherElement[5]; // 最高體感溫度
        $weatherElementMinAT = $cityWeather->weatherElement[11]; // 最低體感溫度
        $weatherElementMaxT = $cityWeather->weatherElement[12]; // 最高溫度
        $weatherElementMinT = $cityWeather->weatherElement[8]; // 最低溫度
        $weatherElementWx = $cityWeather->weatherElement[6]; // 天氣現象
        $weatherElementUVI = $cityWeather->weatherElement[9]; // 紫外線指數
        $weatherElementTd = $cityWeather->weatherElement[14]; // 平均露點溫度
        // foreach($cityWeather AS $temp) {
        //     echo $temp->locationName . "<br>";
        // }
        // var_dump($weatherElementTime);
        echo $result;
        curl_close($ch);
    }
?>