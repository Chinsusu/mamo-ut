<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function test_composer_manifest_names_the_project_package(): void
    {
        $manifest = json_decode(
            (string) file_get_contents(dirname(__DIR__, 2).'/composer.json'),
            associative: true,
            flags: JSON_THROW_ON_ERROR,
        );

        $this->assertIsArray($manifest);
        $this->assertSame('chinsusu/mamo-ut', $manifest['name'] ?? null);
    }
}
