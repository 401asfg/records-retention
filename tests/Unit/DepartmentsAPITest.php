<?php

namespace Tests\Feature;

use App\Models\Department;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DepartmentSeeder;

class DepartmentsAPITest extends TestCase
{
    // TODO: test transactions?
    // TODO: test database failures?
    // TODO: test sql injection attacks?
    // TODO: test network errors?

    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $departmentSeeder = new DepartmentSeeder();
        $departmentSeeder->run();
    }

    public function testIndexNoData()
    {
        $this->assertSearchValidationErrors("", ['query' => 'The query field is required.']);
    }

    public function testIndexWrongParameter()
    {
        $this->assertSearchValidationErrors("?q=a", ['query' => 'The query field is required.']);
    }

    public function testIndexEmptyQuery()
    {
        $this->assertSearchValidationErrors("?query=", ['query' => 'The query field is required.']);
    }

    public function testIndexSingleLetterQuery()
    {
        $this->assertSearchSuccessful('a', [
            [
                'id' => 1,
                'name' => 'Engineering Labs'
            ],
            [
                'id' => 2,
                'name' => 'Medical Testing'
            ],
            [
                'id' => 4,
                'name' => 'Admin Offices'
            ],
            [
                'id' => 5,
                'name' => 'Shipping and Receiving'
            ],
            [
                'id' => 7,
                'name' => 'Yellow Painting Room'
            ],
            [
                'id' => 9,
                'name' => 'Automotive Repair'
            ]
        ]);
    }

    public function testIndexPartOfWordQuery()
    {
        $this->assertSearchSuccessful('oom', [
            [
                'id' => 3,
                'name' => 'Xylophone Room'
            ],
            [
                'id' => 7,
                'name' => 'Yellow Painting Room'
            ]
        ]);
    }

    public function testIndexSingleWordQuery()
    {
        $this->assertSearchSuccessful('Shipping', [
            [
                'id' => 5,
                'name' => 'Shipping and Receiving'
            ],
            [
                'id' => 6,
                'name' => 'Shipping or Receiving'
            ]
        ]);
    }

    public function testIndexSpaceContainingQuery()
    {
        $this->assertSearchSuccessful('cal T', [
            [
                'id' => 2,
                'name' => 'Medical Testing'
            ]
        ]);
    }

    public function testIndexExactMatchQuery()
    {
        $this->assertSearchSuccessful('Shipping or Receiving', [
            [
                'id' => 6,
                'name' => 'Shipping or Receiving'
            ]
        ]);
    }

    public function testIndexNoResultsQuery()
    {
        $this->assertSearchSuccessful('not found', []);
    }

    public function testIndexPreviouslyValidQueryWithDataChanged()
    {
        Department::destroy([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        $this->assertSearchSuccessful('Shipping or Receiving', []);
    }

    public function testIndexImmuneToSqlInjectionAttack()
    {
        // FIXME: is this correct?
        $this->assertSearchSuccessful("x' or name like '", []);
    }

    public function testShowValidId()
    {
        $this->assertIdSuccessful(3, [
            'id' => 3,
            'name' => 'Xylophone Room'
        ]);
    }

    public function testShowZeroId()
    {
        $this->assertIdFailed(0, 'No query results for model [App\Models\Department] 0');
    }

    public function testShowNegativeId()
    {
        $this->assertIdFailed(-1, 'No query results for model [App\Models\Department] -1');
    }

    public function testShowTooLargeId()
    {
        $this->assertIdFailed(1000000, 'No query results for model [App\Models\Department] 1000000');
    }

    private function assertIdSuccessful(int $id, array $expectedResult)
    {
        $response = $this->get('api/departments/' . $id);
        $response->assertStatus(200);
        $response->assertExactJson(['data' => $expectedResult]);
    }

    private function assertIdFailed(int $id, string $expectedErrors)
    {
        $response = $this->get('api/departments/' . $id);
        $response->assertStatus(400);
        $response->assertContent($expectedErrors);
    }

    private function assertSearchSuccessful(string $query, array $expectedResult)
    {
        $response = $this->get('api/departments?query=' . $query);
        $response->assertStatus(200);
        $response->assertExactJson(['data' => $expectedResult]);
    }

    private function assertSearchValidationErrors(string $header, array $errors)
    {
        $response = $this->get('api/departments' . $header);
        $response->assertStatus(302);
        $response->assertSessionHasErrors($errors);
    }
}
