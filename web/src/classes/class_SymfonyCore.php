<?php

use ArcaSolutions\MultiDomainBundle\HttpFoundation\MultiDomainRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SymfonyCore
{
    /**
     * @var string
     */
    private static $environment = null;
    /**
     * @var \ArcaSolutions\CoreBundle\Kernel\Kernel
     */
    private static $kernel = null;
    /**
     * @var \ArcaSolutions\ModStoresBundle\Kernel\Kernel
     */
    private static $modstoreKernel = null;
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private static $container = null;

    /**
     * Initializes Symfony kernel and container
     * @throws Exception
     */
    public static function initialize()
    {
        if (!self::$kernel) {
            $s = DIRECTORY_SEPARATOR;
            $autoloadFile = EDIRECTORY_ROOT."{$s}..{$s}app{$s}autoload.php";
            $kernelFile = EDIRECTORY_ROOT."{$s}..{$s}app{$s}AppKernel.php";

            if (file_exists($autoloadFile)) {
                require_once $autoloadFile;
            }

            if (file_exists($kernelFile)) {
                require_once $kernelFile;
            } else {
                throw new \Exception("\n\n\nUnable to locate App Kernel\nLocation: {$kernelFile}\n\n\n");
            }

            $env = static::getEnvironment();

            $kernel = new \AppKernel($env, $env == 'dev');
            $kernel->boot();
            $container = $kernel->getContainer();

            $request = MultiDomainRequest::createFromGlobals();
            // $locale = self::getLocale($container);

            $request->attributes->set('is_legacy', true);
            $request->server->set('SCRIPT_FILENAME', 'app.php');
            // $request->setLocale($locale);

            $container->enterScope('request');
            $container->get('request_stack')->push($request);
            $container->set('request', $request);
            // $container->get('translator')->setLocale($locale);

            self::$kernel = $kernel;
            self::$container = $container;

            /* ModStores Hooks */
            self::$modstoreKernel = new \ArcaSolutions\ModStoresBundle\ModStoresBundle();
        }
    }

    /**
     * Returns the active environment. Will attempt to get it from .htaccess if the environment variable is not set.
     * If all else fails, will assume prod.
     * @return string
     */
    public static function getEnvironment()
    {
        if (static::$environment === null) {
            static::$environment = getenv('SYMFONY_ENV');

            /* SYMFONY_ENV is not defined. This means execution skipped .htaccess file */
            if (!static::$environment) {
                /* All right, let's steal the environment from .htaccess */
                $htaccessFile = EDIRECTORY_ROOT.DIRECTORY_SEPARATOR.".htaccess";

                if (file_exists($htaccessFile)) {
                    $htaccessFileContent = file_get_contents($htaccessFile);

                    $matches = [];
                    preg_match("/(?<=setEnv SYMFONY\\_ENV \\')\\w+(?=\\')/", $htaccessFileContent, $matches);

                    /* Got it */
                    $matches and static::$environment = array_pop($matches);
                } else {
                    /* When all else fails, we'll take the safe bet */
                    static::$environment = "prod";
                }
            }
        }

        return static::$environment;
    }

    /**
     * Retrieve locale based on current request area
     *
     * @param $container
     * @return string
     */
    public static function getLocale($container)
    {
        $locale = $container->get("multi_domain.information")->getLocale();
        if (strpos($_SERVER["PHP_SELF"], SITEMGR_ALIAS)) {
            $locale = $container->get('settings')->getSetting('sitemgr_language');
        }

        return  $locale == 'ge_ge' ? 'de_de' : $locale;
    }

    /**
     * Rebuilds ES location index
     */
    public static function rebuildElasticsearchLocations()
    {
        try {
            self::getContainer()->get("location.synchronization")->generateAll();
        } catch (Exception $e) {
            $logger = SymfonyCore::getContainer()->get("logger");
            $logger->critical("Elasticsearch Synchronization Failure", ["exception" => $e]);
        }
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public static function getContainer()
    {
        return self::$container;
    }

    /**
     * @param integer $domainId The domain id
     */
    public static function setDomainDB($domainId)
    {
        $container = self::$container;
        $connection = $container->get("doctrine.dbal.domain_connection");
        $params = $connection->getParams();

        /* @var $domain \ArcaSolutions\CoreBundle\Entity\Domain */
        if ($domain = $container->get("doctrine")->getRepository("CoreBundle:Domain", "main")->find($domainId)) {
            $dbname = $domain->getDatabaseName();

            if ($dbname != $params['dbname']) {
                $params['dbname'] = $dbname;
                if ($connection->isConnected()) {
                    $connection->close();
                }

                $connection->__construct(
                    $params,
                    $connection->getDriver(),
                    $connection->getConfiguration(),
                    $connection->getEventManager()
                );

                try {
                    $connection->connect();

                    $container->get("multi_domain.information")->setActiveHost($domain->getUrl());
                } catch (\Exception $e) {
                    $container->get('logger')->critical('Could not instantiate domain connection on Sitemgr');
                }
            }
        } else {
            $container->get('logger')->critical('Could not instantiate domain connection on Sitemgr');
        }
    }

    /**
     * Forwards the request to another controller.
     *
     * @param string $controller The controller name (a string like BlogBundle:Post:index)
     * @param array $path An array of path parameters
     * @param array $query An array of query parameters
     *
     * @return Response A Response instance
     */
    public static function forward($controller, array $path = [], array $query = [])
    {
        /* Removes the profiler */
        if (in_array(static::$environment, ['dev', 'test'])) {
            self::$container->get('profiler')->disable();
        }

        /* Gets sitemgr sessions */
        $session = self::$container->get('session');
        if (isset($_SESSION)) {
            foreach ($_SESSION as $name => $value) {
                $session->set($name, $value);
            }
        }

        /* Sets session */
        $request = self::$container->get('request_stack')->getCurrentRequest();
        $request->setSession($session);

        /* Sets locale */
        $locale = self::getLocale(self::$container);
        $request->setLocale($locale);

        /* Load the content from symfony bundle */
        $path['_controller'] = $controller;
        $subRequest = self::$container->get('request_stack')->getCurrentRequest()->duplicate($query,
            $request->request->all(), $path);

        return SymfonyCore::getKernel()->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * @return \ArcaSolutions\CoreBundle\Kernel\Kernel
     */
    public static function getKernel()
    {
        return self::$kernel;
    }

    /**
     * @return \ArcaSolutions\ModStoresBundle\Kernel\Kernel
     */
    public static function getModstoreKernel()
    {
        return self::$modstoreKernel;
    }
}
