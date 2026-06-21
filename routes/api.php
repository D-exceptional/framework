<?php

// Import middleware, validation and controller classes
use App\Http\Middlewares\{
    AuthMiddleware,
    CsrfMiddleware,
    RateLimitMiddleware,
    ValidationMiddleware,
};

// Import controllers
use App\Http\Controllers\{
    UserController,
    NotificationController,
    CategoryController,
    BlogController,
    WalletController,
    MailController,
    TaskController,
    TestController,
};

// Import blog validations
use App\Validations\Rules\Blog\{
    CreateBlogRequest,
    DeleteBlogRequest,
    FindByBlogStatusRequest,
    FindByBlogUserRequest,
    FindOneBlogRequest,
    UpdateBannerRequest,
    UpdateCounterRequest,
    UpdateBlogDetailsRequest,
    UpdateBlogStatusRequest,
};

// Import category validations
use App\Validations\Rules\Category\{
    CreateCategoryRequest,
    DeleteCategoryRequest,
    UpdateCategoryRequest,
};

// Import mail validations
use App\Validations\Rules\Mail\{
    DeleteMailRequest,
    GetInboxRequest,
    GetMailRequest,
    GetOutboxRequest,
    SubscribeMailRequest,
};

// Import notification validations
use App\Validations\Rules\Notification\{
    CreateNotificationRequest,
    FetchByIdRequest,
    GetUnreadRequest,
};

// Import task validations
use App\Validations\Rules\Task\{
    AttemptTaskRequest,
    CreateTaskRequest,
    DeleteTaskRequest,
    FinalizeAttemptRequest,
    FindByTaskStatusRequest,
    FindOneTaskRequest,
    GetAttemptRequest,
    GetDescriptionRequest,
    LoadTaskRequest,
    UpdateAllTaskRequest,
    UpdateTaskDetailsRequest,
    UpdateTaskStatusRequest,
};

// Import user validations
use App\Validations\Rules\User\{
    ContactRequest,
    DeleteRequest,
    FetchRequest,
    LoginRequest,
    OtpRequest,
    PasswordRequest,
    ProfileRequest,
    RegisterRequest,
    ResetRequest,
    SocialRequest,
    StatusRequest,
    SubscribeRequest,
    SwitchRequest,
    UnsubscribeRequest,
    UpdateRequest,
};

// Import wallet validations
use App\Validations\Rules\Wallet\{
    BulkTransferRequest,
    CreatePaymentRequest,
    GetPaymentByReferenceRequest,
    GetPaymentsByChannelRequest,
    GetPaymentsByStatusRequest,
    GetPaymentsByTypeRequest,
    GetPaymentsByUserRequest,
    GetPaymentSummaryRequest,
    GetPayoutsByStatusRequest,
    RequestFundsRequest,
    SingleTransferRequest,
    UpdatePaymentDetailsRequest,
    VerifyPaymentAutoRequest,
    VerifyPaymentManualRequest,
};

// -------------------------------------------------
// TEST ROUTES
// ------------------------------------------------
$router->group('/api/test', function ($router) {
    $router->get('/ping', [TestController::class, 'ping']);
    $router->get('/beep', [TestController::class, 'beep']);
},
// Add all group middlewares here
[
    [RateLimitMiddleware::class, 'handle', ['scope' => 'api', 'userLimit' => 100, 'anonLimit' => 20]],
]
);

