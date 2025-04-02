@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'BakeHub')
<img src="https://ui-avatars.com/api/?name=Bake+Hub&background=f97316&color=fff&bold=true&size=128" class="logo" alt="BakeHub Logo">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
