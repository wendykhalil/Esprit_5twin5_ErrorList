@extends('layouts.backend')
@section('content')<div class="mx-auto max-w-2xl rounded-xl border border-slate-200 bg-white p-6"><h2 class="text-2xl font-bold text-slate-900">Ajouter un relevé</h2><form class="mt-6" method="POST" action="{{ route('admin.impact.store') }}">@include('backend.impact._form')</form></div>@endsection
