import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Auth\RegisteredUserController::create
 * @see app/Http/Controllers/Auth/RegisteredUserController.php:20
 * @route '//pupsraes.test/register'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '//pupsraes.test/register',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Auth\RegisteredUserController::create
 * @see app/Http/Controllers/Auth/RegisteredUserController.php:20
 * @route '//pupsraes.test/register'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\RegisteredUserController::create
 * @see app/Http/Controllers/Auth/RegisteredUserController.php:20
 * @route '//pupsraes.test/register'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Auth\RegisteredUserController::create
 * @see app/Http/Controllers/Auth/RegisteredUserController.php:20
 * @route '//pupsraes.test/register'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Auth\RegisteredUserController::create
 * @see app/Http/Controllers/Auth/RegisteredUserController.php:20
 * @route '//pupsraes.test/register'
 */
    const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Auth\RegisteredUserController::create
 * @see app/Http/Controllers/Auth/RegisteredUserController.php:20
 * @route '//pupsraes.test/register'
 */
        createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Auth\RegisteredUserController::create
 * @see app/Http/Controllers/Auth/RegisteredUserController.php:20
 * @route '//pupsraes.test/register'
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
* @see \App\Http\Controllers\Auth\RegisteredUserController::store
 * @see app/Http/Controllers/Auth/RegisteredUserController.php:30
 * @route '//pupsraes.test/register'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '//pupsraes.test/register',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Auth\RegisteredUserController::store
 * @see app/Http/Controllers/Auth/RegisteredUserController.php:30
 * @route '//pupsraes.test/register'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\RegisteredUserController::store
 * @see app/Http/Controllers/Auth/RegisteredUserController.php:30
 * @route '//pupsraes.test/register'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Auth\RegisteredUserController::store
 * @see app/Http/Controllers/Auth/RegisteredUserController.php:30
 * @route '//pupsraes.test/register'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Auth\RegisteredUserController::store
 * @see app/Http/Controllers/Auth/RegisteredUserController.php:30
 * @route '//pupsraes.test/register'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
const RegisteredUserController = { create, store }

export default RegisteredUserController