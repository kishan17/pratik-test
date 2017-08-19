<?php
/**
 * Created by PhpStorm.
 * User: Ashish Mulani
 * Date: 27-01-2017
 * Time: 11:47
 */

set_time_limit(0);
ini_set('memory_limit', '-1');

$base_url = 'http://localhost/wp_demo/';
$wp_username = 'admin';
$wp_password = 'ser@123';


//database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "test";

// Create connection
$mysqli = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if (!mysqli_set_charset($mysqli, "utf8")) {
    // printf("Error loading character set utf8: %s\n", mysqli_error($conn));
    // exit();
} else {
    // printf("Current character set: %s\n", mysqli_character_set_name($conn));
}


function get_love_links($mysqli)
{
    $year = $_GET['year'];
    $url = "http://www.theloverspoint.com/$year/";
    $html_string = file_get_contents($url);
    $dom = new DOMDocument();
    $dom->preserveWhiteSpace = false;
    libxml_use_internal_errors(true);
    $dom->loadHTML($html_string);
    libxml_clear_errors();
    $xpath = new DOMXpath($dom);

    $pg_data = $xpath->query('//a[@class="page-numbers"]/text()');
    $pg_array = array();
    foreach ($pg_data as $pg) {
        $pg_array[] = $pg->textContent;
    }
    $pg_no = end($pg_array);
    $last_page_no = isset($pg_no)?end($pg_array):0;
    for ($i = 1; $i <= $last_page_no; $i++) {
        $url = "http://www.theloverspoint.com/$year/page/$i";
        $html_string = file_get_contents($url);
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        libxml_use_internal_errors(true);
        $dom->loadHTML($html_string);
        libxml_clear_errors();
        $xpath = new DOMXpath($dom);


        $td_data = $xpath->query('//a[@class="read-more-button"]/@href');
//    print_r($td_data);
//    exit;
        foreach ($td_data as $d) {
            $love_link = ($d->value);
            $sql = "INSERT INTO love_link (`love_url`,`year`) VALUES ('$love_link','$year')";
            if ($mysqli->query($sql) === TRUE) {
                echo "$love_link New record created successfully";
                echo "<br><br>";
            } else {
                echo "Error: " . $sql . "<br>" . $mysqli->error;
                echo "<br><br>";
            }

        }
    }

}

get_love_links($mysqli);