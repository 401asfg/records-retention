<?php

namespace Tests\Feature;

use App\Mail\PendingRecordRetentionRequest;
use App\Mail\RetentionRequestSuccessfullySubmitted;
use App\Models\Department;
use App\Models\RetentionRequest;
use App\Models\Box;
use Carbon\Carbon;
use Database\Seeders\BoxSeeder;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\RetentionRequestSeeder;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Valuestore\Valuestore;

class RetentionRequestsAPITest extends TestCase
{
    // TODO: test transactions?
    // TODO: test emails queued?
    // TODO: test network errors?
    // TODO: test database failures?
    // TODO: test sql injection attacks?

    // FIXME: stop tests from whipping the database
    // FIXME: use DatabaseMigrations?
    use RefreshDatabase;
    // FIXME: find a way to pass valid csrf tokens and remove
    use WithoutMiddleware;

    // FIXME: reset tracking number in tear down
    private $settings = null;
    private $originalNextTrackingNumber = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(DepartmentSeeder::class);
        $this->seed(RoleSeeder::class);
        $this->seed(UserSeeder::class);
        $this->seed(RetentionRequestSeeder::class);
        $this->seed(BoxSeeder::class);

        Mail::fake();

        $this->settings = Valuestore::make(config_path('settings.json'));
        $this->originalNextTrackingNumber = $this->settings->get('next_tracking_number');
        $this->settings->put('next_tracking_number', 1);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->settings->put('next_tracking_number', $this->originalNextTrackingNumber);
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

    public function testUpdateNoData()
    {
        $this->assertUpdateFailed(
            '1',
            [],
            302,
            [
                "authorizing_user_id" => "The authorizing user id field is required."
            ]
        );
    }

    public function testUpdateNoId()
    {
        $this->assertUpdateFailed(
            '',
            [
                "authorizing_user_id" => 3,
                "boxes" => [
                    [
                        "id" => 1,
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ]
                ]
            ],
            405,
            []
        );
    }

    public function testUpdateNonNumericId()
    {
        $this->assertUpdateFailed(
            'x',
            [
                "authorizing_user_id" => 3,
                "boxes" => [
                    [
                        "id" => 1,
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ]
                ]
            ],
            302,
            []
        );
    }

    public function testUpdateNonExistentId()
    {
        $this->assertUpdateFailed(
            '10000',
            [
                "authorizing_user_id" => 3,
                "boxes" => [
                    [
                        "id" => 1,
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ]
                ]
            ],
            302,
            ['id' => 'The selected id is invalid.']
        );
    }

    public function testUpdateNoAuthorizingUserId()
    {
        $this->assertUpdateFailed(
            '1',
            [
                "boxes" => [
                    [
                        "id" => 1,
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ]
                ]
            ],
            302,
            ["authorizing_user_id" => "The authorizing user id field is required."]
        );
    }

    public function testUpdateNonNumericAuthorizingUserId()
    {
        $this->assertUpdateFailed(
            '1',
            [
                "authorizing_user_id" => 'x',
                "boxes" => [
                    [
                        "id" => 1,
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ]
                ]
            ],
            302,
            ["authorizing_user_id" => "The authorizing user id field must be a number."]
        );
    }

    public function testUpdateSomeBoxesDontBelongToRetentionRequest()
    {
        $this->assertUpdateFailed(
            '1',
            [
                "authorizing_user_id" => 3,
                "boxes" => [
                    [
                        "id" => 3,
                        "description" => "Box 3 description",
                        "destroy_date" => Carbon::today()
                    ],
                    [
                        "id" => 1,
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ],
                    [
                        "id" => 2,
                        "description" => "Box 2 description",
                        "destroy_date" => Carbon::today()
                    ]
                ]
            ],
            302,
            ["boxes.*.id" => "Attempted to update a box that doesn't correspond to any box assigned to the retention request that has the give id."]
        );
    }

