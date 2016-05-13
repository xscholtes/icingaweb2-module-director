<?php

namespace Tests\Icinga\Module\Director\Objects;

use Icinga\Module\Director\Objects\IcingaCommand;
use Icinga\Module\Director\Test\BaseTestCase;

class IcingaCommandTest extends BaseTestCase
{
    protected $testCommandName = '___TEST___command';

    public function testCommandsWithArgumentsCanBeCreated()
    {
        $command = $this->command();
        $command->arguments = array(
            '-H' => '$host$'
        );

        $this->assertEquals(
            '-H',
            $command->arguments()->get('-H')->argument_name
        );

        $this->assertEquals(
            '$host$',
            $command->toPlainObject()->arguments['-H']
        );

        $command->arguments()->get('-H')->required = true;
    }

    public function testCommandsWithArgumentsCanBeModified()
    {
        $command = $this->command();
        $command->arguments = array(
            '-H' => '$host$'
        );

        $command->arguments = array(
            '-x' => (object) array(
                'required' => true
            )
        );

        $this->assertEquals(
            null,
            $command->arguments()->get('-H')
        );

        $this->assertEquals(
            'y',
            $command->arguments()->get('-x')->get('required')
        );

        $this->assertEquals(
            true,
            $command->toPlainObject()->arguments['-x']->required
        );
    }

    public function testAbsolutePathsAreDetected()
    {
        $command = $this->command();
        $command->command = 'C:\\test.exe';

        $this->assertEquals(
            $this->loadRendered('command1'),
            (string) $command
        );

        $command->command = '/tmp/bla';

        $this->assertEquals(
            $this->loadRendered('command2'),
            (string) $command
        );

        $command->command = 'tmp/bla';

        $this->assertEquals(
            $this->loadRendered('command3'),
            (string) $command
        );

        $command->command = '\\\\network\\share\\bla.exe';

        $this->assertEquals(
            $this->loadRendered('command4'),
            (string) $command
        );

        $command->command = 'BlahDir + \\network\\share\\bla.exe';

        $this->assertEquals(
            $this->loadRendered('command5'),
            (string) $command
        );

        $command->command = 'network\\share\\bla.exe';

        $this->assertEquals(
            $this->loadRendered('command6'),
            (string) $command
        );
    }

    protected function command()
    {
        return IcingaCommand::create(
            array(
                'object_name' => $this->testCommandName,
                'object_type' => 'object'
            )
        );
    }

    protected function loadRendered($name)
    {
        return file_get_contents(__DIR__ . '/rendered/' . $name . '.out');
    }

    public function tearDown()
    {
        $db = $this->getDb();
        if (IcingaCommand::exists($this->testCommandName, $db)) {
            IcingaCommand::load($this->testCommandName, $db)->delete();
        }
    }
}
