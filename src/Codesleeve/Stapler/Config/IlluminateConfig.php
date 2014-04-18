<?php namespace Codesleeve\Stapler\Config;

use Illuminate\Config;

class IlluminateConfig implements ConfigInterface
{
    /**
     * An instance of Laravel's config class.
     *
     * @var Config
     */
    protected $config;

    /**
	 * Constructor method.
	 *
     * @param Config $config
	 */
	function __construct(Config $config)
	{
		$this->config = $config;
	}

    /**
     * Retrieve a configuration value.
     *
     * @param $name
     * @return mixed
     */
    public function get($name){
        return $this->config->get("stapler::$name");
    }

    /**
     * Set a configuration value.
     *
     * @param $name
     * @param $value
     * @return mixed
     */
    public function set($name, $value){
        return $this->config->set("stapler::$name", $value);
    }
}