    public function testUpdateNoBoxesBelongToRetentionRequest()
    {
        $this->assertUpdateFailed(
            '1',
            [
                "authorizing_user_id" => 3,
                "boxes" => [
                    [
                        "id" => 3,
                        "description" => "Box 3 description",
                        "destroy_date" => Carbon::today()
                    ],
                    [
                        "id" => 5,
                        "description" => "Box 5 description",
                        "destroy_date" => Carbon::today()
                    ],
                    [
                        "id" => 6,
                        "description" => "Box 6 description",
                        "destroy_date" => Carbon::today()
                    ]
                ]
            ],
            302,
            ["boxes.*.id" => "Attempted to update a box that doesn't correspond to any box assigned to the retention request that has the give id."]
        );
    }

    public function testUpdateNonExistentAuthorizingUserId()
    {
        $this->assertUpdateFailed(
            '1',
            [
                "authorizing_user_id" => 1000000,
                "boxes" => [
                    [
                        "id" => 1,
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ]
                ]
            ],
            302,
            ["authorizing_user_id" => "The selected authorizing user id is invalid."]
        );
    }

    public function testUpdateUserIsViewer()
    {
        $this->assertUpdateFailed(
            '1',
            [
                "authorizing_user_id" => 1,
                "boxes" => [
                    [
                        "id" => 1,
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ]
                ]
            ],
            302,
            ["authorizing_user_id" => "The authorizing_user_id field is not a user that can authorize requests."]
        );
    }

    public function testUpdateUserIsAuthorizer()
    {
        $this->assertUpdateSuccessful(
            '1',
            [
                "authorizing_user_id" => 3,
                "boxes" => [
                    [
                        "id" => 1,
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ]
                ]
            ],
            3,
            4,
            [
                1 => [
                    "description" => "Box 1 description",
                    "destroy_date" => Carbon::today(),
                    "tracking_number" => 1
                ],
                2 => [
                    'description' => "Test Box 2",
                    'destroy_date' => "2022-02-01",
                    "tracking_number" => 2
                ],
                4 => [
                    'description' => "Test Box 4",
                    'destroy_date' => "2022-04-01",
                    "tracking_number" => 3
                ]
            ]
        );
    }

    public function testUpdateUserIsAdmin()
    {
        $this->assertUpdateSuccessful(
            '1',
            [
                "authorizing_user_id" => 6,
                "boxes" => [
                    [
                        "id" => 1,
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ]
                ]
            ],
            6,
            4,
            [
                1 => [
                    "description" => "Box 1 description",
                    "destroy_date" => Carbon::today(),
                    "tracking_number" => 1
                ],
                2 => [
                    'description' => "Test Box 2",
                    'destroy_date' => "2022-02-01",
                    "tracking_number" => 2
                ],
                4 => [
                    'description' => "Test Box 4",
                    'destroy_date' => "2022-04-01",
                    "tracking_number" => 3
                ]
            ]
        );
    }

    public function testUpdateNoBoxes()
    {
        $this->assertUpdateFailed(
            '1',
            [
                "authorizing_user_id" => 4
            ],
            302,
            ["boxes" => "The boxes field must be an array."]
        );
    }

    public function testUpdateEmptyBoxes()
    {
        $this->assertUpdateSuccessful(
            '1',
            [
                "authorizing_user_id" => 4,
                "boxes" => []
            ],
            4,
            4,
            [
                1 => [
                    "description" => "Test Box 1",
                    "destroy_date" => "2022-01-01",
                    "tracking_number" => 1
                ],
                2 => [
                    'description' => "Test Box 2",
                    'destroy_date' => "2022-02-01",
                    "tracking_number" => 2
                ],
                3 => [
                    'description' => "Test Box 3",
                    'destroy_date' => "2022-03-01",
                    'retention_request_id' => null
                ],
                4 => [
                    'description' => "Test Box 4",
                    'destroy_date' => "2022-04-01",
                    "tracking_number" => 3
                ],
                5 => [

                    'description' => "Test Box 5",
                    'destroy_date' => "2022-05-01",
                    "tracking_number" => null
                ],
                6 => [
                    'description' => "Test Box 6",
                    'destroy_date' => "2022-06-01",
                    "tracking_number" => null
                ]
            ]
        );
    }