// -------------------------------------------------
// USER ROUTES
// -------------------------------------------------
$router->group('/api/user', function ($router) {
    $router->get('/count', [UserController::class, 'count'], [
        [AuthMiddleware::class, 'handle', ['role' => ['admin']]],
    ]);
    $router->get('/fetch', [UserController::class, 'fetch'], [
        [AuthMiddleware::class, 'handle', ['role' => ['admin']]],
        [ValidationMiddleware::class, 'handle', ['rules' => FetchRequest::rules()]]
    ]);
    $router->post('/register', [UserController::class, 'register'], [
        [ValidationMiddleware::class, 'handle', ['rules' => RegisterRequest::rules()]]
    ]);
    $router->post('/login', [UserController::class, 'login'], [
        [ValidationMiddleware::class, 'handle', ['rules' => LoginRequest::rules()]]
    ]);
    $router->post('/otp', [UserController::class, 'otp'], [
        [ValidationMiddleware::class, 'handle', ['rules' => OtpRequest::rules()]]
    ]);
    $router->post('/logout', [UserController::class, 'logout'], [
        [AuthMiddleware::class, 'handle']
    ]);
    $router->post('/contact', [UserController::class, 'contact'], [
        [ValidationMiddleware::class, 'handle', ['rules' => ContactRequest::rules()]]
    ]);
    $router->post('/subscribe', [UserController::class, 'subscribe'], [
        [ValidationMiddleware::class, 'handle', ['rules' => SubscribeRequest::rules()]]
    ]);
    $router->put('/password/reset', [UserController::class, 'reset'], [
        [ValidationMiddleware::class, 'handle', ['rules' => ResetRequest::rules()]]
    ]);
    $router->put('/update', [UserController::class, 'update'], [
        [CsrfMiddleware::class, 'handle'],
        [ValidationMiddleware::class, 'handle', ['rules' => UpdateRequest::rules()]]
    ]);
    $router->put('/profile', [UserController::class, 'profile'], [
        [CsrfMiddleware::class, 'handle'],
        [ValidationMiddleware::class, 'handle', ['rules' => ProfileRequest::rules()]]
    ]);
    $router->put('/social', [UserController::class, 'social'], [
        [CsrfMiddleware::class, 'handle'],
        [ValidationMiddleware::class, 'handle', ['rules' => SocialRequest::rules()]]
    ]);
    $router->put('/password/change', [UserController::class, 'password'], [
        [CsrfMiddleware::class, 'handle'],
        [ValidationMiddleware::class, 'handle', ['rules' => PasswordRequest::rules()]]
    ]);
    $router->put('/status', [UserController::class, 'status'], [
        [AuthMiddleware::class, 'handle', ['role' => ['admin']]], 
        [CsrfMiddleware::class, 'handle'],
        [ValidationMiddleware::class, 'handle', ['rules' => StatusRequest::rules()]]
    ]);
    $router->put('/switch', [UserController::class, 'switch'], [
        [ValidationMiddleware::class, 'handle', ['rules' => SwitchRequest::rules()]]
    ]);
    $router->delete('/unsubscribe', [UserController::class, 'unsubscribe'], [
        [CsrfMiddleware::class, 'handle'],
        [ValidationMiddleware::class, 'handle', ['rules' => UnsubscribeRequest::rules()]]
    ]);             
    $router->delete('/delete', [UserController::class, 'delete'], [
        [AuthMiddleware::class, 'handle', ['role' => ['admin']]],  
        [CsrfMiddleware::class, 'handle'],
        [ValidationMiddleware::class, 'handle', ['rules' => DeleteRequest::rules()]]
    ]);
},
// Add all group middlewares here
[
    [RateLimitMiddleware::class, 'handle', ['scope' => 'api', 'userLimit' => 100, 'anonLimit' => 20]],
]
);

// -------------------------------------------------
// NOTIFICATION ROUTES
// -------------------------------------------------
$router->group('/api/notification', function ($router) {
    $router->get('', [NotificationController::class, 'fetchById'], [
        [AuthMiddleware::class, 'handle', ['role' => ['admin']]]
    ]);
    $router->get('/all/count', [NotificationController::class, 'countAll'], [
        [AuthMiddleware::class, 'handle', ['role' => ['admin']]]
    ]);
    $router->get('/user/count', [NotificationController::class, 'countAllById'], [
        [AuthMiddleware::class, 'handle']
    ]);
    $router->get('/unread/count', [NotificationController::class, 'countUnreadById'], [
        [AuthMiddleware::class, 'handle']
    ]);
    $router->get('/unread', [NotificationController::class, 'getUnread'], [
        [AuthMiddleware::class, 'handle']
    ]);
    $router->post('', [NotificationController::class, 'create']);
    $router->put('/mark/read', [NotificationController::class, 'markAsRead'], [
        [AuthMiddleware::class, 'handle'], 
        [CsrfMiddleware::class, 'handle'],
    ]);
},
// Add all group middlewares here
[
    [RateLimitMiddleware::class, 'handle', ['scope' => 'api', 'userLimit' => 100, 'anonLimit' => 20]],
]
);

