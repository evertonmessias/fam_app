<?php

require_once (__DIR__ . '/PHPMailer/PHPMailerAutoload.php');

function enviar_email_login($fromP, $from, $destinatario, $assunto, $template, $opt) {
	$mail = new PHPMailer(true);
	$mail->CharSet = 'UTF-8';

	$destinatario_nome = '';
	$destinatario_email = $destinatario;

	if (is_array($destinatario)) {
		$destinatario_nome = $destinatario['nome'];
		$destinatario_email = $destinatario['email'];
	}

	$mail->SMTPDebug = 3;                               // Enable verbose debug output

	$mail->isSMTP();
	$mail->Host = $opt['host'];
	$mail->SMTPAuth = true;
	$mail->Username = $opt['user'];
	$mail->Password = $opt['pass'];
	$mail->SMTPSecure = 'ssl';
	$mail->Port = $opt['port'];
	$mail->SMTPOptions = [
	    'ssl' => [
	        'verify_peer' => false,
	        'verify_peer_name' => false,
	        'allow_self_signed' => true
	    ]
	];

	$mail->Sender = $from;
	$mail->setFrom($from, $fromP);
	$mail->addReplyTo($from, $fromP);

	if (!empty($destinatario_nome))
		$mail->addAddress($destinatario_email, $destinatario_nome);
	else
		$mail->addAddress($destinatario_email);

	$mail->isHTML(true);                                  // Set email format to HTML

	$mail->Subject = $assunto;
	$mail->Body    = $template;
	// $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

	if(!$mail->send()) return $mail->ErrorInfo;
    else return true;
}

/***
 * Relay de SMTP pra FAM
 */

$to = json_decode($_POST['to']);
if (empty($to))
	$to = $_POST['to'];

$subject = $_POST['subject'];
$message = $_POST['body'];

$opt = [
	'host' => $_POST['host'],
	'from' => $_POST['from'],
	'user' => $_POST['user'],
	'pass' => $_POST['pass'],
	'port' => $_POST['port'],
];

echo json_encode(enviar_email_login ($opt['from'], $opt['user'], $to, $subject, $message, $opt));