    public function testUpdateNoBoxId()
    {
        $this->assertUpdateFailed(
            '1',
            [
                "authorizing_user_id" => 6,
                "boxes" => [
                    [
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ]
                ]
            ],
            302,
            ["boxes.0.id" => "The boxes.0.id field is required."]
        );
    }

    public function testUpdateNonNumericBoxId()
    {
        $this->assertUpdateFailed(
            '1',
            [
                "authorizing_user_id" => 6,
                "boxes" => [
                    [
                        "id" => "x",
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ]
                ]
            ],
            302,
            ["boxes.0.id" => "The boxes.0.id field must be a number."]
        );
    }

    public function testUpdateNonExistentBoxId()
    {
        $this->assertUpdateFailed(
            '1',
            [
                "authorizing_user_id" => 6,
                "boxes" => [
                    [
                        "id" => 10000000000,
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ]
                ]
            ],
            302,
            ["boxes.0.id" => "The selected boxes.0.id is invalid."]
        );
    }

    public function testUpdateNoDescription()
    {
        $this->assertUpdateSuccessful(
            '1',
            [
                "authorizing_user_id" => 6,
                "boxes" => [
                    [
                        "id" => 1,
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ],
                    [
                        "id" => 2,
                        'destroy_date' => "2043-02-01"
                    ]
                ]
            ],
            6,
            4,
            [
                1 => [
                    "description" => "Box 1 description",
                    "destroy_date" => Carbon::today(),
                    "tracking_number" => 1
                ],
                2 => [
                    'description' => "Test Box 2",
                    'destroy_date' => "2043-02-01",
                    "tracking_number" => 2
                ],
                4 => [
                    'description' => "Test Box 4",
                    'destroy_date' => "2022-04-01",
                    "tracking_number" => 3
                ]
            ]
        );
    }

    public function testUpdateNullDestroyDate()
    {
        $this->assertUpdateSuccessful(
            '1',
            [
                "authorizing_user_id" => 6,
                "boxes" => [
                    [
                        "id" => 1,
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ],
                    [
                        "id" => 2,
                        "description" => "Box 2 description",
                        'destroy_date' => null
                    ]
                ]
            ],
            6,
            4,
            [
                1 => [
                    "description" => "Box 1 description",
                    "destroy_date" => Carbon::today(),
                    "tracking_number" => 1
                ],
                2 => [
                    "description" => "Box 2 description",
                    'destroy_date' => null,
                    "tracking_number" => 2
                ],
                4 => [
                    'description' => "Test Box 4",
                    'destroy_date' => "2022-04-01",
                    "tracking_number" => 3
                ]
            ]
        );
    }

    public function testUpdateNoDestroyDate()
    {
        $this->assertUpdateSuccessful(
            '1',
            [
                "authorizing_user_id" => 6,
                "boxes" => [
                    [
                        "id" => 1,
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ],
                    [
                        "id" => 2,
                        "description" => "Box 2 description"
                    ]
                ]
            ],
            6,
            4,
            [
                1 => [
                    "description" => "Box 1 description",
                    "destroy_date" => Carbon::today(),
                    "tracking_number" => 1
                ],
                2 => [
                    "description" => "Box 2 description",
                    'destroy_date' => "2022-02-01",
                    "tracking_number" => 2
                ],
                4 => [
                    'description' => "Test Box 4",
                    'destroy_date' => "2022-04-01",
                    "tracking_number" => 3
                ]
            ]
        );
    }

    public function testUpdateNonDateDestroyDate()
    {
        $this->assertUpdateFailed(
            '1',
            [
                "authorizing_user_id" => 6,
                "boxes" => [
                    [
                        "id" => 1,
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ],
                    [
                        "id" => 2,
                        "description" => "Box 2 description",
                        "destroy_date" => 'x'
                    ]
                ]
            ],
            302,
            ["boxes.1.destroy_date" => "The boxes.1.destroy_date field must be a valid date."]
        );
    }

