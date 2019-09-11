<?php namespace Eternity2\Attachment;

interface AttachmentOwnerInterface{

	const EVENT__ATTACHMENT_ADDED = 'attachment_added';
	const EVENT__ATTACHMENT_REMOVED = 'attachment_removed';

	public function getPath();
	public function on($event, $data);

}