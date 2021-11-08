<?php

namespace App\System;

use PHPMailerAutoload; 
use PHPMailer;

use App\System\Event;

class Email {

	protected $mail = null;
	protected $to = [];

	protected function __construct ($subject) {
		$this->mail = new PHPMailer(true);
		$this->mail->CharSet = 'UTF-8';
	}

	public function to ($email, $nome = '') {

		// Processa o 'para' para poder usar arrays, combinações, etc

		$to = [];
		if (is_array($email)) {
			foreach ($email as $k => $v) {
				$addr = $k;
				$nome = '';
				if (is_numeric($k))
					$addr = $v;
				else {
					$addr = $k;
					$nome = $v;
				}
				$to[] = ['email' => $addr, 'nome' => $nome];
			}
		} else {
			$to[] = ['email' => $email, 'nome' => $nome];
		}
		$this->to = array_merge(array_values($this->to), array_values($to));

		return $this;
	}

	public function from ($email, $nome = '') {
		// Seta o 'de'
		$this->mail->Sender = $email;
		$this->mail->setFrom($email, $nome);

		return $this;
	}

	public function html ($html, $altbody = null) {

		// Seta corpo em HTML

		$this->mail->isHTML(true);
		$this->mail->Body = $html;

		// Caso não exista alternativa não-HTML
		if (is_null($altbody))
			$altbody = strip_tags($html);

		// Corpo alternativo
		$this->mail->AltBody = $altbody;

		return $this;
	}

	public function subject ($subject) {
		$this->mail->Subject = $subject;

		return $this;
	}

	public function smtp_auth ($auth = []) {

		// Autenticação SMTP

		$this->mail->isSMTP();
		$this->mail->Host = isset($auth['host']) ? $auth['host'] : env('SMTP_HOST');
		$this->mail->Port = isset($auth['port']) ? $auth['port'] : env('SMTP_PORT', 465);
		$this->mail->SMTPAuth = true;
		$this->mail->Username = isset($auth['user']) ? $auth['user'] : env('SMTP_USER');
		$this->mail->Password = isset($auth['pass']) ? $auth['pass'] : env('SMTP_PASS');
		$this->mail->SMTPSecure = isset($auth['security']) ? $auth['security'] : env('SMTP_SECURE', 'ssl');
		$this->mail->SMTPOptions = [
		    'ssl' => [
		        'verify_peer' => false,
		        'verify_peer_name' => false,
		        'allow_self_signed' => true
		    ]
		];

		return $this;
	}

	public function reply_to ($email, $nome = '') {
		// Responder Para
		$this->mail->addReplyTo($email, $nome);

		return $this;
	}

	public function debug () {

		// Verbose SMTP debug

		$this->mail->SMTPDebug = 3;

		return $this;
	}

	public function send () {
		$event = Event::register('email', 'Envio de e-mail');
		$event->meta('to', $this->to);
		$event->meta('subject', $this->mail->Subject);

		try {

			// Adicionar destinatários

			foreach ($this->to as $to) {
				$this->mail->addAddress($to['email'], $to['nome']);
			}

			// Enviar

			$result = $this->mail->send();

			$event->finish();
			return $result;

		} catch (\Exception $e) {
			$event->error($e)->finish();

			return false;
		}
	}

	public function error () {
		// Informações sobre erros, etc
		return $this->mail->ErrorInfo;
	}

	public function view () {
		return $this->mail->Body;
	}

	// Inicializadores estáticos

	public static function create ($subject) {
		$mail = new static($subject);

		return $mail->subject($subject);
	}
}