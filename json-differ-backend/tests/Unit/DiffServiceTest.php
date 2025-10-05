<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\DiffService;

class DiffServiceTest extends TestCase
{
    protected $diffService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->diffService = new DiffService();
    }

    /**
     * @test
     * Verifies that identical JSON objects produce only unchanged diff lines
     */
    public function it_detects_no_differences_for_identical_objects()
    {
        $obj1 = ['id' => 1, 'name' => 'Product A'];
        $obj2 = ['id' => 1, 'name' => 'Product A'];

        $result = $this->diffService->compareJsonAsLines($obj1, $obj2);

        foreach ($result as $line) {
            $this->assertEquals('unchanged', $line['status']);
        }
    }

    /**
     * @test
     * Ensures that changed property values are detected and marked as modified
     */
    public function it_detects_modified_lines()
    {
        $obj1 = ['id' => 1, 'name' => 'Product A'];
        $obj2 = ['id' => 1, 'name' => 'Product B'];

        $result = $this->diffService->compareJsonAsLines($obj1, $obj2);

        $modifiedLine = collect($result)->firstWhere('status', 'modified');

        $this->assertNotNull($modifiedLine);
        $this->assertStringContainsString('Product A', $modifiedLine['left']);
        $this->assertStringContainsString('Product B', $modifiedLine['right']);
    }

    /**
     * @test
     * Verifies that new properties in the second object are marked as added
     * with null leftNumber and populated rightNumber
     */
    public function it_detects_added_lines()
    {
        $obj1 = ['id' => 1];
        $obj2 = ['id' => 1, 'name' => 'Product A'];

        $result = $this->diffService->compareJsonAsLines($obj1, $obj2);

        $addedLine = collect($result)->firstWhere('status', 'added');

        $this->assertNotNull($addedLine);
        $this->assertNull($addedLine['leftNumber']);
        $this->assertNotNull($addedLine['rightNumber']);
        $this->assertStringContainsString('name', $addedLine['right']);
    }

    /**
     * @test
     * Verifies that properties removed from the second object are marked as removed
     * with populated leftNumber and null rightNumber
     */
    public function it_detects_removed_lines()
    {
        $obj1 = ['id' => 1, 'name' => 'Product A'];
        $obj2 = ['id' => 1];

        $result = $this->diffService->compareJsonAsLines($obj1, $obj2);

        $removedLine = collect($result)->firstWhere('status', 'removed');

        $this->assertNotNull($removedLine);
        $this->assertNotNull($removedLine['leftNumber']);
        $this->assertNull($removedLine['rightNumber']);
        $this->assertStringContainsString('name', $removedLine['left']);
    }

    /**
     * @test
     * Ensures that changes within nested objects are properly detected
     * when comparing multi-level JSON structures
     */
    public function it_handles_nested_objects()
    {
        $obj1 = [
            'id' => 1,
            'metadata' => ['color' => 'red', 'size' => 'M']
        ];
        $obj2 = [
            'id' => 1,
            'metadata' => ['color' => 'blue', 'size' => 'M']
        ];

        $result = $this->diffService->compareJsonAsLines($obj1, $obj2);

        $modifiedLine = collect($result)->firstWhere(function ($line) {
            return $line['status'] === 'modified' &&
                   strpos($line['left'], 'color') !== false;
        });

        $this->assertNotNull($modifiedLine);
        $this->assertStringContainsString('red', $modifiedLine['left']);
        $this->assertStringContainsString('blue', $modifiedLine['right']);
    }

    /**
     * @test
     * Verifies that additions to array elements are properly detected
     * when comparing JSON arrays
     */
    public function it_handles_arrays()
    {
        $obj1 = ['items' => [1, 2, 3]];
        $obj2 = ['items' => [1, 2, 3, 4]];

        $result = $this->diffService->compareJsonAsLines($obj1, $obj2);

        $addedLine = collect($result)->firstWhere('status', 'added');
        $this->assertNotNull($addedLine);
    }

    /**
     * @test
     * Ensures that empty objects are handled gracefully without errors
     * and produce valid diff structure with unchanged braces
     */
    public function it_returns_empty_array_for_empty_inputs()
    {
        $obj1 = [];
        $obj2 = [];

        $result = $this->diffService->compareJsonAsLines($obj1, $obj2);

        $this->assertNotEmpty($result);
        $this->assertEquals('unchanged', $result[0]['status']);
    }

    /**
     * @test
     * Verifies that line numbers increment correctly and sequentially
     * for both left and right sides of the diff output
     */
    public function it_assigns_correct_line_numbers()
    {
        $obj1 = ['a' => 1, 'b' => 2];
        $obj2 = ['a' => 1, 'ab' => 1.5, 'b' => 2, 'c' => 3];

        $result = $this->diffService->compareJsonAsLines($obj1, $obj2);

        $leftNumbers = array_filter(array_column($result, 'leftNumber'));
        $rightNumbers = array_filter(array_column($result, 'rightNumber'));

        $this->assertEquals(range(1, count($leftNumbers)), array_values($leftNumbers));
        $this->assertEquals(range(1, count($rightNumbers)), array_values($rightNumbers));
    }
}
