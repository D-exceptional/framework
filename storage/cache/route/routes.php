<?php return array (
  'static' => 
  array (
    'GET' => 
    array (
      '/api/test/ping' => 
      array (
        'method' => 'GET',
        'path' => '/api/test/ping',
        'controller' => 'App\\Http\\Controllers\\TestController',
        'action' => 'ping',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
        ),
      ),
      '/api/test/beep' => 
      array (
        'method' => 'GET',
        'path' => '/api/test/beep',
        'controller' => 'App\\Http\\Controllers\\TestController',
        'action' => 'beep',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
        ),
      ),
      '/api/user/count' => 
      array (
        'method' => 'GET',
        'path' => '/api/user/count',
        'controller' => 'App\\Http\\Controllers\\UserController',
        'action' => 'count',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
            2 => 
            array (
              'role' => 
              array (
                0 => 'admin',
              ),
            ),
          ),
        ),
      ),
      '/api/user/fetch' => 
      array (
        'method' => 'GET',
        'path' => '/api/user/fetch',
        'controller' => 'App\\Http\\Controllers\\UserController',
        'action' => 'fetch',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
            2 => 
            array (
              'role' => 
              array (
                0 => 'admin',
              ),
            ),
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'role' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'page' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/notification' => 
      array (
        'method' => 'GET',
        'path' => '/api/notification',
        'controller' => 'App\\Http\\Controllers\\NotificationController',
        'action' => 'fetchById',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
            2 => 
            array (
              'role' => 
              array (
                0 => 'admin',
              ),
            ),
          ),
        ),
      ),
      '/api/notification/all/count' => 
      array (
        'method' => 'GET',
        'path' => '/api/notification/all/count',
        'controller' => 'App\\Http\\Controllers\\NotificationController',
        'action' => 'countAll',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
            2 => 
            array (
              'role' => 
              array (
                0 => 'admin',
              ),
            ),
          ),
        ),
      ),
      '/api/notification/user/count' => 
      array (
        'method' => 'GET',
        'path' => '/api/notification/user/count',
        'controller' => 'App\\Http\\Controllers\\NotificationController',
        'action' => 'countAllById',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
          ),
        ),
      ),
      '/api/notification/unread/count' => 
      array (
        'method' => 'GET',
        'path' => '/api/notification/unread/count',
        'controller' => 'App\\Http\\Controllers\\NotificationController',
        'action' => 'countUnreadById',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
          ),
        ),
      ),
      '/api/notification/unread' => 
      array (
        'method' => 'GET',
        'path' => '/api/notification/unread',
        'controller' => 'App\\Http\\Controllers\\NotificationController',
        'action' => 'getUnread',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
          ),
        ),
      ),
      '/api/category' => 
      array (
        'method' => 'GET',
        'path' => '/api/category',
        'controller' => 'App\\Http\\Controllers\\CategoryController',
        'action' => 'all',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
        ),
      ),
      '/api/category/count' => 
      array (
        'method' => 'GET',
        'path' => '/api/category/count',
        'controller' => 'App\\Http\\Controllers\\CategoryController',
        'action' => 'count',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
            2 => 
            array (
              'role' => 
              array (
                0 => 'admin',
              ),
            ),
          ),
        ),
      ),
      '/api/blog' => 
      array (
        'method' => 'GET',
        'path' => '/api/blog',
        'controller' => 'App\\Http\\Controllers\\BlogController',
        'action' => 'findOne',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'id' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/blog/list/status' => 
      array (
        'method' => 'GET',
        'path' => '/api/blog/list/status',
        'controller' => 'App\\Http\\Controllers\\BlogController',
        'action' => 'findByStatus',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'status' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'page' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/blog/list/user' => 
      array (
        'method' => 'GET',
        'path' => '/api/blog/list/user',
        'controller' => 'App\\Http\\Controllers\\BlogController',
        'action' => 'findByUser',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
            2 => 
            array (
              'role' => 
              array (
                0 => 'admin',
                1 => 'worker',
              ),
            ),
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'id' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
                'page' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/payment/reference' => 
      array (
        'method' => 'GET',
        'path' => '/api/payment/reference',
        'controller' => 'App\\Http\\Controllers\\WalletController',
        'action' => 'getByReference',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'type' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'reference' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/payment/user' => 
      array (
        'method' => 'GET',
        'path' => '/api/payment/user',
        'controller' => 'App\\Http\\Controllers\\WalletController',
        'action' => 'getPaymentsByUser',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'type' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'page' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/payment/type' => 
      array (
        'method' => 'GET',
        'path' => '/api/payment/type',
        'controller' => 'App\\Http\\Controllers\\WalletController',
        'action' => 'getPaymentsByType',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
            2 => 
            array (
              'role' => 
              array (
                0 => 'admin',
              ),
            ),
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'type' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'page' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/payment/status' => 
      array (
        'method' => 'GET',
        'path' => '/api/payment/status',
        'controller' => 'App\\Http\\Controllers\\WalletController',
        'action' => 'getPaymentsByStatus',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
            2 => 
            array (
              'role' => 
              array (
                0 => 'admin',
              ),
            ),
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'table' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'status' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'page' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/payment/channel' => 
      array (
        'method' => 'GET',
        'path' => '/api/payment/channel',
        'controller' => 'App\\Http\\Controllers\\WalletController',
        'action' => 'getPaymentsByChannel',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
            2 => 
            array (
              'role' => 
              array (
                0 => 'admin',
                1 => 'worker',
              ),
            ),
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'channel' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'status' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'page' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/payment/payouts/status' => 
      array (
        'method' => 'GET',
        'path' => '/api/payment/payouts/status',
        'controller' => 'App\\Http\\Controllers\\WalletController',
        'action' => 'getPayoutsByStatus',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
            2 => 
            array (
              'role' => 
              array (
                0 => 'admin',
              ),
            ),
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'status' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'page' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/payment/summary' => 
      array (
        'method' => 'GET',
        'path' => '/api/payment/summary',
        'controller' => 'App\\Http\\Controllers\\WalletController',
        'action' => 'getPaymentSummary',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
            2 => 
            array (
              'role' => 
              array (
                0 => 'admin',
              ),
            ),
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'view' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'period' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'start' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'end' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/mail/inbox/count' => 
      array (
        'method' => 'GET',
        'path' => '/api/mail/inbox/count',
        'controller' => 'App\\Http\\Controllers\\MailController',
        'action' => 'countInbox',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
          ),
        ),
      ),
      '/api/mail/outbox/count' => 
      array (
        'method' => 'GET',
        'path' => '/api/mail/outbox/count',
        'controller' => 'App\\Http\\Controllers\\MailController',
        'action' => 'countOutbox',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
          ),
        ),
      ),
      '/api/mail/inbox' => 
      array (
        'method' => 'GET',
        'path' => '/api/mail/inbox',
        'controller' => 'App\\Http\\Controllers\\MailController',
        'action' => 'getInbox',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'page' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/mail/outbox' => 
      array (
        'method' => 'GET',
        'path' => '/api/mail/outbox',
        'controller' => 'App\\Http\\Controllers\\MailController',
        'action' => 'getOutbox',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'page' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/mail' => 
      array (
        'method' => 'GET',
        'path' => '/api/mail',
        'controller' => 'App\\Http\\Controllers\\MailController',
        'action' => 'getMail',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'id' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/task' => 
      array (
        'method' => 'GET',
        'path' => '/api/task',
        'controller' => 'App\\Http\\Controllers\\TaskController',
        'action' => 'findOne',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'id' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/task/load' => 
      array (
        'method' => 'GET',
        'path' => '/api/task/load',
        'controller' => 'App\\Http\\Controllers\\TaskController',
        'action' => 'loadTask',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'page' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/task/list/status' => 
      array (
        'method' => 'GET',
        'path' => '/api/task/list/status',
        'controller' => 'App\\Http\\Controllers\\TaskController',
        'action' => 'findByStatus',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'status' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'page' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/task/attempt' => 
      array (
        'method' => 'GET',
        'path' => '/api/task/attempt',
        'controller' => 'App\\Http\\Controllers\\TaskController',
        'action' => 'getAttempt',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
            2 => 
            array (
              'role' => 
              array (
                0 => 'admin',
                1 => 'worker',
              ),
            ),
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'id' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
                'page' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/task/description' => 
      array (
        'method' => 'GET',
        'path' => '/api/task/description',
        'controller' => 'App\\Http\\Controllers\\TaskController',
        'action' => 'getDescription',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'id' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
              ),
            ),
          ),
        ),
      ),
    ),
    'POST' => 
    array (
      '/api/user/register' => 
      array (
        'method' => 'POST',
        'path' => '/api/user/register',
        'controller' => 'App\\Http\\Controllers\\UserController',
        'action' => 'register',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'avatar' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'firstname' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'lastname' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'email' => 
                array (
                  0 => 'required',
                  1 => 'email',
                ),
                'contact' => 
                array (
                  0 => 'required',
                  1 => 'string',
                  2 => 'min:7',
                ),
                'country' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'password' => 
                array (
                  0 => 'required',
                  1 => 'string',
                  2 => 'min:6',
                ),
                'membership' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'code' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'currency' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'narration' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'facilitator' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'id' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
                'amount' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
                'receipt' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'state' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/user/login' => 
      array (
        'method' => 'POST',
        'path' => '/api/user/login',
        'controller' => 'App\\Http\\Controllers\\UserController',
        'action' => 'login',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'email' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'password' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/user/otp' => 
      array (
        'method' => 'POST',
        'path' => '/api/user/otp',
        'controller' => 'App\\Http\\Controllers\\UserController',
        'action' => 'otp',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'email' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/user/logout' => 
      array (
        'method' => 'POST',
        'path' => '/api/user/logout',
        'controller' => 'App\\Http\\Controllers\\UserController',
        'action' => 'logout',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
          ),
        ),
      ),
      '/api/user/contact' => 
      array (
        'method' => 'POST',
        'path' => '/api/user/contact',
        'controller' => 'App\\Http\\Controllers\\UserController',
        'action' => 'contact',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'name' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'email' => 
                array (
                  0 => 'required',
                  1 => 'email',
                ),
                'contact' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'country' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'subject' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'message' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'code' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/user/subscribe' => 
      array (
        'method' => 'POST',
        'path' => '/api/user/subscribe',
        'controller' => 'App\\Http\\Controllers\\UserController',
        'action' => 'subscribe',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'token' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'device_id' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/notification' => 
      array (
        'method' => 'POST',
        'path' => '/api/notification',
        'controller' => 'App\\Http\\Controllers\\NotificationController',
        'action' => 'create',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
        ),
      ),
      '/api/category' => 
      array (
        'method' => 'POST',
        'path' => '/api/category',
        'controller' => 'App\\Http\\Controllers\\CategoryController',
        'action' => 'create',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
            2 => 
            array (
              'role' => 
              array (
                0 => 'admin',
              ),
            ),
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'category' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/blog' => 
      array (
        'method' => 'POST',
        'path' => '/api/blog',
        'controller' => 'App\\Http\\Controllers\\BlogController',
        'action' => 'createBlog',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
            2 => 
            array (
              'role' => 
              array (
                0 => 'admin',
                1 => 'worker',
              ),
            ),
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'banner' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'title' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'category' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'article' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/payment/create' => 
      array (
        'method' => 'POST',
        'path' => '/api/payment/create',
        'controller' => 'App\\Http\\Controllers\\WalletController',
        'action' => 'createPayment',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'amount' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
                'channel' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'facilitatorId' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
                'identifier' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'narration' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'receipt' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/payment/verify/auto' => 
      array (
        'method' => 'POST',
        'path' => '/api/payment/verify/auto',
        'controller' => 'App\\Http\\Controllers\\WalletController',
        'action' => 'verifyPaymentAuto',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'id' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
                'reference' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/payment/verify/manual' => 
      array (
        'method' => 'POST',
        'path' => '/api/payment/verify/manual',
        'controller' => 'App\\Http\\Controllers\\WalletController',
        'action' => 'verifyPaymentManual',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'reference' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/payment/withdraw' => 
      array (
        'method' => 'POST',
        'path' => '/api/payment/withdraw',
        'controller' => 'App\\Http\\Controllers\\WalletController',
        'action' => 'requestFunds',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'amount' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
                'narration' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'description' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/payment/transfer/single' => 
      array (
        'method' => 'POST',
        'path' => '/api/payment/transfer/single',
        'controller' => 'App\\Http\\Controllers\\WalletController',
        'action' => 'singleTransfer',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
            2 => 
            array (
              'role' => 
              array (
                0 => 'admin',
              ),
            ),
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'bank' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'account' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'amount' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
                'narration' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'currency' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'reference' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/payment/transfer/bulk' => 
      array (
        'method' => 'POST',
        'path' => '/api/payment/transfer/bulk',
        'controller' => 'App\\Http\\Controllers\\WalletController',
        'action' => 'bulkTransfer',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
            2 => 
            array (
              'role' => 
              array (
                0 => 'admin',
              ),
            ),
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'title' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'bulk_data' => 
                array (
                  0 => 'required',
                  1 => 'array',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/mail/send' => 
      array (
        'method' => 'POST',
        'path' => '/api/mail/send',
        'controller' => 'App\\Http\\Controllers\\MailController',
        'action' => 'sendBulk',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
            2 => 
            array (
              'role' => 
              array (
                0 => 'admin',
              ),
            ),
          ),
        ),
      ),
      '/api/mail/subscribe' => 
      array (
        'method' => 'POST',
        'path' => '/api/mail/subscribe',
        'controller' => 'App\\Http\\Controllers\\MailController',
        'action' => 'subscribeMail',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'email' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/task' => 
      array (
        'method' => 'POST',
        'path' => '/api/task',
        'controller' => 'App\\Http\\Controllers\\TaskController',
        'action' => 'createTask',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
            2 => 
            array (
              'role' => 
              array (
                0 => 'admin',
                1 => 'worker',
              ),
            ),
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'name' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'description' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'reward' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
                'start' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'end' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/task/attempt' => 
      array (
        'method' => 'POST',
        'path' => '/api/task/attempt',
        'controller' => 'App\\Http\\Controllers\\TaskController',
        'action' => 'attemptTask',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'link' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'id' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
              ),
            ),
          ),
        ),
      ),
    ),
    'PUT' => 
    array (
      '/api/user/password/reset' => 
      array (
        'method' => 'PUT',
        'path' => '/api/user/password/reset',
        'controller' => 'App\\Http\\Controllers\\UserController',
        'action' => 'reset',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'email' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'password' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'otp' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/user/update' => 
      array (
        'method' => 'PUT',
        'path' => '/api/user/update',
        'controller' => 'App\\Http\\Controllers\\UserController',
        'action' => 'update',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\CsrfMiddleware',
            1 => 'handle',
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'bio' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/user/profile' => 
      array (
        'method' => 'PUT',
        'path' => '/api/user/profile',
        'controller' => 'App\\Http\\Controllers\\UserController',
        'action' => 'profile',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\CsrfMiddleware',
            1 => 'handle',
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'avatar' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/user/social' => 
      array (
        'method' => 'PUT',
        'path' => '/api/user/social',
        'controller' => 'App\\Http\\Controllers\\UserController',
        'action' => 'social',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\CsrfMiddleware',
            1 => 'handle',
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'facebook' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'instagram' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'tiktok' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'twitter' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/user/password/change' => 
      array (
        'method' => 'PUT',
        'path' => '/api/user/password/change',
        'controller' => 'App\\Http\\Controllers\\UserController',
        'action' => 'password',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\CsrfMiddleware',
            1 => 'handle',
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'password' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'newpassword' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/user/status' => 
      array (
        'method' => 'PUT',
        'path' => '/api/user/status',
        'controller' => 'App\\Http\\Controllers\\UserController',
        'action' => 'status',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
            2 => 
            array (
              'role' => 
              array (
                0 => 'admin',
              ),
            ),
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\CsrfMiddleware',
            1 => 'handle',
          ),
          3 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'status' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'id' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/user/switch' => 
      array (
        'method' => 'PUT',
        'path' => '/api/user/switch',
        'controller' => 'App\\Http\\Controllers\\UserController',
        'action' => 'switch',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'email' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'role' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/notification/mark/read' => 
      array (
        'method' => 'PUT',
        'path' => '/api/notification/mark/read',
        'controller' => 'App\\Http\\Controllers\\NotificationController',
        'action' => 'markAsRead',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\CsrfMiddleware',
            1 => 'handle',
          ),
        ),
      ),
      '/api/category' => 
      array (
        'method' => 'PUT',
        'path' => '/api/category',
        'controller' => 'App\\Http\\Controllers\\CategoryController',
        'action' => 'update',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
            2 => 
            array (
              'role' => 
              array (
                0 => 'admin',
              ),
            ),
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\CsrfMiddleware',
            1 => 'handle',
          ),
          3 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'name' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'id' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/blog/details' => 
      array (
        'method' => 'PUT',
        'path' => '/api/blog/details',
        'controller' => 'App\\Http\\Controllers\\BlogController',
        'action' => 'updateDetails',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
            2 => 
            array (
              'role' => 
              array (
                0 => 'admin',
                1 => 'worker',
              ),
            ),
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\CsrfMiddleware',
            1 => 'handle',
          ),
          3 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'title' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'article' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'id' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/blog/banner' => 
      array (
        'method' => 'PUT',
        'path' => '/api/blog/banner',
        'controller' => 'App\\Http\\Controllers\\BlogController',
        'action' => 'updateBanner',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
            2 => 
            array (
              'role' => 
              array (
                0 => 'admin',
                1 => 'worker',
              ),
            ),
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\CsrfMiddleware',
            1 => 'handle',
          ),
          3 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'id' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
                'url' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/blog/status' => 
      array (
        'method' => 'PUT',
        'path' => '/api/blog/status',
        'controller' => 'App\\Http\\Controllers\\BlogController',
        'action' => 'updateStatus',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
            2 => 
            array (
              'role' => 
              array (
                0 => 'admin',
                1 => 'worker',
              ),
            ),
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\CsrfMiddleware',
            1 => 'handle',
          ),
          3 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'status' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'id' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/blog/stat' => 
      array (
        'method' => 'PUT',
        'path' => '/api/blog/stat',
        'controller' => 'App\\Http\\Controllers\\BlogController',
        'action' => 'updateCounter',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'id' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
                'type' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/payment/details/update' => 
      array (
        'method' => 'PUT',
        'path' => '/api/payment/details/update',
        'controller' => 'App\\Http\\Controllers\\WalletController',
        'action' => 'updateDetails',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\CsrfMiddleware',
            1 => 'handle',
          ),
          3 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'account' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
                'bank' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'code' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/task/details' => 
      array (
        'method' => 'PUT',
        'path' => '/api/task/details',
        'controller' => 'App\\Http\\Controllers\\TaskController',
        'action' => 'updateDetails',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
            2 => 
            array (
              'role' => 
              array (
                0 => 'admin',
                1 => 'worker',
              ),
            ),
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'id' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
                'name' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'description' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'reward' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
                'start' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'end' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'status' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/task/status' => 
      array (
        'method' => 'PUT',
        'path' => '/api/task/status',
        'controller' => 'App\\Http\\Controllers\\TaskController',
        'action' => 'updateStatus',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
            2 => 
            array (
              'role' => 
              array (
                0 => 'admin',
                1 => 'worker',
              ),
            ),
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'status' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'id' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/task/status/all' => 
      array (
        'method' => 'PUT',
        'path' => '/api/task/status/all',
        'controller' => 'App\\Http\\Controllers\\TaskController',
        'action' => 'updateAll',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'status' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/task/finalize' => 
      array (
        'method' => 'PUT',
        'path' => '/api/task/finalize',
        'controller' => 'App\\Http\\Controllers\\TaskController',
        'action' => 'finalizeAttempt',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
            2 => 
            array (
              'role' => 
              array (
                0 => 'admin',
                1 => 'worker',
              ),
            ),
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'id' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
                'user' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
                'status' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
              ),
            ),
          ),
        ),
      ),
    ),
    'DELETE' => 
    array (
      '/api/user/unsubscribe' => 
      array (
        'method' => 'DELETE',
        'path' => '/api/user/unsubscribe',
        'controller' => 'App\\Http\\Controllers\\UserController',
        'action' => 'unsubscribe',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\CsrfMiddleware',
            1 => 'handle',
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'token' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
                'device_id' => 
                array (
                  0 => 'required',
                  1 => 'string',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/user/delete' => 
      array (
        'method' => 'DELETE',
        'path' => '/api/user/delete',
        'controller' => 'App\\Http\\Controllers\\UserController',
        'action' => 'delete',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
            2 => 
            array (
              'role' => 
              array (
                0 => 'admin',
              ),
            ),
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\CsrfMiddleware',
            1 => 'handle',
          ),
          3 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'id' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/category' => 
      array (
        'method' => 'DELETE',
        'path' => '/api/category',
        'controller' => 'App\\Http\\Controllers\\CategoryController',
        'action' => 'delete',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
            2 => 
            array (
              'role' => 
              array (
                0 => 'admin',
              ),
            ),
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\CsrfMiddleware',
            1 => 'handle',
          ),
          3 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'id' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/blog' => 
      array (
        'method' => 'DELETE',
        'path' => '/api/blog',
        'controller' => 'App\\Http\\Controllers\\BlogController',
        'action' => 'deleteBlog',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
            2 => 
            array (
              'role' => 
              array (
                0 => 'admin',
                1 => 'worker',
              ),
            ),
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\CsrfMiddleware',
            1 => 'handle',
          ),
          3 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'id' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/mail' => 
      array (
        'method' => 'DELETE',
        'path' => '/api/mail',
        'controller' => 'App\\Http\\Controllers\\MailController',
        'action' => 'deleteMail',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
            2 => 
            array (
              'role' => 
              array (
                0 => 'admin',
              ),
            ),
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\CsrfMiddleware',
            1 => 'handle',
          ),
          3 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'id' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
              ),
            ),
          ),
        ),
      ),
      '/api/task' => 
      array (
        'method' => 'DELETE',
        'path' => '/api/task',
        'controller' => 'App\\Http\\Controllers\\TaskController',
        'action' => 'deleteTask',
        'middlewares' => 
        array (
          0 => 
          array (
            0 => 'App\\Http\\Middlewares\\RateLimitMiddleware',
            1 => 'handle',
            2 => 
            array (
              'scope' => 'api',
              'userLimit' => 100,
              'anonLimit' => 20,
            ),
          ),
          1 => 
          array (
            0 => 'App\\Http\\Middlewares\\AuthMiddleware',
            1 => 'handle',
            2 => 
            array (
              'role' => 
              array (
                0 => 'admin',
                1 => 'worker',
              ),
            ),
          ),
          2 => 
          array (
            0 => 'App\\Http\\Middlewares\\CsrfMiddleware',
            1 => 'handle',
          ),
          3 => 
          array (
            0 => 'App\\Http\\Middlewares\\ValidationMiddleware',
            1 => 'handle',
            2 => 
            array (
              'rules' => 
              array (
                'id' => 
                array (
                  0 => 'required',
                  1 => 'number',
                ),
              ),
            ),
          ),
        ),
      ),
    ),
  ),
  'dynamic' => 
  array (
  ),
);