    public function testUpdateDayMonthYearFormattedDestroyDate()
    {
        $this->assertUpdateSuccessful(
            '1',
            [
                "authorizing_user_id" => 6,
                "boxes" => [
                    [
                        "id" => 1,
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ],
                    [
                        "id" => 2,
                        "description" => "Box 2 description",
                        "destroy_date" => '31-12-2022'
                    ]
                ]
            ],
            6,
            4,
            [
                1 => [
                    "description" => "Box 1 description",
                    "destroy_date" => Carbon::today(),
                    "tracking_number" => 1
                ],
                2 => [
                    "description" => "Box 2 description",
                    "destroy_date" => '31-12-2022',
                    "tracking_number" => 2
                ],
                4 => [
                    'description' => "Test Box 4",
                    'destroy_date' => "2022-04-01",
                    "tracking_number" => 3
                ]
            ]
        );
    }

    public function testUpdateMonthDayYearFormattedDestroyDate()
    {
        $this->assertUpdateFailed(
            '1',
            [
                "authorizing_user_id" => 6,
                "boxes" => [
                    [
                        "id" => 1,
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ],
                    [
                        "id" => 2,
                        "description" => "Box 2 description",
                        "destroy_date" => '12-31-2022'
                    ]
                ]
            ],
            302,
            ["boxes.1.destroy_date" => "The boxes.1.destroy_date field must be a valid date."]
        );
    }

    public function testUpdateWithOneBox()
    {
        $this->assertUpdateSuccessful(
            '1',
            [
                "authorizing_user_id" => 6,
                "boxes" => [
                    [
                        "id" => 1,
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ]
                ]
            ],
            6,
            4,
            [
                1 => [
                    "description" => "Box 1 description",
                    "destroy_date" => Carbon::today(),
                    "tracking_number" => 1
                ],
                2 => [
                    'description' => "Test Box 2",
                    'destroy_date' => "2022-02-01",
                    "tracking_number" => 2
                ],
                4 => [
                    'description' => "Test Box 4",
                    'destroy_date' => "2022-04-01",
                    "tracking_number" => 3
                ]
            ]
        );
    }

    public function testUpdateWithMultipleBoxes()
    {
        $this->assertUpdateSuccessful(
            '1',
            [
                "authorizing_user_id" => 6,
                "boxes" => [
                    [
                        "id" => 1,
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ],
                    [
                        "id" => 4,
                        "description" => "Box 4 description",
                        "destroy_date" => '2022-12-12'
                    ]
                ]
            ],
            6,
            4,
            [
                1 => [
                    "description" => "Box 1 description",
                    "destroy_date" => Carbon::today(),
                    "tracking_number" => 1
                ],
                2 => [
                    "description" => "Test Box 2",
                    'destroy_date' => "2022-02-01",
                    "tracking_number" => 2
                ],
                4 => [
                    "description" => "Box 4 description",
                    "destroy_date" => '2022-12-12',
                    "tracking_number" => 3
                ]
            ]
        );
    }

    public function testUpdateRetentionRequestWithOneBox()
    {
        $this->assertUpdateSuccessful(
            '3',
            [
                "authorizing_user_id" => 6,
                "boxes" => [
                    [
                        "id" => 5,
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ]
                ]
            ],
            6,
            2,
            [
                1 => [
                    "description" => "Test Box 1",
                    'destroy_date' => "2022-01-01",
                    "tracking_number" => null
                ],
                2 => [
                    "description" => "Test Box 2",
                    'destroy_date' => "2022-02-01",
                    "tracking_number" => null
                ],
                3 => [
                    "description" => "Test Box 3",
                    'destroy_date' => "2022-03-01",
                    "tracking_number" => null
                ],
                4 => [
                    "description" => "Test Box 4",
                    "destroy_date" => '2022-04-01',
                    "tracking_number" => null
                ],
                5 => [
                    "description" => "Box 1 description",
                    "destroy_date" => Carbon::today(),
                    "tracking_number" => 1
                ],
                6 => [
                    "description" => "Test Box 6",
                    "destroy_date" => '2022-06-01',
                    "tracking_number" => null
                ]
            ]
        );
    }

