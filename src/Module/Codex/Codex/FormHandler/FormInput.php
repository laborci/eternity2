<?php namespace Eternity2\Module\Codex\Codex\FormHandler;

use JsonSerializable;

class FormInput implements JsonSerializable{

	protected $type;
	protected $label;
	protected $field;
	protected $options;

	public function __construct($type, $label, $field, $options = []){
		$this->type = $type;
		$this->label = $label;
		$this->field = $field;
		$this->options = $options;
	}

	public function __invoke($option, $value = true){
		return $this->option($option, $value);
	}

	public function option($option, $value = true){
		$this->options[$option] = $value;
		return $this;
	}

	public function jsonSerialize(){
		return [
			'type'    => $this->type,
			'label'   => $this->label,
			'field'   => $this->field,
			'options' => $this->options,
		];
	}

	public function getField(){ return $this->field; }
}
