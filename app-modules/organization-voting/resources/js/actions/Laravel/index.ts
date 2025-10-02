import Sanctum from './Sanctum'
import Fortify from './Fortify'
const Laravel = {
    Sanctum: Object.assign(Sanctum, Sanctum),
Fortify: Object.assign(Fortify, Fortify),
}

export default Laravel