<?php

namespace App\Contracts;

interface SmsSender
{
    public function send(string $mobile, string $message): void;
}
