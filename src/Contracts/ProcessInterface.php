<?php

namespace Dodocanfly\SolidEdgeConverter\Contracts;

interface ProcessInterface
{
    public function __construct(array $command = []);
    public function setCommand(array $command): self;
    public function run(): int;
    public function getOutput(): string;
    public function getExitCode(): int;
    public function isSuccessful(): bool;
}
