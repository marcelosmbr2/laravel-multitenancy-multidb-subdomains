<?php

use Spatie\Multitenancy\Jobs\TenantAware;
use Illuminate\Broadcasting\BroadcastEvent;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Mail\SendQueuedMailable;
use Spatie\Multitenancy\Jobs\NotTenantAware;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Queue\CallQueuedClosure;
use Spatie\Multitenancy\Actions\ForgetCurrentTenantAction;
use Spatie\Multitenancy\Actions\MakeQueueTenantAwareAction;
use Spatie\Multitenancy\Actions\MakeTenantCurrentAction;
use Spatie\Multitenancy\Actions\MigrateTenantAction;

return [
    /*
     * This class is responsible for determining which tenant should be current
     * for the given request.
     *
     * This class should extend `Spatie\Multitenancy\TenantFinder\TenantFinder`
     *
     * This application deliberately leaves this `null` even though the tenant is now identified by
     * the host. `Multitenancy::configureRequests()` only calls the finder when the application is
     * not `runningInConsole()`, and it does so once, during the service provider boot phase. Under
     * PHPUnit `PHP_SAPI` is `cli`, so a finder would never run in a feature test. The subdomain is
     * resolved by `App\Http\Middleware\IdentifyTenantBySubdomain` instead, prepended to the `web`
     * group, which runs on every request no matter how it was made.
     */
    'tenant_finder' => null,

    /*
     * These fields are used by tenant:artisan command to match one or more tenant.
     */
    'tenant_artisan_search_fields' => [
        'id',
        'slug',
    ],

    /*
     * These tasks will be performed when switching tenants.
     *
     * A valid task is any class that implements Spatie\Multitenancy\Tasks\SwitchTenantTask
     *
     * `PrefixCacheTask` is intentionally left out: the cache store is `database` and lives in the
     * landlord database, and prefixing the cache per tenant only really pays off with a
     * memory-based store such as Redis.
     */
    'switch_tenant_tasks' => [
        \Spatie\Multitenancy\Tasks\SwitchTenantDatabaseTask::class,
    ],

    /*
     * This class is the model used for storing configuration on tenants.
     *
     * It must  extend `Spatie\Multitenancy\Models\Tenant::class` or
     * implement `Spatie\Multitenancy\Contracts\IsTenant::class` interface
     */
    'tenant_model' => \App\Models\Company::class,

    /*
     * The directory, relative to the project root, that holds the SQLite file of each tenant.
     *
     * This key is not part of the package: it is read by `App\Models\Company::getDatabaseName()`
     * to turn the `database` column into an absolute SQLite path.
     */
    'tenant_database_directory' => env('TENANT_DATABASE_DIRECTORY', 'database/tenants'),

    /*
     * The bare host the application answers on. Every company gets `{slug}.{central_domain}`.
     *
     * This key is not part of the package either. It holds the host only, without a port: route
     * domains are matched against `Request::getHost()`, and the URL generator re-appends the port
     * of the current request when it builds an absolute URL.
     *
     * `APP_URL` is the single source of truth, with `CENTRAL_DOMAIN` as an escape hatch for setups
     * where the public host differs from the one the application generates links with.
     */
    'central_domain' => env('CENTRAL_DOMAIN') ?: parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST),

    /*
     * If there is a current tenant when dispatching a job, the id of the current tenant
     * will be automatically set on the job. When the job is executed, the set
     * tenant on the job will be made current.
     */
    'queues_are_tenant_aware_by_default' => true,

    /*
     * The connection name to reach the tenant database.
     *
     * Set to `null` to use the default connection.
     */
    'tenant_database_connection_name' => 'tenant',

    /*
     * The connection name to reach the landlord database.
     */
    'landlord_database_connection_name' => 'landlord',

    /*
     * This key will be used to associate the current tenant in the context
     */
    'current_tenant_context_key' => 'tenantId',

    /*
     * This key will be used to bind the current tenant in the container.
     */
    'current_tenant_container_key' => 'currentTenant',

    /*
     * Set it to `true` if you like to cache the tenant(s) routes
     * in a shared file using the `SwitchRouteCacheTask`.
     */
    'shared_routes_cache' => false,

    /*
     * You can customize some of the behavior of this package by using your own custom action.
     * Your custom action should always extend the default one.
     */
    'actions' => [
        'make_tenant_current_action' => MakeTenantCurrentAction::class,
        'forget_current_tenant_action' => ForgetCurrentTenantAction::class,
        'make_queue_tenant_aware_action' => MakeQueueTenantAwareAction::class,
        'migrate_tenant' => MigrateTenantAction::class,
    ],

    /*
     * You can customize the way in which the package resolves the queueable to a job.
     *
     * For example, using the package laravel-actions (by Loris Leiva), you can
     * resolve JobDecorator to getAction() like so: JobDecorator::class => 'getAction'
     */
    'queueable_to_job' => [
        SendQueuedMailable::class => 'mailable',
        SendQueuedNotifications::class => 'notification',
        CallQueuedClosure::class => 'closure',
        CallQueuedListener::class => 'class',
        BroadcastEvent::class => 'event',
    ],

    /*
    * Interface that once implemented, will make the job tenant aware
    */
    'tenant_aware_interface' => TenantAware::class,

    /*
     * Interface that once implemented, will make the job not tenant aware
     */
    'not_tenant_aware_interface' => NotTenantAware::class,

    /*
     * Jobs tenant aware even if these don't implement the TenantAware interface.
     */
    'tenant_aware_jobs' => [
        // ...
    ],

    /*
     * Jobs not tenant aware even if these don't implement the NotTenantAware interface.
     */
    'not_tenant_aware_jobs' => [
        // ...
    ],
];