    public function testUpdateOneBoxInDBOneBoxUpdated()
    {
        $this->reseedDB(
            [
                [
                    'manager_name' => "Test Manager 1",
                    'requestor_name' => "Test Requestor 1",
                    'requestor_email' => "test_requestor_one@gmail.com",
                    'department_id' => 1
                ]
            ],
            [
                [
                    'description' => "Test Box 1",
                    'destroy_date' => "2022-01-01",
                    'retention_request_id' => 1
                ]
            ]
        );

        $this->assertUpdateSuccessful(
            '1',
            [
                "authorizing_user_id" => 6,
                "boxes" => [
                    [
                        "id" => 1,
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ]
                ]
            ],
            6,
            2,
            [
                1 => [
                    "description" => "Box 1 description",
                    "destroy_date" => Carbon::today(),
                    "tracking_number" => 1
                ]
            ]
        );
    }

    public function testUpdateMultipleBoxesInDBOneBoxUpdated()
    {
        $this->reseedDB(
            [
                [
                    'manager_name' => "Test Manager 1",
                    'requestor_name' => "Test Requestor 1",
                    'requestor_email' => "test_requestor_one@gmail.com",
                    'department_id' => 1
                ]
            ],
            [
                [
                    'description' => "Test Box 1",
                    'destroy_date' => "2022-01-01",
                    'retention_request_id' => 1
                ],
                [
                    'description' => "Test Box 2",
                    'destroy_date' => "2022-02-01",
                    'retention_request_id' => 1
                ]
            ]
        );

        $this->assertUpdateSuccessful(
            '1',
            [
                "authorizing_user_id" => 6,
                "boxes" => [
                    [
                        "id" => 1,
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ]
                ]
            ],
            6,
            3,
            [
                1 => [
                    "description" => "Box 1 description",
                    "destroy_date" => Carbon::today(),
                    "tracking_number" => 1
                ],
                2 => [
                    'description' => "Test Box 2",
                    'destroy_date' => "2022-02-01",
                    "tracking_number" => 2
                ]
            ]
        );
    }

    public function testUpdateMultipleBoxesInDBMultipleBoxesUpdated()
    {
        $this->reseedDB(
            [
                [
                    'manager_name' => "Test Manager 1",
                    'requestor_name' => "Test Requestor 1",
                    'requestor_email' => "test_requestor_one@gmail.com",
                    'department_id' => 1
                ]
            ],
            [
                [
                    'description' => "Test Box 1",
                    'destroy_date' => "2022-01-01",
                    'retention_request_id' => 1
                ],
                [
                    'description' => "Test Box 2",
                    'destroy_date' => "2022-02-01",
                    'retention_request_id' => 1
                ],
                [
                    'description' => "Test Box 3",
                    'destroy_date' => "2022-03-01",
                    'retention_request_id' => 1
                ]
            ]
        );

        $this->assertUpdateSuccessful(
            '1',
            [
                "authorizing_user_id" => 6,
                "boxes" => [
                    [
                        "id" => 1,
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ],
                    [
                        "id" => 3,
                        "description" => "Box 3 description",
                        "destroy_date" => Carbon::today()
                    ]
                ]
            ],
            6,
            4,
            [
                1 => [
                    "description" => "Box 1 description",
                    "destroy_date" => Carbon::today(),
                    "tracking_number" => 1
                ],
                2 => [
                    'description' => "Test Box 2",
                    'destroy_date' => "2022-02-01",
                    "tracking_number" => 2
                ],
                3 => [
                    "description" => "Box 3 description",
                    "destroy_date" => Carbon::today(),
                    "tracking_number" => 3
                ]
            ]
        );
    }

