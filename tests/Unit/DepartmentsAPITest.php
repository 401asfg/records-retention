<?php

namespace Tests\Feature;

use App\Models\Department;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DepartmentSeeder;

class DepartmentsAPITest extends TestCase
{
    // TODO: test transactions?
    // TODO: test sql injection attacks?

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
                'id' => 28,
                'name' => 'Engineering Labs'
            ],
            [
                'id' => 29,
                'name' => 'Medical Testing'
            ],
            [
                'id' => 31,
                'name' => 'Admin Offices'
            ],
            [
                'id' => 32,
                'name' => 'Shipping and Receiving'
            ],
            [
                'id' => 34,
                'name' => 'Yellow Painting Room'
            ],
            [
                'id' => 36,
                'name' => 'Automotive Repair'
            ]
        ]);
    }

    public function testIndexPartOfWordQuery()
    {
        $this->assertSearchSuccessful('oom', [
            [
                'id' => 39,
                'name' => 'Xylophone Room'
            ],
            [
                'id' => 43,
                'name' => 'Yellow Painting Room'
            ]
        ]);
    }

    public function testIndexSingleWordQuery()
    {
        $this->assertSearchSuccessful('Shipping', [
            [
                'id' => 50,
                'name' => 'Shipping and Receiving'
            ],
            [
                'id' => 51,
                'name' => 'Shipping or Receiving'
            ]
        ]);
    }

    public function testIndexSpaceContainingQuery()
    {
        $this->assertSearchSuccessful('cal T', [
            [
                'id' => 56,
                'name' => 'Medical Testing'
            ]
        ]);
    }

    public function testIndexExactMatchQuery()
    {
        $this->assertSearchSuccessful('Shipping or Receiving', [
            [
                'id' => 69,
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
        Department::destroy([82, 83, 84, 85, 86, 87, 88, 89, 90]);
        $this->assertSearchSuccessful('Shipping or Receiving', []);
    }

    public function testIndexImmuneToSqlInjectionAttack()
    {
        // FIXME: is this correct?
        $this->assertSearchSuccessful("x' or name like '", []);
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
