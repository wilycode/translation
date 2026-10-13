<?php

declare(strict_types=1);

namespace WilyCode\Translation;

enum DateStyle: string
{
    case Short = 'short';
    case Medium = 'medium';
    case Long = 'long';
}