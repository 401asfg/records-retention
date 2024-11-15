<?php

namespace Tests\Feature;

use App\Mail\PendingRecordRetentionRequest;
use App\Mail\RetentionRequestSuccessfullySubmitted;
use App\Models\Department;
use App\Models\RetentionRequest;
use App\Models\Box;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\DepartmentSeeder;
use Illuminate\Support\Facades\Mail;

class RetentionRequestsAPITest extends TestCase
{
    // TODO: test transactions?
    // TODO: test network errors?
    // TODO: test database failures?
    // TODO: test sql injection attacks?

    // FIXME: stop tests from whipping the database

    use RefreshDatabase;
    // FIXME: find a way to pass valid csrf tokens and remove
    use WithoutMiddleware;

    public function setUp(): void
    {
        parent::setUp();
        $departmentSeeder = new DepartmentSeeder();
        $departmentSeeder->run();
        Mail::fake();
    }

    public function testPostNoData()
    {
        $this->assertPostValidationErrors([], [
            'retention_request.manager_name' => 'The retention request.manager name field is required.',
            'retention_request.requestor_name' => 'The retention request.requestor name field is required.',
            'retention_request.requestor_email' => 'The retention request.requestor email field is required.',
            'retention_request.department_id' => 'The retention request.department id field is required.',
            'boxes' => 'The boxes field is required.',
        ]);
    }

    public function testPostNoRetentionRequest()
    {
        $this->assertPostValidationErrors(
            [
                'boxes' => [
                    [
                        'description' => 'Box 1',
                        'destroy_date' => null
                    ],
                    [
                        'description' => 'Box 2',
                        'destroy_date' => null
                    ]
                ]
            ],
            [
                'retention_request.manager_name' => 'The retention request.manager name field is required.',
                'retention_request.requestor_name' => 'The retention request.requestor name field is required.',
                'retention_request.requestor_email' => 'The retention request.requestor email field is required.',
                'retention_request.department_id' => 'The retention request.department id field is required.',
            ]
        );
    }

    public function testPostNoBoxes()
    {
        $id = Department::firstOrFail()->getAttributes()['id'];
        $this->assertPostValidationErrors(
            [
                'retention_request' => [
                    'manager_name' => 'Dave',
                    'requestor_name' => 'Bob',
                    'requestor_email' => 'bob@gmail.com',
                    'department_id' => $id,
                ]
            ],
            [
                'boxes' => 'The boxes field is required.',
            ]
        );
    }

    public function testPostBoxesFieldEmpty()
    {
        $id = Department::firstOrFail()->getAttributes()['id'];
        $this->assertPostValidationErrors(
            [
                'retention_request' => [
                    'manager_name' => 'Dave',
                    'requestor_name' => 'Bob',
                    'requestor_email' => 'bob@gmail.com',
                    'department_id' => $id,
                ],
                'boxes' => []
            ],
            [
                'boxes' => 'The boxes field is required.',
            ]
        );
    }

    public function testPostOneBox()
    {
        $this->assertPostSuccessful([
            'retention_request' => [
                'manager_name' => 'Dave',
                'requestor_name' => 'Bob',
                'requestor_email' => 'bob@gmail.com'
            ],
            'boxes' => [
                [
                    'description' => 'Box 1 description',
                    'destroy_date' => Carbon::today()
                ]
            ]
        ]);
    }

    public function testPostMultipleBoxes()
    {
        $this->assertPostSuccessful([
            'retention_request' => [
                'manager_name' => 'Dave',
                'requestor_name' => 'Bob',
                'requestor_email' => 'bob@gmail.com'
            ],
            'boxes' => [
                [
                    'description' => 'Box 1 description',
                    'destroy_date' => Carbon::today()
                ],
                [
                    'description' => 'Box 2 description',
                    'destroy_date' => Carbon::yesterday()
                ],
                [
                    'description' => 'Box 3 description',
                    'destroy_date' => Carbon::tomorrow()
                ]
            ]
        ]);
    }

    public function testPostNoManagerName()
    {
        $this->assertPostSuccessful([
            'retention_request' => [
                'manager_name' => 'Dave',
                'requestor_name' => 'Bob',
                'requestor_email' => 'bob@gmail.com'
            ],
            'boxes' => [
                [
                    'description' => 'Box 1 description',
                    'destroy_date' => Carbon::today()
                ],
                [
                    'description' => 'Box 2 description',
                    'destroy_date' => Carbon::yesterday()
                ],
                [
                    'description' => 'Box 3 description',
                    'destroy_date' => Carbon::tomorrow()
                ]
            ]
        ]);
    }

