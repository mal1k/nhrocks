<?php

namespace ArcaSolutions\MultiDomainBundle\Services;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Parameter
 *
 * @author Diego de Biagi <diego.biagi@arcasolutions.com>
 * @since VERSION
 */
class Parameter
{
    /** @var Settings */
    private $multidomainSettings;

    /** @var string */
    private $kernelDir;

    /** @var array */
    private $parameters = [];

    public function __construct(Settings $multidomainSettings, $kernelDir)
    {
        $this->multidomainSettings = $multidomainSettings;
        $this->kernelDir = $kernelDir;

        $this->load();
    }

    private function load()
    {
        $exts = ['configs\.yml', 'payment\.yml', 'route\.yml'];
        $pattern = sprintf('/%s/', implode('$|', $exts));

        $iterator = Finder::create()
            ->depth(0)
            ->name($pattern)
            ->in($this->kernelDir.'/config/domains')
            ->getIterator();

        foreach ($iterator as $file) {
            $host = preg_replace($pattern, '', $file->getFilename());
            $host = trim(str_replace('-', '_', $host), '.');
            $config = Yaml::parse($file->getContents());

            if(is_array($this->parameters[$host])) {
                $this->parameters[$host] = array_merge($this->parameters[$host], array_pop($config));

                continue;
            }

            $this->parameters[$host] = array_pop($config);
        }
    }

    /**
     * Get domain parameter
     *
     * @author Diego de Biagi <diego.biagi@arcasolutions.com>
     * @since VERSION
     * @param $key
     * @param null $domain If null uses the the active domain
     * @return string|null
     */
    public function get($key, $domain = null)
    {
        if (!$domain) {
            $domain = $this->multidomainSettings->getActiveHost();
        }

        return isset($this->parameters[$domain][$key]) ? $this->parameters[$domain][$key] : null;
    }
}