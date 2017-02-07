<?php

  $ch = curl_init();

  // Authenticate
  $authUrl = 'https://www.e-activist.com/ens/service/authenticate';
  curl_setopt($ch, CURLOPT_URL, $authUrl);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json; charset=UTF-8',
    'ens-auth-token: b62c9e45-8fc1-4a9a-b83a-163236824e15'
  ));
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, 'b62c9e45-8fc1-4a9a-b83a-163236824e15');  // UPDATE
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

  $res = curl_exec($ch);
  $authRes = $res;
  $json = json_decode($res, true);
  print_r($json);
	$token = $json['ens-auth-token'];
  $email = 'simonmwhite@gmail.com';

curl_close($ch);

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_VERBOSE, true);
  //$values = [];
  $suppUrl = 'https://www.e-activist.com/ens/service/supporter?includeQuestions=true&email=' . $email;
print $suppUrl;
  // get supporter data
  curl_setopt($ch, CURLOPT_HTTPGET, 1);
  curl_setopt($ch, CURLOPT_URL, $suppUrl);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'ens-auth-token: ' . $token
  ));

  $res = curl_exec($ch);
  $suppRes = $res;
  print_r ($res);
  $supporterDeets = json_decode($res, true);
  print_r($supporterDeets);

  // Create a curl handle to a non-existing location
  //$ch = 'http://google.com';
  if(curl_error($ch)) {
    echo 'error:' . curl_error($ch);
  }

  // Close handle
  //curl_close($ch);

?>