// -------------------------------------------------
// CATEGORY ROUTES
// -------------------------------------------------
$router->group('/api/category', function ($router) {
    $router->get('', [CategoryController::class, 'all']);
    $router->get('/count', [CategoryController::class, 'count'], [
        [AuthMiddleware::class, 'handle', ['role' => ['admin']]],
    ]);
    $router->post('', [CategoryController::class, 'create'], [
        [AuthMiddleware::class, 'handle', ['role' => ['admin']]],
        [ValidationMiddleware::class, 'handle', ['rules' => CreateCategoryRequest::rules()]],
    ]);
    $router->put('', [CategoryController::class, 'update'], [
        [AuthMiddleware::class, 'handle', ['role' => ['admin']]], 
        [CsrfMiddleware::class, 'handle'],
        [ValidationMiddleware::class, 'handle', ['rules' => UpdateCategoryRequest::rules()]],
    ]);
    $router->delete('', [CategoryController::class, 'delete'], [
        [AuthMiddleware::class, 'handle', ['role' => ['admin']]], 
        [CsrfMiddleware::class, 'handle'],
        [ValidationMiddleware::class, 'handle', ['rules' => DeleteCategoryRequest::rules()]],
    ]);
},
// Add all group middlewares here
[
    [RateLimitMiddleware::class, 'handle', ['scope' => 'api', 'userLimit' => 100, 'anonLimit' => 20]],
]
);

// -------------------------------------------------
// BLOG ROUTES
// -------------------------------------------------
$router->group('/api/blog', function ($router) {
    $router->get('', [BlogController::class, 'findOne'], [
        [ValidationMiddleware::class, 'handle', ['rules' => FindOneBlogRequest::rules()]],
    ]);
    $router->get('/list/status', [BlogController::class, 'findByStatus'], [
        [ValidationMiddleware::class, 'handle', ['rules' => FindByBlogStatusRequest::rules()]],
    ]);
    $router->get('/list/user', [BlogController::class, 'findByUser'], [
        [AuthMiddleware::class, 'handle', ['role' => ['admin','worker']]],
        [ValidationMiddleware::class, 'handle', ['rules' => FindByBlogUserRequest::rules()]],
    ]);
    $router->post('', [BlogController::class, 'createBlog'], [
        [AuthMiddleware::class, 'handle', ['role' => ['admin','worker']]],
        [ValidationMiddleware::class, 'handle', ['rules' => CreateBlogRequest::rules()]],
    ]);
    $router->put('/details', [BlogController::class, 'updateDetails'], [
        [AuthMiddleware::class, 'handle', ['role' => ['admin','worker']]], 
        [CsrfMiddleware::class, 'handle'],
        [ValidationMiddleware::class, 'handle', ['rules' => UpdateBlogDetailsRequest::rules()]],
    ]);
    $router->put('/banner', [BlogController::class, 'updateBanner'], [ 
        [AuthMiddleware::class, 'handle', ['role' => ['admin','worker']]], 
        [CsrfMiddleware::class, 'handle'],
        [ValidationMiddleware::class, 'handle', ['rules' => UpdateBannerRequest::rules()]],
    ]);
    $router->put('/status', [BlogController::class, 'updateStatus'], [
        [AuthMiddleware::class, 'handle', ['role' => ['admin','worker']]], 
        [CsrfMiddleware::class, 'handle'],
        [ValidationMiddleware::class, 'handle', ['rules' => UpdateBlogStatusRequest::rules()]],
    ]);
    $router->put('/stat', [BlogController::class, 'updateCounter'], [
        // [AuthMiddleware::class, 'handle', ['role' => ['admin','worker']]], 
        // [CsrfMiddleware::class, 'handle'],
        [ValidationMiddleware::class, 'handle', ['rules' => UpdateCounterRequest::rules()]],
    ]);
    $router->delete('', [BlogController::class, 'deleteBlog'], [
        [AuthMiddleware::class, 'handle', ['role' => ['admin','worker']]], 
        [CsrfMiddleware::class, 'handle'],
        [ValidationMiddleware::class, 'handle', ['rules' => DeleteBlogRequest::rules()]],
    ]);
},
// Add all group middlewares here
[
    [RateLimitMiddleware::class, 'handle', ['scope' => 'api', 'userLimit' => 100, 'anonLimit' => 20]],
]
);

