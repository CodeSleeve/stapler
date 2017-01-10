<?php

namespace Codesleeve\Stapler;

use Codesleeve\Stapler\Interfaces\ConfigInterface;

class NativeConfig implements ConfigInterface
{
    /**
     * An array of configuration values that have been
     * previously loaded.
     *
     * @var array
     */
    protected $items = [
        'stapler' => [
            'public_path' => '',
            'base_path' => '',
            'storage' => 'filesystem',
            'image_processing_library' => 'Imagine\Gd\Imagine',
            'default_url' => '/:attachment/:style/missing.png',
            'default_style' => 'original',
            'styles' => [],
            'hash_secret' => null,
            'keep_old_files' => false,
            'preserve_files' => false,
        ],
        'filesystem' => [
            'url' => '/system/:class/:attachment/:id_partition/:style/:filename',
            'path' => ':app_root/public:url',
            'override_file_permissions' => null,
        ],
        's3' => [
            's3_client_config' => [
                'key' => '',
                'secret' => '',
                'region' => '',
                'scheme' => 'http',
            ],
            's3_object_config' => [
                'Bucket' => '',
                'ACL' => 'public-read',
            ],
            'path' => ':attachment/:id/:style/:filename',
        ],
        'bindings' => [
            'attachment' => '\Codesleeve\Stapler\Attachment',
            'interpolator' => '\Codesleeve\Stapler\Interpolator',
            'resizer' => '\Codesleeve\Stapler\File\Image\Resizer',
            'style' => '\Codesleeve\Stapler\Style',
            'validator' => '\Codesleeve\Stapler\Validator',
        ],
    ];

    /**
     * Constructor method.
     *
     * @param array $items
     */
    public function __construct(array $items = null)
    {
        if ($items) {
            $this->items = $items;
        }
    }

    /**
     * Retrieve a configuration value.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function get(string $name)
    {
        list($group, $item) = array_pad(explode('.', $name), 2, null);

        if ($item) {
            return $this->loadItemFromFile($group, $item);
        }

        return $this->loadAllFromFile($group);
    }

    /**
     * Set a configuration value.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function set(string $name, $value)
    {
        list($group, $item) = array_pad(explode('.', $name), 2, null);

        if ($item) {
            $this->items[$group][$item] = $value;
        } else {
            $this->items[$group] = $value;
        }
    }

    /**
     * Load a specific configuration item from a specific
     * configuration group.
     *
     * @param string $group
     * @param string $item
     */
    protected function loadItemFromFile(string $group, string $item)
    {
        if (array_key_exists($group, $this->items) && array_key_exists($item, $this->items[$group])) {
            return $this->items[$group][$item];
        }
    }

    /**
     * Load all configuration items from a specific
     * configuration group.
     *
     * @param string $group
     */
    protected function loadAllFromFile(string $group)
    {
        if (array_key_exists($group, $this->items)) {
            return $this->items[$group];
        }
    }
}
