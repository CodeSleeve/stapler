<?php

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;

class AttachmentTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $mockFileUploadDir = vfsStream::setup('foo/bar');
    }

    public function tearDown()
    {
        $this->mockFileUploadDir = null;
    }

    /**
     * makeMethodPublic method
     * 
     * @param  string $name - The name of the method we're making public
     * @return callable
     */
    protected function makeMethodPublic($name) 
    {
        $class = new ReflectionClass('Codesleeve\Stapler\Attachment');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        
        return $method;
    }

    public function testReturnResourceReturnsAPath()
    {
        $expected = 'foo';
        $attachment = $this->getMock('Codesleeve\Stapler\Attachment', ['validateOptions', 'returnPath'], ['testAttachment']);
        $attachment->expects($this->once())
            ->method('returnPath')
            ->will($this->returnValue('foo'));

        $method = $this->makeMethodPublic('returnResource');
        $actual = $method->invokeArgs($attachment, ['path']);

        $this->assertEquals($expected, $actual);
    }

    public function testReturnResourceReturnsAUrl()
    {
        $expected = 'bar';
        $attachment = $this->getMock('Codesleeve\Stapler\Attachment', ['validateOptions', 'returnUrl'], ['testAttachment']);
        $attachment->expects($this->once())
            ->method('returnUrl')
            ->will($this->returnValue('bar'));

        $method = $this->makeMethodPublic('returnResource');
        $actual = $method->invokeArgs($attachment, ['url']);

        $this->assertEquals($expected, $actual);
    }

    /**
     * testReturnPath method
     *
     * @dataProvider returnPathDataProvider
     * @return void
     */
    public function testReturnPath($expected, $attachment)
    {
        $method = $this->makeMethodPublic('returnPath');
        $actual = $method->invokeArgs($attachment, ['thumbnail']);

        $this->assertEquals($expected, $actual);
    }

    /**
     * returnPathDataProvider method
     * 
     * @return array
     */
    public function returnPathDataProvider()
    {
        $attachment1 = $this->getMock('Codesleeve\Stapler\Attachment', ['validateOptions', 'path'], ['testAttachment']);
        $attachment1->expects($this->once())
            ->method('path')
            ->will($this->returnValue(vfsStream::url('foo')));

        $attachment2 = $this->getMock('Codesleeve\Stapler\Attachment', ['validateOptions', 'path', 'defaultPath'], ['testAttachment']);
        $attachment2->expects($this->once())
            ->method('path')
            ->will($this->returnValue(''));
        
        $attachment2->expects($this->once())
            ->method('defaultPath')
            ->will($this->returnValue('bar'));

        return [
            'file exists' => [vfsStream::url('foo'), $attachment1],
            'file does not exist' => ['bar', $attachment2]
        ];
    }

    /**
     * testReturnUrl method
     *
     * @dataProvider returnUrlDataProvider
     * @return void
     */
    public function testReturnUrl($expected, $attachment)
    {
        $method = $this->makeMethodPublic('returnUrl');
        $actual = $method->invokeArgs($attachment, ['thumbnail']);

        $this->assertEquals($expected, $actual);
    }

    /**
     * returnUrlDataProvider method
     * 
     * @return array
     */
    public function returnUrlDataProvider()
    {
        $attachment1 = $this->getMock('Codesleeve\Stapler\Attachment', ['validateOptions', 'absoluteUrl', 'url'], ['testAttachment']);
        $attachment1->expects($this->once())
            ->method('absoluteUrl')
            ->will($this->returnValue(vfsStream::url('foo')));
        
        $attachment1->expects($this->once())
            ->method('url')
            ->will($this->returnValue('foo'));

        $attachment2 = $this->getMock('Codesleeve\Stapler\Attachment', ['validateOptions', 'absoluteUrl', 'defaultUrl'], ['testAttachment']);
        $attachment2->expects($this->once())
            ->method('absoluteUrl')
            ->will($this->returnValue(''));
        
        $attachment2->expects($this->once())
            ->method('defaultUrl')
            ->will($this->returnValue('bar'));

        return [
            'file exists' => ['foo', $attachment1],
            'file does not exist' => ['bar', $attachment2]
        ];
    }

    /**
     * testPath method
     * 
     * @return void
     */
    public function testPath()
    {
        $attachment = $this->getMock('Codesleeve\Stapler\Attachment', ['validateOptions', 'publicPath', 'url'], ['testAttachment']);
        $attachment->expects($this->once())
            ->method('publicPath')
            ->will($this->returnValue('foo'));

        $attachment->expects($this->once())
            ->method('url')
            ->will($this->returnValue('bar'));

        $expected = 'foobar';
        $method = $this->makeMethodPublic('path');
        $actual = $method->invokeArgs($attachment, []);

        $this->assertEquals($expected, $actual);
    }

    /**
     * testDefaultPath method
     * 
     * @return void
     */
    public function testDefaultPath()
    {
        $attachment = $this->getMock('Codesleeve\Stapler\Attachment', ['validateOptions', 'publicPath', 'defaultUrl'], ['testAttachment']);
        $attachment->expects($this->once())
            ->method('publicPath')
            ->will($this->returnValue('foo'));

        $attachment->expects($this->once())
            ->method('defaultUrl')
            ->will($this->returnValue('bar'));

        $expected = 'foobar';
        $method = $this->makeMethodPublic('defaultPath');
        $actual = $method->invokeArgs($attachment, []);

        $this->assertEquals($expected, $actual);
    }

    /**
     * testUrl method
     * 
     * @return void
     */
    public function testUrl()
    {
        $attachment = $this->getMock('Codesleeve\Stapler\Attachment', ['validateOptions', 'interpolateString'], ['testAttachment', ['url' => 'foo']]);
        $attachment->expects($this->once())
            ->method('interpolateString')
            ->with($this->equalTo('foo'))
            ->will($this->returnValue('bar'));

        $expected = 'bar';
        $method = $this->makeMethodPublic('url');
        $actual = $method->invokeArgs($attachment, []);

        $this->assertEquals($expected, $actual);
    }

    /**
     * testDefaultUrl method
     * 
     * @return void
     * @dataProvider defaultUrlDataProvider
     */
    public function testDefaultUrl($expected, $attachment)
    {
        $method = $this->makeMethodPublic('defaultUrl');
        $actual = $method->invokeArgs($attachment, []);

        $this->assertEquals($expected, $actual);    
    }

    /**
     * defaultUrlDataProvider method
     * 
     * @return array
     */
    public function defaultUrlDataProvider()
    {
        $attachment1 = $this->getMock('Codesleeve\Stapler\Attachment', ['validateOptions', 'interpolateString'], ['testAttachment']);

        $attachment2 = $this->getMock('Codesleeve\Stapler\Attachment', ['validateOptions', 'interpolateString'], ['testAttachment', ['default_url' => 'foo']]);
        $attachment2->expects($this->once())
            ->method('interpolateString')
            ->with($this->equalTo('foo'))
            ->will($this->returnValue('bar'));

        return [
            'No default url' => ['', $attachment1],
            'Default url present' => ['bar', $attachment2]
        ];
    }
    
    /**
     * testRealPath method
     * 
     * @return void
     */
    public function testRealPath()
    {
        $attachment = $this->getMock('Codesleeve\Stapler\Attachment', ['validateOptions'], ['testAttachment']);
        $method = $this->makeMethodPublic('realPath');
        $actual = $method->invokeArgs($attachment, ['foo']);

        $this->assertFalse($actual);
    }

    /**
     * testValidateOptions method
     *
     * @return void
     */
    public function testValidateOptionsWithValidOptionsWillPass()
    {
        $attachment = $this->getMockBuilder('Codesleeve\Stapler\Attachment')
            ->disableOriginalConstructor()
            ->getMock();

        $options = [
            'url' => '/system/:class/:attachment/:id_partition/:style/:filename',
            'default_url' => '/:attachment/:style/missing.png',
            'default_style' => 'original',
            'styles' => [],
            'keep_old_files' => false
        ];

        $method = $this->makeMethodPublic('validateOptions');
        $actual = $method->invokeArgs($attachment, [$options]);
    }

    /**
     * testValidateOptionsWithInvalidDataWillFail method
     *
     * @expectedException Codesleeve\Stapler\Exceptions\InvalidUrlOptionException
     * @expectedExceptionMessage Invalid file url: an :id or :id_partition is required.
     * @return void
     */
    public function testValidateOptionsWithInvalidDataThrowsExceptionl()
    {
        $attachment = $this->getMockBuilder('Codesleeve\Stapler\Attachment')
            ->disableOriginalConstructor()
            ->getMock();

        $options = [
            'url' => '/system/:class/:attachment/:style/:filename',
            'default_url' => '/:attachment/:style/missing.png',
            'default_style' => 'original',
            'styles' => [],
            'keep_old_files' => false
        ];

        $method = $this->makeMethodPublic('validateOptions');
        $actual = $method->invokeArgs($attachment, [$options]);
    }

    /**
     * testIsImage method
     * 
     * @return void
     */
    public function testIsImage()
    {
        // Mock a config object
        $config = $this->getMockBuilder('Illuminate\Config\Repository')
            ->disableOriginalConstructor()
            ->getMock();

        $config->expects($this->once())
            ->method('get')
            ->will($this->returnValue(['jpg' => ['foo']]));

        // Mock the L4 app container and insert a config object into it.
        $app = new Illuminate\Foundation\Application;
        Illuminate\Support\Facades\Facade::setFacadeApplication($app);
        $app['config'] = $config;

        // Mock an uploaded file object
        $uploadedFile = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->disableOriginalConstructor()
            ->getMock();
            
        $uploadedFile->expects($this->once())
            ->method('getMimeType')
            ->will($this->returnValue('foo'));

        // Mock the actual attachment and attach the uploaded file mock to it.
        $attachment = $this->getMock('Codesleeve\Stapler\Attachment', ['validateOptions'], ['testAttachment']);
        $attachment->setUploadedFile($uploadedFile);
        $method = $this->makeMethodPublic('isImage');
        $actual = $method->invokeArgs($attachment, []);

        $this->assertTrue($actual);
    }
}
