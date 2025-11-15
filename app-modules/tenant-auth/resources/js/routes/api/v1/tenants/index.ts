import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\V01\TenantsController::store
 * @see app/Http/Controllers/Api/V01/TenantsController.php:16
 * @route '//pupsrc-aes.test/api/v1/tenants'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '//pupsrc-aes.test/api/v1/tenants',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Api\V01\TenantsController::store
 * @see app/Http/Controllers/Api/V01/TenantsController.php:16
 * @route '//pupsrc-aes.test/api/v1/tenants'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\V01\TenantsController::store
 * @see app/Http/Controllers/Api/V01/TenantsController.php:16
 * @route '//pupsrc-aes.test/api/v1/tenants'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Api\V01\TenantsController::store
 * @see app/Http/Controllers/Api/V01/TenantsController.php:16
 * @route '//pupsrc-aes.test/api/v1/tenants'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Api\V01\TenantsController::store
 * @see app/Http/Controllers/Api/V01/TenantsController.php:16
 * @route '//pupsrc-aes.test/api/v1/tenants'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
const tenants = {
    store: Object.assign(store, store),
}

export default tenants