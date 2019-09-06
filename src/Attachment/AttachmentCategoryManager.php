<?php namespace Eternity2\Attachment;

use Eternity2\Attachment\Exception\FileCount;
use Eternity2\Attachment\Exception\FileNotAcceptable;
use Eternity2\Attachment\Exception\FileSize;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @property-read Attachment[] $all
 * @property-read Attachment $first
 * @property-read int $count
 */
class AttachmentCategoryManager {

	/** @var AttachmentOwnerInterface */
	protected $owner;
	protected $path;
	protected $url;

	/** @var Attachment[] */
	protected $attachments = null;
	/** @var AttachmentStorage */
	private $attachmentStorage;
	/** @var \Eternity2\Attachment\AttachmentCategory */
	private $category;

	public function __construct(AttachmentCategory $category, AttachmentOwnerInterface $owner) {
		$this->category = $category;
		$this->owner = $owner;
		$this->attachmentStorage = $category->getAttachmentStorage();
		$this->category = $category;
		$this->path = $this->attachmentStorage->getPath() . $owner->getPath();
		$this->url = $this->attachmentStorage->getUrl() . $owner->getPath();
	}

	public function getOwner(): AttachmentOwnerInterface { return $this->owner; }
	public function getCategory(): AttachmentCategory { return $this->category; }
	public function getPath(): string { return $this->path; }
	public function getUrl(): string { return $this->url; }

	public function addFile(File $file, $description = '', $ordinal = 0, $meta = []) {

		if ($this->count >= $this->category->getMaxFileCount()) {
			throw new FileCount();
		} else if ($file->getSize() > $this->category->getMaxFileSize()) {
			throw new FileSize();
		} else if (count($this->category->getAcceptedExtensions()) && !in_array($file->getExtension(), $this->category->getAcceptedExtensions())) {
			throw new FileNotAcceptable();
		}

		if (!is_dir($this->path)) mkdir($this->path, 0777, true);

		if ($file instanceof UploadedFile) {
			$file = $file->move($this->path, $file->getClientOriginalName());
		} else {
			copy($file->getPath() . '/' . $file->getFilename(), $this->path . $file->getFilename());
		}

		$attachment = new Attachment($file->getFilename(), $this, $description, $ordinal, $meta);

		$this->owner->on(AttachmentOwnerInterface::EVENT__ATTACHMENT_ADDED, [
			'category'   => $this->category,
			'attachment' => $attachment,
		]);

		$this->store($attachment);
	}

	/** @return Attachment[] */
	public function getAttachments(): array {
		if (is_null($this->attachments))
			$this->collect();
		return $this->attachments;
	}

	public function hasAttachments(): bool { return (bool)count($this->getAttachments()); }

	public function get($filename) {
		$attachments = $this->getAttachments();
		foreach ($attachments as $attachment)
			if ($filename === $attachment->getFilename())
				return $attachment;
		return null;
	}

	public function __get($name) {
		$attachments = $this->getAttachments();
		switch ($name) {
			case 'all':
				return $attachments;
				break;
			case 'first':
				return count($attachments) ? reset($attachments) : null;
				break;
			case 'count':
				return count($attachments);
		}
		return null;
	}

	public function store(Attachment $attachment) {
		$record = $attachment->getRecord();
		$statement = $this->attachmentStorage->getMetaDBConnection()
			->prepare("INSERT OR REPLACE INTO file (path, file, size, meta, description, category, ordinal) 
						VALUES (:path, :file, :size, :meta, :description, :category, :ordinal)");
		$statement->bindValue(':path', $record['path']);
		$statement->bindValue(':file', $record['file']);
		$statement->bindValue(':size', $record['size'], SQLITE3_INTEGER);
		$statement->bindValue(':description', $record['description']);
		$statement->bindValue(':meta', json_encode($record['meta']));
		$statement->bindValue(':category', $record['category']);
		$statement->bindValue(':ordinal', $record['ordinal'], SQLITE3_INTEGER);
		$statement->execute();
		$this->attachments = null;
	}

	protected function collect() {
		$statement = $this->attachmentStorage->getMetaDBConnection()
			->prepare("SELECT * FROM file WHERE path = :path AND category = :category ORDER BY ordinal, file");
		$statement->bindValue(':path', $this->owner->getPath());
		$statement->bindValue(':category', $this->category->getName());
		$result = $statement->execute();
		$this->attachments = [];
		while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
			$this->attachments[] = new Attachment($row['file'], $this, $row['description'], $row['ordinal'], json_decode($row['meta'], true));
		}
		return $this->attachments;
	}

	public function remove(Attachment $attachment) {
		$statement = $this->attachmentStorage->getMetaDBConnection()
			->prepare("DELETE FROM file WHERE path = :path AND file = :file AND category = :category");
		$statement->bindValue(':path', $this->owner->getPath());
		$statement->bindValue(':category', $this->category->getName());
		$statement->bindValue(':file', $attachment->getFilename());
		$statement->execute();

		$statement = $this->attachmentStorage->getMetaDBConnection()
			->prepare("SELECT count(*) as `count` FROM file WHERE path = :path AND file = :file ORDER BY ordinal, file");
		$statement->bindValue(':path', $this->owner->getPath());
		$statement->bindValue(':file', $attachment->getFilename());

		$hasFile = (bool)intval($statement->execute()->fetchArray(SQLITE3_ASSOC)['count']);

		if (!$hasFile)
			unlink($attachment->getRealPath());

		$this->owner->on(AttachmentOwnerInterface::EVENT__ATTACHMENT_REMOVED, ['category' => $this->category]);

		$this->attachments = null;
	}

}

