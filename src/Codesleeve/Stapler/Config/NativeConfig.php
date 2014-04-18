<?php namespace Codesleeve\Stapler\Config;

class NativeConfig implements ConfigInterface
{
    /**
     * The directory where configuration files will be loaded
     * from.
     */
    public $location;

    /**
     * An array of configuration values that have been
     * previously loaded.
     *
     * @var array
     */
    protected $items = [];

    /**
     * Constructor method.
     *
     * @param mixed $location
     */
    function __construct($location = null)
    {
        $this->location = $location ?: realpath(__DIR__ . '/../../..' . '/config');

        $files = glob("{$this->location}/*.php");

        foreach ($files as $file) {
            $this->loadFile($file);
        }
    }

    /**
     * Retrieve a configuration value.
     *
     * @param $name
     * @return mixed
     */
    public function get($name)
    {
        list($file, $item) = array_pad(explode('.', $name), 2, null);

        if ($item) {
            return $this->loadItemFromFile($file, $item);
        }

        return $this->loadAllFromFile($file);
    }

    /**
     * Set a configuration value.
     *
     * @param $name
     * @param $value
     * @return mixed
     */
    public function set($name, $value)
    {
        list($file, $item) = array_pad(explode('.', $name), 2, null);

        if ($item) {
            $this->items[$file][$item] = $value;
        }
        else {
            $this->items[$file] = $value;
        }
    }

    /**
     * Load a specific configuration item from a specific
     * configuration file.
     *
     * @param string $file
     * @param string $item
     */
    protected function loadItemFromFile($file, $item)
    {
        if (array_key_exists($file, $this->items) && array_key_exists($this->items[$file], $item)) {
            return $this->items[$file][$item];
        }
    }

    /**
     * Load all configuration items from a specific
     * configuration file
     *
     * @param string $file
     */
    protected function loadAllFromFile($file)
    {
        if (array_key_exists($file, $this->items)) {
            return $this->items[$file];
        }
    }

    /**
     * Load a configuration file into the items array.
     *
     * @param string $file
     */
    protected function loadFile($file)
    {
        $fileName = basename($file, '.php');
        $this->items[$fileName] = include $file;
    }
}

