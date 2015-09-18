<?php namespace Codesleeve\Stapler\Config;

use Illuminate\Config\Repository;

class IlluminateConfig implements ConfigurableInterface
{
    /**
     * An instance of Laravel's config class.
     *
     * @var Repository
     */
    protected $config;

    /**
     * The name of the package this driver is being used with.
     *
     * @var string
     */
    protected $packageName;

    /**
     * Constructor method.
     *
     * @param Repository $config
     * @param string $packageName
     */
    function __construct(Repository $config, $packageName)
    {
        $this->config = $config;
        $this->packageName = $packageName;
    }

    /**
     * Retrieve a configuration value.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get($name, $default ){
        return $this->config->get("$this->packageName::$name", $default);
    }

    /**
     * Set a configuration value.
     *
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function set($name, $value){
        return $this->config->set("$this->packageName::$name", $value);
    }
}
