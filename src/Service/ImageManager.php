<?php

namespace App\Service;

use App\Entity\Image;
use Aws\CloudFront\CloudFrontClient;
use Aws\S3\S3Client;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageManager
{
    private EntityManagerInterface $em;
    private LoggerInterface $logger;
    private Filesystem $filesystem;
    private S3Client $s3Client;
    private CloudFrontClient $cloudFrontClient;
    private string $distributionId;
    private string $s3CacheBucket;

    public function __construct(
        EntityManagerInterface $em,
        LoggerInterface        $logger,
        Filesystem             $filesystem,
        S3Client               $s3Client,
        CloudFrontClient       $cloudFrontClient,
        string                 $distributionId,
        string                 $s3CacheBucket
    )
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->filesystem = $filesystem;
        $this->s3Client = $s3Client;
        $this->cloudFrontClient = $cloudFrontClient;
        $this->distributionId = $distributionId;
        $this->s3CacheBucket = $s3CacheBucket;
    }

    /**
     * Create file on abstracted filesystem (currently S3)
     * @throws FilesystemException
     */
    public function upload(UploadedFile $file, string $coasterSlug = null): string
    {
        $filename = $this->generateFilename($file, $coasterSlug);

        $this->filesystem->write(
            $filename,
            $file->getContent()
        );

        return $filename;
    }

    /**
     * Remove file from abstracted filesystem (currently S3)
     * @throws FilesystemException
     */
    public function remove(string $filename)
    {
        $this->filesystem->delete($filename);
    }

    /**
     * Remove file from S3 Cache Bucket & CloudFront cache
     */
    public function removeCache(Image $image)
    {
        $this->s3Client->deleteObjects([
            'Bucket' => $this->s3CacheBucket,
            'Delete' => [
                'Objects' => [
                    ['Key' => '1440x1440/' . $image->getFilename()],
                    ['Key' => '600x336/' . $image->getFilename()],
                    ['Key' => '280x210/' . $image->getFilename()],
                    ['Key' => '96x96/' . $image->getFilename()]
                ]
            ]
        ]);

        $this->cloudFrontClient->createInvalidation([
            'DistributionId' => $this->distributionId,
            'InvalidationBatch' => [
                'CallerReference' => uniqid(),
                'Paths' => [
                    'Items' => [
                        // cannot use a wildcard at the beginning
                        '/1440x1440/' . $image->getFilename(),
                        '/600x336/' . $image->getFilename(),
                        '/280x210/' . $image->getFilename(),
                        '/96x96/' . $image->getFilename()
                    ],
                    'Quantity' => 4
                ]
            ]
        ]);
    }

    /**
     * Generates a filename like fury-325-carowinds-64429c62b6b23.jpg
     */
    private function generateFilename(UploadedFile $file, string $coasterSlug = null): string
    {
        return sprintf('%s-%s.%s', $coasterSlug, uniqid(), $file->guessExtension());
    }

    /**
     * Update main image property of all coasters
     * @todo faire mieux :)
     */
    public function setMainImages()
    {
        $conn = $this->em->getConnection();

        $sql = 'update coaster c
            left join (
	            select sub.id, sub.coaster_id from (
		            select * from image
		            where enabled = 1
		            order by like_counter desc, updated_at desc
		            limit 18446744073709551615) as sub
	            group by coaster_id
            ) as i on i.coaster_id = c.id
            set c.main_image_id = i.id;';

        try {
            $stmt = $conn->prepare($sql);
            $stmt->executeStatement();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Update like counters for all images
     * @todo faire mieux :)
     */
    public function updateLikeCounters()
    {
        $conn = $this->em->getConnection();

        $sql = 'update image i1
            join (
                select i.id, count(li.image_id) as nb from image i
                left join liked_image li on li.image_id = i.id
                group by i.id
            ) as i2
            on i2.id = i1.id
            set i1.like_counter = i2.nb;';

        try {
            $stmt = $conn->prepare($sql);
            $stmt->executeStatement();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
