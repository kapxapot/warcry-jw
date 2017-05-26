<?php

namespace App\Validation\Rules;

class MatchesPassword extends ContainerRule {
	protected $password;
	
	public function __construct($password) {
		$this->password = $password;
	}
	
	public function validate($input) {
		return password_verify($input, $this->password);
	}
}
