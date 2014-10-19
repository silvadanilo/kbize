<?php
namespace Test\Kbize\Console\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;

use Kbize\Console\Command\TaskListCommand;
use Kbize\Collection\Tasks;

class TaskListCommandTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->kernel = $this->getMock('Kbize\KbizeKernel');
        $this->kernelFactory = $this->getMock('Kbize\KbizeKernelFactory');
        $this->kernelFactory->expects($this->any())
            ->method('forProfile')
            ->will($this->returnValue($this->kernel))
        ;
        $this->taskList = new TaskListCommand($this->kernelFactory);
        $this->application = new Application();
        $this->application->add($this->taskList);
    }

    public function testCallsGetAllTasksWithBoardIdAndFIltersReceivedInInputOption()
    {
        $projectId = 1;
        $boardId = 2;
        $filters = ['foo'];

        $this->kernel->expects($this->once())
            ->method('getAllTasks')
            ->with($boardId)
            ->will($this->returnValue($this->simpleTaskCollection($filters)));
        ;

        $command = $this->application->find('task:list');
        $dialog = $command->getHelper('question');
        $dialog->setInputStream($this->getInputStream("http://www.exaple.url\nemail@email.com\npassword"));

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'filters' => $filters,
            '--project' => $projectId,
            '--board' => $boardId,
        ]);
    }

    public function testDelegateRenderToTaskListOutputObject()
    {
        $this->markTestSkipped();
    }

    public function testDelegateRenderToTaskShowOutputObjectIfShowOptionIsSetted()
    {
        $this->markTestSkipped();
    }

    private function simpleTaskCollection($filters = [])
    {
        $yamlParser = new Parser();

        $taskCollection = $this->getMock('Kbize\Collection\TasksInterface');

        $taskCollection->expects($this->once())
            ->method('filter')
            ->with($filters)
            ->will($this->returnValue($taskCollection))
        ;

        $taskCollection->expects($this->once())
            ->method('tasks')
            ->will($this->returnValue($yamlParser->parse(file_get_contents('fixtures/tasks.yml'))))
        ;

        return $taskCollection;
    }

    protected function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fputs($stream, $input);
        rewind($stream);

        return $stream;
    }
}
