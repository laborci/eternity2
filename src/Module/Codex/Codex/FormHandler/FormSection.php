<?php namespace Eternity2\Module\Codex\Codex\FormHandler;

use Eternity2\Module\Codex\Codex\AdminDescriptor;
use Eternity2\Module\Codex\Codex\FormHandler\FormInput;
use JsonSerializable;

class FormSection implements JsonSerializable{
	/** @var FormInput[] */
	protected $inputs = [];
	protected $label;
	protected $adminDescriptor;

	public function __construct($label, AdminDescriptor $adminDescriptor){
		$this->label = $label;
		$this->adminDescriptor = $adminDescriptor;
	}

	public function input($type, $field, $label = null){
		if (is_null($label)){
			$label = $this->adminDescriptor->getFieldLabel($field);
		}
		$input = new FormInput($type, $label, $field);
		$this->inputs[] = $input;
		return $input;
	}

	public function jsonSerialize(){
		return [
			'label'  => $this->label,
			'inputs' => $this->inputs,
		];
	}

	/** @return FormInput[] */
	public function getInputs(): array{ return $this->inputs; }

}
