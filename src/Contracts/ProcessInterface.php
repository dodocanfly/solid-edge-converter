<?php

namespace Dodocanfly\SolidEdgeConverter\Contracts;

interface ProcessInterface
{
    public function __construct(array $command = []);
    public function setCommand(array $command): self;
    public function run(): int;
    public function getCommandLine(): string;
    public function getOutput(): array;
    public function getExitCode(): int;
    public function isSuccessful(): bool;
}
