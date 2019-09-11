<?php namespace Eternity2\Mission\Web\Pipeline;

abstract class Responder extends Segment {
	abstract protected function respond();
}