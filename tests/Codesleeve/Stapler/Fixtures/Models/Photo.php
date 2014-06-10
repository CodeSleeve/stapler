<?php namespace Codesleeve\Stapler\Fixtures\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Codesleeve\Stapler\ORM\StaplerableInterface;
use Codesleeve\Stapler\ORM\EloquentTrait;

class Photo extends Eloquent implements StaplerableInterface
{
	use EloquentTrait;

	protected $fillable = ['id'];

	/**
	 * Constructor method.
	 * 
	 * @param array $attributes
	 */
	function __construct($attributes = ['id' => 1])
	{
		$this->hasAttachedFile('photo', [
            'styles' => [
                'thumbnail' => '100x100'
            ],
            'url' => '/system/:attachment/:id_partition/:style/:filename',
            'default_url' => '/defaults/:style/missing.png',
            'convert_options' => [
                'thumbnail' => ['quality' => 100, 'auto-orient' => true]
            ]
        ]);

        parent::__construct($attributes);
	}
}