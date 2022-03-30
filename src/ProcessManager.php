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
        exec($this->getCommandLine(), $this->output, $this->resultCode);
        return $this->resultCode;
    }


    public function getCommandLine(): string
    {
        return implode(' ', $this->command);
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
