<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @php
                        $userRole = auth()->user()->role->name;
                    @endphp

                    @if($userRole === 'System_Admin')
                        <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">{{ __('ຈັດການຜູ້ໃຊ້') }}</x-nav-link>
                        <x-nav-link :href="route('admin.roles.index')" :active="request()->routeIs('admin.roles.*')">{{ __('ຈັດການບົດບາດ') }}</x-nav-link>
                        <x-nav-link :href="route('admin.departments.index')" :active="request()->routeIs('admin.departments.*')">{{ __('ຈັດການພາກສ່ວນ') }}</x-nav-link>
                        <x-nav-link :href="route('admin.document-types.index')" :active="request()->routeIs('admin.document-types.*')">{{ __('ຈັດການປະເພດເອກະສານ') }}</x-nav-link>

                    @elseif($userRole === 'Staff')
                        <x-nav-link :href="route('staff.documents.index')" :active="request()->routeIs('staff.documents.index')">{{ __('ໜ້າຈັດການເອກະສານ') }}</x-nav-link>
                        <x-nav-link :href="route('staff.history.approved')" :active="request()->routeIs('staff.history.approved')">{{ __('ເອກະສານທີ່ໄດ້ສະເໜີທັງໝົດ') }}</x-nav-link>
                        <x-nav-link :href="route('staff.history.rejected')" :active="request()->routeIs('staff.history.rejected')">{{ __('ເອກະສານທີ່ຕ້ອງໄດ້ປັບປຸງຄືນ/ຖືກປະຕິເສດ') }}</x-nav-link>

                    @elseif($userRole === 'Dean_Secretary')
                        <x-nav-link :href="route('secretary.dashboard')" :active="request()->routeIs('secretary.dashboard')">{{ __('ໜ້າຫຼັກເລຂາ') }}</x-nav-link>
                        <x-nav-link :href="route('secretary.history.approved')" :active="request()->routeIs('secretary.history.approved')">{{ __('ເອກະສານທີ່ອະນຸມັດຜ່ານ') }}</x-nav-link>
                        <x-nav-link :href="route('secretary.history.rejected')" :active="request()->routeIs('secretary.history.rejected')">{{ __('ເອກະສານທີ່ໃຫ້ປັບປຸງຄືນ') }}</x-nav-link>

                    @elseif($userRole === 'Finance_Preparer')
                        <x-nav-link :href="route('finance.preparer.dashboard')" :active="request()->routeIs('finance.preparer.*')">{{ __('ໜ້າຫຼັກຝ່າຍກວດສອບ') }}</x-nav-link>
                        <x-nav-link :href="route('finance.preparer.history.approved')" :active="request()->routeIs('finance.preparer.history.approved')">{{ __('ເອກະສານທີ່ໄດ້ກວດສອບຜ່ານ') }}
                        </x-nav-link>
                        <x-nav-link :href="route('finance.preparer.history.rejected')" :active="request()->routeIs('finance.preparer.history.rejected')">{{ __('ເອກະສານທີ່ໃຫ້ປັບປຸງຄືນ') }}
                        </x-nav-link>

                    @elseif($userRole === 'Accountant')
                        <x-nav-link :href="route('accountant.dashboard')" :active="request()->routeIs('accountant.*')"> {{ __('ໜ້າຫຼັກນັກບັນຊີ') }}</x-nav-link>
                        <x-nav-link :href="route('accountant.history.approved')" :active="request()->routeIs('accountant.history.approved')">{{ __('ເອກະສານທີ່ໄດ້ກວດສອບຜ່ານ') }}           </x-nav-link>
                        <x-nav-link :href="route('accountant.history.rejected')" :active="request()->routeIs('accountant.history.rejected')">{{ __('ເອກະສານທີ່ໃຫ້ປັບປຸງຄືນ') }}
                        </x-nav-link>
 
                    @elseif($userRole === 'Vice_Dean')
                        <x-nav-link :href="route('vicedean.dashboard')" :active="request()->routeIs('vicedean.*')">
                            {{ __('ໜ້າຫຼັກຮອງຄະນະບໍດີ') }}
                        </x-nav-link>  
                        <x-nav-link :href="route('vicedean.history.approved')" :active="request()->routeIs('vicedean.history.approved')">
                            {{ __('ເອກະສານທີ່ໄດ້ອະນຸມັດ/ກວດສອບຜ່ານ') }}
                        </x-nav-link>
                        <x-nav-link :href="route('vicedean.history.rejected')" :active="request()->routeIs('vicedean.history.rejected')">
                            {{ __('ເອກະສານທີ່ໃຫ້ປັບປຸງຄືນ/ບໍ່ອະນຸມັດ') }}
                        </x-nav-link> 

                    @elseif($userRole === 'Head_of_Finance')
                        <x-nav-link :href="route('headfinance.dashboard')" :active="request()->routeIs('headfinance.*')">{{ __('ໜ້າຫຼັກຫົວໜ້າພະແນກການເງິນ') }}
                        </x-nav-link>   
                        <x-nav-link :href="route('headfinance.history.approved')" :active="request()->routeIs('headfinance.history.approved')">
                            {{ __('ເອກະສານທີ່ໄດ້ກວດສອບ/ເຊັນຢັ້ງຢືນ') }}
                        </x-nav-link>
                        <x-nav-link :href="route('headfinance.history.rejected')" :active="request()->routeIs('headfinance.history.rejected')">
                            {{ __('ເອກະສານທີ່ໃຫ້ປັບປຸງຄືນ') }}
                        </x-nav-link>

                    @elseif($userRole === 'Dean')
                        <x-nav-link :href="route('dean.dashboard')" :active="request()->routeIs('dean.*')">
                            {{ __('ໜ້າຫຼັກຄະນະບໍດີ') }}
                        </x-nav-link>   
                        <x-nav-link :href="route('dean.history.approved')" :active="request()->routeIs('dean.history.approved')">
                            {{ __('ເອກະສານທີ່ໄດ້ອະນຸມັດ') }}
                        </x-nav-link>
                        <x-nav-link :href="route('dean.history.rejected')" :active="request()->routeIs('dean.history.rejected')">
                            {{ __('ເອກະສານທີ່ໃຫ້ປັບປຸງຄືນ') }}
                        </x-nav-link>

                    @elseif($userRole === 'Cashier')
                        <x-nav-link :href="route('cashier.dashboard')" :active="request()->routeIs('cashier.dashboard')">{{ __('ໜ້າຫຼັກຄັງເງິນສົດ') }}</x-nav-link>
                        <x-nav-link :href="route('cashier.history.approved')" :active="request()->routeIs('cashier.history.approved')">{{ __('ເອກະສານທີ່ຈ່າຍແລ້ວ') }}</x-nav-link>
        
                    @elseif($userRole === 'Procurement_Staff')
                        <x-nav-link :href="route('procurement.dashboard')" :active="request()->routeIs('procurement.dashboard')">{{ __('ໜ້າຫຼັກຝ່າຍຈັດຊື້') }}</x-nav-link>
                        <x-nav-link :href="route('procurement.history.approved')" :active="request()->routeIs('procurement.history.approved')">{{ __('ເອກະສານທີ່ອະນຸມັດຜ່ານ') }}</x-nav-link>
                        <!--<x-nav-link :href="route('procurement.history.rejected')" :active="request()->routeIs('procurement.history.rejected')">{{ __('ເອກະສານທີ່ໃຫ້ປັບປຸງຄືນ') }}</x-nav-link>-->
                    @endif

                    {{-- เมนูรายงาน (แสดงสำหรับ Admin และ Head_of_Finance) --}}
                    @if(in_array($userRole, ['System_Admin', 'Head_of_Finance']))
                        <x-nav-link :href="route('reports.documents.index')" :active="request()->routeIs('reports.documents.*')">{{ __('ລາຍງານ') }}</x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <!--
                <div class="ms-3 relative">
                    <a href="#" class="relative inline-flex items-center p-2 text-sm font-medium text-center text-gray-500 bg-white rounded-lg hover:text-gray-700 focus:outline-none">
                        <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path></svg>
                        <span class="sr-only">Notifications</span>
                        @if(auth()->user()->unreadNotifications->count() > 0)
                            <div class="absolute inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 border-2 border-white rounded-full -top-1 -end-1">
                                {{ auth()->user()->unreadNotifications->count() }}
                            </div>
                        @endif
                    </a>
                    {{-- ເຮົາຈະເຮັດ Dropdown ສະແດງການ Notification ໃນພາຍຫຼັງ --}}
                </div>-->
                
                {{-- ===== Notification Dropdown ===== --}}
                <div class="hidden sm:flex sm:items-center sm:ms-6">
                    <x-dropdown align="right" width="w-96"> {{-- ເຮັດໃຫ້ Dropdown ກວ້າງຂື້ນ --}}
                        <x-slot name="trigger">
                            <button class="relative inline-flex items-center p-2 text-sm font-medium text-center text-gray-500 bg-white rounded-lg hover:text-gray-700 focus:outline-none">
                                <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path></svg>
                                <span class="sr-only">Notifications</span>
                                @if(auth()->user()->unreadNotifications->count() > 0)
                                    <div class="absolute inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 border-2 border-white rounded-full -top-1 -end-1">
                                        {{ auth()->user()->unreadNotifications->count() }}
                                    </div>
                                @endif
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="block px-4 py-2 text-xs text-gray-400">
                                {{ __('ການແຈ້ງເຕືອນ') }}
                            </div>
            
                            {{-- ວົນ Loop ສະແດງຮາຍການແຈ້ງເຕືອນ 5 ລາຍການຫຼ້າສຸດ --}}
                            @forelse (auth()->user()->notifications->take(5) as $notification)
                                <a href="{{ route('notifications.read', $notification->id) }}" class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out {{ $notification->read_at ? '' : 'font-bold bg-blue-50' }}"> {{-- ຖ້າຍັງບໍ່ອ່ານ ໃຫ້ເປັນ ຕົວເຂັ້ມ ແລະ ມີພື້ນຫຼັງສີຟ້າ --}}
                    
                                    <div>{{ $notification->data['message'] }}</div>
                                    <div class="text-xs text-gray-500 mt-1"> 
                                        {{ $notification->created_at->diffForHumans() }} {{-- ສະແດງເວລາແລລ "5 minutes ago" --}}
                                    </div>
                                </a>
                            @empty
                                <div class="px-4 py-4 text-sm text-gray-500">
                                    ບໍ່ມີການແຈ້ງເຕືອນ
                                </div>
                            @endforelse

                            <div class="border-t border-gray-200"></div>

                            {{-- ເຮົາຈະສ້າງ Route ນີ້ໃນອະນາຄົດ --}}
                            <a href="{{ route('notifications.index') }}" class="block w-full px-4 py-2 text-center text-sm leading-5 text-blue-600 hover:bg-gray-100">
                                ເບິ່ງການແຈ້ງເຕືອນທັງໝົດ
                            </a>
                        </x-slot>
                    </x-dropdown>
                </div>

                {{-- ... (ສ່ວນຂອງ Settings Dropdown) ... --}}
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->displayName }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @php
                $userRole = auth()->user()->role->name;
            @endphp

            @if($userRole === 'System_Admin')
                <x-responsive-nav-link ...>{{ __('ຈັດການຜູ້ໃຊ້') }}</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.roles.index')" :active="request()->routeIs('admin.roles.*')">
                            {{ __('ຈັດການບົດບາດ') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('admin.departments.index')" :active="request()->routeIs('admin.departments.*')">
                    {{ __('ຈັດການພາກວິຊາ/ພະແນກ') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('admin.document-types.index')" :active="request()->routeIs('admin.document-types.*')">
                    {{ __('ຈັດການປະເພດເອກະສານ') }}
                </x-responsive-nav-link>
        
            @elseif($userRole === 'Staff')
                <x-responsive-nav-link :href="route('staff.documents.index')" :active="request()->routeIs('staff.documents.index')">{{ __('ຈັດການເອກະສານ') }}</x-responsive-nav-link>

                <x-responsive-nav-link :href="route('staff.history.approved')" :active="request()->routeIs('staff.history.approved')">
                    {{ __('ເອກະສານທີ່ໄດ້ສະເໜີ') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('staff.history.rejected')" :active="request()->routeIs('staff.history.rejected')">
                    {{ __('ເອກະສານທີ່ຕ້ອງໄດ້ປັບປຸງຄືນ/ຖືກປະຕິເສດ') }}
                </x-responsive-nav-link>
        
            @elseif($userRole === 'Dean_Secretary')
                <x-responsive-nav-link :href="route('secretary.dashboard')" :active="request()->routeIs('secretary.*')">
                    {{ __('ໜ້າຫຼັກເລຂາ') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('secretary.history.approved')" :active="request()->routeIs('secretary.history.approved')">
                    {{ __('ເອກະສານທີ່ໄດ້ກວດສອບຜ່ານ') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('secretary.history.rejected')" :active="request()->routeIs('secretary.history.rejected')">
                    {{ __('ເອກະສານທີ່ໄດ້ສະເໜີໃຫ້ປັບປຸງຄືນ') }}
                </x-responsive-nav-link>

            @elseif($userRole === 'Finance_Preparer')
                <x-responsive-nav-link :href="route('finance.preparer.dashboard')" :active="request()->routeIs('finance.preparer.*')">
                    {{ __('ໜ້າຫຼັກຝ່າຍກວດສອບເອກະສານ') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('finance.preparer.history.approved')" :active="request()->routeIs('finance.preparer.history.approved')">
                    {{ __('ເອກະສານທີ່ໄດ້ກວດສອບຜ່ານ') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('finance.preparer.history.rejected')" :active="request()->routeIs('finance.preparer.history.rejected')">
                    {{ __('ເອກະສານທີ່ໄດ້ສະເໜີໃຫ້ປັບປຸງຄືນ') }}
                </x-responsive-nav-link>

            @elseif($userRole === 'Accountant')
                <x-responsive-nav-link :href="route('accountant.dashboard')" :active="request()->routeIs('accountant.*')">
                    {{ __('ໜ້າຫຼັກນາຍບັນຊີ') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('accountant.history.approved')" :active="request()->routeIs('accountant.history.approved')">
                    {{ __('ເອກະສານທີ່ໄດ້ກວດສອບຜ່ານ') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('accountant.history.rejected')" :active="request()->routeIs('accountant.history.rejected')">
                    {{ __('ເອກະສານທີ່ໄດ້ສະເໜີໃຫ້ປັບປຸງຄືນ') }}
                </x-responsive-nav-link>

            @elseif($userRole === 'Vice_Dean')
                <x-responsive-nav-link :href="route('vicedean.dashboard')" :active="request()->routeIs('vicedean.*')">
                    {{ __('ໜ້າຫຼັກຮອງຄະນະບໍດີ') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('vicedean.history.approved')" :active="request()->routeIs('vicedean.history.approved')">
                    {{ __('ເອກະສານທີ່ໄດ້ອະນຸມັດ/ກວດສອບຜ່ານ') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('vicedean.history.rejected')" :active="request()->routeIs('vicedean.history.rejected')">
                    {{ __('ເອກະສານທີ່ບໍ່ໄດ້ອະນຸຍາດໃຫ້ປະຕິບັດ') }}
                </x-responsive-nav-link> 

            @elseif($userRole === 'Head_of_Finance')
                <x-responsive-nav-link :href="route('headfinance.dashboard')" :active="request()->routeIs('headfinance.*')">
                    {{ __('ໜ້າຫຼັກຫົວໜ້າພະແນກການເງິນ') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('headfinance.history.approved')" :active="request()->routeIs('headfinance.history.approved')">
                    {{ __('ເອກະສານທີ່ໄດ້ກວດສອບ/ເຊັນຢັ້ງຢືນ') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('headfinance.history.rejected')" :active="request()->routeIs('headfinance.history.rejected')">
                    {{ __('ເອກະສານທີ່ໄດ້ສະເໜີໃຫ້ປັບປຸງຄືນ') }}
                </x-responsive-nav-link>

            @elseif($userRole === 'Dean')
                <x-responsive-nav-link :href="route('dean.dashboard')" :active="request()->routeIs('dean.*')">
                    {{ __('ໜ້າຫຼັກຄະນະບໍດີ') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('dean.history.approved')" :active="request()->routeIs('dean.history.approved')">
                    {{ __('ເອກະສານທີ່ໄດ້ອະນຸມັດ') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('dean.history.rejected')" :active="request()->routeIs('dean.history.rejected')">
                    {{ __('ເອກະສານທີ່ບໍ່ໄດ້ອະນຸມັດ') }}
                </x-responsive-nav-link>

            @elseif($userRole === 'Procurement_Staff')
                <x-responsive-nav-link :href="route('procurement.dashboard')" :active="request()->routeIs('procurement.*')">
                    {{ __('ໜ້າຫຼັກການຈັດຊື້') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('procurement.history.approved')" :active="request()->routeIs('procurement.history.approved')">
                    {{ __('ເອກະສານທີ່ໄດ້ກວດສອບຜ່ານ') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('procurement.history.rejected')" :active="request()->routeIs('procurement.history.rejected')">
                    {{ __('ເອກະສານທີ່ໄດ້ສະເໜີໃຫ້ປັບປຸງຄືນ') }}
                </x-responsive-nav-link>  

            @elseif($userRole === 'Cashier')
                <x-responsive-nav-link :href="route('cashier.dashboard')" :active="request()->routeIs('cashier.*')">
                    {{ __('ລາຍການເອກະສານທີ່ລໍຖ້າຈ່າຍເງິນ') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('cashier.history.approved')" :active="request()->routeIs('cashier.history.approved')">
                    {{ __('ເອກະສານທີ່ໄດ້ຈ່າຍເງິນແລ້ວ') }}
                </x-responsive-nav-link>

            @else
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>

            @endif

            {{-- เมนูรายงาน --}}
            @if(in_array($userRole, ['System_Admin', 'Head_of_Finance']))
                <x-responsive-nav-link :href="route('reports.documents.index')" :active="request()->routeIs('reports.documents.*')">{{ __('ລາຍງານ') }}</x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->displayName }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