    public function testUpdateMultipleBoxesInDBAllBoxesUpdated()
    {
        $this->reseedDB(
            [
                [
                    'manager_name' => "Test Manager 1",
                    'requestor_name' => "Test Requestor 1",
                    'requestor_email' => "test_requestor_one@gmail.com",
                    'department_id' => 1
                ]
            ],
            [
                [
                    'description' => "Test Box 1",
                    'destroy_date' => "2022-01-01",
                    'retention_request_id' => 1
                ],
                [
                    'description' => "Test Box 2",
                    'destroy_date' => "2022-02-01",
                    'retention_request_id' => 1
                ],
                [
                    'description' => "Test Box 3",
                    'destroy_date' => "2022-03-01",
                    'retention_request_id' => 1
                ]
            ]
        );

        $this->assertUpdateSuccessful(
            '1',
            [
                "authorizing_user_id" => 6,
                "boxes" => [
                    [
                        "id" => 1,
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ],
                    [
                        "id" => 2,
                        "description" => "Box 2 description",
                        "destroy_date" => Carbon::today()
                    ],
                    [
                        "id" => 3,
                        "description" => "Box 3 description",
                        "destroy_date" => Carbon::today()
                    ]
                ]
            ],
            6,
            4,
            [
                1 => [
                    "description" => "Box 1 description",
                    "destroy_date" => Carbon::today(),
                    "tracking_number" => 1
                ],
                2 => [
                    "description" => "Box 2 description",
                    "destroy_date" => Carbon::today(),
                    "tracking_number" => 2
                ],
                3 => [
                    "description" => "Box 3 description",
                    "destroy_date" => Carbon::today(),
                    "tracking_number" => 3
                ]
            ]
        );
    }

    public function testUpdateOneBoxInDBInvalidBoxUpdated()
    {
        $this->reseedDB(
            [
                [
                    'manager_name' => "Test Manager 1",
                    'requestor_name' => "Test Requestor 1",
                    'requestor_email' => "test_requestor_one@gmail.com",
                    'department_id' => 1
                ]
            ],
            [
                [
                    'description' => "Test Box 1",
                    'destroy_date' => "2022-01-01",
                    'retention_request_id' => 1
                ]
            ]
        );

        $this->assertUpdateFailed(
            '1',
            [
                "authorizing_user_id" => 6,
                "boxes" => [
                    [
                        "id" => 2,
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ]
                ]
            ],
            302,
            ["boxes.0.id" => "The selected boxes.0.id is invalid."]
        );
    }

    public function testUpdateOneBoxInDBMultipleInvalidBoxesUpdated()
    {
        $this->reseedDB(
            [
                [
                    'manager_name' => "Test Manager 1",
                    'requestor_name' => "Test Requestor 1",
                    'requestor_email' => "test_requestor_one@gmail.com",
                    'department_id' => 1
                ]
            ],
            [
                [
                    'description' => "Test Box 1",
                    'destroy_date' => "2022-01-01",
                    'retention_request_id' => 1
                ]
            ]
        );

        $this->assertUpdateFailed(
            '1',
            [
                "authorizing_user_id" => 6,
                "boxes" => [
                    [
                        "id" => 2,
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ],
                    [
                        "id" => 4,
                        "description" => "Box 3 description",
                        "destroy_date" => Carbon::today()
                    ]
                ]
            ],
            302,
            [
                "boxes.0.id" => "The selected boxes.0.id is invalid.",
                "boxes.1.id" => "The selected boxes.1.id is invalid."
            ]
        );
    }

    public function testUpdateOneBoxInDBSomeInvalidBoxesUpdated()
    {
        $this->reseedDB(
            [
                [
                    'manager_name' => "Test Manager 1",
                    'requestor_name' => "Test Requestor 1",
                    'requestor_email' => "test_requestor_one@gmail.com",
                    'department_id' => 1
                ]
            ],
            [
                [
                    'description' => "Test Box 1",
                    'destroy_date' => "2022-01-01",
                    'retention_request_id' => 1
                ]
            ]
        );

        $this->assertUpdateFailed(
            '1',
            [
                "authorizing_user_id" => 6,
                "boxes" => [
                    [
                        "id" => 2,
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ],
                    [
                        "id" => 4,
                        "description" => "Box 3 description",
                        "destroy_date" => Carbon::today()
                    ],
                    [
                        "id" => 1,
                        "description" => "Box 2 description",
                        "destroy_date" => Carbon::today()
                    ]
                ]
            ],
            302,
            [
                "boxes.0.id" => "The selected boxes.0.id is invalid.",
                "boxes.1.id" => "The selected boxes.1.id is invalid."
            ]
        );
    }

