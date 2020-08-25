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
        curl_setopt($ch, CURLOPT_URL, "https://opendata.cwb.gov.tw/api/v1/rest/datastore/F-C0032-001?Authorization=CWB-237F0446-FCE5-4CD0-A343-4C4B52E42D65&sort=time");
        curl_setopt($ch, CURLOPT_HEADER, false);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // $result = curl_exec($ch);
        // $answer = json_decode($result);
        // var_dump($answer);
        curl_exec($ch);
        curl_close($ch);
    }
?>