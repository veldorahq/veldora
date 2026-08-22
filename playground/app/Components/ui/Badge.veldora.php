<span style="font-size: 0.7rem; font-weight: 600; padding: 2px 8px; border-radius: 4px; border: 1px solid #222222; background-color: #0c0c0c; color: #ededed; display: inline-flex; align-items: center; gap: 6px;">
    @if(($type ?? 'info') === 'success')
        <span style="width: 6px; height: 6px; border-radius: 50%; background-color: #10b981; display: inline-block;"></span>
    @else
        <span style="width: 6px; height: 6px; border-radius: 50%; background-color: #3b82f6; display: inline-block;"></span>
    @endif
    {{ $slot }}
</span>
