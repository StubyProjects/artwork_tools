<template>
    <div>
        <TransitionRoot as="template" :show="sidebarOpen">
            <Dialog as="div" class="fixed inset-0 flex z-40 md:hidden" @close="sidebarOpen = false">
                <TransitionChild as="template" enter="transition-opacity ease-linear duration-300"
                                 enter-from="opacity-0" enter-to="opacity-100"
                                 leave="transition-opacity ease-linear duration-300" leave-from="opacity-100"
                                 leave-to="opacity-0">
                    <DialogOverlay class="fixed inset-0 bg-gray-600 bg-opacity-75"/>
                </TransitionChild>
                <TransitionChild as="template" enter="transition ease-in-out duration-300 transform"
                                 enter-from="-translate-x-full" enter-to="translate-x-0"
                                 leave="transition ease-in-out duration-300 transform" leave-from="translate-x-0"
                                 leave-to="-translate-x-full">
                    <div class="relative flex-1 flex flex-col max-w-xs w-full pt-5 pb-4 bg-primary">
                        <TransitionChild as="template" enter="ease-in-out duration-300" enter-from="opacity-0"
                                         enter-to="opacity-100" leave="ease-in-out duration-300"
                                         leave-from="opacity-100" leave-to="opacity-0">
                            <div class="absolute top-0 right-0 -mr-12 pt-2">
                                <button type="button"
                                        class="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primaryText"
                                        @click="sidebarOpen = false">
                                    <span class="sr-only">Close sidebar</span>
                                    <XIcon class="h-6 w-6 text-primaryText" aria-hidden="true"/>
                                </button>
                            </div>
                        </TransitionChild>
                        <div class="text-2xl ml-4 font-bold text-secondary">
                            <p>ArtWork.tools</p>
                        </div>
                        <div class="mt-5 flex-1 h-0 overflow-y-auto">
                            <nav class="px-2 space-y-1">
                                <a v-for="item in navigation" :key="item.name" :href="item.href"
                                   :class="[isCurrent(item.route) ? 'bg-primaryHover text-secondaryHover' : 'text-secondary hover:bg-primaryHover', 'group flex items-center px-2 py-2 text-base font-medium rounded-md']">
                                    <component :is="item.icon" class="mr-4 flex-shrink-0 h-6 w-6 text-primaryText"
                                               aria-hidden="true"/>
                                    {{ item.name }}
                                </a>
                                <h2 v-on:click="showSystemSettings = !showSystemSettings"
                                    class="text-lg pt-12 pb-2 ml-4 flex font-bold text-secondaryHover">System
                                    <ChevronUpIcon v-if="showSystemSettings"
                                                   class=" ml-1 mr-3 flex-shrink-0 mt-1 h-4 w-4"></ChevronUpIcon>
                                    <ChevronDownIcon v-else
                                                     class=" ml-1 mr-3 flex-shrink-0 mt-1 h-4 w-4"></ChevronDownIcon>
                                </h2>

                                <Link v-if="showSystemSettings" v-for="item in managementNavigation" :key="item.name"
                                   :href="item.href"
                                   :class="[isCurrent(item.route) ? 'bg-primaryHover text-secondaryHover'
                                   :'text-secondary hover:bg-primaryHover',
                                   'group flex items-center px-2 py-2 text-base font-medium rounded-md']">
                                    <component :is="item.icon" class="mr-3 flex-shrink-0 h-6 w-6 text-primaryText"
                                               aria-hidden="true"/>
                                    {{ item.name }}
                                </Link>
                            </nav>
                        </div>
                    </div>
                </TransitionChild>
                <div class="flex-shrink-0 w-14" aria-hidden="true">
                    <!-- Dummy element to force sidebar to shrink to fit close icon -->
                </div>
            </Dialog>
        </TransitionRoot>

        <!-- Static sidebar for desktop -->
        <!-- Sidebar component, swap this element with another sidebar if you like -->
        <div class="flex">
            <div class="hidden w-28 bg-primary md:block bottom-0 top-0 fixed">
                <div class="w-full py-6 mt-12 flex flex-col items-center">
                    <div class="text-2xl font-bold text-secondaryHover">
                        <p>ArtWork</p>
                    </div>
                    <div class="flex-1 mt-12 w-full px-2 space-y-1">
                        <a v-for="item in navigation" :key="item.name" :href="item.href"
                           :class="[isCurrent(item.route) ? 'bg-primaryHover text-secondaryHover' : 'text-secondary hover:bg-primaryHover hover:text-secondaryHover', ' font-semibold group w-full p-3 rounded-md flex flex-col items-center text-sm']">
                            <component :is="item.icon"
                                       :class="[isCurrent(item.route) ? 'text-secondaryHover' : 'text-secondary group-hover:text-secondaryHover', 'h-6 w-6 mb-1']"
                                       aria-hidden="true"/>
                            {{ item.name }}
                        </a>
                        <h2 v-if="$page.props.can.view_users || $page.props.can.view_departments" @click="showSystemSettings = !showSystemSettings"
                            class="text-md pt-12 pb-2 flex items-center justify-center ml-4 font-bold text-secondaryHover cursor-pointer">
                            System
                            <ChevronUpIcon v-if="showSystemSettings"
                                           class=" ml-1 mr-3 flex-shrink-0 mt-1 h-4 w-4"></ChevronUpIcon>
                            <ChevronDownIcon v-else class=" ml-1 mr-3 flex-shrink-0 mt-1 h-4 w-4"></ChevronDownIcon>
                        </h2>

                        <template v-for="item in managementNavigation" :key="item.name">
                            <Link v-if="showSystemSettings && item.has_permission"
                                  :href="item.href"
                                  :class="[isCurrent(item.route) ? 'bg-primaryHover text-secondaryHover' : 'text-secondary hover:bg-primaryHover hover:text-secondaryHover', 'group w-full p-3 rounded-md flex flex-col items-center text-sm font-semibold']">
                                <component :is="item.icon"
                                           :class="[isCurrent(item.route) ? 'text-secondaryHover' : 'text-secondary group-hover:text-secondaryHover', 'h-6 w-6 mb-1']"
                                           aria-hidden="true"/>
                                {{ item.name }}
                            </Link>
                        </template>

                    </div>
                </div>
            </div>

            <div class="md:pl-28 flex flex-col flex-1">
                <div class="sticky top-0 z-10 flex-shrink-0 flex h-16">
                    <button type="button"
                            class="px-4 border-r border-primaryText text-primaryText focus:outline-none md:hidden"
                            @click="sidebarOpen = true">
                        <span class="sr-only">Open sidebar</span>
                        <MenuAlt2Icon class="h-6 w-6" aria-hidden="true"/>
                    </button>
                    <div class="flex-1 px-4 flex justify-end">
                        <div class="ml-4 flex items-center md:ml-6">
                            <button type="button"
                                    class="p-1 rounded-full text-black hover:text-primaryText focus:outline-none">
                                <span class="sr-only">View notifications</span>
                                <BellIcon class="h-6 w-6" aria-hidden="true"/>
                            </button>
                            <Menu as="div" class="ml-3 relative">
                                <div>
                                    <MenuButton @click="showUserMenu = !showUserMenu"
                                                class="flex items-center rounded-full focus:outline-none">
                                        <span class="sr-only">Open user menu</span>
                                        <p class="subpixel-antialiased flex mr-4 pl-2">Hallo
                                            {{ $page.props.user.first_name }}
                                            <ChevronUpIcon v-if="showUserMenu"
                                                           class="ml-1 flex-shrink-0 mt-1 h-4 w-4"></ChevronUpIcon>
                                            <ChevronDownIcon v-else
                                                             class="ml-1 flex-shrink-0 mt-1 h-4 w-4"></ChevronDownIcon>
                                        </p>
                                        <img class="h-10 w-10 rounded-full"
                                             :src="$page.props.user.profile_photo_url"
                                             alt=""/>
                                    </MenuButton>
                                </div>
                                <transition enter-active-class="transition ease-out duration-100"
                                            enter-from-class="transform opacity-0 scale-95"
                                            enter-to-class="transform opacity-100 scale-100"
                                            leave-active-class="transition ease-in duration-75"
                                            leave-from-class="transform opacity-100 scale-100"
                                            leave-to-class="transform opacity-0 scale-95">
                                    <MenuItems
                                        class="origin-top-right absolute right-0 mt-2 w-48 shadow-lg py-1 bg-primary ring-1 ring-black ring-opacity-5 focus:outline-none">
                                        <MenuItem v-slot="{ active }">
                                            <Link :href="route('profile.show')"
                                                  :class="[active ? 'bg-primaryHover text-secondaryHover' : 'text-secondary', 'group flex items-center px-4 py-2 text-sm subpixel-antialiased']">
                                                Dein Konto
                                            </Link>
                                        </MenuItem>
                                        <MenuItem v-slot="{ active }">
                                            <Link :href="route('profile.show')"
                                                  :class="[active ? 'bg-primaryHover text-secondaryHover' : 'text-secondary', 'group flex items-center px-4 py-2 text-sm subpixel-antialiased']">
                                                Benachrichtigungen
                                            </Link>
                                        </MenuItem>
                                        <MenuItem v-slot="{ active }">
                                            <a @click="logout"
                                               :class="[active ? 'bg-primaryHover text-secondaryHover' : 'text-secondary', 'group flex items-center px-4 py-2 text-sm subpixel-antialiased']">Ausloggen</a>
                                        </MenuItem>
                                    </MenuItems>
                                </transition>
                            </Menu>
                        </div>
                    </div>
                </div>
                <main class="flex-1 h-full w-full overflow-auto">
                    <slot></slot>
                </main>
            </div>
        </div>
    </div>