    public function testPostNoRequestorName()
    {
        $id = Department::firstOrFail()->getAttributes()['id'];
        $this->assertPostValidationErrors(
            [
                'retention_request' => [
                    'manager_name' => 'Dave',
                    'requestor_email' => 'bob@gmail.com',
                    'department_id' => $id,
                ],
                'boxes' => [
                    [
                        'description' => 'Box 1 description',
                        'destroy_date' => Carbon::today()
                    ],
                    [
                        'description' => 'Box 2 description',
                        'destroy_date' => Carbon::yesterday()
                    ],
                    [
                        'description' => 'Box 3 description',
                        'destroy_date' => Carbon::tomorrow()
                    ]
                ]
            ],
            [
                'retention_request.requestor_name' => 'The retention request.requestor name field is required.',
            ]
        );
    }

    public function testPostNoRequestorEmail()
    {
        $id = Department::firstOrFail()->getAttributes()['id'];
        $this->assertPostValidationErrors(
            [
                'retention_request' => [
                    'requestor_name' => 'Bob',
                    'manager_name' => 'Dave',
                    'department_id' => $id,
                ],
                'boxes' => [
                    [
                        'description' => 'Box 1 description',
                        'destroy_date' => Carbon::today()
                    ],
                    [
                        'description' => 'Box 2 description',
                        'destroy_date' => Carbon::yesterday()
                    ],
                    [
                        'description' => 'Box 3 description',
                        'destroy_date' => Carbon::tomorrow()
                    ]
                ]
            ],
            [
                'retention_request.requestor_email' => 'The retention request.requestor email field is required.',
            ]
        );
    }

    public function testPostInvalidRequestorEmail()
    {
        $id = Department::firstOrFail()->getAttributes()['id'];
        $this->assertPostValidationErrors(
            [
                'retention_request' => [
                    'requestor_name' => 'Bob',
                    'requestor_email' => 'Bob.com',
                    'manager_name' => 'Dave',
                    'department_id' => $id,
                ],
                'boxes' => [
                    [
                        'description' => 'Box 1 description',
                        'destroy_date' => Carbon::today()
                    ],
                    [
                        'description' => 'Box 2 description',
                        'destroy_date' => Carbon::yesterday()
                    ],
                    [
                        'description' => 'Box 3 description',
                        'destroy_date' => Carbon::tomorrow()
                    ]
                ]
            ],
            [
                'retention_request.requestor_email' => 'The retention request.requestor email field must be a valid email address.',
            ]
        );
    }

    public function testPostNoDepartmentId()
    {
        $id = Department::firstOrFail()->getAttributes()['id'];
        $this->assertPostValidationErrors(
            [
                'retention_request' => [
                    'requestor_name' => 'Bob',
                    'requestor_email' => 'bob@gmail.com',
                    'manager_name' => 'Dave',
                ],
                'boxes' => [
                    [
                        'description' => 'Box 1 description',
                        'destroy_date' => Carbon::today()
                    ],
                    [
                        'description' => 'Box 2 description',
                        'destroy_date' => Carbon::yesterday()
                    ],
                    [
                        'description' => 'Box 3 description',
                        'destroy_date' => Carbon::tomorrow()
                    ]
                ]
            ],
            [
                'retention_request.department_id' => 'The retention request.department id field is required.',
            ]
        );
    }

    public function testPostInvalidDepartmentId()
    {
        $this->assertPostValidationErrors(
            [
                'retention_request' => [
                    'manager_name' => 'Dave',
                    'requestor_name' => 'Bob',
                    'requestor_email' => 'bob@gmail.com',
                    'department_id' => 5000
                ],
                'boxes' => [
                    [
                        'description' => 'Box 1 description',
                        'destroy_date' => Carbon::today()
                    ]
                ]
            ],
            [
                'retention_request.department_id' => 'The selected retention request.department id is invalid.',
            ]
        );
    }

    public function testPostBoxNoDescription()
    {
        $id = Department::firstOrFail()->getAttributes()['id'];
        $this->assertPostValidationErrors(
            [
                'retention_request' => [
                    'manager_name' => 'Dave',
                    'requestor_name' => 'Bob',
                    'requestor_email' => 'bob@gmail.com',
                    'department_id' => $id
                ],
                'boxes' => [
                    [
                        'destroy_date' => Carbon::today()
                    ]
                ]
            ],
            [
                'boxes.0.description' => 'The boxes.0.description field is required.',
            ]
        );
    }

    public function testPostBoxInvalidDestroyDate()
    {
        $id = Department::firstOrFail()->getAttributes()['id'];
        $this->assertPostValidationErrors(
            [
                'retention_request' => [
                    'manager_name' => 'Dave',
                    'requestor_name' => 'Bob',
                    'requestor_email' => 'bob@gmail.com',
                    'department_id' => $id
                ],
                'boxes' => [
                    [
                        'description' => 'Box 1 description',
                        'destroy_date' => "bad date"
                    ]
                ]
            ],
            [
                'boxes.0.destroy_date' => 'The boxes.0.destroy_date field must be a valid date.'
            ]
        );
    }

