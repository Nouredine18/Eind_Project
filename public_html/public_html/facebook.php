<html>  
<head>  
    <title>Facebook Login Form</title>  
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" /> 
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous"> 
</head>
<style>
 .box
 {
  width:100%;
  max-width:400px;
  background-color:#f9f9f9;
  border:1px solid #ccc;
  border-radius:5px;
  padding:16px;
  margin:0 auto;
 }
</style>
<body> 
<?php
session_start();
require_once 'vendor/autoload.php';

$fb = new Facebook\Facebook([
    'app_id' => '593933119633236', 
    'app_secret' => '20744f332f44687ece8c1718b99f6d35', 
    'default_graph_version' => 'v21.0',
]);

$helper = $fb->getRedirectLoginHelper();
$permissions = ['email']; // optioneel
$loginUrl = $helper->getLoginUrl('https://ecoligocollective.com/login.php', $permissions);

try {
    if (isset($_SESSION['facebook_access_token'])) {
        $accessToken = $_SESSION['facebook_access_token'];
    } else {
        $accessToken = $helper->getAccessToken();
    }
} catch(Facebook\Exceptions\FacebookResponseException $e) {
    // Als Graph een error teruggeeft
    echo 'Graph returned an error: ' . $e->getMessage();
    exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
    // Als validatie mislukt of andere lokale issues
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;
}
if (isset($accessToken)) {
    if (isset($_SESSION['facebook_access_token'])) {
        $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
    } else {
        // verkrijg kortlevende access token
        $_SESSION['facebook_access_token'] = (string) $accessToken;
        // OAuth 2.0 client handler
        $oAuth2Client = $fb->getOAuth2Client();
        // Wisselt een kortlevende access token om voor een langlevende
        $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);
        $_SESSION['facebook_access_token'] = (string) $longLivedAccessToken;
        // stel standaard access token in voor gebruik in script
        $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
    }
    // stuur gebruiker door naar homepagina als "code" GET variabele bestaat
    if (isset($_GET['code'])) {
        header('Location: home.php');
    }
    // verkrijg basisinfo over gebruiker
    try {
        $profile_request = $fb->get('/me?fields=name,first_name,last_name,email');
        $profile = $profile_request->getGraphUser();
        $fbid = $profile->getProperty('id');           // Om Facebook ID te krijgen
        $fbfullname = $profile->getProperty('name');   // Om volledige Facebook naam te krijgen
        $fbemail = $profile->getProperty('email');    //  Om Facebook e-mail te krijgen
        $fbpic = "<img src='https://graph.facebook.com/$fbid/picture?redirect=true'>";
        // sla gebruikersinformatie op in sessievariabele
        $_SESSION['fb_id'] = $fbid;
        $_SESSION['fb_name'] = $fbfullname;
        $_SESSION['fb_email'] = $fbemail;
        $_SESSION['fb_pic'] = $fbpic;
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
        // Als Graph een error teruggeeft
        echo 'Graph returned an error: ' . $e->getMessage();
        session_destroy();
        // stuur gebruiker terug naar app login pagina
        header("Location: ./");
        exit;
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
        // Als validatie mislukt of andere lokale issues
        echo 'Facebook SDK returned an error: ' . $e->getMessage();
        exit;
    }
} else {
    ?>
    <div class="container">  
    <div class="table-responsive">  
    <h3 align="center">Login using Facebook in PHP</h3>
     <div class="box">
      <div class="form-group">
       <label for="email">Emailid</label>
       <input type="text" name="email" id="email" placeholder="Enter Email" class="form-control" required />
      </div>
      <div class="form-group">
       <label for="password">Password</label>
       <input type="password" name="pwd" id="pwd" placeholder="Enter Password" class="form-control"/>
      </div>
      <div class="form-group">
       <input type="submit" id="login" name="login" value="Login" class="btn btn-success form-control"/>
       <hr>
       <center><a href="<?php echo $loginUrl; ?>" class="btn btn-primary btn-block"><i class="fab fa-facebook-square"></i> Log in with Facebook!</a></center>
      </div>
      </div>
   </div>  
  </div>
<?php } ?>
</body>  
</html>