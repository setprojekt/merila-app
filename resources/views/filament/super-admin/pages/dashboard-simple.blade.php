<x-filament-panels::page>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-top: 1rem;">
        @foreach($modules as $module)
            <a href="{{ $module['url'] }}" style="text-decoration: none; color: inherit;">
                <div style="background: white; border-radius: 0.75rem; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); transition: all 0.2s; border: 1px solid #e5e7eb;">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">{{ $module['icon'] }}</div>
                    <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem; color: #111827;">
                        {{ $module['name'] }}
                    </h3>
                    @if($module['count'] !== null)
                        <div style="font-size: 2rem; font-weight: bold; color: {{ $module['color'] }}; margin-top: 0.75rem;">
                            {{ $module['count'] }}
                        </div>
                    @endif
                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                        <span style="color: {{ $module['color'] }}; font-size: 0.875rem; font-weight: 500;">
                            Odpri →
                        </span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
    
    <div style="margin-top: 2rem; background: #FEF2F2; border: 1px solid #FECACA; border-radius: 0.75rem; padding: 1rem;">
        <div style="display: flex; align-items: start;">
            <span style="font-size: 1.25rem; margin-right: 0.5rem;">⚠️</span>
            <div>
                <h4 style="font-weight: 600; color: #991B1B; margin-bottom: 0.25rem;">Super Admin Opozorilo</h4>
                <p style="color: #B91C1C; font-size: 0.875rem;">Imate popoln dostop do sistema. Spremembe tukaj vplivajo na vse uporabnike in module.</p>
            </div>
        </div>
    </div>
</x-filament-panels::page>
