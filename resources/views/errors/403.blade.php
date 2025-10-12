@extends('errors::minimal')

@section('title', __('Forbidden'))
@section('code', '403')
@section('message', __($exception->getMessage() ?: 'Forbidden'))

{{-- ເພີ່ມຂໍ້ຄວາມພາສາລາວ --}}
@section('message')
    <div class="text-lg mb-4">
        {{ __('ຂໍໂທດຫຼາຍໆ, ທ່ານບໍ່ໄດ້ຮັບສິດໃນການເຂົ້າເຖິງໜ້ານີ້ໄດ້.') }}
    </div>
    <a href="{{ url()->previous() }}" class="text-blue-600 hover:underline">
        &larr; ກັບໄປໜ້າກ່ອນໜ້ານີ້
    </a>
@endsection