// -------------------------------------------------
// WALLET ROUTES
// -------------------------------------------------
$router->group('/api/payment', function ($router) {   
    $router->get('/reference', [WalletController::class, 'getByReference'], [
        [ValidationMiddleware::class, 'handle', ['rules' => GetPaymentByReferenceRequest::rules()]],
    ]);
    $router->get('/user', [WalletController::class, 'getPaymentsByUser'], [
        [AuthMiddleware::class, 'handle'],
        [ValidationMiddleware::class, 'handle', ['rules' => GetPaymentsByUserRequest::rules()]],
    ]);
    $router->get('/type', [WalletController::class, 'getPaymentsByType'], [
        [AuthMiddleware::class, 'handle', ['role' => ['admin']]],
        [ValidationMiddleware::class, 'handle', ['rules' => GetPaymentsByTypeRequest::rules()]],
    ]);
    $router->get('/status', [WalletController::class, 'getPaymentsByStatus'], [
        [AuthMiddleware::class, 'handle', ['role' => ['admin']]],
        [ValidationMiddleware::class, 'handle', ['rules' => GetPaymentsByStatusRequest::rules()]],
    ]);
    $router->get('/channel', [WalletController::class, 'getPaymentsByChannel'], [
        [AuthMiddleware::class, 'handle', ['role' => ['admin','worker']]],
        [ValidationMiddleware::class, 'handle', ['rules' => GetPaymentsByChannelRequest::rules()]],
    ]);
    $router->get('/payouts/status', [WalletController::class, 'getPayoutsByStatus'], [
        [AuthMiddleware::class, 'handle', ['role' => ['admin']]],
        [ValidationMiddleware::class, 'handle', ['rules' => GetPayoutsByStatusRequest::rules()]],
    ]);
    $router->get('/summary', [WalletController::class, 'getPaymentSummary'], [
        [AuthMiddleware::class, 'handle', ['role' => ['admin']]],
        [ValidationMiddleware::class, 'handle', ['rules' => GetPaymentSummaryRequest::rules()]],
    ]);
    $router->post('/create', [WalletController::class, 'createPayment'], [
        [AuthMiddleware::class, 'handle'],
        [ValidationMiddleware::class, 'handle', ['rules' => CreatePaymentRequest::rules()]],
    ]);
    $router->post('/verify/auto', [WalletController::class, 'verifyPaymentAuto'], [
        [ValidationMiddleware::class, 'handle', ['rules' => VerifyPaymentAutoRequest::rules()]],
    ]);
    $router->post('/verify/manual', [WalletController::class, 'verifyPaymentManual'], [
        [ValidationMiddleware::class, 'handle', ['rules' => VerifyPaymentManualRequest::rules()]],
    ]);
    $router->post('/withdraw', [WalletController::class, 'requestFunds'], [
        [AuthMiddleware::class, 'handle'],
        [ValidationMiddleware::class, 'handle', ['rules' => RequestFundsRequest::rules()]],
    ]);
    $router->post('/transfer/single', [WalletController::class, 'singleTransfer'], [
        [AuthMiddleware::class, 'handle', ['role' => ['admin']]],
        [ValidationMiddleware::class, 'handle', ['rules' => SingleTransferRequest::rules()]],
    ]);
    $router->post('/transfer/bulk', [WalletController::class, 'bulkTransfer'], [
        [AuthMiddleware::class, 'handle', ['role' => ['admin']]],
        [ValidationMiddleware::class, 'handle', ['rules' => BulkTransferRequest::rules()]],
    ]);
    $router->put('/details/update', [WalletController::class, 'updateDetails'], [
        [AuthMiddleware::class, 'handle'], 
        [CsrfMiddleware::class, 'handle'],
        [ValidationMiddleware::class, 'handle', ['rules' => UpdatePaymentDetailsRequest::rules()]],
    ]);
},
// Add all group middlewares here
[
    [RateLimitMiddleware::class, 'handle', ['scope' => 'api', 'userLimit' => 100, 'anonLimit' => 20]],
]
);

