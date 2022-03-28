<?php

namespace Dodocanfly\SolidEdgeConverter\Enums;

enum Resolution: int
{
    case Minimal = 100;
    case Low = 200;
    case Medium = 300;
    case High = 600;
    case Maximum = 1200;
}