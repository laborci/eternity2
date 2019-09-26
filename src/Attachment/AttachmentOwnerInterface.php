<?php namespace Eternity2\Attachment;

interface AttachmentOwnerInterface{
	public function getPath();
	public function onAttachmentAdded($data);
	public function onAttachmentRemoved($data);

}