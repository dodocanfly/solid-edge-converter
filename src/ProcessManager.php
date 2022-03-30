<?php

namespace Dodocanfly\SolidEdgeConverter;

use Dodocanfly\SolidEdgeConverter\Contracts\ProcessInterface;

class ProcessManager implements ProcessInterface
{
    private array $command = [];
    private array $output = [];
    private int $resultCode = 0;


    public function __construct(array $command = [])
    {
        $this->command = $command;
    }


    public function setCommand(array $command): self
    {
        $this->command = $command;
        return $this;
    }


    public function run(): int
    {
        $commandString = implode(' ', $this->command);
        exec($commandString, $this->output, $this->resultCode);
        return $this->resultCode;
    }


    public function getOutput(): array
    {
        return $this->output;
    }


    public function getExitCode(): int
    {
        return $this->resultCode;
    }


    public function isSuccessful(): bool
    {
        return 0 === $this->getExitCode();
    }
}
