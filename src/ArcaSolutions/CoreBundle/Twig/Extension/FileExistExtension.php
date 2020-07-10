<?php

namespace ArcaSolutions\CoreBundle\Twig\Extension;

/**
 * Class FileExistExtension
 *
 * Adds php's file_exists in twig
 *
 * @package ArcaSolutions\CoreBundle\Twig\Extension
 */
class FileExistExtension extends \Twig_Extension
{
    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            'file_exists' => new \Twig_SimpleFunction('file_exists', 'file_exists'),
            new \Twig_SimpleFunction('fileCache', [$this, 'fileCache'], [
                'is_safe' => ['html']
            ]),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'file_exist';
    }

    public function fileCache($file_path)
    {
        $change_date = @filemtime($_SERVER['DOCUMENT_ROOT'].'/'.$file_path);
        if (!$change_date) {
            //Fallback if mtime could not be found:
            $change_date = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        }
        return preg_replace('{\\.([^./]+)$}', ".$change_date.\$1", $file_path);
    }
}
