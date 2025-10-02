import Auth from './Auth'
import Settings from './Settings'
import TenantsController from './TenantsController'
const Controllers = {
    Auth: Object.assign(Auth, Auth),
Settings: Object.assign(Settings, Settings),
TenantsController: Object.assign(TenantsController, TenantsController),
}

export default Controllers