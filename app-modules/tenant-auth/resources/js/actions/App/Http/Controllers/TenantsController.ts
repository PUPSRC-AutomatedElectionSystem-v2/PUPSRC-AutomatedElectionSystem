import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\TenantsController::create
 * @see app/Http/Controllers/TenantsController.php:19
 * @route '//pupsraes.test/tenants/create'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '//pupsraes.test/tenants/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\TenantsController::create
 * @see app/Http/Controllers/TenantsController.php:19
 * @route '//pupsraes.test/tenants/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\TenantsController::create
 * @see app/Http/Controllers/TenantsController.php:19
 * @route '//pupsraes.test/tenants/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\TenantsController::create
 * @see app/Http/Controllers/TenantsController.php:19
 * @route '//pupsraes.test/tenants/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\TenantsController::create
 * @see app/Http/Controllers/TenantsController.php:19
 * @route '//pupsraes.test/tenants/create'
 */
    const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\TenantsController::create
 * @see app/Http/Controllers/TenantsController.php:19
 * @route '//pupsraes.test/tenants/create'
 */
        createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\TenantsController::create
 * @see app/Http/Controllers/TenantsController.php:19
 * @route '//pupsraes.test/tenants/create'
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
/**
* @see \App\Http\Controllers\TenantsController::store
 * @see app/Http/Controllers/TenantsController.php:26
 * @route '//pupsraes.test/tenants'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '//pupsraes.test/tenants',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\TenantsController::store
 * @see app/Http/Controllers/TenantsController.php:26
 * @route '//pupsraes.test/tenants'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\TenantsController::store
 * @see app/Http/Controllers/TenantsController.php:26
 * @route '//pupsraes.test/tenants'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\TenantsController::store
 * @see app/Http/Controllers/TenantsController.php:26
 * @route '//pupsraes.test/tenants'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\TenantsController::store
 * @see app/Http/Controllers/TenantsController.php:26
 * @route '//pupsraes.test/tenants'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
const TenantsController = { create, store }

export default TenantsController