    public function testUpdateMultipleBoxesInDBInvalidBoxUpdated()
    {
        $this->reseedDB(
            [
                [
                    'manager_name' => "Test Manager 1",
                    'requestor_name' => "Test Requestor 1",
                    'requestor_email' => "test_requestor_one@gmail.com",
                    'department_id' => 1
                ]
            ],
            [
                [
                    'description' => "Test Box 1",
                    'destroy_date' => "2022-01-01",
                    'retention_request_id' => 1
                ],
                [
                    'description' => "Test Box 2",
                    'destroy_date' => "2022-02-01",
                    'retention_request_id' => 1
                ]
            ]
        );

        $this->assertUpdateFailed(
            '1',
            [
                "authorizing_user_id" => 6,
                "boxes" => [
                    [
                        "id" => 4,
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ]
                ]
            ],
            302,
            ["boxes.0.id" => "The selected boxes.0.id is invalid."]
        );
    }

    public function testUpdateMultipleBoxesInDBMultipleInvalidBoxesUpdated()
    {
        $this->reseedDB(
            [
                [
                    'manager_name' => "Test Manager 1",
                    'requestor_name' => "Test Requestor 1",
                    'requestor_email' => "test_requestor_one@gmail.com",
                    'department_id' => 1
                ]
            ],
            [
                [
                    'description' => "Test Box 1",
                    'destroy_date' => "2022-01-01",
                    'retention_request_id' => 1
                ],
                [
                    'description' => "Test Box 2",
                    'destroy_date' => "2022-02-01",
                    'retention_request_id' => 1
                ],
                [
                    'description' => "Test Box 3",
                    'destroy_date' => "2022-03-01",
                    'retention_request_id' => 1
                ],
                [
                    'description' => "Test Box 4",
                    'destroy_date' => "2022-04-01",
                    'retention_request_id' => 1
                ]
            ]
        );

        $this->assertUpdateFailed(
            '1',
            [
                "authorizing_user_id" => 6,
                "boxes" => [
                    [
                        "id" => 8,
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ],
                    [
                        "id" => 9,
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ]
                ]
            ],
            302,
            [
                "boxes.0.id" => "The selected boxes.0.id is invalid.",
                "boxes.1.id" => "The selected boxes.1.id is invalid."
            ]
        );
    }

    public function testUpdateMultipleBoxesInDBSomeInvalidBoxesUpdated()
    {
        $this->reseedDB(
            [
                [
                    'manager_name' => "Test Manager 1",
                    'requestor_name' => "Test Requestor 1",
                    'requestor_email' => "test_requestor_one@gmail.com",
                    'department_id' => 1
                ]
            ],
            [
                [
                    'description' => "Test Box 1",
                    'destroy_date' => "2022-01-01",
                    'retention_request_id' => 1
                ],
                [
                    'description' => "Test Box 2",
                    'destroy_date' => "2022-02-01",
                    'retention_request_id' => 1
                ],
                [
                    'description' => "Test Box 3",
                    'destroy_date' => "2022-03-01",
                    'retention_request_id' => 1
                ],
                [
                    'description' => "Test Box 4",
                    'destroy_date' => "2022-04-01",
                    'retention_request_id' => 1
                ]
            ]
        );

        $this->assertUpdateFailed(
            '1',
            [
                "authorizing_user_id" => 6,
                "boxes" => [
                    [
                        "id" => 8,
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ],
                    [
                        "id" => 9,
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ],
                    [
                        "id" => 1,
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ]
                ]
            ],
            302,
            [
                "boxes.0.id" => "The selected boxes.0.id is invalid.",
                "boxes.1.id" => "The selected boxes.1.id is invalid."
            ]
        );
    }

    public function testUpdateMultipleBoxesHaveTheSameId()
    {
        $this->assertUpdateFailed(
            '1',
            [
                "authorizing_user_id" => 6,
                "boxes" => [
                    [
                        "id" => 1,
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ],
                    [
                        "id" => 1,
                        "description" => "Box 2 description",
                        "destroy_date" => Carbon::today()
                    ]
                ]
            ],
            302,
            ["boxes.*.id" => "Attempted to update a box that doesn't correspond to any box assigned to the retention request that has the give id."]
        );
    }

