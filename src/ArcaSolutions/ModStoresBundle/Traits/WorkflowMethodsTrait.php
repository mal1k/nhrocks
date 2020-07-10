<?php

namespace ArcaSolutions\ModStoresBundle\Traits;

use Symfony\Component\Yaml\Yaml;

/**
 * Class CommandBaseMethods
 *
 * @package ArcaSolutions\ModStoresBundle\Traits
 * @author Gabriel Fernandes <gabriel.fernandes@arcasolutions.com>
 */
trait WorkflowMethodsTrait
{
    /**
     * @var string
     */
    private $rootPath = __DIR__.'/../../../../';

    /**
     * @var string
     */
    private $pluginsPath = __DIR__.'/../Plugins/';

    /**
     * @var
     */
    private $configPath = __DIR__.'/../../../../app/config/';

    /**
     * @var string
     */
    private $indexPath = __DIR__.'/../../../../ElasticConfigs/IndexCreation.json';

    /**
     * @var string
     */
    private $migrationsDomainPath = __DIR__.'/../../../../app/DoctrineMigrations/Domain/';

    /**
     * @var string
     */
    private $migrationsMainPath = __DIR__.'/../../../../app/DoctrineMigrations/Main/';

    /**
     * @var string
     */
    private $themesBasePath = __DIR__.'/../../../../app/Resources/base/styles/';

    /**
     * @var string
     */
    private $csvSamplePath = __DIR__.'/../../../../web/sitemgr/content/import/';

    /**
     * @var string
     */
    private $cronPath = __DIR__.'/../../../../web/cron/';

    /**
     * @var string
     */
    private $jsPath = __DIR__.'/../../../../web/assets/js/';

    /**
     * @var string
     */
    private $imagePath = __DIR__.'/../../../../web/assets/images/';

    /**
     * @var string
     */
    private $widgetPlaceholderPath = __DIR__.'/../../../../web/sitemgr/assets/img/widget-placeholder/';

    /**
     * @var string
     */
    private $gitignorePath = __DIR__.'/../../../../.gitignore';

    /**
     * Update Yml files
     *
     * @param string $ymlName
     * @param array $keys
     * @param string $value
     * @return void
     */
    public function overwriteYml($ymlName = '', $keys = [], $value = null)
    {
        if (is_array($keys) && !empty($ymlName)) {
            $filesOrigin = glob($this->configPath.$ymlName);
            if ($filesOrigin != null) {
                foreach ($filesOrigin as $file) {
                    $yml = Yaml::parse(file_get_contents($file));

                    $pointer = &$yml;
                    foreach ($keys as $key) {
                        $pointer = &$pointer[$key];
                    }
                    $pointer = $value;

                    $yml = Yaml::dump($yml, 999);
                    file_put_contents($file, $yml);
                }
            }
        }
    }

    /**
     * Update ElasticSearch index json
     *
     * @param array $keys
     * @param string $value
     */
    public function overwriteIndexCreation($keys = [], $value = null)
    {
        if (is_array($keys)) {
            $indexCreation = json_decode(file_get_contents($this->indexPath), true);

            $pointer = &$indexCreation;
            foreach ($keys as $k) {
                $pointer = &$pointer[$k];
            }
            $pointer = $value;

            $indexCreation = json_encode($indexCreation, 999);
            file_put_contents($this->indexPath, $indexCreation);
        }
    }

    /**
     * Copy plugins Migrations to right path
     *
     * @param string $pluginBundle
     * @return void
     */
    public function copyMigrations($pluginBundle = null)
    {
        if (!empty($pluginBundle)) {

            $pluginFolder = $this->extractPluginName($pluginBundle);

            // copy domain migrations
            $this->copyResources($this->pluginsPath.$pluginFolder.'/Migrations/Domain/*.php',
                $this->migrationsDomainPath);
            // copy main migrations
            $this->copyResources($this->pluginsPath.$pluginFolder.'/Migrations/Main/*.php', $this->migrationsMainPath);
        }
    }

    /**
     * Removes bundle name reference to get plugin folder as Symfony folfer pathern
     *
     * @param string $pluginBundle
     * @return string
     */
    public function extractPluginName($pluginBundle)
    {
        return str_replace('Bundle', '', $pluginBundle);
    }

    /**
     * Copy plugins files to a destiny path
     *
     * @param string $origin
     * @param string $destinyPath
     * @return void
     */
    public function copyResources($origin = null, $destinyPath = null)
    {
        if (!empty($origin) && !empty($destinyPath)) {
            $filesOrigin = glob($origin, GLOB_BRACE);
            if ($filesOrigin != null) {
                foreach ($filesOrigin as $file) {
                    $fileName = preg_replace('/^(.)*(\/)/', '', $file);
                    copy($file, $destinyPath.$fileName);

                    $this->overwriteGitignore($destinyPath.$fileName);
                }
            }
        }
    }

    /**
     * Update base gitignore file
     *
     * @param string $rule
     * @return void
     */
    public function overwriteGitignore($rule = null)
    {
        if (!empty($rule)) {

            $rule = preg_replace('/^(.)*(\.\.\/)+/', '', $rule);

            if (file_exists($this->gitignorePath)) {
                $file = file_get_contents($this->gitignorePath);

                if (strpos($file, $rule) === false) {
                    file_put_contents($this->gitignorePath, PHP_EOL.$rule, FILE_APPEND);
                }
            }
        }
    }

