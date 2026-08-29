<?php
// src/Service/FileUploader.php
namespace App\Service;

use App\Entity\Event;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/public/assets/img/uploads/events')]
        private string $targetDirectory,
        private SluggerInterface $slugger,
    ) {}

    public function upload(UploadedFile $file, Event $event): string
    {
        $safeTitle = $this->slugger->slug($event->getTitle());
        $date = $event->getDate()->format('Y-m-d');
        $fileName = $safeTitle . '-' . $date . '-' . uniqid() . '.' . $file->guessExtension();

        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
        }

        return $fileName;
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}