</template>

<script>
import {ref} from 'vue'
import {
    Dialog,
    DialogOverlay,
    Menu,
    MenuButton,
    MenuItem,
    MenuItems,
    TransitionChild,
    TransitionRoot,
} from '@headlessui/vue'
import {
    BellIcon,
    HomeIcon,
    MenuAlt2Icon,
    CalendarIcon,
    XIcon,
    ArrowCircleRightIcon,
    ClipboardCheckIcon,
    ChevronDownIcon,
    ChevronUpIcon,
} from '@heroicons/vue/outline'
import {SearchIcon} from '@heroicons/vue/solid'
import {Link} from "@inertiajs/inertia-vue3";

const navigation = [
    {name: 'Dashboard', href: route('dashboard'), route: ['/dashboard'], icon: HomeIcon},
    {name: 'Projekte', href: '#', route: [], icon: ArrowCircleRightIcon},
    {name: 'Raumbelegung', href: '#', route: [], icon: CalendarIcon,},
    {name: 'Aufgaben', href: '#', route: [], icon: ClipboardCheckIcon,},
]

const userNavigation = [
    {name: 'Your Profile', href: '#'},
    {name: 'Settings', href: '#'},
]

export default {
    components: {
        Dialog,
        DialogOverlay,
        Menu,
        MenuButton,
        MenuItem,
        MenuItems,
        TransitionChild,
        TransitionRoot,
        BellIcon,
        MenuAlt2Icon,
        SearchIcon,
        XIcon,
        ChevronDownIcon,
        ChevronUpIcon,
        Link
    },
    computed: {
        managementNavigation() {
            return [
                {
                    has_permission: this.$page.props.can.view_users,
                    name: 'Nutzer*innen',
                    href: route('users'),
                    route: ['/users'],
                    icon: ArrowCircleRightIcon
                },
                {
                    name: 'Teams',
                    has_permission: this.$page.props.can.view_departments,
                    href: route('departments'),
                    route: ['/departments'],
                    icon: ArrowCircleRightIcon
                },
            ]
        }
    },
    methods: {
        isCurrent(routes) {
            for (let url of routes) {
                if (this.$page.url === url) {
                    return true
                }
            }
        },
        logout() {
            this.$inertia.post(route('logout'))
        }
    },
    data() {
        return {
            showSystemSettings: true,
            showUserMenu: false,
        }
    },
    setup() {
        const sidebarOpen = ref(false)

        return {
            navigation,
            userNavigation,
            sidebarOpen,
        }
    },
}
</script>
