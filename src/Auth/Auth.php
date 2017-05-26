<?php

namespace App\Auth;

use Warcry\Contained;

class Auth extends Contained {
	public function user() {
		return $this->db->selectMany('users')->findOne($_SESSION['jw']['user']);
	}
	
	public function userString() {
		$user = $this->user();
		return "[{$user->id}] {$user->name}";
	}
	
	public function check() {
		return isset($_SESSION['jw']['user']);
	}
	
	public function attempt($email, $password) {
		$user = $this->db->forTable('users')
			->where('email', $email)
			->findOne();

		$ok = false;
		
		if ($user) {
			if (password_verify($password, $user['password'])) {
				$_SESSION['jw']['user'] = $user['id'];
				
				$user['token'] = bin2hex(openssl_random_pseudo_bytes(16));
				$user['token_expire'] = date('Y-m-d H:i:s', strtotime('+1 hour'));
            	
            	$user->save();

				$ok = true;
			}
		}

		return $ok;
	}
	
	public function logout() {
		unset($_SESSION['jw']['user']);
	}
}
