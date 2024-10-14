<?php

namespace App\Models\MockModel;

use App\Models\ModelInterface;


class MockModel implements ModelInterface
{
    public function getResource(array $args): array
    {
        return ['MockModel'];
    }

    public function createResource(array $args): array
    {
        return ['MockModel'];
    }
}
