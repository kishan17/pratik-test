<?php

/**
 * Created by PhpStorm.
 * User: Ashish Mulani
 * Date: 27-01-2017
 * Time: 02:44
 */

set_time_limit(0);
ini_set('memory_limit', '-1');

$base_url = 'http://localhost/wp_demo/';
$wp_username = 'admin';
$wp_password = 'ser@1234';


//database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "wp_demo";

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


// Method: POST, PUT, GET etc
// Data: array("param" => "value") ==> index.php?param=value

function CallAPI($method, $url, $data = false)
{
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

/////////// Image Compress ////////////
function compress_image($source_url, $destination_url)
{

    $info = getimagesize($source_url);

    if ($info['mime'] == 'image/jpeg')
        $image = imagecreatefromjpeg($source_url);

    elseif ($info['mime'] == 'image/gif')
        $image = imagecreatefromgif($source_url);

    elseif ($info['mime'] == 'image/png')
        $image = imagecreatefrompng($source_url);

    imagejpeg($image, $destination_url, 40);
    return $destination_url;
}


                    $cookie_nonce = CallAPI('POST', $base_url . 'api/get_nonce/?controller=auth&method=generate_auth_cookie');
                    $cookie_nonce = $cookie_nonce->nonce;
                    $cookie = CallAPI('POST', $base_url . 'api/auth/generate_auth_cookie/?nonce=' . $cookie_nonce . '&username=' . $wp_username . '&password=' . $wp_password . '&insecure=cool');
                    $cookie = $cookie->cookie;
                    $post_nonce = CallAPI('POST', $base_url . 'api/get_nonce/?cookie=' . $cookie . '&controller=posts&method=create_post&insecure=cool');
                    $post_nonce = $post_nonce->nonce;


                    $page =  isset($_POST['page']) ? $_POST['page'] : 1;
                    $limit = 5;
                    $offset = ($page * $limit) - $limit;
                    if($page < 1){
                        exit;
                    }
                    $sql = "Select love_url from love_link limit $limit OFFSET $offset";
                    $result = $conn->query($sql);


                    foreach($result as $value) {

                        $url = $value['love_url'];
                        $html_string = file_get_contents($url);
                        $dom = new DOMDocument();
                        $dom->preserveWhiteSpace = false;
                        libxml_use_internal_errors(true);
                        $dom->loadHTML($html_string);
                        libxml_clear_errors();
                        $xpath = new DOMXpath($dom);

                        $pg_data = $xpath->query('//div[@class="entry-content"]/div/div/h1/span/text()|//div[@class="post-info"]/p/span/a');
                        $story_array = array();
                        foreach ($pg_data as $pg) {
                            $story_array[] = $pg->textContent;
                        }
                        $story_text = $xpath->query('//div[@class="entry-content-inner"]/p');
                        foreach ($story_text as $st) {
                            $story_array[] = $st->textContent;
                        }

                        $c = (count($story_array));
                        $content = "";
                        for($j=4;$j<$c;$j++){
                            $content .= $story_array[$j];
                        }

                 
                        $data = array(
                            'author' => 'admin',
                            'status' => 'publish',
                            'title' => $story_array[0],
                            'categories' => "$story_array[1]",
                            'content' => $content,
                            'tags' => "love story "
                        );
                        $post_blog = CallAPI('POST', $base_url . 'api/create_post?nonce=' . $post_nonce . '&cookie=' . $cookie . '&insecure=cool', $data);
                 
                    }

$conn->close();


