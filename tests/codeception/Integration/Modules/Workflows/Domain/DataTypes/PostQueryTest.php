<?php

namespace PublishPress\Future\Tests\Integration\Modules\Workflows\Domain\DataTypes;

use PHPUnit\Framework\TestCase;
use PublishPress\Future\Modules\Workflows\Domain\DataType\PostQuery;
use PublishPress\Future\Modules\Workflows\Interfaces\DataTypeInterface;

class PostQueryTest extends TestCase
{
    private PostQuery $postQuery;

    protected function setUp(): void
    {
        parent::setUp();
        $this->postQuery = new PostQuery();
    }

    public function testImplementsDataTypeInterface(): void
    {
        $this->assertInstanceOf(DataTypeInterface::class, $this->postQuery);
    }

    public function testGetName(): void
    {
        $this->assertIsString($this->postQuery->getName());
    }

    public function testGetLabel(): void
    {
        $this->assertIsString($this->postQuery->getLabel());
    }

    public function testGetDescription(): void
    {
        $this->assertIsString($this->postQuery->getDescription());
    }
}
