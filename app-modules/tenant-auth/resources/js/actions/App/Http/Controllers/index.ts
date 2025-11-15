import Auth from './Auth'
import Settings from './Settings'
import TenantsController from './TenantsController'
import Api from './Api'
const Controllers = {
    Auth: Object.assign(Auth, Auth),
Settings: Object.assign(Settings, Settings),
TenantsController: Object.assign(TenantsController, TenantsController),
Api: Object.assign(Api, Api),
}

export default Controllers