<?php
    $method = $_SERVER['REQUEST_METHOD'];
    $dbLink = mysqli_connect("localhost", "root", "root", "RD1_Assignment", 8889) or die(mysqli_connect_error());
    mysqli_query($dbLink, "set names utf8");

    function getAllCities() {
        global $dbLink;
        $result = mysqli_query($dbLink, "SELECT * FROM cities");
        while($row = mysqli_fetch_assoc($result)) {
            $allData[] = $row;
        }
        echo json_encode($allData);
        mysqli_close($dbLink);
    }

    function getOneCity() {
        global $dbLink;
        $cityId = $_GET["cityId"];
        $sqlCommand = "SELECT cityName FROM cities WHERE cityId = $cityId";
        $result = mysqli_query($dbLink, $sqlCommand);
        $row = mysqli_fetch_assoc($result);
        echo json_encode($row);
        mysqli_close($dbLink);
    }

    function getWeatherInfoForInternet() {
        global $dbLink;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://opendata.cwb.gov.tw/api/v1/rest/datastore/F-D0047-091?Authorization=CWB-237F0446-FCE5-4CD0-A343-4C4B52E42D65&format=JSON&sort=time");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        $answer = json_decode($result);
        // var_dump($answer->records->locations[0]);
        // $cityWeather = $answer->records->locations[0]->location[0];
        // $cityName = $cityWeather->locationName;

        $allCityWeather = $answer->records->locations[0]->location;

        for($i = 0; $i < count($allCityWeather); $i++) {
            $cityName = $allCityWeather[$i]->locationName;
            $weatherElementPoP12H = $allCityWeather[$i]->weatherElement[0]; // 12小時降雨機率
            $weatherElementRH = $allCityWeather[$i]->weatherElement[2]; // 平均相對濕度
            $weatherElementWS = $allCityWeather[$i]->weatherElement[4]; // 風速
            $weatherElementWD = $allCityWeather[$i]->weatherElement[13]; // 風向
            $weatherElementMaxAT = $allCityWeather[$i]->weatherElement[5]; // 最高體感溫度
            $weatherElementMinAT = $allCityWeather[$i]->weatherElement[11]; // 最低體感溫度
            $weatherElementMaxT = $allCityWeather[$i]->weatherElement[12]; // 最高溫度
            $weatherElementMinT = $allCityWeather[$i]->weatherElement[8]; // 最低溫度
            $weatherElementWx = $allCityWeather[$i]->weatherElement[6]; // 天氣現象

            $allWeatherElementPoP12H = $weatherElementPoP12H->time;
            foreach($allWeatherElementPoP12H AS $oneRow) {
                $oneRowData['locationName'] = $cityName;
                $oneRowData['startTime'] = $oneRow->startTime;
                $oneRowData['endTime'] = $oneRow->endTime;
                if($oneRow->elementValue[0]->value == " ") {
                    $oneRowData['PoP12H'] = '0';
                }
                else {
                    $oneRowData['PoP12H'] = $oneRow->elementValue[0]->value;
                }

                $allData[] = $oneRowData;
            }
            $allWeatherElementRH = $weatherElementRH->time;
            for($i = 0; $i < count($allWeatherElementRH); $i++) {
                $allData[$i]['RH'] = $allWeatherElementRH[$i]->elementValue[0]->value;
            }
            $allWeatherElementWS = $weatherElementWS->time;
            for($i = 0; $i < count($allWeatherElementWS); $i++) {
                $allData[$i]['WS1'] = $allWeatherElementWS[$i]->elementValue[0]->value;
                $allData[$i]['WS2'] = $allWeatherElementWS[$i]->elementValue[1]->value;
            }
            $allWeatherElementWD = $weatherElementWD->time;
            for($i = 0; $i < count($allWeatherElementWD); $i++) {
                $allData[$i]['WD'] = $allWeatherElementWD[$i]->elementValue[0]->value;
            }
            $allWeatherElementMaxAT = $weatherElementMaxAT->time;
            for($i = 0; $i < count($allWeatherElementMaxAT); $i++) {
                $allData[$i]['MaxAT'] = $allWeatherElementMaxAT[$i]->elementValue[0]->value;
            }
            $allWeatherElementMinAT = $weatherElementMinAT->time;
            for($i = 0; $i < count($allWeatherElementMinAT); $i++) {
                $allData[$i]['MinAT'] = $allWeatherElementMinAT[$i]->elementValue[0]->value;
            }
            $allWeatherElementMaxT = $weatherElementMaxT->time;
            for($i = 0; $i < count($allWeatherElementMaxT); $i++) {
                $allData[$i]['MaxT'] = $allWeatherElementMaxT[$i]->elementValue[0]->value;
            }
            $allWeatherElementMinT = $weatherElementMinT->time;
            for($i = 0; $i < count($allWeatherElementMinT); $i++) {
                $allData[$i]['MinT'] = $allWeatherElementMinT[$i]->elementValue[0]->value;
            }
            $allWeatherElementWx = $weatherElementWx->time;
            for($i = 0; $i < count($allWeatherElementWx); $i++) {
                $allData[$i]['Wx'] = $allWeatherElementWx[$i]->elementValue[0]->value;
            }

            // var_dump($allData);
            // unset($allData);
        }

        // $weatherElementPoP12H = $cityWeather->weatherElement[0]; // 12小時降雨機率
        // $weatherElementRH = $cityWeather->weatherElement[2]; // 平均相對濕度
        // $weatherElementWS = $cityWeather->weatherElement[4]; // 風速
        // $weatherElementWD = $cityWeather->weatherElement[13]; // 風向
        // $weatherElementMaxAT = $cityWeather->weatherElement[5]; // 最高體感溫度
        // $weatherElementMinAT = $cityWeather->weatherElement[11]; // 最低體感溫度
        // $weatherElementMaxT = $cityWeather->weatherElement[12]; // 最高溫度
        // $weatherElementMinT = $cityWeather->weatherElement[8]; // 最低溫度
        // $weatherElementWx = $cityWeather->weatherElement[6]; // 天氣現象

        // $allWeatherElementPoP12H = $weatherElementPoP12H->time;
        // foreach($allWeatherElementPoP12H AS $oneRow) {
        //     $oneRowData['locationName'] = $cityName;
        //     $oneRowData['startTime'] = $oneRow->startTime;
        //     $oneRowData['endTime'] = $oneRow->endTime;
        //     if($oneRow->elementValue[0]->value == " ") {
        //         $oneRowData['PoP12H'] = '0';
        //     }
        //     else {
        //         $oneRowData['PoP12H'] = $oneRow->elementValue[0]->value;
        //     }

        //     $allData[] = $oneRowData;
        // }
        // $allWeatherElementRH = $weatherElementRH->time;
        // for($i = 0; $i < count($allWeatherElementRH); $i++) {
        //     $allData[$i]['RH'] = $allWeatherElementRH[$i]->elementValue[0]->value;
        // }
        // $allWeatherElementWS = $weatherElementWS->time;
        // for($i = 0; $i < count($allWeatherElementWS); $i++) {
        //     $allData[$i]['WS1'] = $allWeatherElementWS[$i]->elementValue[0]->value;
        //     $allData[$i]['WS2'] = $allWeatherElementWS[$i]->elementValue[1]->value;
        // }
        // $allWeatherElementWD = $weatherElementWD->time;
        // for($i = 0; $i < count($allWeatherElementWD); $i++) {
        //     $allData[$i]['WD'] = $allWeatherElementWD[$i]->elementValue[0]->value;
        // }
        // $allWeatherElementMaxAT = $weatherElementMaxAT->time;
        // for($i = 0; $i < count($allWeatherElementMaxAT); $i++) {
        //     $allData[$i]['MaxAT'] = $allWeatherElementMaxAT[$i]->elementValue[0]->value;
        // }
        // $allWeatherElementMinAT = $weatherElementMinAT->time;
        // for($i = 0; $i < count($allWeatherElementMinAT); $i++) {
        //     $allData[$i]['MinAT'] = $allWeatherElementMinAT[$i]->elementValue[0]->value;
        // }
        // $allWeatherElementMaxT = $weatherElementMaxT->time;
        // for($i = 0; $i < count($allWeatherElementMaxT); $i++) {
        //     $allData[$i]['MaxT'] = $allWeatherElementMaxT[$i]->elementValue[0]->value;
        // }
        // $allWeatherElementMinT = $weatherElementMinT->time;
        // for($i = 0; $i < count($allWeatherElementMinT); $i++) {
        //     $allData[$i]['MinT'] = $allWeatherElementMinT[$i]->elementValue[0]->value;
        // }
        // $allWeatherElementWx = $weatherElementWx->time;
        // for($i = 0; $i < count($allWeatherElementWx); $i++) {
        //     $allData[$i]['Wx'] = $allWeatherElementWx[$i]->elementValue[0]->value;
        // }

        // var_dump($allData);

        // $elementArray = $weatherElementPoP12H->time;
        // foreach($elementArray AS $oneElement) {
        //     $startTime = $oneElement->startTime;
        //     $endTime = $oneElement->endTime;
        //     $elementValue = $oneElement->elementValue[0]->value;
        //     if($elementValue == " ") {
        //         $elementValue = 0;
        //     }
        //     $elementUnit = $oneElement->elementValue[0]->measures;
        //     $sqlCommand = "REPLACE INTO PoP12H(cityId, startTime, endTime, elementValue, unit) VALUES ((SELECT cityId FROM cities WHERE cityName = '$cityName'), '$startTime', '$endTime', $elementValue, '$elementUnit')";
        //     mysqli_query($dbLink, $sqlCommand);
        // }
        // $elementArray = $weatherElementRH->time;
        // // var_dump($elementArray);
        // foreach($elementArray AS $oneElement) {
        //     $startTime = $oneElement->startTime;
        //     $endTime = $oneElement->endTime;
        //     $elementValue = $oneElement->elementValue[0]->value;
        //     $elementUnit = $oneElement->elementValue[0]->measures;
        //     $sqlCommand = "REPLACE INTO RH(cityId, startTime, endTime, elementValue, unit) VALUES ((SELECT cityId FROM cities WHERE cityName = '$cityName'), '$startTime', '$endTime', $elementValue, '$elementUnit')";
        //     mysqli_query($dbLink, $sqlCommand);
        // }
        // $elementArray = $weatherElementWS->time;
        // // var_dump($elementArray);
        // foreach($elementArray AS $oneElement) {
        //     $startTime = $oneElement->startTime;
        //     $endTime = $oneElement->endTime;
        //     $elementValue = $oneElement->elementValue[0]->value;
        //     $elementUnit = $oneElement->elementValue[0]->measures;
        //     $elementValue2 = $oneElement->elementValue[1]->value;
        //     $elementUnit2 = $oneElement->elementValue[1]->measures;
        //     $sqlCommand = "REPLACE INTO WS(cityId, startTime, endTime, elementValue, unit, elementValue2, unit2) VALUES ((SELECT cityId FROM cities WHERE cityName = '$cityName'), '$startTime', '$endTime', $elementValue, '$elementUnit', $elementValue2, '$elementUnit2')";
        //     mysqli_query($dbLink, $sqlCommand);
        // }
        // $elementArray = $weatherElementWD->time;
        // // var_dump($elementArray);
        // foreach($elementArray AS $oneElement) {
        //     $startTime = $oneElement->startTime;
        //     $endTime = $oneElement->endTime;
        //     $elementValue = $oneElement->elementValue[0]->value;
        //     $elementUnit = $oneElement->elementValue[0]->measures;
        //     $sqlCommand = "REPLACE INTO WD(cityId, startTime, endTime, elementValue, unit) VALUES ((SELECT cityId FROM cities WHERE cityName = '$cityName'), '$startTime', '$endTime', '$elementValue', '$elementUnit')";
        //     mysqli_query($dbLink, $sqlCommand);
        // }
        // $elementArray = $weatherElementMaxAT->time;
        // // var_dump($elementArray);
        // foreach($elementArray AS $oneElement) {
        //     $startTime = $oneElement->startTime;
        //     $endTime = $oneElement->endTime;
        //     $elementValue = $oneElement->elementValue[0]->value;
        //     $elementUnit = $oneElement->elementValue[0]->measures;
        //     $sqlCommand = "REPLACE INTO MaxAT(cityId, startTime, endTime, elementValue, unit) VALUES ((SELECT cityId FROM cities WHERE cityName = '$cityName'), '$startTime', '$endTime', $elementValue, '$elementUnit')";
        //     mysqli_query($dbLink, $sqlCommand);
        // }
        // $elementArray = $weatherElementMinAT->time;
        // // var_dump($elementArray);
        // foreach($elementArray AS $oneElement) {
        //     $startTime = $oneElement->startTime;
        //     $endTime = $oneElement->endTime;
        //     $elementValue = $oneElement->elementValue[0]->value;
        //     $elementUnit = $oneElement->elementValue[0]->measures;
        //     $sqlCommand = "REPLACE INTO MinAT(cityId, startTime, endTime, elementValue, unit) VALUES ((SELECT cityId FROM cities WHERE cityName = '$cityName'), '$startTime', '$endTime', $elementValue, '$elementUnit')";
        //     mysqli_query($dbLink, $sqlCommand);
        // }
        // $elementArray = $weatherElementMaxT->time;
        // // var_dump($elementArray);
        // foreach($elementArray AS $oneElement) {
        //     $startTime = $oneElement->startTime;
        //     $endTime = $oneElement->endTime;
        //     $elementValue = $oneElement->elementValue[0]->value;
        //     $elementUnit = $oneElement->elementValue[0]->measures;
        //     $sqlCommand = "REPLACE INTO MaxT(cityId, startTime, endTime, elementValue, unit) VALUES ((SELECT cityId FROM cities WHERE cityName = '$cityName'), '$startTime', '$endTime', $elementValue, '$elementUnit')";
        //     mysqli_query($dbLink, $sqlCommand);
        // }
        // $elementArray = $weatherElementMinT->time;
        // // var_dump($elementArray);
        // foreach($elementArray AS $oneElement) {
        //     $startTime = $oneElement->startTime;
        //     $endTime = $oneElement->endTime;
        //     $elementValue = $oneElement->elementValue[0]->value;
        //     $elementUnit = $oneElement->elementValue[0]->measures;
        //     $sqlCommand = "REPLACE INTO MinT(cityId, startTime, endTime, elementValue, unit) VALUES ((SELECT cityId FROM cities WHERE cityName = '$cityName'), '$startTime', '$endTime', $elementValue, '$elementUnit')";
        //     mysqli_query($dbLink, $sqlCommand);
        // }
        // $elementArray = $weatherElementWx->time;
        // // var_dump($elementArray);
        // foreach($elementArray AS $oneElement) {
        //     $startTime = $oneElement->startTime;
        //     $endTime = $oneElement->endTime;
        //     $elementValue = $oneElement->elementValue[0]->value;
        //     $sqlCommand = "REPLACE INTO Wx(cityId, startTime, endTime, elementValue) VALUES ((SELECT cityId FROM cities WHERE cityName = '$cityName'), '$startTime', '$endTime', '$elementValue')";
        //     mysqli_query($dbLink, $sqlCommand);
        // }
        // curl_close($ch);
    }

    // if($method == "GET") {
    //     if(isset($_GET["cityId"])) {
    //         // getWeatherInfoForInternet();
    //         // getOneCity();
    //         getDataFromDatabase();
    //     }
    //     else {
    //         getAllCities();
    //     }
    // }

    getWeatherInfoForInternet();
?>