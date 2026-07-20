@if($header)
    <th scope="col" class="px-6 py-3 font-medium" {{ $attributes }}>{{ $slot }}</th>
@elseif($rowHeader)
    <th scope="row" class="px-6 py-4 font-medium text-heading whitespace-nowrap" {{ $attributes }}>{{ $slot }}</th>
@else
    <td class="px-6 py-4" {{ $attributes }}>{{ $slot }}</td>
@endif
