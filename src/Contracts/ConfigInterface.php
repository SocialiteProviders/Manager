<?php
declare(strict_types=1);

namespace SocialiteProviders\Manager\Contracts;

interface ConfigInterface
{
    public function get(): array;
}