    public function testPostBoxStringDestroyDate()
    {
        $this->assertPostSuccessful([
            'retention_request' => [
                'manager_name' => 'Dave',
                'requestor_name' => 'Bob',
                'requestor_email' => 'bob@gmail.com',
            ],
            'boxes' => [
                [
                    'description' => 'Box 1 description',
                    'destroy_date' => '2024-10-01 00:00:00.0'
                ]
            ]
        ]);
    }

    public function testPostBoxNoDestroyDate()
    {
        $this->assertPostSuccessful([
            'retention_request' => [
                'manager_name' => 'Dave',
                'requestor_name' => 'Bob',
                'requestor_email' => 'bob@gmail.com',
            ],
            'boxes' => [
                [
                    'description' => 'Box 1 description',
                ]
            ]
        ]);
    }

    public function testPostBoxNullDestroyDate()
    {
        $this->assertPostSuccessful([
            'retention_request' => [
                'manager_name' => 'Dave',
                'requestor_name' => 'Bob',
                'requestor_email' => 'bob@gmail.com',
            ],
            'boxes' => [
                [
                    'description' => 'Box 1 description',
                    'destroy_date' => null
                ]
            ]
        ]);
    }

    public function testPostMailSentToRequestorAndValidUsers()
    {
        $roleSeeder = new RoleSeeder();
        $roleSeeder->run();

        $userSeeder = new UserSeeder();
        $userSeeder->run();

        $data = [
            'retention_request' => [
                'manager_name' => 'Dave',
                'requestor_name' => 'Bob',
                'requestor_email' => 'bob@gmail.com',
                'department_id' => Department::max('id')
            ],
            'boxes' => [
                [
                    'description' => 'Box 1 description',
                    'destroy_date' => Carbon::today()
                ],
                [
                    'description' => 'Box 2 description',
                    'destroy_date' => Carbon::yesterday()
                ],
                [
                    'description' => 'Box 3 description',
                    'destroy_date' => Carbon::tomorrow()
                ]
            ]
        ];

        $this->post('api/retention-requests', $data);

        Mail::assertSent(
            RetentionRequestSuccessfullySubmitted::class,
            function ($mail) use ($data) {
                return $mail->hasTo($data['retention_request']['requestor_email']);
            }
        );

        Mail::assertNotSent(
            PendingRecordRetentionRequest::class,
            function ($mail) {
                return $mail->hasTo(env('USER_EMAIL_VIEWER_RECEIVING_EMAILS'));
            }
        );

        Mail::assertNotSent(
            PendingRecordRetentionRequest::class,
            function ($mail) {
                return $mail->hasTo(env('USER_EMAIL_VIEWER_NOT_RECEIVING_EMAILS'));
            }
        );

        Mail::assertSent(
            PendingRecordRetentionRequest::class,
            function ($mail) {
                return $mail->hasTo(env('USER_EMAIL_AUTHORIZER_RECEIVING_EMAILS'));
            }
        );

        Mail::assertNotSent(
            PendingRecordRetentionRequest::class,
            function ($mail) {
                return $mail->hasTo(env('USER_EMAIL_AUTHORIZER_NOT_RECEIVING_EMAILS'));
            }
        );

        Mail::assertSent(
            PendingRecordRetentionRequest::class,
            function ($mail) {
                return $mail->hasTo(env('USER_EMAIL_ADMIN_RECEIVING_EMAILS'));
            }
        );

        Mail::assertNotSent(
            PendingRecordRetentionRequest::class,
            function ($mail) {
                return $mail->hasTo(env('USER_EMAIL_ADMIN_NOT_RECEIVING_EMAILS'));
            }
        );
    }

    private function assertPostValidationErrors(array $data, array $errors)
    {
        $response = $this->post('api/retention-requests', $data);
        $response->assertStatus(302);
        $response->assertSessionHasErrors($errors);
    }

    private function assertPostSuccessful(array $data, bool $validDepartmentId = true)
    {
        $data['retention_request']['department_id'] = Department::max('id');

        if (!$validDepartmentId)
            $data['retention_request']['department_id']++;

        $response = $this->post('api/retention-requests', $data);
        $response->assertStatus(200);
        $response->assertSessionDoesntHaveErrors();

        $expectedRetentionRequest = $data['retention_request'];
        $expectedRetentionRequestId = RetentionRequest::max('id');
        $expectedRetentionRequest['id'] = $expectedRetentionRequestId;
        $expectedRetentionRequest['authorizing_user_id'] = null;
        $this->assertDatabaseHas('retention_requests', $expectedRetentionRequest);

        $expectedBoxes = $data['boxes'];
        $expectedBoxMaxId = Box::max('id');
        $expectedBoxesCount = count($expectedBoxes);

        foreach ($expectedBoxes as $index => $expectedBox) {
            $expectedBox['id'] = $expectedBoxMaxId - $expectedBoxesCount + $index + 1;
            $expectedBox['tracking_number'] = null;
            $expectedBox['retention_request_id'] = $expectedRetentionRequestId;
            $this->assertDatabaseHas('boxes', $expectedBox);
        }
    }
}
