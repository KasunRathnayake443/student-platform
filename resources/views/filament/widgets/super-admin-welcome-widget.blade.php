<x-filament-widgets::widget class="fi-super-admin-welcome-widget">
    <x-filament::section>
        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
            
            <!-- Header Row: Greeting & System Status Badges -->
            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.875rem;">
                    <div style="display: flex; align-items: center; justify-content: center; width: 3rem; height: 3rem; border-radius: 0.75rem; background-color: rgba(245, 158, 11, 0.15); color: #d97706; flex-shrink: 0;">
                        <x-filament::icon
                            icon="heroicon-o-academic-cap"
                            style="width: 1.75rem; height: 1.75rem;"
                        />
                    </div>
                    <div>
                        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem;">
                            <h2 style="font-size: 1.25rem; font-weight: 700; line-height: 1.5; margin: 0;">
                                Welcome, {{ $userName }}!
                            </h2>
                            <x-filament::badge color="success" size="sm" icon="heroicon-m-check-circle">
                                System Operational
                            </x-filament::badge>
                        </div>
                        <p style="font-size: 0.875rem; opacity: 0.7; margin: 0.15rem 0 0 0;">
                            {{ $today }} &bull; Multi-Tenant Super Admin Command Center
                        </p>
                    </div>
                </div>

                <!-- Platform Quick Counts -->
                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem;">
                    <x-filament::badge color="warning" size="md" icon="heroicon-o-building-office-2">
                        {{ $data['active_schools'] }} / {{ $data['total_schools'] }} Active Schools
                    </x-filament::badge>
                    <x-filament::badge color="success" size="md" icon="heroicon-o-academic-cap">
                        {{ number_format($data['total_students']) }} Students
                    </x-filament::badge>
                    <x-filament::badge color="info" size="md" icon="heroicon-o-user-group">
                        {{ number_format($data['total_teachers']) }} Teachers
                    </x-filament::badge>
                </div>
            </div>

            <!-- Divider -->
            <div style="height: 1px; background-color: currentColor; opacity: 0.1;"></div>

            <!-- Quick Actions Row -->
            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.375rem; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.8;">
                    <x-filament::icon
                        icon="heroicon-m-bolt"
                        style="width: 1rem; height: 1rem; color: #d97706;"
                    />
                    <span>Quick Actions:</span>
                </div>

                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem;">
                    <x-filament::button
                        tag="a"
                        :href="url('/admin/schools/create')"
                        color="primary"
                        size="xs"
                        icon="heroicon-m-plus"
                    >
                        Add School
                    </x-filament::button>

                    <x-filament::button
                        tag="a"
                        :href="url('/admin/school-admins/create')"
                        color="gray"
                        size="xs"
                        icon="heroicon-m-user-plus"
                    >
                        Add Admin
                    </x-filament::button>

                    <x-filament::button
                        tag="a"
                        :href="url('/admin/teachers/create')"
                        color="gray"
                        size="xs"
                        icon="heroicon-m-user-group"
                    >
                        Add Teacher
                    </x-filament::button>

                    <x-filament::button
                        tag="a"
                        :href="url('/admin/students/create')"
                        color="gray"
                        size="xs"
                        icon="heroicon-m-academic-cap"
                    >
                        Add Student
                    </x-filament::button>

                 
                </div>
            </div>

        </div>
    </x-filament::section>
</x-filament-widgets::widget>
