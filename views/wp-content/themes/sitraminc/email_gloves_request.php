<?php
$quantity = $_POST['quantity'];
$delivery = $_POST['delivery'];
$size = $_POST['size'];
$fromemail = $_POST['email'];
$companyname = $_POST['companyname'];
$address1 = $_POST['address1'];
$address2 = $_POST['address2'];
$city = $_POST['city'];
$state = $_POST['state'];
$country = $_POST['country'];
$zip = $_POST['zip'];
//$to = "tmartis@sitraminc.com";
$to = "tmartis@sitraminc.com";
$cc = "anjana.pai.rathod@piranova.com";
$email_subject = "Gloves order request";
$message = "
<html>
<head>
<title>HTML email</title>
</head>
<body>
<table >
<tr><td colspan='2'><h3>Gloves</h3></td></tr>
";
$message = $message. '<tr><td><strong>Quantity Requested:</strong> </td><td></td><td>'. $quantity .'</td></tr>';
$message = $message. '<tr><td><strong>Delivery Preference</strong> </td><td></td><td>'. $delivery .'</td></tr>';
$message = $message. '<tr><td><strong>Sizes interested in</strong> </td><td></td><td>'. $size .'</td></tr>';
$message = $message. '<tr><td colspan='. '2' .' style="padding-top:20px;"><h3>Customer Detail</h3></td></tr>';
$message = $message. '<tr><td><strong>Email Address:</strong> </td><td></td><td>'. $fromemail .'</td></tr>';
$message = $message. '<tr><td><strong>Company Name:</strong> </td><td></td><td>'. $companyname .'</td></tr>';
$message = $message. '<tr><td><strong>Address 1:</strong> </td><td></td><td>'. $address1 .'</td></tr>';
$message = $message. '<tr><td><strong>Address 2:</strong> </td><td></td><td>'. $address2 .'</td></tr>';
$message = $message. '<tr><td><strong>City:</strong> </td><td></td><td>'. $city .'</td></tr>';
$message = $message. '<tr><td><strong>State:</strong> </td><td></td><td>'. $state .'</td></tr>';
$message = $message. '<tr><td><strong>Country:</strong> </td><td></td><td>'. $country .'</td></tr>';
$message = $message. '<tr><td><strong>Zip:</strong> </td><td></td><td>'. $zip .'</td></tr>'; "
</table>
</body>
</html>
";
// To send HTML mail, the Content-type header must be set
$headers  = 'MIME-Version: 1.0' . "\r\n" .
			'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
			"From: $fromemail"  . "\r\n" .
			"Cc: $cc"  . "\r\n" .
   			"Reply-To: $fromemail"  . "\r\n" .
   			"X-Mailer: PHP/" . PHP_VERSION;

// Sending email
/*echo $to.','.$email_subject.','.$message.','.$headers; exit;*/
if(mail($to, $email_subject, $message, $headers)){
    header('Location:https://sitraminc.com');
} else{
    echo 'Failed to send email';
}

?> 