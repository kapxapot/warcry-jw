<?php

namespace App\Validation\Rules;

class EmailAvailable extends ContainerRule {
	public function validate($input) {
		$count = $this->container->db->forTable('users')
			->where('email', $input)
			->count();
		
		return $count == 0;
	}
}
