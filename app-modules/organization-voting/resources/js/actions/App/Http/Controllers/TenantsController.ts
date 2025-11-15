import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\TenantsController::create
 * @see app/Http/Controllers/TenantsController.php:19
 * @route '//pupsrc-aes.test/tenants/create'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '//pupsrc-aes.test/tenants/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\TenantsController::create
 * @see app/Http/Controllers/TenantsController.php:19
 * @route '//pupsrc-aes.test/tenants/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\TenantsController::create
 * @see app/Http/Controllers/TenantsController.php:19
 * @route '//pupsrc-aes.test/tenants/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\TenantsController::create
 * @see app/Http/Controllers/TenantsController.php:19
 * @route '//pupsrc-aes.test/tenants/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\TenantsController::create
 * @see app/Http/Controllers/TenantsController.php:19
 * @route '//pupsrc-aes.test/tenants/create'
 */
    const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\TenantsController::create
 * @see app/Http/Controllers/TenantsController.php:19
 * @route '//pupsrc-aes.test/tenants/create'
 */
        createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\TenantsController::create
 * @see app/Http/Controllers/TenantsController.php:19
 * @route '//pupsrc-aes.test/tenants/create'
 */
        createForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    create.form = createForm
const TenantsController = { create }

export default TenantsController