    /**
     * Copy plugins Less files to all themes and adds import to modstores.less
     *
     * @param string $pluginBundle
     * @return void
     */
    public function copySass($pluginBundle = null)
    {
        if (!empty($pluginBundle)) {
            $pluginFolder = $this->extractPluginName($pluginBundle);
            $styleFile = glob($this->pluginsPath.$pluginFolder.'/Resources/assets/*.scss');

            if ($styleFile != null) {
                $assetsPath = $this->themesBasePath;
                $modstoreFile = $assetsPath.'modstore/modstore.scss';
                $modstorePath = $assetsPath.'modstore/';

                foreach ($styleFile as $file) {
                    $fileName = preg_replace('/^(.)*(\/)/', '', $file);
                    $pluginName = preg_replace('/\.scss/', '', $fileName);
                    $importPlugin = '@import "'.$pluginName."\"; \n";

                    copy($file, $modstorePath.$fileName);

                    if (file_exists($modstoreFile)) {
                        if (strpos(file_get_contents($modstoreFile), $importPlugin) === false) {
                            file_put_contents($modstoreFile, $importPlugin, FILE_APPEND);
                        }
                    } else {
                        file_put_contents($modstoreFile, $importPlugin);
                    }
                }
            }
        }
    }

    /**
     * Update CSV Sanoke file
     *
     * @param string $csvName
     * @param string $sampleHeader
     * @param strin $sampleContent
     * @return void
     */
    public function overwriteCsvSample($csvName = null, $sampleHeader = '', $sampleContent = '')
    {
        if (!empty($csvName)) {

            $file = file_get_contents($this->csvSamplePath.$csvName);

            if (strpos($file, $sampleHeader) === false) {

                $header = true;
                $lines = explode("\n", $file);

                foreach ($lines as $key => &$value) {
                    if (!empty($value)) {
                        if ($header) {
                            $value = $value.','.$sampleHeader;
                            $header = false;
                        } else {
                            $value = $value.','.$sampleContent;
                        }
                    }
                }

                file_put_contents($this->csvSamplePath.$csvName, implode("\n", $lines));
            }
        }
    }

    /**
     * Copy plugins Widget images placeholders to right path
     *
     * @param string $pluginBundle
     * @return void
     */
    public function copyWidgetPlaceholders($pluginBundle = null)
    {
        if (!empty($pluginBundle)) {

            $pluginFolder = $this->extractPluginName($pluginBundle);

            $this->copyResources($this->pluginsPath.$pluginFolder.'/Resources/assets/images/widgets/*.jpg',
                $this->widgetPlaceholderPath);
        }
    }

    /**
     * Copy plugins stubs to right path
     *
     * @param string $pluginBundle
     * @param string $origin
     * @param string $destinyPath
     * @return void
     */
    public function copyStub($pluginBundle = null, $originFile = null, $destinyFile = null)
    {
        if (!empty($pluginBundle) && !empty($originFile) && !empty($destinyFile)) {

            $pluginFolder = $this->extractPluginName($pluginBundle);

            $originFiles = glob($this->pluginsPath.$pluginFolder.'/Stub/'.$originFile.'.stub', GLOB_BRACE);
            if ($originFiles != null) {
                foreach ($originFiles as $file) {
                    copy($file, $this->rootPath.$destinyFile);

                    $this->overwriteGitignore($this->rootPath.$destinyFile);
                }
            }
            $this->overwriteGitignore('app/Resources/base/styles/modstore/*.scss');
        }
    }

    /**
     * Execute a custom SQL query
     *
     * @param string $query
     * @param array $params
     * @param string $database
     * @return void
     */
    public function executeSql($query = null, $params = [], $database = 'domain')
    {
        if (!empty($query)) {
            $em = $this->container->get('doctrine')->getManager();
            $connection = $em->getConnection('main');

            $statement = $connection->prepare($query);
            foreach ($params as $key => $value) {
                $statement->bindValue($key, $value);
            }
            $statement->execute();
        }
    }

    /**
     * Create a new directory
     *
     * @param string $directoryPath
     * @param string $directoryName
     * @return void
     */
    public function makeDirectory($directoryPath = null, $directoryName = null)
    {
        if (!empty($directoryPath) && !empty($directoryName)) {

            !preg_match('/(.)+\/$/', $directoryPath) and $directoryPath = $directoryPath.'/';

            if (!file_exists($this->rootPath.$directoryPath.$directoryName)) {
                mkdir($this->rootPath.$directoryPath.$directoryName, 0755, true);
            }
        }
    }

    /**
     * Create a new file
     *
     * @param string $directoryPath
     * @param string $directoryName
     * @return void
     */
    public function makeFile($filePath = null, $fileName = null, $fileContent = '', $overwrite = false)
    {
        if (!empty($filePath) && !empty($fileName)) {

            !preg_match('/(.)+\/$/', $filePath) and $filePath = $filePath.'/';

            if (!file_exists($this->rootPath.$filePath.$fileName)) {

                $file = fopen($this->rootPath.$filePath.$fileName, 'a');
                fwrite($file, $fileContent);
                fclose($file);

            } else if ($overwrite) {
                // should overwrite file
            }

            $this->overwriteGitignore($this->rootPath.$filePath.$fileName);
        }
    }

    /**
     * Returns project activated themes
     *
     * @return array
     */
    public function getThemes()
    {
        $yml = Yaml::parse(file_get_contents($this->configPath.'config.yml'));

        return $yml['liip_theme']['themes'];
    }
}