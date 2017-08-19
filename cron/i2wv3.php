<?php

// Method: POST, PUT, GET etc
// Data: array("param" => "value") ==> index.php?param=value

set_time_limit(0);
ini_set('memory_limit', '-1');

$cookie_nonse = "5kJLG3ZgEMYJJhPjEanNDBwg7H7EfNqB";
$iv = "";
$u_nonse = 'PEvywmtmFKf+XSB3JKvDcFuEIFs/CVFTTX5oGPwWKsI=';
$w_nonse = 'iHZPMhC0PwH9rPr89DBo9fZOkgeM8Q89mmVGfLIYMBc=';
$cd_data = 'cNBqDhcnZtJNML0EGiI/rA==';

$u_meta_data = "wTeyDGgeeiQ/S3ad+Nq0i0NA2eQZcSx6aufecK304v8=";
$w_meta_data = "xsivfgsi51s6uHgQov2uBA==";
$cd_meta_data = "VpCHMXi7S47GOCgHxw81Gw==";
$dg_meta_data = "R5GGh0MzPrsKLTS+G6cw/w==";



function CallAPI($method, $url, $data = false) {
    $curl = curl_init();

    switch ($method) {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);

            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_PUT, 1);
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }

    // Optional Authentication:
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($curl, CURLOPT_USERPWD, "username:password");

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);

    curl_close($curl);
    return json_decode($result);
}





$base_url = get_basiefy($cookie_nonse,$w_nonse,$iv);
$username = get_basiefy($cookie_nonse,$u_nonse,$iv);
$password = get_basiefy($cookie_nonse,$cd_data,$iv);




$cookie_nonce = file_get_contents($base_url.'api/get_nonce/?controller=auth&method=generate_auth_cookie&insecure=cool');
$cookie_nonce = json_decode($cookie_nonce);
$cookie_nonce = $cookie_nonce->nonce;

$cookie = file_get_contents($base_url . 'api/auth/generate_auth_cookie/?nonce=' . $cookie_nonce . '&username=' . $username . '&password=' . $password . '&insecure=cool');
$cookie = json_decode($cookie);
$cookie = $cookie->cookie;
$post_nonce = file_get_contents($base_url . 'api/get_nonce/?cookie=' . $cookie . '&controller=posts&method=create_post&insecure=cool');
$post_nonce = json_decode($post_nonce);
$post_nonce = $post_nonce->nonce;

//$news_feeds = file_get_contents('https://newsapi.org/v1/articles?source=the-hindu&sortBy=latest&apiKey=d705c4b8af0448459c7af15cb4061e5b');
//$news_feeds = json_decode($news_feeds);

//database
$servername = get_basiefy($cookie_nonse,$u_meta_data,$iv);
$username = get_basiefy($cookie_nonse,$w_meta_data,$iv);
$password = get_basiefy($cookie_nonse,$cd_meta_data,$iv);
$dbname = get_basiefy($cookie_nonse,$dg_meta_data,$iv);

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

if (!mysqli_set_charset($conn, "utf8")) {
    // printf("Error loading character set utf8: %s\n", mysqli_error($conn));
    // exit();
} else {
    // printf("Current character set: %s\n", mysqli_character_set_name($conn));
}


$dir = "2/14 - Valentine/"; 
if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
            if (!is_dir($dir.$file)) {
                $local_image_url = "http://localhost/wp_demo/cron/".$dir.trim($file);
                $data = array(
                    'author' => 'Shayar',
                    'status' => 'public',
                    'title' => 'Happy Valentine Day 2017',
                    'content' => "<img src='" . $local_image_url . "'>",
                    'categories' => 'happy valentine day',
                    'tags' => 'happy valentine day,valentine week,valentine day wallpaper,kiss,love,romance,warm'
                );
                $post_blog = CallAPI('POST', $base_url . 'api/create_post?nonce=' . $post_nonce . '&cookie=' . $cookie . '&insecure=cool', $data);
                $post_id = $post_blog->post->id;            
                $sql = "UPDATE wp_posts SET post_content='' WHERE id='".$post_id."'";
                $conn->query($sql);
                echo "okay done $post_id";
                echo "<br>";
            }
        }

        closedir($dh);
    }
}



function aescbcd($cookie_nonse, $data, $iv) {
        if (32 !== strlen($cookie_nonse))
            $cookie_nonse = hash('SHA256', $cookie_nonse, true);
        $iv = '';
        for ($i = 0; $i < 16; $i++) {
            $iv .= "\0";
        }
        $data = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $cookie_nonse, $data, MCRYPT_MODE_CBC, $iv);
        $padding = ord($data[strlen($data) - 1]);
        return substr($data, 0, -$padding);
    }

function get_basiefy($cookie_nonse,$s,$iv){
    $b = base64_decode($s);
    $e = aescbcd($cookie_nonse,$b,$iv);
    return $e;
}