<?php


class InterpolatorTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // your code here
    }

    public function tearDown()
    {
        // your code here
    }

    /**
     * makeMethodPublic method
     * 
     * @param  string $name - The name of the method we're making public
     * @return callable
     */
    protected function makeMethodPublic($name) 
    {
        $class = new ReflectionClass('Codesleeve\Stapler\Interpolator');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        
        return $method;
    }

   /**
     * testInterpolateString method
     * 
     * @return void
     */
    public function testInterpolateString()
    {
        $interpolatorMethods = ['idPartition', 'style', 'laravelRoot', 'id', 'filename', 'extension', 'getClass', 'basename', 'attachment'];
        $interpolator = $this->getMockForAbstractClass('Codesleeve\Stapler\Interpolator', [], '', TRUE, TRUE, TRUE, $interpolatorMethods);

        $interpolator->expects($this->once())->method('idPartition')->will($this->returnValue('000/000/001'));
        $interpolator->expects($this->once())->method('style')->will($this->returnValue('original'));
        $interpolator->expects($this->once())->method('laravelRoot')->will($this->returnValue('testRoot'));
        $interpolator->expects($this->once())->method('id')->will($this->returnValue(1));
        $interpolator->expects($this->once())->method('filename')->will($this->returnValue('foo.jpg'));
        $interpolator->expects($this->once())->method('extension')->will($this->returnValue('.jpg'));
        $interpolator->expects($this->once())->method('getClass')->will($this->returnValue('testModel'));
        $interpolator->expects($this->once())->method('basename')->will($this->returnValue('foo'));
        $interpolator->expects($this->once())->method('attachment')->will($this->returnValue('testAttachment'));

        $method = $this->makeMethodPublic('interpolateString');
        $string = ':id_partition/:style/:laravel_root/:id/:filename/:extension/:class/:basename/:attachment';
        $expected = '000/000/001/original/testRoot/1/foo.jpg/.jpg/testModel/foo/testAttachment';
        $actual = $method->invokeArgs($interpolator, [$string]);

        $this->assertEquals($expected, $actual);
    }

    /**
     * testAttachment method
     * 
     * @return void
     */
    /*public function testAttachment()
    {
        $interpolator = $this->getMockForAbstractClass('Codesleeve\Stapler\Interpolator');
        $interpolator->setModelName('foo');

        $method = $this->makeMethodPublic('attachment');
        $expected = 'foos';
        $actual = $method->invokeArgs($interpolator, []);

        $this->assertEquals($expected, $actual);
    }*/

    /**
     * testBasename method
     * 
     * @return void
     */
    /*public function testBasename()
    {
        $fileObject = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->disableOriginalConstructor()
            ->getMock();
        $fileObject->expects($this->once())
            ->method('getClientOriginalName')
            ->will($this->returnValue('foo'));

        $interpolator = $this->getMockForAbstractClass('Codesleeve\Stapler\Interpolator');
        $interpolator->setUploadedFile($fileObject);

        $method = $this->makeMethodPublic('basename');
        $expected = 'foo';
        $actual = $method->invokeArgs($interpolator, []);

        $this->assertEquals($expected, $actual);
    }*/

    /**
     * testGetClass method
     * 
     * @return void
     */
    /*public function testGetClass()
    {
        $interpolator = $this->getMockForAbstractClass('Codesleeve\Stapler\Interpolator', [], '', TRUE, TRUE, TRUE, ['handleBackslashes']);
        $interpolator->setModelName('foo');
        $interpolator->expects($this->once())
            ->method('handleBackslashes')
            ->with($this->equalTo('foo'))
            ->will($this->returnValue('bar'));

        $method = $this->makeMethodPublic('getClass');
        $expected = 'bar';
        $actual = $method->invokeArgs($interpolator, []);

        $this->assertEquals($expected, $actual);
    }*/

    /**
     * testExtension method
     * 
     * @return void
     */
    /*public function testExtension()
    {
        $fileObject = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->disableOriginalConstructor()
            ->getMock();
        $fileObject->expects($this->once())
            ->method('guessExtension')
            ->will($this->returnValue('foo'));

        $interpolator = $this->getMockForAbstractClass('Codesleeve\Stapler\Interpolator');
        $interpolator->setUploadedFile($fileObject);

        $method = $this->makeMethodPublic('extension');
        $expected = 'foo';
        $actual = $method->invokeArgs($interpolator, []);

        $this->assertEquals($expected, $actual);
    }*/

    /**
     * testFilename method
     * 
     * @return void
     */
    /*public function testFilename()
    {
        $fileObject = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->disableOriginalConstructor()
            ->getMock();
        $fileObject->expects($this->once())
            ->method('getFilename')
            ->will($this->returnValue('foo'));
       
        $interpolator = $this->getMockForAbstractClass('Codesleeve\Stapler\Interpolator');
        $interpolator->setUploadedFile($fileObject);

        $method = $this->makeMethodPublic('filename');
        $expected = 'foo';
        $actual = $method->invokeArgs($interpolator, []);

        $this->assertEquals($expected, $actual);
    }*/

    /**
     * testId method
     * 
     * @return void
     */
    /*public function testId()
    {
    	$interpolator = $this->getMockForAbstractClass('Codesleeve\Stapler\Interpolator');
    	$interpolator->setRecordId(1);

    	$method = $this->makeMethodPublic('id');
    	$expected = 1;
        $actual = $method->invokeArgs($interpolator, []);

        $this->assertEquals($expected, $actual);
    }*/

    /**
     * testLaravelRoot
     * 
     * @return void
     */
    /*public function testLaravelRoot()
    {
    	$interpolator = $this->getMockForAbstractClass('Codesleeve\Stapler\Interpolator');
    	$interpolator->expects($this->once())
    		->method('basePath')
    		->will($this->returnValue('foo/bar'));

    	$method = $this->makeMethodPublic('laravelRoot');
    	$expected = 'foo/bar';
        $actual = $method->invokeArgs($interpolator, []);

        $this->assertEquals($expected, $actual);
    }*/

    /**
     * testStyle method
     *
     * @dataProvider styleDataProvider
     * @param  string $expected 
     * @return void
     */
    /*public function testStyle($style, $expected, $interpolator)
    {
    	$method = $this->makeMethodPublic('style');
        $actual = $method->invokeArgs($interpolator, [$style]);

        $this->assertEquals($expected, $actual);
    }*/

    /**
     * styleDataProvider method
     * 
     * @return array
     */
    /*public function styleDataProvider()
    {
    	$interpolator1 = $this->getMockForAbstractClass('Codesleeve\Stapler\Interpolator');
    	$interpolator1->default_style = 'foo';

    	$interpolator2 = $this->getMockForAbstractClass('Codesleeve\Stapler\Interpolator');

    	return [
    		'empty style' => ['', 'foo', $interpolator1],
    		'non empty style' => ['bar', 'bar', $interpolator2],
    	];
    }*/

    /**
     * getGetOffset method
     *
     * @dataProvider getOffsetDataProvider
     * @param $id
     * @param $idPartition
     * @param $input
     * @param mixed $expected 
     * @return void
     */
    /*public function testGetOffset($id, $idPartition, $input, $expected)
    {
    	$interpolator = $this->getMockForAbstractClass('Codesleeve\Stapler\Interpolator', [], '', TRUE, TRUE, TRUE, ['idPartition']);
    	$interpolator->setRecordId($id);
    	$interpolator->expects($this->once())
    		->method('idPartition')
    		->will($this->returnValue($idPartition));

    	$method = $this->makeMethodPublic('getOffset');
        $actual = $method->invokeArgs($interpolator, [$input]);

        $this->assertEquals($expected, $actual);
    }*/

    /**
     * getOffsetDataProvider method data provider
     *
     * @return array
     */
    /*public function getOffsetDataProvider()
    {
        return [
            [1, '000/000/001', '/some/file/path/000/000/001/some_style/some_file.jpg', 27],
            [1, '000/000/001', '/some/file/path/1/some_style/some_file.jpg', 17],
            [200, '000/000/200', '/some/file/path/000/000/200/some_style/some_file.jpg', 27],
            [200, '000/000/200', '/some/file/path/200/some_style/some_file.jpg', 19],
            ['abcdef123', 'abc/def/123', '/some/file/path/abc/def/123/some_style/some_file.jpg', 27],
            ['abcdef123', 'abc/def/123', '/some/file/path/abcdef123/some_style/some_file.jpg', 25]
        ];
    }*/

    /**
     * testIdPartition method
     *
     * @dataProvider idPartitionDataProvider
     * @param $recordId
     * @return void
     */
    public function testIdPartition($recordId, $expected)
    {
        $interpolator = $this->getMockForAbstractClass('Codesleeve\Stapler\Interpolator', [], '', TRUE, TRUE, TRUE, ['id']);
        $interpolator->expects($this->once())
            ->method('id')
            ->will($this->returnValue($recordId));

        $method = $this->makeMethodPublic('idPartition');
        $actual = $method->invokeArgs($interpolator, []);

        $this->assertEquals($expected, $actual);
    }

    /**
     * idPartitionDataProvider method
     *
     * @return array
     */
    public function idPartitionDataProvider()
    {
        return [
            ['9', '000/000/009'],
            ['99', '000/000/099'],
            ['999', '000/000/999'],
            ['9999', '000/009/999'],
            ['99999', '000/099/999'],
            ['999999', '000/999/999'],
            ['abcd12345', 'abc/d12/345'],
            ['abcd12345efghighj', 'abc/d12/345']
        ];
    }

    /**
     * handleBackslashes method
     *
     * @dataProvider handleBackslashesDataProvider
     * @param  $string
     * @param  $expected
     * @return void
     */
    public function testHandleBackslashes($string, $expected)
    {
        $interpolator = $this->getMockForAbstractClass('Codesleeve\Stapler\Interpolator');
        $method = $this->makeMethodPublic('handleBackslashes');
        $actual = $method->invokeArgs($interpolator, [$string]);

        $this->assertEquals($expected, $actual);
    }

    /**
     * handleBackslashesDataProvider method
     * 
     * @return array
     */
    public function handleBackslashesDataProvider()
    {
    	return [
            ['foo\\bar', 'foo/bar'],
            ['\\foo\\bar', 'foo/bar']
        ];
    }
}
