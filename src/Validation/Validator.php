<?php

namespace App\Validation;

use Respect\Validation\Exceptions\NestedValidationException;
use Warcry\Validation\Rules\ContainerRule;

class Validator {
	protected $container;
	protected $errors;
	
	public function __construct($container) {
		$this->container = $container;
	}
	
	public function validate($request, array $rules) {
		foreach ($rules as $field => $rule) {
			try {
				foreach ($rule->getRules() as $subRule) {
					if ($subRule instanceof ContainerRule) {
	    				$subRule->setContainer($this->container);
					}
				}

				$rule->setName(ucfirst($field))->assert($request->getParam($field));
			}
			catch (NestedValidationException $e) {
				$this->errors[$field] = $e->getMessages();
			}
		}
		
		$_SESSION['errors'] = $this->errors;
		
		return $this;
	}
	
	public function failed() {
		return !empty($this->errors);
	}
}
