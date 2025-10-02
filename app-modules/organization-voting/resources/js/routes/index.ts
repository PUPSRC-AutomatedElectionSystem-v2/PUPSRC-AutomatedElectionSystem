import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../wayfinder'
/**
* @see \App\Http\Controllers\Auth\AuthenticatedSessionController::login
 * @see app/Http/Controllers/Auth/AuthenticatedSessionController.php:20
 * @route '//pupsraes.test/login'
 */
export const login = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: login.url(options),
    method: 'get',
})

login.definition = {
    methods: ["get","head"],
    url: '//pupsraes.test/login',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Auth\AuthenticatedSessionController::login
 * @see app/Http/Controllers/Auth/AuthenticatedSessionController.php:20
 * @route '//pupsraes.test/login'
 */
login.url = (options?: RouteQueryOptions) => {
    return login.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\AuthenticatedSessionController::login
 * @see app/Http/Controllers/Auth/AuthenticatedSessionController.php:20
 * @route '//pupsraes.test/login'
 */
login.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: login.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Auth\AuthenticatedSessionController::login
 * @see app/Http/Controllers/Auth/AuthenticatedSessionController.php:20
 * @route '//pupsraes.test/login'
 */
login.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: login.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Auth\AuthenticatedSessionController::login
 * @see app/Http/Controllers/Auth/AuthenticatedSessionController.php:20
 * @route '//pupsraes.test/login'
 */
    const loginForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: login.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Auth\AuthenticatedSessionController::login
 * @see app/Http/Controllers/Auth/AuthenticatedSessionController.php:20
 * @route '//pupsraes.test/login'
 */
        loginForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: login.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Auth\AuthenticatedSessionController::login
 * @see app/Http/Controllers/Auth/AuthenticatedSessionController.php:20
 * @route '//pupsraes.test/login'
 */
        loginForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: login.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    login.form = loginForm
/**
* @see \App\Http\Controllers\Auth\AuthenticatedSessionController::logout
 * @see app/Http/Controllers/Auth/AuthenticatedSessionController.php:54
 * @route '//pupsraes.test/logout'
 */
export const logout = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
})

logout.definition = {
    methods: ["post"],
    url: '//pupsraes.test/logout',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Auth\AuthenticatedSessionController::logout
 * @see app/Http/Controllers/Auth/AuthenticatedSessionController.php:54
 * @route '//pupsraes.test/logout'
 */
logout.url = (options?: RouteQueryOptions) => {
    return logout.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\AuthenticatedSessionController::logout
 * @see app/Http/Controllers/Auth/AuthenticatedSessionController.php:54
 * @route '//pupsraes.test/logout'
 */
logout.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Auth\AuthenticatedSessionController::logout
 * @see app/Http/Controllers/Auth/AuthenticatedSessionController.php:54
 * @route '//pupsraes.test/logout'
 */
    const logoutForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: logout.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Auth\AuthenticatedSessionController::logout
 * @see app/Http/Controllers/Auth/AuthenticatedSessionController.php:54
 * @route '//pupsraes.test/logout'
 */
        logoutForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: logout.url(options),
            method: 'post',
        })
    
    logout.form = logoutForm
/**
 * @see routes/web.php:7
 * @route '//pupsraes.test/'
 */
export const home = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: home.url(options),
    method: 'get',
})

home.definition = {
    methods: ["get","head"],
    url: '//pupsraes.test/',
} satisfies RouteDefinition<["get","head"]>

/**
 * @see routes/web.php:7
 * @route '//pupsraes.test/'
 */
home.url = (options?: RouteQueryOptions) => {
    return home.definition.url + queryParams(options)
}

/**
 * @see routes/web.php:7
 * @route '//pupsraes.test/'
 */
home.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: home.url(options),
    method: 'get',
})
/**
 * @see routes/web.php:7
 * @route '//pupsraes.test/'
 */
home.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: home.url(options),
    method: 'head',
})

    /**
 * @see routes/web.php:7
 * @route '//pupsraes.test/'
 */
    const homeForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: home.url(options),
        method: 'get',
    })

            /**
 * @see routes/web.php:7
 * @route '//pupsraes.test/'
 */
        homeForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: home.url(options),
            method: 'get',
        })
            /**
 * @see routes/web.php:7
 * @route '//pupsraes.test/'
 */
        homeForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: home.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    home.form = homeForm
