@php
    $total = 0;
    $total_in_page = 0;
    $count = 0;
    $current_page = 0;
    $total_pages = 0;

    if (isset($pagination['current_page']) && isset($pagination['per_page'])) {
        $total_in_page = $pagination['current_page'] * $pagination['per_page'];

        if ($total_in_page >= $pagination['total']) {
            $total_in_page = $pagination['total'];
        }

        $count = ($total_in_page - $pagination['per_page']) + 1;

        if ($count <= 0) {
            $count = 1;
        }

        if ($total_in_page <= 0) {
            $count = 0;
        }

        $current_page = $pagination['current_page'];
    }

    if (isset($pagination['total'])) {
        $total = $pagination['total'];

        if ($total >= $pagination['total']) {
            $total = $pagination['total'];
        }
    }

    if (isset($pagination['total_pages'])) {
        $total_pages = $pagination['total_pages'];
    }
@endphp

<div class="flex">
    <div>{{ $total }} {{ Str::plural($label, $total) }}</div>
</div>

<div class="flex items-center">
    <div class="mr-3">
        {{ $count }}-{{ $total_in_page }} of {{ $total }}
        <i class="fa-solid fa-caret-down px-3"></i>
    </div>

    <div>
        <a href="{{ $current_page <= 1 ? 'javascript:;' : $url . '?page=' . ($current_page - 1) }}" class="px-3" style="color: #8c93ad"><i class="fa-solid fa-chevron-left"></i></a>

        <a href="{{ $current_page >= $total_pages ? 'javascript:;' : $url . '?page=' . ($current_page + 1) }}" class="px-3" style="color: #8c93ad"><i class="fa-solid fa-chevron-right"></i></a>
    </div>
</div>