// -------------------------------------------------
// MAIL ROUTES
// -------------------------------------------------
$router->group('/api/mail', function ($router) {
    $router->get('/inbox/count', [MailController::class, 'countInbox'], [
        [AuthMiddleware::class, 'handle'],
    ]);
    $router->get('/outbox/count', [MailController::class, 'countOutbox'], [
        [AuthMiddleware::class, 'handle'],
    ]);
    $router->get('/inbox', [MailController::class, 'getInbox'], [
        [AuthMiddleware::class, 'handle'],
        [ValidationMiddleware::class, 'handle', ['rules' => GetInboxRequest::rules()]],
    ]);
    $router->get('/outbox', [MailController::class, 'getOutbox'], [
        [AuthMiddleware::class, 'handle'],
        [ValidationMiddleware::class, 'handle', ['rules' => GetOutboxRequest::rules()]],
    ]);
    $router->get('', [MailController::class, 'getMail'], [
        [ValidationMiddleware::class, 'handle', ['rules' => GetMailRequest::rules()]],
    ]);
    $router->post('/send', [MailController::class, 'sendBulk'], [
        [AuthMiddleware::class, 'handle', ['role' => ['admin']]],
    ]);
    $router->post('/subscribe', [MailController::class, 'subscribeMail'], [
        [ValidationMiddleware::class, 'handle', ['rules' => SubscribeMailRequest::rules()]],
    ]);
    $router->delete('', [MailController::class, 'deleteMail'], [
        [AuthMiddleware::class, 'handle', ['role' => ['admin']]], 
        [CsrfMiddleware::class, 'handle'],
        [ValidationMiddleware::class, 'handle', ['rules' => DeleteMailRequest::rules()]],
    ]);
},
// Add all group middlewares here
[
    [RateLimitMiddleware::class, 'handle', ['scope' => 'api', 'userLimit' => 100, 'anonLimit' => 20]],
]
);

// -------------------------------------------------
// TASK ROUTES
// -------------------------------------------------
$router->group('/api/task', function ($router) {
    $router->get('', [TaskController::class, 'findOne'], [
        [AuthMiddleware::class, 'handle'],
        [ValidationMiddleware::class, 'handle', ['rules' => FindOneTaskRequest::rules()]],
    ]);
    $router->get('/load', [TaskController::class, 'loadTask'], [
        [AuthMiddleware::class, 'handle'],
        [ValidationMiddleware::class, 'handle', ['rules' => LoadTaskRequest::rules()]],
    ]);
    $router->get('/list/status', [TaskController::class, 'findByStatus'], [
        [AuthMiddleware::class, 'handle'],
        [ValidationMiddleware::class, 'handle', ['rules' => FindByTaskStatusRequest::rules()]],
    ]);
    $router->get('/attempt', [TaskController::class, 'getAttempt'], [
        [AuthMiddleware::class, 'handle', ['role' => ['admin','worker']]],
        [ValidationMiddleware::class, 'handle', ['rules' => GetAttemptRequest::rules()]],
    ]);
    $router->get('/description', [TaskController::class, 'getDescription'], [
        [AuthMiddleware::class, 'handle'],
        [ValidationMiddleware::class, 'handle', ['rules' => GetDescriptionRequest::rules()]],
    ]);
    $router->post('', [TaskController::class, 'createTask'], [
        [AuthMiddleware::class, 'handle', ['role' => ['admin','worker']]],
        [ValidationMiddleware::class, 'handle', ['rules' => CreateTaskRequest::rules()]],
    ]);
    $router->post('/attempt', [TaskController::class, 'attemptTask'], [
        [AuthMiddleware::class, 'handle'],
        [ValidationMiddleware::class, 'handle', ['rules' => AttemptTaskRequest::rules()]],
    ]);
    $router->put('/details', [TaskController::class, 'updateDetails'], [
        [AuthMiddleware::class, 'handle', ['role' => ['admin','worker']]],
        [ValidationMiddleware::class, 'handle', ['rules' => UpdateTaskDetailsRequest::rules()]],
    ]);
    $router->put('/status', [TaskController::class, 'updateStatus'], [
        [AuthMiddleware::class, 'handle', ['role' => ['admin','worker']]],
        [ValidationMiddleware::class, 'handle', ['rules' => UpdateTaskStatusRequest::rules()]],
    ]);
    $router->put('/status/all', [TaskController::class, 'updateAll'], [
        [AuthMiddleware::class, 'handle'],
        [ValidationMiddleware::class, 'handle', ['rules' => UpdateAllTaskRequest::rules()]],
    ]);
    $router->put('/finalize', [TaskController::class, 'finalizeAttempt'], [
        [AuthMiddleware::class, 'handle', ['role' => ['admin','worker']]],
        [ValidationMiddleware::class, 'handle', ['rules' => FinalizeAttemptRequest::rules()]],
    ]);
    $router->delete('', [TaskController::class, 'deleteTask'], [
        [AuthMiddleware::class, 'handle', ['role' => ['admin','worker']]], 
        [CsrfMiddleware::class, 'handle'],
        [ValidationMiddleware::class, 'handle', ['rules' => DeleteTaskRequest::rules()]],
    ]);
},
// Add all group middlewares here
[
    [RateLimitMiddleware::class, 'handle', ['scope' => 'api', 'userLimit' => 100, 'anonLimit' => 20]],
]
);
