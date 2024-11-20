<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Horizon Redis Connection
    |--------------------------------------------------------------------------
    |
    | This is the name of the Redis connection where Horizon will store the
    | meta information required for it to function. It includes the list
    | of supervisors, failed jobs, job metrics, and other information.
    |
    */

    'use' => env('HORIZON_REDIS_CONNECTION', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Queue Wait Time Thresholds
    |--------------------------------------------------------------------------
    |
    | This option allows you to configure when the LongWaitDetected event
    | will be fired. Every connection / queue combination may have its
    | own, unique threshold (in seconds) before this event is fired.
    |
    */

    'waits' => [
        env('HORIZON_CONNECTION', 'rabbitmq') . ':' . env('RABBITMQ_QUEUE', 'default') => env('HORIZON_WAIT_TIME', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Job Trimming Times
    |--------------------------------------------------------------------------
    |
    | Here you can configure for how long (in minutes) you desire Horizon to
    | keep recent and failed jobs. Typically, recent jobs are kept for one
    | hour while all failed jobs are stored for an entire week.
    |
    */

    'trim' => [
        'recent' => 60,
        'failed' => 10080,
    ],

    /*
    |--------------------------------------------------------------------------
    | Fast Termination
    |--------------------------------------------------------------------------
    |
    | When this option is enabled, Horizon's "terminate" command will not
    | wait on all of the worker processes to terminate unless the workers
    | have been running for at least 3 seconds. This can speed things up.
    |
    */

    'fast_termination' => false,

    /*
    |--------------------------------------------------------------------------
    | Environments
    |--------------------------------------------------------------------------
    |
    | Here you may define the queue worker settings used by your application
    | in all environments. These supervisors and settings handle all your
    | queued jobs and will be provisioned by Horizon during deployment.
    |
    */

    'environments' => [
        'production' => [
            'supervisor-1' => [
                'connection' => env('HORIZON_CONNECTION', 'rabbitmq'),
                'queue' => [env('RABBITMQ_QUEUE', 'default')],
                'balance' => env('HORIZON_BALANCE', 'simple'),
                'processes' => env('HORIZON_PROCESSES', 3),
                'tries' => env('HORIZON_TRIES', 3),
            ],
        ],

        'local' => [
            'supervisor-1' => [
                'connection' => env('HORIZON_CONNECTION', 'rabbitmq'),
                'queue' => [env('RABBITMQ_QUEUE', 'order_queue')],
                'balance' => env('HORIZON_BALANCE', 'simple'),
                'processes' => env('HORIZON_PROCESSES_LOCAL', 1),
                'tries' => env('HORIZON_TRIES_LOCAL', 3),
            ],
        ],

    ],

];
