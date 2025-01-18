@extends('limonlabs/bigcommerce::layouts.app')

@section('content')
    @include('limonlabs/bigcommerce::layouts.page-title', ['title' => isset($title) ? $title : 'File Upload'])

    <div class="bg-white shadow-md p-5 mt-8">
        <div class="my-3 flash">
            @include('limonlabs/bigcommerce::layouts.flash')
        </div>

        <p class="mb-3">{{ isset($description) ? $description : 'Only accept csv file.' }} <a href="{{ isset($sampleUrl) ? $sampleUrl : '#' }}" class="text-[#4B71FC]">{{ isset($sampleText) ? $sampleText : 'Sample file' }}</a></p>

        <form method="post" class="upload" action="{{ isset($formAction) ? $formAction : '#' }}" enctype="multipart/form-data">
            @csrf

            <input type="file" class="hidden" id="file" name="file" accept=".csv">
            <label class="border border-dashed border-[#4b71fc] p-5 rounded flex justify-center items-center cursor-pointer mb-3" for="file" id="drop-area">
                <div>
                    @if (isset($icon))
                        <img src="{{ $icon }}" alt="icon" class="w-12 h-12 mx-auto mb-3">
                    @else
                        <svg width="99" height="127" viewBox="0 0 99 127" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin: 0 auto;">
                            <path d="M1.32486 49.9918C0.939876 49.3251 1.15891 48.4781 1.81408 48.1L46.548 22.2784C47.2032 21.9002 48.0464 22.1341 48.4314 22.8007L79.254 76.1755C79.639 76.8422 79.42 77.6892 78.7648 78.0674L34.0308 103.889C33.3757 104.267 32.5325 104.033 32.1475 103.367L1.32486 49.9918Z" fill="white" stroke="#92ABFA"></path>
                            <path d="M5.44595 51.0959C5.06097 50.4293 5.28 49.5823 5.93517 49.2041L45.4439 26.3986C46.0991 26.0204 46.9423 26.2543 47.3273 26.921L69.0996 64.6237C69.4846 65.2903 69.2656 66.1373 68.6104 66.5155L29.1017 89.321C28.4465 89.6991 27.6033 89.4653 27.2183 88.7986L5.44595 51.0959Z" fill="#F7F9FF" stroke="#92ABFA"></path>
                            <path d="M28.4144 74.088C28.4247 73.2406 29.1796 72.6119 30.0094 72.7597L38.4908 74.2703C39.6925 74.4843 40.8363 73.7166 41.0964 72.5213L45.1307 53.9814C45.2839 53.2773 45.9494 52.8182 46.6599 52.9264L63.8582 55.5452L69.1008 64.6237C69.4858 65.2903 69.2668 66.1373 68.6116 66.5155L30.325 88.6155C29.4012 89.1487 28.2389 88.4504 28.2521 87.3702L28.4144 74.088Z" fill="#DBE3FE" stroke="#92ABFA"></path>
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M23.0505 57.3616C24.2529 56.6675 24.6649 55.1303 23.9707 53.9282C23.2765 52.726 21.7389 52.3141 20.5365 53.0082C19.3341 53.7022 18.9221 55.2394 19.6164 56.4416C20.3106 57.6437 21.8481 58.0556 23.0505 57.3616Z" fill="#DBE3FE" stroke="#92ABFA"></path>
                            <path d="M43.2244 38.0735C43.2964 37.2505 44.0209 36.6416 44.8425 36.7134L95.9728 41.1858C96.7945 41.2577 97.4022 41.9831 97.3302 42.8061L91.9811 103.933C91.9091 104.756 91.1846 105.365 90.363 105.293L39.2327 100.821C38.411 100.749 37.8033 100.023 37.8753 99.2005L43.2244 38.0735Z" fill="white" stroke="#92ABFA"></path>
                            <path d="M45.9666 41.3413C46.0386 40.5183 46.7631 39.9094 47.5847 39.9813L92.7035 43.9278C93.5252 43.9997 94.1329 44.7251 94.0609 45.5481L90.2894 88.647C90.2174 89.47 89.4929 90.0789 88.6713 90.007L43.5525 86.0605C42.7308 85.9886 42.1231 85.2632 42.1951 84.4402L45.9666 41.3413Z" fill="#F7F9FF" stroke="#92ABFA"></path>
                            <path d="M51.4796 73.388C52.0179 72.639 53.098 72.5518 53.7487 73.2046L59.5567 79.0319C60.5005 79.9789 62.0228 80.0103 63.0054 79.1029L76.6985 66.4584C77.2774 65.9238 78.171 65.931 78.7409 66.4747L91.1912 78.3532L90.2904 88.6466C90.2184 89.4696 89.4939 90.0785 88.6723 90.0067L45.0578 86.1917C43.9039 86.0908 43.2994 84.7696 43.9762 83.8279L51.4796 73.388Z" fill="#DBE3FE" stroke="#92ABFA"></path>
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M56.7653 56.4309C58.1483 56.5518 59.3676 55.5289 59.4886 54.1461C59.6096 52.7632 58.5865 51.5441 57.2035 51.4232C55.8204 51.3022 54.6011 52.3251 54.4801 53.708C54.3591 55.0908 55.3822 56.3099 56.7653 56.4309Z" fill="#DBE3FE" stroke="#92ABFA"></path>
                        </svg>
                    @endif
                    <div class="font-bold name">{{ isset($uploadText) ? $uploadText : 'Drag & Drop file here to upload.' }}</div>
                </div>
            </label>

            <hr class="mb-3" />

            <div class="mb-3">
                <div class="{{ isset($backPosition) && $backPosition == 'left' ? 'flex items-center justify-between' : 'text-right relative' }}">
                    <a href="/{{ $storeHash }}/new" class="click text-[#4b71fc] mr-2"><i class="fa-solid fa-chevron-left text-sm"></i> Back</a>
                    <button type="submit" class="click bg-[#4b71fc] text-white px-3 py-1 ml-2">Import</button>
                </div>
            </div>
        </form>
    </div>
@endsection
