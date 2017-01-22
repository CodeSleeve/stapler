<?php

namespace Codesleeve\Stapler\ORM;

use Mockery as m;
use PHPUnit_Framework_TestCase;
use Codesleeve\Stapler\Fixtures\Models\Photo;

class EloquentTraitTest extends PHPUnit_Framework_TestCase
{
    /**
     * Setup method.
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Teardown method.
     */
    public function tearDown()
    {
        m::close();
    }

    /**
     * Test that the EloquentTrait trait add listeners and methods when used on an eloquent model instance.
     *
     * @test
     */
    public function it_add_methods_to_the_model_that_uses_it()
    {
        // given
        $photo = new Photo;

        // when / then
        $photo->bootEloquentTrait();
        $this->assertTrue(method_exists($photo, 'getAttachments'));
        $this->assertTrue(method_exists($photo, 'hasAttachedFile'));
    }
}