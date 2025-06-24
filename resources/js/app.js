require('./bootstrap')

import { createApp } from 'vue'
import App from './vue/App'

import router from './vue/router/index'
import store from './vue/store/index'

import { library } from '@fortawesome/fontawesome-svg-core'
import {
    faAngleLeft,
    faBarcode,
    faBars,
    faBell,
    faBellSlash,
    faBox,
    faBuilding,
    faCaretDown,
    faCaretUp,
    faCircleExclamation,
    faCircleInfo,
    faComment,
    faEnvelope,
    faFileExport,
    faFileImage,
    faFilter,
    faGear,
    faImage,
    faIndustry,
    faKeyboard,
    faLink,
    faLocationDot,
    faLock,
    faMagnifyingGlass,
    faPen,
    faPlus,
    faRightFromBracket,
    faSuitcase,
    faTrash,
    faTruckRampBox,
    faUnlock,
    faUser,
    faUserGear,
    faUsersGear,
    faWarehouse,
    faWindowMaximize,
    faXmark
} from '@fortawesome/free-solid-svg-icons'

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

import VueSweetalert2 from 'vue-sweetalert2'
import 'sweetalert2/dist/sweetalert2.min.css'

import 'animate.css'

const app = createApp(App)

library.add(
    faAngleLeft,
    faBarcode,
    faBars,
    faBell,
    faBellSlash,
    faBox,
    faBuilding,
    faCaretDown,
    faCaretUp,
    faCircleExclamation,
    faCircleInfo,
    faComment,
    faEnvelope,
    faFileExport,
    faFileImage,
    faFilter,
    faGear,
    faImage,
    faIndustry,
    faKeyboard,
    faLink,
    faLocationDot,
    faLock,
    faMagnifyingGlass,
    faPen,
    faPlus,
    faRightFromBracket,
    faSuitcase,
    faTrash,
    faTruckRampBox,
    faUnlock,
    faUser,
    faUserGear,
    faUsersGear,
    faWarehouse,
    faWindowMaximize,
    faXmark
)
app.component('font-awesome-icon', FontAwesomeIcon)

app.use(VueSweetalert2)
app.use(router)
app.use(store)

app.mount('#app')