    public function testUpdateDoesntAffectBoxesThatBelongToDifferentRetentionRequest()
    {
        $this->assertUpdateSuccessful(
            '1',
            [
                "authorizing_user_id" => 6,
                "boxes" => [
                    [
                        "id" => 1,
                        "description" => "Box 1 description",
                        "destroy_date" => Carbon::today()
                    ]
                ]
            ],
            6,
            4,
            [
                1 => [
                    "description" => "Box 1 description",
                    "destroy_date" => Carbon::today(),
                    "tracking_number" => 1
                ],
                2 => [
                    'description' => "Test Box 2",
                    'destroy_date' => "2022-02-01",
                    "tracking_number" => 2
                ],
                3 => [
                    'description' => "Test Box 3",
                    'destroy_date' => "2022-03-01",
                    "tracking_number" => null
                ],
                4 => [
                    'description' => "Test Box 4",
                    'destroy_date' => "2022-04-01",
                    "tracking_number" => 3
                ],
                5 => [
                    'description' => "Test Box 5",
                    'destroy_date' => "2022-05-01",
                    "tracking_number" => null
                ],
                6 => [
                    'description' => "Test Box 6",
                    'destroy_date' => "2022-06-01",
                    "tracking_number" => null
                ]
            ]
        );
    }

    public function testUpdateNoSettingsFile()
    {
        // TODO: implement
    }

    public function testUpdateNoNextTrackingNumberInSettingsFile()
    {
        // TODO: implement
    }

    private function reseedDB(array $retentionRequests, array $boxes)
    {
        RetentionRequest::truncate();
        Box::truncate();

        foreach ($retentionRequests as $retentionRequest) {
            RetentionRequest::create($retentionRequest);
        }

        foreach ($boxes as $box) {
            Box::create($box);
        }
    }

    private function assertUpdateSuccessful(string $id, array $updateData, int $expectedAuthorizingUserId, int $expectedNextTrackingNumber, array $expectedBoxes)
    {
        $response = $this->put('api/retention-requests/' . $id, $updateData);
        $response->assertStatus(200);

        $retentionRequest = RetentionRequest::findOrFail($id);
        $this->assertEquals($expectedAuthorizingUserId, $retentionRequest->authorizing_user_id);

        $boxes = Box::where('retention_request_id', '=', $id)
            ->orderBy('id')
            ->get();

        foreach ($boxes as $box) {
            $expectedBox = $expectedBoxes[$box->id];
            $this->assertEquals($expectedBox['tracking_number'], $box->tracking_number);
            $this->assertEquals($expectedBox['description'], $box->description);
            $this->assertEquals($expectedBox['destroy_date'], $box->destroy_date);
            $this->assertEquals($id, $box->retention_request_id);
        }

        $actualNextTrackingNumber = $this->settings->get('next_tracking_number');
        $this->assertEquals($expectedNextTrackingNumber, $actualNextTrackingNumber);
    }

    private function assertUpdateFailed(string $id, array $updateData, int $expectedStatus, array $expectedErrors)
    {
        $originalBoxes = Box::where('retention_request_id', '=', $id)
            ->orderBy('id')
            ->get();

        $response = $this->put('api/retention-requests/' . $id, $updateData);
        $response->assertStatus($expectedStatus);

        if (count($expectedErrors) > 0)
            $response->assertSessionHasErrors($expectedErrors);

        $rolledBackBoxes = Box::where('retention_request_id', '=', $id)
            ->orderBy('id')
            ->get();

        // FIXME: is this a valid way to make sure that the boxes in the database were rolled back?
        $this->assertEquals($originalBoxes, $rolledBackBoxes);
    }

    private function assertPostValidationErrors(array $data, array $errors)
    {
        $originalBoxCount = Box::count();

        $response = $this->post('api/retention-requests', $data);
        $response->assertStatus(302);
        $response->assertSessionHasErrors($errors);

        $rolledBackBoxCount = Box::count();
        $this->assertEquals($originalBoxCount, $rolledBackBoxCount);
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
