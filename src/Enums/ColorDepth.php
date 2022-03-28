<?php

namespace Dodocanfly\SolidEdgeConverter\Enums;

enum ColorDepth: int
{
    case TrueColor = 24;
    case EightBitColor = 8;
    case Monochrome = 1;
}
