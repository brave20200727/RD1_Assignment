<?php
    $method = $_SERVER['REQUEST_METHOD'];
    $dbLink = mysqli_connect("localhost", "root", "root", "RD1_Assignment", 8889) or die(mysqli_connect_error());
    mysqli_query($dbLink, "set names utf8");

    if($method == "GET") {
        if(isset($_GET["cityId"])) {
            getOneCity();
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
?>