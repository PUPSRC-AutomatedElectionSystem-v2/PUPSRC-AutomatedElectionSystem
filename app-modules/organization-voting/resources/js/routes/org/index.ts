import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
import sanctum from './sanctum'
import register702019 from './register'
/**
* @see \Modules\TenantAuth\Http\Controllers\RegisteredUserController::register
 * @see app-modules/tenant-auth/src/Http/Controllers/RegisteredUserController.php:20
 * @route '/register'
 */
export const register = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: register.url(options),
    method: 'get',
})

register.definition = {
    methods: ["get","head"],
    url: '/register',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Modules\TenantAuth\Http\Controllers\RegisteredUserController::register
 * @see app-modules/tenant-auth/src/Http/Controllers/RegisteredUserController.php:20
 * @route '/register'
 */
register.url = (options?: RouteQueryOptions) => {
    return register.definition.url + queryParams(options)
}

/**
* @see \Modules\TenantAuth\Http\Controllers\RegisteredUserController::register
 * @see app-modules/tenant-auth/src/Http/Controllers/RegisteredUserController.php:20
 * @route '/register'
 */
register.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: register.url(options),
    method: 'get',
})
/**
* @see \Modules\TenantAuth\Http\Controllers\RegisteredUserController::register
 * @see app-modules/tenant-auth/src/Http/Controllers/RegisteredUserController.php:20
 * @route '/register'
 */
register.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: register.url(options),
    method: 'head',
})

    /**
* @see \Modules\TenantAuth\Http\Controllers\RegisteredUserController::register
 * @see app-modules/tenant-auth/src/Http/Controllers/RegisteredUserController.php:20
 * @route '/register'
 */
    const registerForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: register.url(options),
        method: 'get',
    })

            /**
* @see \Modules\TenantAuth\Http\Controllers\RegisteredUserController::register
 * @see app-modules/tenant-auth/src/Http/Controllers/RegisteredUserController.php:20
 * @route '/register'
 */
        registerForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: register.url(options),
            method: 'get',
        })
            /**
* @see \Modules\TenantAuth\Http\Controllers\RegisteredUserController::register
 * @see app-modules/tenant-auth/src/Http/Controllers/RegisteredUserController.php:20
 * @route '/register'
 */
        registerForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: register.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    register.form = registerForm
const org = {
    sanctum: Object.assign(sanctum, sanctum),
register: Object.assign(register, register702019),
}

export default org