<?php

/**
 * This file is part of the heyflynn/sifoan package.
 */
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RouteCollection;
use Flint\Application;

class Sifoan extends Application {

    public function __construct($rootDir, $env, $debug = false)
    {
        parent::__construct($rootDir, $debug, ['env' => $env]);
        $this->init($env, $debug);
    }

    private function init($env, $debug = false) {

        // register service providers
        $providers = [

            // Sifoan required providers
            new Silex\Provider\HttpCacheServiceProvider(),
            new Silex\Provider\HttpFragmentServiceProvider(),
            new Silex\Provider\MonologServiceProvider(),

            // other availableSilex Providers
            // new Silex\Provider\ValidatorServiceProvider(),
            // new Silex\Provider\FormServiceProvider(),
            // new Silex\Provider\SessionServiceProvider(),
            // new Silex\Provider\SecurityServiceProvider(),
            // new Silex\Provider\ServiceControllerServiceProvider(),
            // new Silex\Provider\DoctrineServiceProvider()
            // new Silex\Provider\RememberMeServiceProvider()
            // new Silex\Provider\TranslationServiceProvider()
            // new Silex\Provider\SwiftmailerServiceProvider()
            // new Silex\Provider\SerializerServiceProvider()
        ];

        // debug providers
        if($debug) {
            $providers[] = new Silex\Provider\ServiceControllerServiceProvider();
            $providers[] = new Silex\Provider\WebProfilerServiceProvider();
        }


        // register each provider with config from settings
        foreach ($providers as $provider) {
            $name = $this->camelToUnderscore(str_replace("ServiceProvider","",$this->getClassName($provider)));
            $this->register($provider);
        }

        // app direction
        $dir =  __DIR__;
        $this['app_dir'] = $dir;
        $this['cache_dir'] = "$dir/cache";
        $this['config_dir'] = "$dir/config";
        $this['config.paths'] = "$dir/config";
        $this['config.cache_dir'] = "{$this['cache_dir']}/$env/config";
        $this['routing.resource'] = "{$this['config_dir']}/routing_$env.yml";
        $this['routing.options'] = array('cache_dir' => "{$this['cache_dir']}/$env/routing");

        if ($debug) {
            $this['profiler.cache_dir'] = "{$this['cache_dir']}/$env/profiler";
        }

        $this['configurator'] = $this->share(function (Sifoan $app) {
            return new Configurator($app['config.loader']);
        });

        // run flints Configurator
        $this->configure("{$this['config_dir']}/config_$env.yml");
    }

    public function getClassName($class)
    {
        if(is_object($class))
            $class = get_class($class);

        if ($pos = strrpos($class, '\\')) return substr($class, $pos + 1);
        return $pos;
    }


    public function camelToUnderscore($camel) {
        return $this->camelTo($camel, "_");
    }


    public function camelToDot($camel) {
        return $this->camelTo($camel, ".");
    }

    public function camelTo($camel, $seperator = "_") {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $camel, $matches);
        $ret = $matches[0];

        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }

        return implode($seperator, $ret);
    }
}
