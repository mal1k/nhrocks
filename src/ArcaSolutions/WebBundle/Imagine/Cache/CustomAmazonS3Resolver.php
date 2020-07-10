<?php

namespace ArcaSolutions\WebBundle\Imagine\Cache;

use ArcaSolutions\WebBundle\Imagine\WebpGenerator;
use Aws\S3\S3Client;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Cache\Resolver\AwsS3Resolver;
use Symfony\Component\Filesystem\Filesystem;

class CustomAmazonS3Resolver extends AwsS3Resolver {

    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var WebpGenerator
     */
    private $webpGenerator;
    /**
     * @var String
     */
    private $cloudFrontBaseUrl;

    /**
     * {@inheritdoc}
     */
    public function __construct(Filesystem $filesystem, $cloudFrontBaseUrl, S3Client $storage, $bucket, $acl = 'public-read', array $getOptions = array(), $putOptions = array()) {
        parent::__construct($storage, $bucket, $acl, $getOptions, $putOptions);
        $this->filesystem = $filesystem;
        $this->webpGenerator = new WebpGenerator();
        $this->cloudFrontBaseUrl = $cloudFrontBaseUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function store(BinaryInterface $binary, $path, $filter) {
        parent::store($binary, $path, $filter);

        $path = $this->getObjectPath($path, $filter);
        $webpPath = $this->webpGenerator->getWebpPath($path);

        // If webp file not exits let's create it
        if (!$this->objectExists($webpPath)) {

            // Create a local original version of cached image
            $tmpOriginalFilePath = $this->filesystem->tempnam('/tmp', 'cache-');
            $this->filesystem->dumpFile($tmpOriginalFilePath, $binary->getContent());

            // Create a local webp version of original cached image
            $tmpWebpFilePath = $this->filesystem->tempnam('/tmp', 'cache-');            
            $this->webpGenerator->createWebpFromImagePath($tmpOriginalFilePath, $tmpWebpFilePath);

            // Puts the webp image version into the bucket
            try {
                $webpContent = file_get_contents($tmpWebpFilePath);
                $this->storage->putObject(
                    array_merge(
                        $this->putOptions,
                        array(
                            'ACL' => $this->acl,
                            'Bucket' => $this->bucket,
                            'Key' => $webpPath,
                            'Body' => $webpContent,
                            'ContentType' => 'image/webp',
                        )
                    )
                );
            } catch (\Exception $e) {
                $this->logError('The object could not be created on Amazon S3.', array(
                    'objectPath' => $webpPath,
                    'filter' => $filter,
                    'bucket' => $this->bucket,
                    'exception' => $e,
                ));
    
                throw new NotStorableException('The object could not be created on Amazon S3.', null, $e);
            } finally {
                $this->filesystem->remove([$tmpOriginalFilePath, $tmpWebpFilePath]);
            }
        }
    }

}