/**
 * @see routes/web.php:11
 * @route '//pupsraes.test/dashboard'
 */
export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = {
    methods: ["get","head"],
    url: '//pupsraes.test/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
 * @see routes/web.php:11
 * @route '//pupsraes.test/dashboard'
 */
dashboard.url = (options?: RouteQueryOptions) => {
    return dashboard.definition.url + queryParams(options)
}

/**
 * @see routes/web.php:11
 * @route '//pupsraes.test/dashboard'
 */
dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})
/**
 * @see routes/web.php:11
 * @route '//pupsraes.test/dashboard'
 */
dashboard.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboard.url(options),
    method: 'head',
})

    /**
 * @see routes/web.php:11
 * @route '//pupsraes.test/dashboard'
 */
    const dashboardForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: dashboard.url(options),
        method: 'get',
    })

            /**
 * @see routes/web.php:11
 * @route '//pupsraes.test/dashboard'
 */
        dashboardForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dashboard.url(options),
            method: 'get',
        })
            /**
 * @see routes/web.php:11
 * @route '//pupsraes.test/dashboard'
 */
        dashboardForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dashboard.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    dashboard.form = dashboardForm
/**
 * @see routes/settings.php:22
 * @route '//pupsraes.test/settings/appearance'
 */
export const appearance = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: appearance.url(options),
    method: 'get',
})

appearance.definition = {
    methods: ["get","head"],
    url: '//pupsraes.test/settings/appearance',
} satisfies RouteDefinition<["get","head"]>

/**
 * @see routes/settings.php:22
 * @route '//pupsraes.test/settings/appearance'
 */
appearance.url = (options?: RouteQueryOptions) => {
    return appearance.definition.url + queryParams(options)
}

/**
 * @see routes/settings.php:22
 * @route '//pupsraes.test/settings/appearance'
 */
appearance.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: appearance.url(options),
    method: 'get',
})
/**
 * @see routes/settings.php:22
 * @route '//pupsraes.test/settings/appearance'
 */
appearance.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: appearance.url(options),
    method: 'head',
})

    /**
 * @see routes/settings.php:22
 * @route '//pupsraes.test/settings/appearance'
 */
    const appearanceForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: appearance.url(options),
        method: 'get',
    })

            /**
 * @see routes/settings.php:22
 * @route '//pupsraes.test/settings/appearance'
 */
        appearanceForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: appearance.url(options),
            method: 'get',
        })
            /**
 * @see routes/settings.php:22
 * @route '//pupsraes.test/settings/appearance'
 */
        appearanceForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: appearance.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    appearance.form = appearanceForm
/**
* @see \App\Http\Controllers\Auth\RegisteredUserController::register
 * @see app/Http/Controllers/Auth/RegisteredUserController.php:20
 * @route '//pupsraes.test/register'
 */
export const register = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: register.url(options),
    method: 'get',
})

register.definition = {
    methods: ["get","head"],
    url: '//pupsraes.test/register',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Auth\RegisteredUserController::register
 * @see app/Http/Controllers/Auth/RegisteredUserController.php:20
 * @route '//pupsraes.test/register'
 */
register.url = (options?: RouteQueryOptions) => {
    return register.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\RegisteredUserController::register
 * @see app/Http/Controllers/Auth/RegisteredUserController.php:20
 * @route '//pupsraes.test/register'
 */
register.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: register.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Auth\RegisteredUserController::register
 * @see app/Http/Controllers/Auth/RegisteredUserController.php:20
 * @route '//pupsraes.test/register'
 */
register.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: register.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Auth\RegisteredUserController::register
 * @see app/Http/Controllers/Auth/RegisteredUserController.php:20
 * @route '//pupsraes.test/register'
 */
    const registerForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: register.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Auth\RegisteredUserController::register
 * @see app/Http/Controllers/Auth/RegisteredUserController.php:20
 * @route '//pupsraes.test/register'
 */
        registerForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: register.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Auth\RegisteredUserController::register
 * @see app/Http/Controllers/Auth/RegisteredUserController.php:20
 * @route '//pupsraes.test/register'
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