<?php

namespace Codesleeve\Stapler\Factories;

use PHPUnit_Framework_TestCase;

class AttachmentTest extends PHPUnit_Framework_TestCase
{
    /**
     * Setup method.
     */
    public function setUp()
    {
    }

    /**
     * Teardown method.
     */
    public function tearDown()
    {
    }

    /**
     * Test that the attachment factory can create an instance
     * of the Attachment class.
     *
     * @test
     */
    public function it_should_be_able_to_build_an_attachment_object()
    {
        $attachment = Attachment::create('testAttachment', []);

        $this->assertInstanceOf('Codesleeve\Stapler\Interfaces\Attachment', $attachment);
    }
}
