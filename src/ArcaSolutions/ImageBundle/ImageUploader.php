<?php

namespace ArcaSolutions\ImageBundle;

use ArcaSolutions\ImageBundle\Entity\Image;
use Symfony;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageUploader
{
    private $targetDir;
    private $selectedDomain;
    private $domainUrl;
    private $rootDir;
    private $translator;
    private $sitemgrLanguage;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->selectedDomain = $container->get('multi_domain.information')->getId();
        $this->domainUrl = $container->get('multi_domain.information')->getOriginalActiveHost();
        $this->rootDir = $container->getParameter('kernel.root_dir');
        $this->translator = $container->get('translator');

        $sitemgr_language = $this->container->get('settings')->getSetting('sitemgr_language');
        $this->sitemgrLanguage = substr($sitemgr_language, 0, 2);
    }

    /**
     * @param UploadedFile $file
     * @param $fileName
     * @param bool $addExtension
     * @return string
     */
    public function upload(UploadedFile $file, $fileName, $addExtension = true)
    {
        $fileName = $fileName.($addExtension ? '.'.$file->guessExtension() : '');

        $file->move($this->targetDir, $fileName);

        return $fileName;
    }

    /**
     * @param UploadedFile $file
     * @param $fileName
     * @return mixed
     */
    public function uploadFavicon(UploadedFile $file, $fileName)
    {
        $return['success'] = false;
        if ($file->isValid()) {
            if (in_array($file->getMimeType(), ['image/x-icon', 'image/vnd.microsoft.icon'])) {
                $fileExtension = $file->guessExtension();
                $rand = rand(1000, 9999999);

                // Set the upload directory
                $this->targetDir = $this->rootDir.'/../web/custom/domain_'.$this->selectedDomain."/content_files/";;

                // Get old favicon file
                $deleteOld = false;
                if ($oldFile = glob($this->targetDir.$fileName.'*')) {
                    $deleteOld = true;
                }

                if ($deleteOld) {
                    $this->container->get('mixpanel.helper')->trackEvent('Favicon updated', [
                        'section' => 'Page Editor'
                    ]);
                } else {
                    $this->container->get('mixpanel.helper')->trackEvent('Favicon uploaded', [
                        'section' => 'Page Editor'
                    ]);
                }

                $fileUploaded = $this->upload($file, $fileName.$rand);

                if ($fileUploaded) {
                    $return['success'] = true;
                    $url = "/custom/domain_".$this->selectedDomain.'/content_files/'.$fileName.$rand.'.'.$fileExtension;
                    $return['url'] = $this->container->get('request')->getSchemeAndHttpHost().$url;
                    if ($deleteOld) {
                        unlink($oldFile[0]);
                    }
                }

                // adds favicon
                $classSymfonyYml = new Symfony('domains/'.$this->domainUrl.'.configs.yml');
                $classSymfonyYml->save('Configs',
                    [
                        'parameters' =>
                            [
                                'domain.favicon' => $url,
                            ],
                    ]);
                unset($classSymfonyYml);
            } else {
                $return['message'] = $this->translator->trans('You must use ".ico" images for favicon', [], 'widgets', $this->sitemgrLanguage);
            }
        } else {
            $return['message'] = $this->translator->trans('Error uploading image. Please try again.', [], 'widgets', $this->sitemgrLanguage);
        }

        return $return;
    }

    /**
     * @param UploadedFile $file
     * @param $fileName
     * @return mixed
     */
    public function uploadLogo(UploadedFile $file, $fileName)
    {
        $return['success'] = false;
        if ($file->isValid()) {
            if (in_array($file->getMimeType(), ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'])) {
                $this->targetDir = $this->rootDir.'/../web/custom/domain_'.$this->selectedDomain.'/content_files/';

                if(file_exists($this->targetDir . $fileName)) {
                    $this->container->get('mixpanel.helper')->trackEvent('Logo updated', [
                        'section' => 'Page Editor'
                    ]);
                } else {
                    $this->container->get('mixpanel.helper')->trackEvent('Logo uploaded', [
                        'section' => 'Page Editor'
                    ]);
                }

                $fileUploaded = $this->upload($file, $fileName, false);

                if ($fileUploaded) {
                    $return['success'] = true;
                    $url = "/custom/domain_".$this->selectedDomain."/content_files/".$fileName;
                    $return['url'] = $this->container->get('request')->getSchemeAndHttpHost().$url;

                    // @todo image cte
                    $classSymfonyYml = new Symfony('domains/'.$this->domainUrl.'.configs.yml');
                    $classSymfonyYml->save('Configs',
                        [
                            'parameters' =>
                                [
                                    'domain.header.image' => $url,
                                ],
                        ]
                    );
                    unset($classSymfonyYml);
                }
            } else {
                $return['message'] = $this->translator->trans('Image logo wrong file extension', [], 'widgets', $this->sitemgrLanguage);
            }
        } else {
            $return['message'] = "Logo: ".$this->translator->trans('Error uploading image. Please try again.',
                    [], 'widgets', $this->sitemgrLanguage);
        }

        return $return;
    }

    /**
     * @param UploadedFile $file
     * @param $fileName
     * @return mixed
     */
    public function uploadBackgroundImage(UploadedFile $file, $fileName)
    {
        $return['success'] = false;
        if ($file->isValid()) {
            if (in_array($file->getMimeType(), ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'])) {
                $this->targetDir = $this->rootDir.'/../web/custom/domain_'.$this->selectedDomain.'/content_files/';

                $fileUploaded = $this->upload($file, $fileName, false);

                if ($fileUploaded) {
                    $return['success'] = true;
                    $url = "/custom/domain_".$this->selectedDomain."/content_files/".$fileName;
                    $return['url'] = $this->container->get('request')->getSchemeAndHttpHost().$url;
                }
            } else {
                $return['message'] = $this->translator->trans('Backgroud image wrong file extension', [], 'widgets', $this->sitemgrLanguage);
            }
        } else {
            $return['message'] = "Background Image: ".$this->translator->trans('Error uploading image. Please try again.',
                    [], 'widgets', $this->sitemgrLanguage);
        }

        return $return;
    }

    /**
     * @param $file
     * @param $width
     * @param $height
     * @param $domain
     * @return array
     */
    public function saveContentImages($file, $width, $height, $domain)
    {
        if(is_array($file)) {
            $file = new UploadedFile($file['tmp_name'], $file['name']);
        }

        $return['success'] = false;

        if ($file->isValid()) {
            if (in_array($file->getMimeType(), ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'])) {
                $this->targetDir = $this->rootDir.'/../web/custom/domain_'.$this->selectedDomain.'/content_files/';

                if ($return = $this->uploadImageAndSaveDatabase($file, $width, $height, $domain)) {
                    $return['success'] = true;
                }
            } else {
                $return['message'] = $this->translator->trans('Image wrong file extension', [], 'widgets', $this->sitemgrLanguage);
            }
        } else {
            $return['message'] = "Logo: ".$this->translator->trans('Error uploading image. Please try again.',
                    [], 'widgets', $this->sitemgrLanguage);
        }

        return $return;
    }

    /**
     * @param string $link
     * @return mixed
     */
    public function saveSliderImagesUnsplash($link = '')
    {
        $return['success'] = false;

        if ($link != '') {
            $info = getimagesize($link);

            // Save Info on Image Table
            $em = $this->container->get('doctrine')->getManager();

            $imageObj = new Image();
            $imageObj->setWidth($info[0]);
            $imageObj->setHeight($info[1]);
            $imageObj->setUnsplash($link);
            $imageObj->setType('JPG');

            $em->persist($imageObj);
            $em->flush($imageObj);

            $return['code'] = $imageObj->getId();
            $return['url'] = $imageObj->getUnsplash();
            $return['success'] = true;
        } else {
            $return['message'] = "Logo: ".$this->translator->trans('Error uploading image. Please try again.',
                    [], 'widgets', $this->sitemgrLanguage);
        }

        return $return;
    }

    /**
     * @param UploadedFile $file The File uploaded
     * @param string $fileName The File name
     * @param int $domain The Domain id
     *
     * @return array
     */
    public function uploadImageCkeditor(UploadedFile $file, $fileName, $domain)
    {
        $basePath = 'custom/domain_'.$domain.'/image_files/ckeditor/';
        $return = [];

        if ($file->isValid()) {
            if ($file->getSize() > 10485760) {
                $return['message'] = $this->translator->trans('The file is too big', [], 'messages', $this->sitemgrLanguage);
            } elseif (!in_array($file->getMimeType(), ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'])) {
                $return['message'] = $this->translator->trans('File with extension not supported', [], 'messages', $this->sitemgrLanguage);
            }

            if (!isset($return['message'])) {
                $this->targetDir = $this->rootDir.'/../web/'.$basePath;

                $fileUploaded = $this->upload($file, $fileName, false);

                if ($fileUploaded) {
                    $return = [
                        'fileName' => $fileName,
                        'url'      => '/'.$basePath.$fileName,
                        'message'  => '',
                    ];
                }
            }
        }

        return $return;
    }

    /**
     * @param UploadedFile $file
     * @param $width
     * @param $height
     * @param $domain
     * @return array
     */
    private function uploadImageAndSaveDatabase(UploadedFile $file, $width, $height, $domain)
    {
        // set dir
        $basePath = 'custom/domain_'.$domain.'/image_files/';
        $this->targetDir = $this->rootDir.'/../web/'.$basePath;

        // Save Info on Image Table
        $em = $this->container->get('doctrine')->getManager();
        $imageObj = new Image();
        $imageObj->setWidth($width);
        $imageObj->setHeight($height);
        $imageObj->setPrefix('sitemgr_');
        $imageObj->setType(strtoupper($file->guessExtension()));

        $em->persist($imageObj);
        $em->flush($imageObj);

        // save file
        $fileName = 'sitemgr_photo_'.$imageObj->getId().'.'.$file->guessExtension();
        $this->upload($file, $fileName, false);

        return [
            'code' => $imageObj->getId(),
            'url'  => '/'.$basePath.$fileName,
        ];
    }

    /**
     * @return mixed
     */
    public function getTargetDir()
    {
        return $this->targetDir;
    }

    /**
     * @param mixed $targetDir
     */
    public function setTargetDir($targetDir)
    {
        $this->targetDir = $targetDir;
    }


    /**
     * @param $file
     * @return mixed
     */
    public function saveFavIcon($file)
    {
        $originalName = 'favicon_';
        $file = new UploadedFile($file['tmp_name'], $originalName);

        return $this->uploadFavicon($file, $originalName);
    }

    /**
     * @param $file
     * @return mixed
     */
    public function saveLogo($file)
    {
        $originalName = 'img_logo.png';
        $file = new UploadedFile($file['tmp_name'], $originalName);

        return $this->uploadLogo($file, $originalName);
    }

    /**
     * @param $file
     * @param $originalName
     * @param string $extension
     * @return mixed
     */
    public function saveBackgroundImage($file, $originalName, $extension = 'jpg')
    {
        $originalName = $originalName.'.'.$extension;
        $file = new UploadedFile($file['tmp_name'], $originalName);

        return $this->uploadBackgroundImage($file, $originalName);
    }

}
