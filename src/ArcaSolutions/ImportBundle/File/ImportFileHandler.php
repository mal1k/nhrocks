<?php

namespace ArcaSolutions\ImportBundle\File;

use ArcaSolutions\MultiDomainBundle\Services\Settings;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImportFileHandler
{
    /** @var RegistryInterface */
    private $importRepository;
    /** @var string */
    private $kernelDir;
    /** @var Settings */
    private $multidomainSettings;
    /** @var array Bundle configuration */
    private $configs;

    public function __construct(RegistryInterface $doctrine, $kernelDir, Settings $multidomainSettings, $configs)
    {
        $this->importRepository = $doctrine->getRepository('ImportBundle:ImportLog');
        $this->kernelDir = $kernelDir;
        $this->multidomainSettings = $multidomainSettings;
        $this->configs = $configs;
    }

    /**
     * Move import folder
     *
     * @param UploadedFile $file
     * @return \Symfony\Component\HttpFoundation\File\File
     * @throws \Exception When file extension is not supported
     */
    public function upload(UploadedFile $file)
    {
        if (!in_array($file->getClientOriginalExtension(), $this->configs['frontend']['extensions'])) {
            throw new \Exception(sprintf('File extension %s not supported', $file->getClientOriginalExtension()));
        }

        return $file->move($this->getImportFolderPath(), $file->getClientOriginalName());
    }

    /**
     * Get import folder path
     *
     * @return string
     */
    public function getImportFolderPath()
    {
        $path = $this->kernelDir.'/../web/'.$this->multidomainSettings->getPath().'import_files';
        /* if the folder for some weird reason does not exists */
        if (!is_dir($path)){
            mkdir($path);
        }

        return $path;
    }

    /**
     * Get import folder URI
     *
     * @return string
     */
    public function getImportFolderUri()
    {
        return '/'.$this->multidomainSettings->getPath().'import_files';
    }
}
