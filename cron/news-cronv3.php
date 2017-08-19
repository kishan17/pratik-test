<?php



set_time_limit(0);
ini_set('memory_limit', '-1');

$base_url = 'http://localhost/wp_demo/';
$wp_username = 'admin';
$wp_password = 'ser@123';


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

    switch ($method)
    {
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
function compress_image($source_url, $destination_url) {

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



$cookie_nonce = CallAPI('POST',$base_url.'api/get_nonce/?controller=auth&method=generate_auth_cookie');
$cookie_nonce = $cookie_nonce->nonce;
$cookie = CallAPI('POST',$base_url.'api/auth/generate_auth_cookie/?nonce='.$cookie_nonce.'&username='.$wp_username.'&password='.$wp_password.'&insecure=cool');
$cookie = $cookie->cookie;
$post_nonce = CallAPI('POST',$base_url.'api/get_nonce/?cookie='.$cookie.'&controller=posts&method=create_post&insecure=cool');
$post_nonce = $post_nonce->nonce;

$o_news_type = isset($_GET['news_type'])?$_GET['news_type']:'business'; 
$d_news_type = urldecode($o_news_type);
$news_type = strtolower($o_news_type);
$news_type = str_replace(" ","-",$news_type);


$news_source = file_get_contents("https://newsapi.org/v1/sources?category=$news_type&apiKey=d705c4b8af0448459c7af15cb4061e5b");
$news_source = json_decode($news_source);

foreach ($news_source->sources as $v) {

    $nn_source = $v->id;
    $nn_name = $v->name;
    $news_url = ("https://newsapi.org/v1/articles/?apiKey=d705c4b8af0448459c7af15cb4061e5b&source=" . $nn_source . "");
    $news_feeds = file_get_contents($news_url);
    $news_feeds = json_decode($news_feeds);
    echo $nn_source = $news_feeds->source;
    echo '<br>';
    $i = 1;

    foreach ($news_feeds->articles as $v) {
        if ($i != 0) {
        if($v->urlToImage != null && $v->author != null){

        $post_title = mysqli_real_escape_string($conn, $v->title);
        $imgurl = $v->urlToImage;
        $sql = "SELECT post_title FROM wp_posts where post_title='" . $post_title . "'";
        $res = $conn->query($sql);
        if ($res->num_rows == 0) {

//            $lastDotPos = strrpos($imgurl, '.');
//            if (!$lastDotPos) return false;
//            $jnk_extention = substr($imgurl, $lastDotPos + 1);
                $img_name = uniqid('sn_') . '.jpeg';// . $jnk_extention;
                $img = "./images/$img_name";
                $arrContextOptions=array(
                    "ssl"=>array(
                        "verify_peer"=>false,
                        "verify_peer_name"=>false,
                    ),
                );
                file_put_contents($img, file("$imgurl",false,stream_context_create($arrContextOptions)));
                $local_image_url = $base_url . "cron/images/$img_name";
                compress_image("images/$img_name","images/$img_name");

                $data = array(
                    'author' => $v->author,
                    'status' => 'publish',
                    'title' => $v->title,
                    'content' => "<img src='" . $local_image_url . "'>",
                    'categories' => "$d_news_type,$nn_name",
                    'tags' => "news today,news live,newspaper,news nation,news india,news paper,$d_news_type"
                );
                $post_blog = CallAPI('POST', $base_url . 'api/create_post?nonce=' . $post_nonce . '&cookie=' . $cookie . '&insecure=cool', $data);

                $post_id = $post_blog->post->id;

                $content = $nn_name.' :' . $v->description . '    <a href="' . $v->url . '">Read More</a>';

                $sql = "UPDATE wp_posts SET post_content='" . $content . "' WHERE id='" . $post_id . "'";
                $conn->query($sql);

                @unlink("images/$img_name");

                }
                $i++;
            }
        }
    }
}
$conn->close();



