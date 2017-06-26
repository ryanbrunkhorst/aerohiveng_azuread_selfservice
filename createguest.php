<html>
<head>
<title>Create</title>
<link rel="stylesheet" type="text/css" href="style.css">
<link href="https://fonts.googleapis.com/css?family=Cutive" rel="stylesheet">
</head>

<body>
<img src="logo.jpg">
<p>
guest credential
<p>
<?php
include 'tokens.php';  
curl_setopt($process, CURLOPT_SSL_VERIFYPEER, true);

# Gets the logged in user's UPN, which is the Azure AD account used to login to the web app.
$userName = $_SERVER["HTTP_X_MS_CLIENT_PRINCIPAL_NAME"];
$email = $_POST["email"];
$phone = $_POST["phone"];
# Adds US country code to mobile phone
$phoneconvert ="1$phone";

#Allows a user to provide email, mobile phone, or both. Displays error if both fields are empty.
#Contents of $phoneinput variable cannot be included in the CURLOPT_POSTFIELDS below if $delivermethod is only EMAIL
if (($email == NULL) && ($phone == NULL)){ 
    $error = "You need to provide a valid email or mobile phone number";
}elseif ($phone == NULL){ 
    $phoneinput = NULL;
    $delivermethod = "EMAIL";
}elseif ($email == NULL){ 
    $phoneinput = ",\r\n \"phone\": \"$phoneconvert\"";
    $delivermethod = "SMS";
}else { 
    $phoneinput = ",\r\n \"phone\": \"$phoneconvert\"";
    $delivermethod = "EMAIL_AND_SMS";
}

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://cloud-va.aerohive.com/xapi/v1/identity/credentials?ownerId=$ownerId",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => "{\r\n  \"deliverMethod\": \"$delivermethod\",\r\n  \"policy\": \"$policy\",\r\n  \"email\": \"$email\",\r\n  \"firstName\": \"$firstName\",\r\n  \"groupId\": \"$groupIdguest\",\r\n  \"lastName\": \"$lastName\",\r\n \"userName\": \"g.$userName\"$phoneinput\r\n}",
  CURLOPT_HTTPHEADER => array(
    "authorization: Bearer $accesstoken",
    "cache-control: no-cache",
    "content-type: application/json",
    "x-ah-api-client-id: $clientid",
    "x-ah-api-client-redirect-uri: $redirecturi",
    "x-ah-api-client-secret: $clientsecret"
  ),
));

$response = curl_exec($curl);
$json = json_decode($response);
$err = curl_error($curl);
curl_close($curl);

$ssid = $json->data->ssid[0];
$password = $json->data->password;

#If user already exists this will show the error reponse. Otherwise the SSID and PPSK will display.
if ($password == NULL) {
  echo $response;
} else { 
    echo "SSID: " .$ssid;
    echo "<br>Password: " .$password; 
}
#Conditions for credential delivery messages
if ($password == NULL){ 
    echo " ";
}elseif ($_POST["email"] == NULL){ 
    echo "<p>Credentials have been sent via text to " .$phoneconvert;
}
elseif ($_POST["phone"] == NULL){
    echo "<p>Credentials have been emailed to " .$email;
}else {
    echo "<p>Credentials have been emailed to " .$email;
    echo "<p>Credentials have been sent via text to " .$phoneconvert;
}
?>

<p>
<a id="returnbutton" href="<?php echo $returnurl ?>">Return</a>

</body>
</html>